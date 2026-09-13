<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MedicalRecord;
use App\Models\Order;
use App\Models\Appointment;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class MedicalRecordController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        abort_unless($user && $user->hasModuleAccess('medical-records'), 403);

        $restaurantId = $user->effectiveRestaurantId();
        $records = MedicalRecord::with(['customer', 'appointment'])->where('restaurant_id', $restaurantId)
            ->latest()
            ->get();

        $customers = Customer::where('restaurant_id', $restaurantId)->orderBy('name')->get();
        $appointments = Appointment::where('restaurant_id', $restaurantId)->whereIn('status', ['scheduled', 'confirmed'])->orderBy('starts_at')->get();

        $salesSummary = Order::where('restaurant_id', $restaurantId)
            ->whereDate('created_at', today())
            ->selectRaw('COUNT(*) as orders_today, COALESCE(SUM(total), 0) as sales_today')
            ->first();

        $recentOrders = Order::where('restaurant_id', $restaurantId)
            ->latest('created_at')
            ->limit(5)
            ->get();

        return view('manager.medical-records.index', compact('records', 'salesSummary', 'recentOrders', 'customers', 'appointments'));
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        abort_unless($user && $user->hasModuleAccess('medical-records'), 403);

        $data = $request->validate([
            'customer_id' => ['nullable', 'integer', Rule::exists('customers', 'id')->where(fn($query) => $query->where('restaurant_id', $user->effectiveRestaurantId()))],
            'appointment_id' => ['nullable', 'integer', Rule::exists('appointments', 'id')->where(fn($query) => $query->where('restaurant_id', $user->effectiveRestaurantId()))],
            'patient_name' => 'required|string|max:255',
            'medicine_name' => 'required|string|max:255',
            'doctor_name' => 'nullable|string|max:255',
            'diagnosis' => 'nullable|string|max:2000',
            'follow_up_at' => 'nullable|date',
            'notes' => 'nullable|string|max:1000',
        ]);

        MedicalRecord::create([
            'restaurant_id' => $user->effectiveRestaurantId(),
            'customer_id' => $data['customer_id'] ?? null,
            'appointment_id' => $data['appointment_id'] ?? null,
            'patient_name' => $data['patient_name'],
            'medicine_name' => $data['medicine_name'],
            'doctor_name' => $data['doctor_name'] ?? null,
            'diagnosis' => $data['diagnosis'] ?? null,
            'follow_up_at' => $data['follow_up_at'] ?? null,
            'notes' => $data['notes'] ?? null,
        ]);

        return redirect()->route('manager.medical-records.index')->with('success', 'Medical record saved.');
    }

    public function destroy($id)
    {
        $user = Auth::user();
        abort_unless($user && $user->hasModuleAccess('medical-records'), 403);

        return redirect()->route('manager.medical-records.index')->with('success', 'Medical record removed.');
    }
}
