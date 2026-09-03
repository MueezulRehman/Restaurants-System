<?php

namespace App\Models;

use App\Models\Concerns\BelongsToRestaurant;
use Illuminate\Database\Eloquent\Model;

/**
 * Item-level sale: percent or fixed Rs off a menu item.
 *
 * @author Mueez Ul Rehman
 */
class ItemPromotion extends Model
{
    use BelongsToRestaurant;

    protected $fillable = [
        'restaurant_id',
        'menu_item_id',
        'label',
        'type',
        'value',
        'starts_at',
        'ends_at',
        'is_active',
    ];

    protected $casts = [
        'value' => 'decimal:2',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    public function menuItem()
    {
        return $this->belongsTo(MenuItem::class);
    }

    public function restaurant()
    {
        return $this->belongsTo(Restaurant::class);
    }

    public function scopeCurrentlyActive($query)
    {
        $now = now();

        return $query->where('is_active', true)
            ->where(function ($q) use ($now) {
                $q->whereNull('starts_at')->orWhere('starts_at', '<=', $now);
            })
            ->where(function ($q) use ($now) {
                $q->whereNull('ends_at')->orWhere('ends_at', '>=', $now);
            });
    }

    public function isLive(): bool
    {
        if (! $this->is_active) {
            return false;
        }
        if ($this->starts_at && $this->starts_at->isFuture()) {
            return false;
        }
        if ($this->ends_at && $this->ends_at->isPast()) {
            return false;
        }

        return true;
    }

    /**
     * Apply this promotion to a base price. Returns sale price (never negative).
     */
    public function applyTo(float $basePrice): float
    {
        if ($this->type === 'percent') {
            $off = $basePrice * ((float) $this->value / 100);

            return max(0, round($basePrice - $off, 2));
        }

        // fixed Rs off
        return max(0, round($basePrice - (float) $this->value, 2));
    }
}
