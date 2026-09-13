<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\MenuItem;
use App\Models\ProductDevice;
use App\Models\ProductVariant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class ProductDeviceController extends Controller
{
    public function index(Request $request)
    {
        $restaurantId = Auth::user()->effectiveRestaurantId();
        $devices = ProductDevice::with(['menuItem', 'variant', 'customer'])
            ->where('restaurant_id', $restaurantId)
            ->when($request->filled('q'), fn($query) => $query->where('identifier_value', 'like', '%' . $request->string('q') . '%'))
            ->latest()
            ->paginate(20)
            ->withQueryString();
        $items = MenuItem::with('variants')->where('restaurant_id', $restaurantId)->orderBy('name')->get();
        $customers = Customer::where('restaurant_id', $restaurantId)->orderBy('name')->get();

        return view('manager.product-devices.index', compact('devices', 'items', 'customers'));
    }

    public function store(Request $request)
    {
        $restaurantId = Auth::user()->effectiveRestaurantId();
        $data = $request->validate([
            'menu_item_id' => ['nullable', 'integer', Rule::exists('menu_items', 'id')->where(fn($query) => $query->where('restaurant_id', $restaurantId))],
            'product_variant_id' => ['nullable', 'integer', Rule::exists('product_variants', 'id')->where(fn($query) => $query->where('restaurant_id', $restaurantId))],
            'customer_id' => ['nullable', 'integer', Rule::exists('customers', 'id')->where(fn($query) => $query->where('restaurant_id', $restaurantId))],
            'identifier_type' => ['required', Rule::in(['imei', 'serial'])],
            'identifier_value' => ['required', 'string', 'max:150', Rule::unique('product_devices', 'identifier_value')->where(fn($query) => $query->where('restaurant_id', $restaurantId))],
            'purchase_date' => 'nullable|date',
            'warranty_until' => 'nullable|date|after_or_equal:purchase_date',
            'status' => ['required', Rule::in(['in_stock', 'sold', 'repair', 'returned'])],
        ]);
        ProductDevice::create(array_merge($data, ['restaurant_id' => $restaurantId]));

        return back()->with('success', 'Device registered.');
    }

    public function update(Request $request, ProductDevice $productDevice)
    {
        abort_unless((int) $productDevice->restaurant_id === (int) Auth::user()->effectiveRestaurantId(), 404);
        $data = $request->validate(['status' => ['required', Rule::in(['in_stock', 'sold', 'repair', 'returned'])], 'customer_id' => 'nullable|integer']);
        $productDevice->update($data);

        return back()->with('success', 'Device status updated.');
    }
}
