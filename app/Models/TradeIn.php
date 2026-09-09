<?php

namespace App\Models;

use App\Models\Concerns\BelongsToRestaurant;
use Illuminate\Database\Eloquent\Model;

class TradeIn extends Model
{
    use BelongsToRestaurant;
    protected $fillable = ['restaurant_id', 'customer_id', 'item_name', 'serial_number', 'estimated_value', 'condition', 'status'];
    protected $casts = ['estimated_value' => 'decimal:2'];
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
}
