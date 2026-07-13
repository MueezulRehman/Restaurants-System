<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\BelongsToRestaurant;

class StockAdjustment extends Model
{
    use BelongsToRestaurant;

    protected $fillable = [
        'restaurant_id', 'product_variant_id', 'menu_item_id', 'user_id', 'quantity_before',
        'quantity_after', 'change_quantity', 'reason', 'reference_id', 'notes',
    ];

    public function restaurant()
    {
        return $this->belongsTo(Restaurant::class);
    }

    public function menuItem()
    {
        return $this->belongsTo(MenuItem::class, 'menu_item_id');
    }

    public function variant()
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get a human-readable reason label.
     */
    public function getReasonLabel(): string
    {
        return match ($this->reason) {
            'sale' => 'Sale',
            'return' => 'Customer Return',
            'recount' => 'Physical Recount',
            'damage' => 'Damaged',
            'expiry' => 'Expired',
            'purchase' => 'Purchase/Received',
            'adjustment' => 'Manual Adjustment',
            'correction' => 'Inventory Correction',
            default => 'Other',
        };
    }
}
