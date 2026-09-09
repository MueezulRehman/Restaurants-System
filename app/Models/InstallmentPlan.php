<?php

namespace App\Models;

use App\Models\Concerns\BelongsToRestaurant;
use Illuminate\Database\Eloquent\Model;

class InstallmentPlan extends Model
{
    use BelongsToRestaurant;
    protected $fillable = ['restaurant_id', 'customer_id', 'item_name', 'total_amount', 'deposit', 'months', 'status', 'next_due_at'];
    protected $casts = ['total_amount' => 'decimal:2', 'deposit' => 'decimal:2', 'next_due_at' => 'date'];
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
}
