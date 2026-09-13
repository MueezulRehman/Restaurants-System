<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\MenuItem;
use App\Models\ServiceCase;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class ServiceCaseController extends Controller
{
    public function index(Request $request)
    {
        $restaurantId = Auth::user()->effectiveRestaurantId();
        $cases = ServiceCase::with(['customer', 'menuItem', 'technician'])->where('restaurant_id', $restaurantId)->when($request->filled('type'), fn($q) => $q->where('case_type', $request->type))->latest()->paginate(20)->withQueryString();
        $customers = Customer::where('restaurant_id', $restaurantId)->orderBy('name')->get();
        $items = MenuItem::where('restaurant_id', $restaurantId)->orderBy('name')->get();
        $technicians = User::where('restaurant_id', $restaurantId)->whereIn('role', ['admin', 'manager', 'staff'])->where('is_active', true)->orderBy('name')->get();
        return view('manager.service-cases.index', compact('cases', 'customers', 'items', 'technicians'));
    }

    public function store(Request $request)
    {
        $restaurantId = Auth::user()->effectiveRestaurantId();
        $data = $request->validate(['case_type' => 'required|in:warranty,repair', 'customer_id' => ['nullable', 'integer', Rule::exists('customers', 'id')->where(fn($q) => $q->where('restaurant_id', $restaurantId))], 'menu_item_id' => ['nullable', 'integer', Rule::exists('menu_items', 'id')->where(fn($q) => $q->where('restaurant_id', $restaurantId))], 'assigned_to' => ['nullable', 'integer', Rule::exists('users', 'id')->where(fn($q) => $q->where('restaurant_id', $restaurantId))], 'serial_number' => 'nullable|string|max:100', 'title' => 'required|string|max:255', 'description' => 'nullable|string|max:2000', 'estimated_cost' => 'nullable|numeric|min:0', 'due_at' => 'nullable|date']);
        ServiceCase::create(array_merge($data, ['restaurant_id' => $restaurantId, 'received_at' => now()->toDateString(), 'created_by' => Auth::id()]));
        return back()->with('success', ucfirst($data['case_type']) . ' case created.');
    }

    public function update(Request $request, ServiceCase $serviceCase)
    {
        abort_unless($serviceCase->restaurant_id === Auth::user()->effectiveRestaurantId(), 404);
        $data = $request->validate(['status' => 'required|in:received,diagnosing,approved,in_progress,ready,completed,cancelled', 'resolution' => 'nullable|string|max:2000', 'parts_used' => 'nullable|string|max:2000', 'final_cost' => 'nullable|numeric|min:0', 'assigned_to' => ['nullable', 'integer', Rule::exists('users', 'id')->where(fn($q) => $q->where('restaurant_id', $serviceCase->restaurant_id))]]);
        $serviceCase->update(array_merge($data, ['completed_at' => in_array($data['status'], ['completed', 'cancelled'], true) ? now()->toDateString() : null]));
        return back()->with('success', 'Service case updated.');
    }

    public function notifyCollection(ServiceCase $serviceCase)
    {
        $restaurantId = Auth::user()->effectiveRestaurantId();
        abort_unless($serviceCase->restaurant_id === $restaurantId && $serviceCase->customer, 404);

        $customer = $serviceCase->customer;
        $channels = array_values(array_filter([
            $customer->email ? 'email' : null,
            $customer->phone ? 'whatsapp' : null,
        ]));
        if ($channels === []) {
            return back()->with('error', 'This customer has no email or phone for notification.');
        }

        NotificationService::send(
            $restaurantId,
            'service_case_ready',
            'Repair ready for collection',
            "Your {$serviceCase->title} is ready for collection. Please contact us for pickup details.",
            $channels,
            null,
            $customer
        );
        $serviceCase->update(['collection_notified_at' => now(), 'status' => 'ready']);

        return back()->with('success', 'Customer notified that the repair is ready for collection.');
    }
}
