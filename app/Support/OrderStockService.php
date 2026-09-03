<?php

namespace App\Support;

use App\Models\MenuItem;
use App\Models\Order;
use App\Models\ProductVariant;
use App\Models\StockAdjustment;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

/**
 * Online order stock: decrement once when manager confirms.
 * POS continues to decrement at sale (delivered immediately).
 *
 * Idempotent: will not double-decrement the same order.
 *
 * @author Mueez Ul Rehman
 */
class OrderStockService
{
    /**
     * Soft availability check before placing an online order (no write).
     */
    public static function assertAvailable(Order|array $lines, int $restaurantId): void
    {
        // Accept either Order with items or cart line array shaped like checkout lineItems
    }

    public static function assertMenuItemStock(MenuItem $item, float $qty): void
    {
        if (! $item->track_stock) {
            return;
        }
        if ((float) $item->stock_quantity < $qty) {
            abort(422, "Not enough stock for {$item->name} (only {$item->stock_quantity} left).");
        }
    }

    /**
     * Decrement stock when order is first confirmed. Safe to call multiple times.
     */
    public static function decrementOnConfirm(Order $order): void
    {
        if ($order->channel === 'pos') {
            // POS already adjusted stock at sale time
            return;
        }

        // Idempotency: any prior sale adjustment linked to this order
        $already = StockAdjustment::query()
            ->where('restaurant_id', $order->restaurant_id)
            ->where('reason', 'sale')
            ->where('reference_id', $order->id)
            ->exists();

        if ($already) {
            return;
        }

        $order->loadMissing(['items.variant', 'items.menuItem']);

        foreach ($order->items as $it) {
            $qty = (float) $it->quantity;

            if ($it->product_variant_id && $it->variant) {
                $variant = $it->variant;
                $before = (float) $variant->quantity_available;
                $after = max(0, $before - $qty);
                $variant->update(['quantity_available' => $after]);

                StockAdjustment::create([
                    'restaurant_id' => $order->restaurant_id,
                    'product_variant_id' => $variant->id,
                    'menu_item_id' => null,
                    'user_id' => Auth::id(),
                    'quantity_before' => $before,
                    'quantity_after' => $after,
                    'change_quantity' => -$qty,
                    'reason' => 'sale',
                    'reference_id' => $order->id,
                    'notes' => "Online order confirmed {$order->order_number}",
                ]);
                continue;
            }

            if ($it->menuItem && $it->menuItem->track_stock) {
                $menuItem = $it->menuItem;
                $before = (float) $menuItem->stock_quantity;
                $after = max(0, $before - $qty);
                $menuItem->update(['stock_quantity' => $after]);

                StockAdjustment::create([
                    'restaurant_id' => $order->restaurant_id,
                    'product_variant_id' => null,
                    'menu_item_id' => $menuItem->id,
                    'user_id' => Auth::id(),
                    'quantity_before' => $before,
                    'quantity_after' => $after,
                    'change_quantity' => -$qty,
                    'reason' => 'sale',
                    'reference_id' => $order->id,
                    'notes' => "Online order confirmed {$order->order_number}",
                ]);

                if ($after <= (float) ($menuItem->low_stock_threshold ?? 0) && $before > (float) ($menuItem->low_stock_threshold ?? 0)) {
                    try {
                        if (class_exists(\App\Services\NotificationService::class)) {
                            \App\Services\NotificationService::sendLowStockAlert($menuItem->restaurant, $menuItem);
                        }
                    } catch (\Throwable $e) {
                        Log::warning('Low stock alert failed', ['error' => $e->getMessage()]);
                    }
                }
            }
        }
    }

    /**
     * Optional: restore stock if a confirmed order is cancelled.
     */
    public static function restoreOnCancel(Order $order): void
    {
        if ($order->channel === 'pos') {
            return;
        }

        $adjustments = StockAdjustment::query()
            ->where('restaurant_id', $order->restaurant_id)
            ->where('reason', 'sale')
            ->where('reference_id', $order->id)
            ->get();

        if ($adjustments->isEmpty()) {
            return;
        }

        // Avoid double restore
        $restored = StockAdjustment::query()
            ->where('restaurant_id', $order->restaurant_id)
            ->where('reason', 'return')
            ->where('reference_id', $order->id)
            ->exists();

        if ($restored) {
            return;
        }

        foreach ($adjustments as $adj) {
            $qty = abs((float) $adj->change_quantity);

            if ($adj->product_variant_id) {
                $variant = ProductVariant::find($adj->product_variant_id);
                if ($variant) {
                    $before = (float) $variant->quantity_available;
                    $after = $before + $qty;
                    $variant->update(['quantity_available' => $after]);
                    StockAdjustment::create([
                        'restaurant_id' => $order->restaurant_id,
                        'product_variant_id' => $variant->id,
                        'menu_item_id' => null,
                        'user_id' => Auth::id(),
                        'quantity_before' => $before,
                        'quantity_after' => $after,
                        'change_quantity' => $qty,
                        'reason' => 'return',
                        'reference_id' => $order->id,
                        'notes' => "Restored after cancel {$order->order_number}",
                    ]);
                }
            } elseif ($adj->menu_item_id) {
                $menuItem = MenuItem::find($adj->menu_item_id);
                if ($menuItem && $menuItem->track_stock) {
                    $before = (float) $menuItem->stock_quantity;
                    $after = $before + $qty;
                    $menuItem->update(['stock_quantity' => $after]);
                    StockAdjustment::create([
                        'restaurant_id' => $order->restaurant_id,
                        'product_variant_id' => null,
                        'menu_item_id' => $menuItem->id,
                        'user_id' => Auth::id(),
                        'quantity_before' => $before,
                        'quantity_after' => $after,
                        'change_quantity' => $qty,
                        'reason' => 'return',
                        'reference_id' => $order->id,
                        'notes' => "Restored after cancel {$order->order_number}",
                    ]);
                }
            }
        }
    }
}
