<?php

namespace App\Models;

use App\Models\Concerns\BelongsToRestaurant;
use Illuminate\Database\Eloquent\Model;

class ProductDevice extends Model
{
    use BelongsToRestaurant;

    protected $fillable = [
        'restaurant_id',
        'menu_item_id',
        'product_variant_id',
        'customer_id',
        'identifier_type',
        'identifier_value',
        'purchase_date',
        'warranty_until',
        'status',
    ];

    protected $casts = [
        'purchase_date' => 'date',
        'warranty_until' => 'date',
    ];

    public function menuItem()
    {
        return $this->belongsTo(MenuItem::class);
    }
    public function variant()
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
}
