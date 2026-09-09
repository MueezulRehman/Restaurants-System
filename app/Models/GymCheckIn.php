<?php

namespace App\Models;

use App\Models\Concerns\BelongsToRestaurant;
use Illuminate\Database\Eloquent\Model;

class GymCheckIn extends Model
{
    use BelongsToRestaurant;

    protected $fillable = ['restaurant_id', 'gym_membership_id', 'customer_id', 'checked_in_at', 'notes'];
    protected $casts = ['checked_in_at' => 'datetime'];

    public function membership()
    {
        return $this->belongsTo(GymMembership::class, 'gym_membership_id');
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
}
