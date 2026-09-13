<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\ServicePackage;
use App\Models\ServicePackagePurchase;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ServicePackageController extends Controller
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
        $packages = ServicePackage::where('restaurant_id', $id)->latest()->get();
        $purchases = ServicePackagePurchase::with(['package', 'customer'])->where('restaurant_id', $id)->latest()->paginate(20);
        $customers = Customer::where('restaurant_id', $id)->orderBy('name')->get();
        return view('manager.service-packages.index', compact('packages', 'purchases', 'customers'));
    }

    public function storePackage(Request $request)
    {
        $data = $request->validate(['name' => 'required|string|max:150', 'description' => 'nullable|string|max:1000', 'included_visits' => 'required|integer|min:1|max:1000', 'validity_days' => 'required|integer|min:1|max:3650', 'price' => 'required|numeric|min:0']);
        ServicePackage::create([...$data, 'restaurant_id' => $this->restaurantId(), 'is_active' => true]);
        return back()->with('success', 'Service package created.');
    }

    public function sellPackage(Request $request)
    {
        $id = $this->restaurantId();
        $data = $request->validate([
            'service_package_id' => ['required', 'integer', Rule::exists('service_packages', 'id')->where(fn($q) => $q->where('restaurant_id', $id)->where('is_active', true))],
            'customer_id' => ['required', 'integer', Rule::exists('customers', 'id')->where(fn($q) => $q->where('restaurant_id', $id))],
            'starts_at' => 'required|date',
            'amount_paid' => 'nullable|numeric|min:0',
        ]);
        $package = ServicePackage::where('restaurant_id', $id)->findOrFail($data['service_package_id']);
        $start = now()->parse($data['starts_at'])->startOfDay();
        ServicePackagePurchase::create(['restaurant_id' => $id, 'service_package_id' => $package->id, 'customer_id' => $data['customer_id'], 'starts_at' => $start, 'ends_at' => $start->copy()->addDays($package->validity_days - 1), 'remaining_visits' => $package->included_visits, 'amount_paid' => $data['amount_paid'] ?? $package->price, 'status' => 'active']);
        return back()->with('success', 'Service package sold to customer.');
    }

    public function consumeVisit(ServicePackagePurchase $purchase)
    {
        abort_unless($purchase->restaurant_id === $this->restaurantId(), 404);
        abort_unless($purchase->status === 'active' && $purchase->remaining_visits > 0 && ! $purchase->ends_at->isPast(), 422, 'This package has no usable visits remaining.');
        $purchase->decrement('remaining_visits');
        if ($purchase->fresh()->remaining_visits < 1) $purchase->update(['status' => 'completed']);
        return back()->with('success', 'Package visit consumed.');
    }
}
