<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    protected $fillable = [
        'order_id',
        'item_type',
        'menu_item_id',
        'deal_id',
        'product_variant_id',
        'medicine_batch_id',
        'item_name',
        'size_label',
        'quantity',
        'unit_price',
        'discount_amount',
        'discount_type',
        'original_total',
        'total_price',
        'special_request',
    ];

    protected $casts = [
        'quantity' => 'decimal:3',
        'unit_price' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'original_total' => 'decimal:2',
        'total_price' => 'decimal:2',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function menuItem()
    {
        return $this->belongsTo(MenuItem::class);
    }

    public function variant()
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }

    public function deal()
    {
        return $this->belongsTo(Deal::class);
    }

    public function medicineBatch()
    {
        return $this->belongsTo(MedicineBatch::class, 'medicine_batch_id');
    }

    public function toppings()
    {
        return $this->hasMany(OrderItemTopping::class);
    }
}
