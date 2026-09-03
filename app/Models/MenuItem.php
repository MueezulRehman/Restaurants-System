<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\BelongsToRestaurant;

class MenuItem extends Model
{
    use BelongsToRestaurant;

    protected $fillable = [
        'restaurant_id',
        'category_id',
        'name',
        'sku',
        'barcode',
        'description',
        'price',
        'cost_price',
        'unit',
        'unit_type',
        'price_per_unit',
        'allow_fractional_qty',
        'has_sizes',
        'has_variants',
        'image',
        'is_available',
        'allows_toppings',
        'sort_order',
        'track_stock',
        'stock_quantity',
        'low_stock_threshold',
        'pos_show_line_edit',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'cost_price' => 'decimal:2',
        'has_sizes' => 'boolean',
        'has_variants' => 'boolean',
        'is_available' => 'boolean',
        'allows_toppings' => 'boolean',
        'track_stock' => 'boolean',
        'low_stock_threshold' => 'integer',
        'stock_quantity' => 'decimal:3',
        'allow_fractional_qty' => 'boolean',
        'price_per_unit' => 'decimal:2',
        'pos_show_line_edit' => 'boolean',
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

    public function effectiveUnitPrice(): float
    {
        if ($this->price_per_unit !== null) {
            return (float) $this->price_per_unit;
        }
        return (float) ($this->price ?? 0);
    }

    public function unitLabel(): string
    {
        $type = $this->unit_type ?: ($this->unit ?: 'piece');
        return match (strtolower((string) $type)) {
            'kg', 'kilogram' => 'kg',
            'g', 'gram' => 'g',
            'liter', 'litre', 'l' => 'L',
            'dozen' => 'dozen',
            'piece', 'pcs', 'pc' => 'pc',
            default => (string) $type,
        };
    }

    public function allowsFractionalQty(): bool
    {
        if ($this->allow_fractional_qty) {
            return true;
        }
        $type = strtolower((string) ($this->unit_type ?: $this->unit ?: 'piece'));
        return in_array($type, ['kg', 'kilogram', 'g', 'gram', 'liter', 'litre', 'l'], true);
    }
}
