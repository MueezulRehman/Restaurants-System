<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\BelongsToRestaurant;

class ProductVariant extends Model
{
    use BelongsToRestaurant;

    protected $fillable = [
        'restaurant_id', 'menu_item_id', 'sku', 'variant_name', 
        'price_override', 'quantity_available', 'is_available', 'sort_order',
    ];

    protected $casts = [
        'price_override' => 'decimal:2',
        'is_available' => 'boolean',
    ];

    public function menuItem()
    {
        return $this->belongsTo(MenuItem::class);
    }

    public function restaurant()
    {
        return $this->belongsTo(Restaurant::class);
    }

    public function attributeValues()
    {
        return $this->hasMany(VariantAttributeValue::class);
    }

    /**
     * Get the effective price for this variant.
     * If price_override is set, use it; otherwise use menu item price.
     */
    public function getEffectivePrice()
    {
        return $this->price_override ?? $this->menuItem->price;
    }
}
