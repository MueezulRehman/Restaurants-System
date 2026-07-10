<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\BelongsToRestaurant;

class Notification extends Model
{
    use BelongsToRestaurant;

    protected $fillable = [
        'restaurant_id', 'user_id', 'customer_id', 'type', 'title', 'message',
        'channels', 'status', 'sent_at', 'read_at',
    ];

    protected $casts = [
        'channels' => 'json',
        'sent_at' => 'datetime',
        'read_at' => 'datetime',
    ];

    public function restaurant()
    {
        return $this->belongsTo(Restaurant::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Mark notification as read.
     */
    public function markAsRead(): void
    {
        $this->update(['read_at' => now()]);
    }
}
