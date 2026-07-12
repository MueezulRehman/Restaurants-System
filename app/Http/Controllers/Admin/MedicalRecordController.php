<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MedicalRecord;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MedicalRecordController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        abort_unless($user && $user->hasModuleAccess('medical-records'), 403);

        $restaurantId = $user->effectiveRestaurantId();
        $records = MedicalRecord::where('restaurant_id', $restaurantId)
            ->latest()
            ->get();

        $salesSummary = Order::where('restaurant_id', $restaurantId)
            ->whereDate('created_at', today())
            ->selectRaw('COUNT(*) as orders_today, COALESCE(SUM(total), 0) as sales_today')
            ->first();

        $recentOrders = Order::where('restaurant_id', $restaurantId)
            ->latest('created_at')
            ->limit(5)
            ->get();

        return view('admin.medical-records.index', compact('records', 'salesSummary', 'recentOrders'));
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        abort_unless($user && $user->hasModuleAccess('medical-records'), 403);

        $data = $request->validate([
            'patient_name' => 'required|string|max:255',
            'medicine_name' => 'required|string|max:255',
            'notes' => 'nullable|string|max:1000',
        ]);

        MedicalRecord::create([
            'restaurant_id' => $user->effectiveRestaurantId(),
            'patient_name' => $data['patient_name'],
            'medicine_name' => $data['medicine_name'],
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
