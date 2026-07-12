<?php

namespace App\Http\Controllers\Admin;

use App\Models\Prescription;
use App\Models\Customer;
use App\Models\Order;
use Illuminate\Http\Request;
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

        return view('admin.medical.prescriptions.index', [
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

        return view('admin.medical.prescriptions.form', [
            'prescription' => new Prescription(),
            'restaurant' => $restaurant,
            'customers' => $customers,
        ]);
    }

    public function store(Request $request)
    {
        $user = auth()->user();
        $restaurant = $user->restaurant;

        $validated = $request->validate([
            'prescription_number' => 'required|string|unique:prescriptions,prescription_number',
            'customer_id' => 'nullable|exists:customers,id',
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

        if ($request->hasFile('image_path')) {
            $validated['image_path'] = $request->file('image_path')->store('prescriptions', 'public');
        }

        // Verify customer belongs to this restaurant if selected
        if ($validated['customer_id']) {
            $customer = Customer::find($validated['customer_id']);
            if ($customer && $customer->restaurant_id && $customer->restaurant_id !== $restaurant->id) {
                return redirect()->back()->withErrors(['customer_id' => 'Invalid customer selection']);
            }
            // Set restaurant_id on customer if not set
            if ($customer && !$customer->restaurant_id) {
                $customer->update(['restaurant_id' => $restaurant->id]);
            }
        }

        Prescription::create($validated);

        return redirect()->route('manager.prescriptions.index')->with('success', 'Prescription created successfully');
    }

    public function show(Prescription $prescription)
    {
        $this->authorize('view', $prescription);

        return view('admin.medical.prescriptions.show', [
            'prescription' => $prescription,
        ]);
    }
}
