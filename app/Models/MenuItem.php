<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\BelongsToRestaurant;

class MenuItem extends Model
{
    use BelongsToRestaurant;

    protected $fillable = [
        'restaurant_id', 'category_id', 'name', 'sku', 'description', 'price', 'has_sizes',
        'image', 'is_available', 'allows_toppings', 'sort_order',
        'track_stock', 'stock_quantity', 'low_stock_threshold',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'has_sizes' => 'boolean',
        'is_available' => 'boolean',
        'allows_toppings' => 'boolean',
        'track_stock' => 'boolean',
        'stock_quantity' => 'integer',
        'low_stock_threshold' => 'integer',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function restaurant()
    {
        return $this->belongsTo(Restaurant::class);
    }

    public function sizes()
    {
        return $this->hasMany(MenuItemSize::class)->orderBy('sort_order');
    }

    public function variants()
    {
        return $this->hasMany(ProductVariant::class)->orderBy('sort_order');
    }

    public function variantAttributes()
    {
        return $this->hasMany(VariantAttribute::class)->orderBy('sort_order');
    }

    // returns the lowest price to show on menu cards, whether sized or flat-priced
    public function getDisplayPriceAttribute()
    {
        if ($this->has_sizes) {
            return $this->sizes->min('price') ?? 0;
        }
        return $this->price;
    }

    public function isLowStock(): bool
    {
        return $this->track_stock && $this->stock_quantity <= $this->low_stock_threshold;
    }
}
