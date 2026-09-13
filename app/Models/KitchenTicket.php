<?php

namespace App\Models;

use App\Models\Concerns\BelongsToRestaurant;
use Illuminate\Database\Eloquent\Model;

class KitchenTicket extends Model
{
    use BelongsToRestaurant;
    protected $fillable = ['restaurant_id', 'order_id', 'ticket_number', 'station', 'status', 'priority', 'started_at', 'completed_at', 'notes'];
    protected $casts = ['started_at' => 'datetime', 'completed_at' => 'datetime'];
    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
