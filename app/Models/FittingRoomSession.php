<?php

namespace App\Models;

use App\Models\Concerns\BelongsToRestaurant;
use Illuminate\Database\Eloquent\Model;

class FittingRoomSession extends Model
{
    use BelongsToRestaurant;
    protected $fillable = ['restaurant_id', 'customer_id', 'staff_id', 'room_label', 'status', 'started_at', 'ended_at', 'notes'];
    protected $casts = ['started_at' => 'datetime', 'ended_at' => 'datetime'];
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
    public function staff()
    {
        return $this->belongsTo(User::class, 'staff_id');
    }
    public function items()
    {
        return $this->hasMany(FittingRoomItem::class);
    }
}
