<?php

namespace App\Http\Controllers\Admin;

use App\Models\Prescription;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Patient;
use App\Models\Doctor;
use App\Models\Visit;
use App\Models\CustomerAllergy;
use App\Models\PatientAllergy;
use App\Models\Medicine;
use App\Models\MedicineInteraction;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

class PrescriptionController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $restaurant = $user->restaurant;

        $prescriptions = Prescription::where('restaurant_id', $restaurant->id)
            ->with('customer', 'order')
            ->orderBy('prescription_date', 'desc')
            ->paginate(15);

        return view('manager.medical.prescriptions.index', [
            'prescriptions' => $prescriptions,
            'restaurant' => $restaurant,
        ]);
    }

    public function create()
    {
        $restaurant = auth()->user()->restaurant;
        
        // Get customers for this restaurant - handle both with and without restaurant_id
        try {
            $customers = Customer::where('restaurant_id', $restaurant->id)
                ->orderBy('name')
                ->get();
        } catch (\Exception $e) {
            // If restaurant_id column doesn't exist yet, get all customers
            $customers = Customer::orderBy('name')->get();
        }

        return view('manager.medical.prescriptions.form', [
            'prescription' => new Prescription(),
            'restaurant' => $restaurant,
            'customers' => $customers,
            'patients' => Patient::orderBy('name')->get(),
            'doctors' => Doctor::where('status', 'active')->orderBy('name')->get(),
            'visits' => Visit::with('patient')->latest('checked_in_at')->limit(100)->get(),
            'medicines' => Medicine::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $user = auth()->user();
        $restaurant = $user->restaurant;

        $validated = $request->validate([
            'prescription_number' => ['required', 'string', Rule::unique('prescriptions', 'prescription_number')->where('restaurant_id', $restaurant->id)],
            'customer_id' => ['nullable', Rule::exists('customers', 'id')->where('restaurant_id', $restaurant->id)],
            'patient_id' => ['nullable', 'exists:patients,id'],
            'doctor_id' => ['nullable', 'exists:doctors,id'],
            'visit_id' => ['nullable', 'exists:visits,id'],
            'patient_name' => 'required|string|max:255',
            'doctor_name' => 'nullable|string|max:255',
            'prescription_date' => 'required|date',
            'valid_until' => 'nullable|date|after:prescription_date',
            'medicines' => 'nullable|json',
            'image_path' => 'nullable|image|max:2048',
            'status' => 'required|in:pending,verified,used,expired,rejected',
            'verification_notes' => 'nullable|string',
        ]);

        $validated['restaurant_id'] = $restaurant->id;
        foreach (['patient_id', 'doctor_id', 'visit_id'] as $key) {
            if (! empty($validated[$key])) {
                $model = ['patient_id' => Patient::class, 'doctor_id' => Doctor::class, 'visit_id' => Visit::class][$key];
                abort_unless($model::where('restaurant_id', $restaurant->id)->whereKey($validated[$key])->exists(), 422);
            }
        }
        if (! empty($validated['visit_id'])) {
            $visit = Visit::where('restaurant_id', $restaurant->id)->findOrFail($validated['visit_id']);
            $validated['patient_id'] ??= $visit->patient_id;
            $validated['doctor_id'] ??= $visit->doctor_id;
        }
        $medicineIds = collect(($validated['medicines'] ?? null) ? json_decode($validated['medicines'], true) : [])
            ->map(fn ($medicine) => is_array($medicine) ? ($medicine['medicine_id'] ?? $medicine['id'] ?? null) : $medicine)
            ->filter()->map(fn ($id) => (int) $id)->values();
        abort_unless ($medicineIds->every(fn ($id) => Medicine::where('restaurant_id', $restaurant->id)->whereKey($id)->exists()), 422);
        $allergies = collect();
        if (! empty($validated['patient_id'])) {
            $allergies = PatientAllergy::query()
                ->where('restaurant_id', $restaurant->id)
                ->where('patient_id', $validated['patient_id'])
                ->where('is_active', true)
                ->get();
        }
        if (! empty($validated['customer_id'])) {
            $allergies = $allergies->concat(
                CustomerAllergy::query()
                    ->where('customer_id', $validated['customer_id'])
                    ->where('is_active', true)
                    ->get()
            );
        }
        foreach ($allergies as $allergy) {
            if ($medicineIds->intersect($allergy->trigger_medicines ?: [])->isNotEmpty()) {
                return back()->withInput()->withErrors(['medicines' => "Prescription conflicts with {$allergy->allergy_name} allergy."]);
            }
        }
        foreach ($medicineIds as $index => $medicineId) {
            foreach ($medicineIds->slice($index + 1) as $otherMedicineId) {
                if (MedicineInteraction::hasInteraction($medicineId, $otherMedicineId)) {
                    return back()->withInput()->withErrors(['medicines' => 'Prescription contains interacting medicines.']);
                }
            }
        }

        if ($request->hasFile('image_path')) {
            $validated['image_path'] = $request->file('image_path')->store('prescriptions', 'public');
        }

        // Verify customer belongs to this restaurant if selected
        if (! empty($validated['customer_id'])) {
            $customer = Customer::where('restaurant_id', $restaurant->id)->findOrFail($validated['customer_id']);
        }

        Prescription::create($validated);

        return redirect()->route('manager.prescriptions.index')->with('success', 'Prescription created successfully');
    }

    public function show(int $prescription)
    {
        $prescription = Prescription::with(['patient', 'doctor', 'visit'])->findOrFail($prescription);

        return view('manager.medical.prescriptions.show', [
            'prescription' => $prescription,
        ]);
    }

    public function print(int $prescription)
    {
        $prescription = Prescription::findOrFail($prescription);
        $prescription->load(['patient', 'doctor', 'visit']);
        return view('manager.medical.prescriptions.print', compact('prescription'));
    }

    public function dispense(int $prescription)
    {
        $user = auth()->user();
        abort_unless(
            $user && (in_array($user->role, ['admin', 'manager'], true) || $user->staff_type === 'pharmacist'),
            403,
            'Only pharmacists or restaurant managers can dispense prescriptions.'
        );

        $prescription = Prescription::findOrFail($prescription);

        DB::transaction(function () use ($prescription): void {
            $prescription = Prescription::query()->lockForUpdate()->findOrFail($prescription->getKey());
            abort_unless($prescription->status === 'verified', 422, 'Only verified prescriptions can be dispensed.');

            $items = collect($prescription->medicines ?: [])
                ->map(fn ($item) => is_array($item) ? ['id' => (int) ($item['medicine_id'] ?? $item['id'] ?? 0), 'quantity' => (float) ($item['quantity'] ?? 1)] : ['id' => (int) $item, 'quantity' => 1])
                ->filter(fn ($item) => $item['id'] > 0 && $item['quantity'] > 0)
                ->groupBy('id')
                ->map(fn ($group) => $group->sum('quantity'));

            abort_unless($items->isNotEmpty(), 422, 'This prescription has no medicines to dispense.');

            foreach ($items as $medicineId => $quantity) {
                $remaining = (float) $quantity;
                $batches = Medicine::findOrFail($medicineId)->batches()
                    ->where('quantity', '>', 0)
                    ->where(function ($query): void {
                        $query->whereNull('expiry_date')->orWhereDate('expiry_date', '>=', today());
                    })
                    ->orderByRaw('expiry_date IS NULL')
                    ->orderBy('expiry_date')
                    ->lockForUpdate()
                    ->get();

                foreach ($batches as $batch) {
                    $deduct = min($remaining, (float) $batch->quantity);
                    $batch->decrement('quantity', $deduct);
                    $remaining -= $deduct;
                    if ($remaining <= 0) {
                        break;
                    }
                }

                abort_unless($remaining <= 0, 422, 'Insufficient stock for the prescribed medicines.');
            }

            $prescription->update([
                'status' => 'used',
                'dispensed_by' => auth()->id(),
                'dispensed_at' => now(),
            ]);
        });

        return back()->with('success', 'Prescription dispensed and stock deducted successfully.');
    }
}
