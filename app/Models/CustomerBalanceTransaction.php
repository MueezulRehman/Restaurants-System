<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomerBalanceTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'restaurant_id',
        'customer_id',
        'order_id',
        'created_by',
        'type',
        'amount',
        'source',
        'description',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
