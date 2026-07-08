<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderItemTopping extends Model
{
    protected $fillable = ['order_item_id', 'topping_id', 'topping_name', 'price'];

    protected $casts = ['price' => 'decimal:2'];

    public function orderItem()
    {
        return $this->belongsTo(OrderItem::class);
    }
}
