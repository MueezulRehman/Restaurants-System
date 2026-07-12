<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MenuItem;
use App\Models\Medicine;
use App\Models\MedicineBatch;
use App\Models\ProductVariant;
use App\Models\StockAdjustment;
use App\Models\InventoryAuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StockController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $restaurantId = $user->effectiveRestaurantId();
        $restaurant = $user->effectiveRestaurant();
        $posMode = $restaurant?->getPosMode() ?? 'retail';

        $items = [];
        $medicines = [];

        // Load items based on POS mode
        if ($posMode === 'medical') {
            $medicines = Medicine::with(['batches' => function ($q) use ($restaurantId) {
                $q->where('restaurant_id', $restaurantId)->orderBy('expiry_date');
            }])
                ->where(function ($q) use ($restaurantId) {
                    $q->whereNull('restaurant_id')->orWhere('restaurant_id', $restaurantId);
                })
                ->orderBy('name')
                ->get();
        } else {
            $items = MenuItem::where('restaurant_id', $restaurantId)
                ->with('variants')
                ->orderBy('name')
                ->get();
        }

        $adjustments = StockAdjustment::where('restaurant_id', $restaurantId)
            ->with(['variant', 'user'])
            ->latest()
            ->take(20)
            ->get();

        return view('admin.stock.index', compact('items', 'medicines', 'posMode', 'adjustments'));
    }

    public function adjust(Request $request)
    {
        $validated = $request->validate([
            'item_type' => 'required|in:menu_item,variant,medicine_batch',
            'item_id' => 'required|string',
            'quantity' => 'required|integer',
            'reason' => 'required|in:sale,return,recount,damage,expiry,purchase,adjustment,correction,other',
            'notes' => 'nullable|string|max:1000',
        ]);

        $user = Auth::user();
        $restaurantId = $user->effectiveRestaurantId();
        $delta = (int) $validated['quantity'];
        $itemId = $validated['item_id'];
        $itemType = $validated['item_type'];

        if ($itemType === 'medicine_batch') {
            // Handle medicine batch stock adjustment
            $batchId = (int) str_replace('medicine_batch_', '', (string) $itemId);
            $batch = MedicineBatch::where('restaurant_id', $restaurantId)->findOrFail($batchId);
            $before = (int) $batch->quantity;
            $after = max(0, $before + $delta);
            $actualDelta = $after - $before;
            
            $batch->update(['quantity' => $after]);

            StockAdjustment::create([
                'restaurant_id' => $restaurantId,
                'product_variant_id' => null,
                'user_id' => Auth::id(),
                'quantity_before' => $before,
                'quantity_after' => $after,
                'change_quantity' => $actualDelta,
                'reason' => $validated['reason'],
                'reference_id' => 'manual-adjustment',
                'notes' => ($validated['notes'] ?? '') . " [Batch: {$batch->batch_number}]",
            ]);

            // Log to inventory audit trail
            InventoryAuditLog::log(
                $restaurantId,
                'medicine_batch',
                $batchId,
                'adjusted',
                ['quantity' => $before],
                ['quantity' => $after],
                Auth::id(),
                $validated['notes'] ?? $validated['reason']
            );
        } elseif ($itemType === 'menu_item' && str_contains((string) $itemId, 'menu_item_')) {
            $item = MenuItem::where('restaurant_id', $restaurantId)->findOrFail((int) str_replace('menu_item_', '', (string) $itemId));
            $before = (int) $item->stock_quantity;
            $after = max(0, $before + $delta);
            $actualDelta = $after - $before;
            $item->update(['stock_quantity' => $after]);

            StockAdjustment::create([
                'restaurant_id' => $restaurantId,
                'product_variant_id' => null,
                'user_id' => Auth::id(),
                'quantity_before' => $before,
                'quantity_after' => $after,
                'change_quantity' => $actualDelta,
                'reason' => $validated['reason'],
                'reference_id' => 'manual-adjustment',
                'notes' => $validated['notes'],
            ]);

            // Log to inventory audit trail
            InventoryAuditLog::log(
                $restaurantId,
                'menu_item',
                $item->id,
                'adjusted',
                ['stock_quantity' => $before],
                ['stock_quantity' => $after],
                Auth::id(),
                $validated['notes'] ?? $validated['reason']
            );
        } elseif ($itemType === 'variant' && str_contains((string) $itemId, 'variant_')) {
            $variant = ProductVariant::where('restaurant_id', $restaurantId)->findOrFail((int) str_replace('variant_', '', (string) $itemId));
            $before = (int) $variant->quantity_available;
            $after = max(0, $before + $delta);
            $actualDelta = $after - $before;
            $variant->update(['quantity_available' => $after]);

            StockAdjustment::create([
                'restaurant_id' => $restaurantId,
                'product_variant_id' => $variant->id,
                'user_id' => Auth::id(),
                'quantity_before' => $before,
                'quantity_after' => $after,
                'change_quantity' => $actualDelta,
                'reason' => $validated['reason'],
                'reference_id' => 'manual-adjustment',
                'notes' => $validated['notes'],
            ]);

            // Log to inventory audit trail
            InventoryAuditLog::log(
                $restaurantId,
                'variant',
                $variant->id,
                'adjusted',
                ['quantity_available' => $before],
                ['quantity_available' => $after],
                Auth::id(),
                $validated['notes'] ?? $validated['reason']
            );
        } elseif ($itemType === 'menu_item') {
            $item = MenuItem::where('restaurant_id', $restaurantId)->findOrFail($itemId);
            $before = (int) $item->stock_quantity;
            $after = max(0, $before + $delta);
            $actualDelta = $after - $before;
            $item->update(['stock_quantity' => $after]);

            StockAdjustment::create([
                'restaurant_id' => $restaurantId,
                'product_variant_id' => null,
                'user_id' => Auth::id(),
                'quantity_before' => $before,
                'quantity_after' => $after,
                'change_quantity' => $actualDelta,
                'reason' => $validated['reason'],
                'reference_id' => 'manual-adjustment',
                'notes' => $validated['notes'],
            ]);

            // Log to inventory audit trail
            InventoryAuditLog::log(
                $restaurantId,
                'menu_item',
                $item->id,
                'adjusted',
                ['stock_quantity' => $before],
                ['stock_quantity' => $after],
                Auth::id(),
                $validated['notes'] ?? $validated['reason']
            );
        } else {
            $variant = ProductVariant::where('restaurant_id', $restaurantId)->findOrFail($validated['item_id']);
            $before = (int) $variant->quantity_available;
            $after = max(0, $before + $delta);
            $actualDelta = $after - $before;
            $variant->update(['quantity_available' => $after]);

            StockAdjustment::create([
                'restaurant_id' => $restaurantId,
                'product_variant_id' => $variant->id,
                'user_id' => Auth::id(),
                'quantity_before' => $before,
                'quantity_after' => $after,
                'change_quantity' => $actualDelta,
                'reason' => $validated['reason'],
                'reference_id' => 'manual-adjustment',
                'notes' => $validated['notes'],
            ]);

            // Log to inventory audit trail
            InventoryAuditLog::log(
                $restaurantId,
                'variant',
                $variant->id,
                'adjusted',
                ['quantity_available' => $before],
                ['quantity_available' => $after],
                Auth::id(),
                $validated['notes'] ?? $validated['reason']
            );
        }

        return redirect()->back()->with('success', 'Stock adjusted successfully');
    }
}
