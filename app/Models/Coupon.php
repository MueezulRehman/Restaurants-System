<?php

namespace App\Models;

use App\Models\Concerns\BelongsToRestaurant;
use Illuminate\Database\Eloquent\Model;

class Coupon extends Model
{
    use BelongsToRestaurant;

    protected $fillable = ['restaurant_id', 'code', 'type', 'value', 'minimum_order', 'max_discount', 'starts_at', 'ends_at', 'usage_limit', 'usage_count', 'is_active'];
    protected $casts = ['value' => 'decimal:2', 'minimum_order' => 'decimal:2', 'max_discount' => 'decimal:2', 'starts_at' => 'datetime', 'ends_at' => 'datetime', 'usage_limit' => 'integer', 'usage_count' => 'integer', 'is_active' => 'boolean'];

    public function isUsableFor(float $subtotal): bool
    {
        return $this->is_active
            && (! $this->starts_at || $this->starts_at->isPast())
            && (! $this->ends_at || $this->ends_at->isFuture())
            && (! $this->usage_limit || $this->usage_count < $this->usage_limit)
            && $subtotal >= (float) $this->minimum_order;
    }

    public function discountFor(float $subtotal): float
    {
        $discount = $this->type === 'percent'
            ? $subtotal * ((float) $this->value / 100)
            : (float) $this->value;

        if ($this->max_discount !== null) {
            $discount = min($discount, (float) $this->max_discount);
        }

        return round(min($subtotal, max(0, $discount)), 2);
    }
}
