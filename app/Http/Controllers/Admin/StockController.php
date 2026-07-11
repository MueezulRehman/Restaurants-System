<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MenuItem;
use App\Models\ProductVariant;
use App\Models\StockAdjustment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StockController extends Controller
{
    public function index()
    {
        $restaurantId = Auth::user()->restaurant_id;

        $items = MenuItem::where('restaurant_id', $restaurantId)
            ->with('variants')
            ->orderBy('name')
            ->get();

        $adjustments = StockAdjustment::where('restaurant_id', $restaurantId)
            ->with(['variant', 'user'])
            ->latest()
            ->take(15)
            ->get();

        return view('admin.stock.index', compact('items', 'adjustments'));
    }

    public function adjust(Request $request)
    {
        $validated = $request->validate([
            'item_type' => 'required|in:menu_item,variant',
            'item_id' => 'required|string',
            'quantity' => 'required|integer',
            'reason' => 'required|in:sale,return,recount,damage,expiry,purchase,adjustment,correction,other',
            'notes' => 'nullable|string|max:1000',
        ]);

        $restaurantId = Auth::user()->restaurant_id;
        $delta = (int) $validated['quantity'];

        $itemId = $validated['item_id'];
        $itemType = $validated['item_type'];

        if ($itemType === 'menu_item' && str_contains((string) $itemId, 'menu_item_')) {
            $item = MenuItem::where('restaurant_id', $restaurantId)->findOrFail((int) str_replace('menu_item_', '', (string) $itemId));
            $before = (int) $item->stock_quantity;
            $after = $before + $delta;
            $item->update(['stock_quantity' => $after]);

            StockAdjustment::create([
                'restaurant_id' => $restaurantId,
                'product_variant_id' => null,
                'user_id' => Auth::id(),
                'quantity_before' => $before,
                'quantity_after' => $after,
                'change_quantity' => $delta,
                'reason' => $validated['reason'],
                'reference_id' => 'manual-adjustment',
                'notes' => $validated['notes'],
            ]);
        } elseif ($itemType === 'variant' && str_contains((string) $itemId, 'variant_')) {
            $variant = ProductVariant::where('restaurant_id', $restaurantId)->findOrFail((int) str_replace('variant_', '', (string) $itemId));
            $before = (int) $variant->quantity_available;
            $after = $before + $delta;
            $variant->update(['quantity_available' => $after]);

            StockAdjustment::create([
                'restaurant_id' => $restaurantId,
                'product_variant_id' => $variant->id,
                'user_id' => Auth::id(),
                'quantity_before' => $before,
                'quantity_after' => $after,
                'change_quantity' => $delta,
                'reason' => $validated['reason'],
                'reference_id' => 'manual-adjustment',
                'notes' => $validated['notes'],
            ]);
        } elseif ($itemType === 'menu_item') {
            $item = MenuItem::where('restaurant_id', $restaurantId)->findOrFail($itemId);
            $before = (int) $item->stock_quantity;
            $after = $before + $delta;
            $item->update(['stock_quantity' => $after]);

            StockAdjustment::create([
                'restaurant_id' => $restaurantId,
                'product_variant_id' => null,
                'user_id' => Auth::id(),
                'quantity_before' => $before,
                'quantity_after' => $after,
                'change_quantity' => $delta,
                'reason' => $validated['reason'],
                'reference_id' => 'manual-adjustment',
                'notes' => $validated['notes'],
            ]);
        } else {
            $variant = ProductVariant::where('restaurant_id', $restaurantId)->findOrFail($validated['item_id']);
            $before = (int) $variant->quantity_available;
            $after = $before + $delta;
            $variant->update(['quantity_available' => $after]);

            StockAdjustment::create([
                'restaurant_id' => $restaurantId,
                'product_variant_id' => $variant->id,
                'user_id' => Auth::id(),
                'quantity_before' => $before,
                'quantity_after' => $after,
                'change_quantity' => $delta,
                'reason' => $validated['reason'],
                'reference_id' => 'manual-adjustment',
                'notes' => $validated['notes'],
            ]);
        }

        return back()->with('success', 'Stock updated successfully.');
    }
}
