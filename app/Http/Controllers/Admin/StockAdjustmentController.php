<?php

namespace App\Http\Controllers\Admin;

use App\Models\StockAdjustment;
use App\Models\MenuItem;
use App\Models\ProductVariant;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;

class StockAdjustmentController extends Controller
{
    /**
     * Show stock adjustment history for manager
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $restaurant = $user->effectiveRestaurant();

        $adjustments = StockAdjustment::where('restaurant_id', $restaurant->id)
            ->with('menuItem', 'user')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('admin.stock.adjustment-history', compact('adjustments'));
    }

    /**
     * Record new stock adjustment (in, out, or correction)
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        $restaurant = $user->effectiveRestaurant();

        $validated = $request->validate([
            'product_variant_id' => 'required|exists:product_variants,id',
            'adjustment_type' => 'required|in:in,out,correction',
            'quantity' => 'required|integer|min:1',
            'reason' => 'nullable|string|max:500',
        ]);

        $variant = ProductVariant::findOrFail($validated['product_variant_id']);

        if ($variant->restaurant_id !== $restaurant->id) {
            abort(403);
        }

        $quantityBefore = $variant->quantity_available ?? 0;

        // Calculate new quantity
        $quantityAfter = match ($validated['adjustment_type']) {
            'in' => $quantityBefore + $validated['quantity'],
            'out' => max(0, $quantityBefore - $validated['quantity']),
            'correction' => $validated['quantity'],
        };

        // Record adjustment
        $adjustment = StockAdjustment::create([
            'restaurant_id' => $restaurant->id,
            'product_variant_id' => $variant->id,
            'adjustment_type' => $validated['adjustment_type'],
            'quantity_before' => $quantityBefore,
            'quantity_after' => $quantityAfter,
            'change_quantity' => $quantityAfter - $quantityBefore,
            'reason' => $validated['reason'],
            'user_id' => $user->id,
        ]);

        // Update variant stock
        $variant->update(['quantity_available' => $quantityAfter]);

        // Check if stock is now below threshold on parent menu item
        if ($variant->menuItem && $variant->menuItem->low_stock_threshold && $quantityAfter <= $variant->menuItem->low_stock_threshold) {
            NotificationService::notifyLowStock($variant->menuItem);
        }

        return redirect()->back()->with('success', 'Stock adjustment recorded.');
    }
}
