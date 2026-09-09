<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\MenuItem;
use App\Models\ServiceCase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ServiceCaseController extends Controller
{
    public function index(Request $request)
    {
        $restaurantId = Auth::user()->effectiveRestaurantId();
        $cases = ServiceCase::with(['customer', 'menuItem'])->where('restaurant_id', $restaurantId)->when($request->filled('type'), fn($q) => $q->where('case_type', $request->type))->latest()->paginate(20)->withQueryString();
        $customers = Customer::where('restaurant_id', $restaurantId)->orderBy('name')->get();
        $items = MenuItem::where('restaurant_id', $restaurantId)->orderBy('name')->get();
        return view('admin.service-cases.index', compact('cases', 'customers', 'items'));
    }

    public function store(Request $request)
    {
        $restaurantId = Auth::user()->effectiveRestaurantId();
        $data = $request->validate(['case_type' => 'required|in:warranty,repair', 'customer_id' => 'nullable|integer', 'menu_item_id' => 'nullable|integer', 'serial_number' => 'nullable|string|max:100', 'title' => 'required|string|max:255', 'description' => 'nullable|string|max:2000', 'estimated_cost' => 'nullable|numeric|min:0', 'due_at' => 'nullable|date']);
        ServiceCase::create(array_merge($data, ['restaurant_id' => $restaurantId, 'received_at' => now()->toDateString(), 'created_by' => Auth::id()]));
        return back()->with('success', ucfirst($data['case_type']) . ' case created.');
    }

    public function update(Request $request, ServiceCase $serviceCase)
    {
        abort_unless($serviceCase->restaurant_id === Auth::user()->effectiveRestaurantId(), 404);
        $data = $request->validate(['status' => 'required|in:received,diagnosing,approved,in_progress,ready,completed,cancelled', 'resolution' => 'nullable|string|max:2000']);
        $serviceCase->update(array_merge($data, ['completed_at' => in_array($data['status'], ['completed', 'cancelled'], true) ? now()->toDateString() : null]));
        return back()->with('success', 'Service case updated.');
    }
}
