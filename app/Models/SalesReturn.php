<?php

namespace App\Models;

use App\Models\Concerns\BelongsToRestaurant;
use Illuminate\Database\Eloquent\Model;

class SalesReturn extends Model
{
    use BelongsToRestaurant;

    protected $fillable = [
        'restaurant_id',
        'order_id',
        'order_item_id',
        'customer_id',
        'processed_by',
        'quantity',
        'amount',
        'refund_method',
        'reason',
    ];

    protected $casts = [
        'quantity' => 'decimal:3',
        'amount' => 'decimal:2',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
    public function orderItem()
    {
        return $this->belongsTo(OrderItem::class);
    }
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
    public function processor()
    {
        return $this->belongsTo(User::class, 'processed_by');
    }
}
