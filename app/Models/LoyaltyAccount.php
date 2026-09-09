<?php

namespace App\Models;

use App\Models\Concerns\BelongsToRestaurant;
use Illuminate\Database\Eloquent\Model;

class LoyaltyAccount extends Model
{
    use BelongsToRestaurant;
    protected $fillable = ['restaurant_id', 'customer_id', 'points'];
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
}
