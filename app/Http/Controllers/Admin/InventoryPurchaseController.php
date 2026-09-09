<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\InventoryPurchase;
use App\Models\InventoryPurchaseItem;
use App\Models\MenuItem;
use App\Models\ProductVariant;
use App\Models\StockAdjustment;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class InventoryPurchaseController extends Controller
{
    public function index()
    {
        $restaurantId = Auth::user()->effectiveRestaurantId();
        $purchases = InventoryPurchase::with(['supplier', 'items.menuItem', 'items.variant'])->where('restaurant_id', $restaurantId)->latest()->paginate(20);
        return view('admin.inventory-purchases.index', compact('purchases'));
    }

    public function expiryTracking()
    {
        $restaurantId = Auth::user()->effectiveRestaurantId();
        $items = InventoryPurchaseItem::with(['purchase', 'menuItem', 'variant'])
            ->whereHas('purchase', fn($query) => $query->where('restaurant_id', $restaurantId))
            ->whereNotNull('expiry_date')
            ->orderBy('expiry_date')
            ->get();

        $today = now()->startOfDay();
        $cutoff = $today->copy()->addDays(30);
        $withinNinety = $today->copy()->addDays(90);
        $buckets = [
            'expired' => $items->filter(fn($item) => $item->expiry_date->lt($today)),
            'within_30_days' => $items->filter(fn($item) => $item->expiry_date->betweenIncluded($today, $cutoff)),
            'within_90_days' => $items->filter(fn($item) => $item->expiry_date->gt($cutoff) && $item->expiry_date->lte($withinNinety)),
            'good' => $items->filter(fn($item) => $item->expiry_date->gt($withinNinety)),
        ];

        return view('admin.inventory-purchases.expiry-tracking', compact('buckets', 'items'));
    }

    public function create()
    {
        $restaurantId = Auth::user()->effectiveRestaurantId();
        $items = MenuItem::with('variants')->where('restaurant_id', $restaurantId)->orderBy('name')->get();
        $suppliers = Supplier::where('restaurant_id', $restaurantId)->where('is_active', true)->orderBy('name')->get();
        return view('admin.inventory-purchases.create', compact('items', 'suppliers'));
    }

    public function store(Request $request)
    {
        $restaurantId = Auth::user()->effectiveRestaurantId();
        $data = $request->validate([
            'supplier_id' => ['nullable', 'integer', Rule::exists('suppliers', 'id')->where(fn($query) => $query->where('restaurant_id', $restaurantId))],
            'supplier_name' => 'nullable|string|max:255',
            'invoice_no' => 'nullable|string|max:100',
            'purchase_date' => 'required|date',
            'menu_item_id' => ['required', 'integer', Rule::exists('menu_items', 'id')->where(fn($query) => $query->where('restaurant_id', $restaurantId))],
            'product_variant_id' => 'nullable|integer',
            'quantity' => 'required|numeric|min:0.001',
            'purchase_price' => 'required|numeric|min:0',
            'selling_price' => 'nullable|numeric|min:0',
            'expiry_date' => 'nullable|date',
            'notes' => 'nullable|string|max:1000',
        ]);
        DB::transaction(function () use ($data, $restaurantId): void {
            $item = MenuItem::where('restaurant_id', $restaurantId)->findOrFail($data['menu_item_id']);
            $variant = null;
            if (! empty($data['product_variant_id'])) {
                $variant = ProductVariant::where('restaurant_id', $restaurantId)->where('menu_item_id', $item->id)->findOrFail($data['product_variant_id']);
            }
            $quantity = (float) $data['quantity'];
            $total = round($quantity * (float) $data['purchase_price'], 2);
            $purchase = InventoryPurchase::create(['restaurant_id' => $restaurantId, 'supplier_id' => $data['supplier_id'] ?? null, 'supplier_name' => $data['supplier_name'] ?? null, 'invoice_no' => $data['invoice_no'] ?? null, 'purchase_date' => $data['purchase_date'], 'total' => $total, 'created_by' => Auth::id(), 'notes' => $data['notes'] ?? null]);
            InventoryPurchaseItem::create(['inventory_purchase_id' => $purchase->id, 'menu_item_id' => $variant ? null : $item->id, 'product_variant_id' => $variant?->id, 'quantity' => $quantity, 'purchase_price' => $data['purchase_price'], 'selling_price' => $data['selling_price'] ?? null, 'line_total' => $total, 'expiry_date' => $data['expiry_date'] ?? null]);
            if ($variant) {
                $before = (float) $variant->quantity_available;
                $variant->increment('quantity_available', $quantity);
                $after = $before + $quantity;
                $menuItemId = null;
                $variantId = $variant->id;
            } else {
                $before = (float) $item->stock_quantity;
                $item->update(['track_stock' => true, 'stock_quantity' => $before + $quantity]);
                $after = $before + $quantity;
                $menuItemId = $item->id;
                $variantId = null;
            }
            StockAdjustment::create(['restaurant_id' => $restaurantId, 'product_variant_id' => $variantId, 'menu_item_id' => $menuItemId, 'user_id' => Auth::id(), 'quantity_before' => $before, 'quantity_after' => $after, 'change_quantity' => $quantity, 'reason' => 'purchase', 'reference_id' => $purchase->id, 'notes' => 'Inventory purchase received']);
        });
        return redirect()->route('manager.purchasing.index')->with('success', 'Purchase received and stock updated.');
    }
}
