<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ControlledMedicineLog;
use App\Models\Customer;
use App\Models\Medicine;
use App\Models\Prescription;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ControlledMedicineController extends Controller
{
    private function restaurantId(): int
    {
        $id = auth()->user()?->effectiveRestaurantId();
        abort_unless($id, 403);
        return (int) $id;
    }

    public function index()
    {
        $id = $this->restaurantId();
        $logs = ControlledMedicineLog::with(['medicine', 'customer', 'dispenser'])->where('restaurant_id', $id)->latest('dispensed_at')->paginate(20);
        $medicines = Medicine::where('is_controlled', true)->orderBy('name')->get();
        $customers = Customer::where('restaurant_id', $id)->orderBy('name')->get();
        $prescriptions = Prescription::latest()->limit(100)->get();
        return view('manager.medical.controlled-medicines.index', compact('logs', 'medicines', 'customers', 'prescriptions'));
    }

    public function store(Request $request)
    {
        $id = $this->restaurantId();
        $data = $request->validate(['medicine_id' => ['required', 'integer', Rule::exists('medicines', 'id')->where(fn($q) => $q->where('is_controlled', true))], 'customer_id' => ['nullable', 'integer', Rule::exists('customers', 'id')->where(fn($q) => $q->where('restaurant_id', $id))], 'prescription_id' => 'nullable|integer|exists:prescriptions,id', 'quantity' => 'required|numeric|min:0.001', 'witness_name' => 'required|string|max:150', 'reason' => 'required|string|max:1000']);
        $medicine = Medicine::findOrFail($data['medicine_id']);
        if ($medicine->requires_prescription && empty($data['prescription_id'])) {
            return back()->withInput()->withErrors(['prescription_id' => 'A prescription is required for this medicine.']);
        }
        ControlledMedicineLog::create([...$data, 'restaurant_id' => $id, 'dispensed_by' => auth()->id(), 'dispensed_at' => now()]);
        return back()->with('success', 'Controlled medicine dispensing recorded in the audit log.');
    }
}
