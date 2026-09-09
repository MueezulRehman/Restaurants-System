<?php

namespace App\Http\Controllers\Admin;

use App\Models\StockAdjustment;
use App\Models\User;
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
            ->with(['menuItem', 'variant.menuItem', 'medicineBatch.medicine'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        $central = config('tenancy.central_connection', env('DB_CONNECTION', 'mysql'));
        $actors = User::on($central)
            ->whereIn('id', $adjustments->getCollection()->pluck('user_id')->filter()->unique())
            ->pluck('name', 'id');

        return view('admin.stock.adjustment-history', compact('adjustments', 'actors'));
    }

    public function edit(string $adjustment)
    {
        $adjustment = $this->findForCurrentRestaurant($adjustment);

        return view('admin.stock.adjustment-edit', compact('adjustment'));
    }

    public function update(Request $request, string $adjustment)
    {
        $adjustment = $this->findForCurrentRestaurant($adjustment);

        $validated = $request->validate([
            'reason' => 'required|in:sale,return,recount,damage,expiry,purchase,adjustment,correction,other',
            'notes' => 'nullable|string|max:1000',
        ]);

        $adjustment->update($validated);

        return redirect()->route('manager.stock.adjustments.index')
            ->with('success', 'Stock adjustment details updated.');
    }

    protected function findForCurrentRestaurant(string $adjustmentId): StockAdjustment
    {
        return StockAdjustment::query()
            ->whereKey($adjustmentId)
            ->where('restaurant_id', Auth::user()->effectiveRestaurantId())
            ->firstOrFail();
    }

    /**
     * Record new stock adjustment (in, out, or correction).
     *
     * New adjustments are handled by StockController; this legacy endpoint
     * remains available for existing links but is not shown in the history UI.
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
