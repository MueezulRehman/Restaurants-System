<?php

namespace App\Services;

use App\Models\ProductVariant;
use App\Models\StockAdjustment;
use App\Models\User;

class StockService
{
    /**
     * Adjust stock for a product variant and log the change.
     */
    public static function adjust(
        ProductVariant $variant,
        int $quantityChange,
        string $reason = 'adjustment',
        $referenceId = null,
        $notes = null,
        User $user = null
    ): StockAdjustment {
        $user = $user ?? auth()->user();
        $quantityBefore = $variant->quantity_available;
        $quantityAfter = max(0, $quantityBefore + $quantityChange); // Prevent negative stock

        // Update variant stock
        $variant->update(['quantity_available' => $quantityAfter]);

        // Log the adjustment
        $adjustment = StockAdjustment::create([
            'restaurant_id' => $variant->restaurant_id,
            'product_variant_id' => $variant->id,
            'user_id' => $user->id,
            'quantity_before' => $quantityBefore,
            'quantity_after' => $quantityAfter,
            'change_quantity' => $quantityChange,
            'reason' => $reason,
            'reference_id' => $referenceId,
            'notes' => $notes,
        ]);

        // Check if stock fell below threshold and send alert
        if ($quantityAfter < 10 && $quantityBefore >= 10) {
            NotificationService::sendLowStockAlert($variant->restaurant, $variant);
        }

        return $adjustment;
    }

    /**
     * Bulk adjust stock for multiple variants (e.g., from a stock recount).
     */
    public static function bulkAdjust(
        $restaurantId,
        array $adjustments, // [['variant_id' => 1, 'quantity_change' => -5], ...]
        string $reason = 'adjustment',
        $notes = null,
        User $user = null
    ): array {
        $user = $user ?? auth()->user();
        $results = [];

        foreach ($adjustments as $adj) {
            $variant = ProductVariant::where('restaurant_id', $restaurantId)
                ->where('id', $adj['variant_id'])
                ->first();

            if ($variant) {
                $results[] = self::adjust(
                    $variant,
                    $adj['quantity_change'] ?? 0,
                    $reason,
                    $adj['reference_id'] ?? null,
                    $notes,
                    $user
                );
            }
        }

        return $results;
    }

    /**
     * Decrement stock on sale (called during order checkout).
     */
    public static function decrementOnSale(ProductVariant $variant, int $quantity): StockAdjustment
    {
        return self::adjust(
            $variant,
            -$quantity,
            'sale',
            null,
            'Decremented on order checkout'
        );
    }
}
