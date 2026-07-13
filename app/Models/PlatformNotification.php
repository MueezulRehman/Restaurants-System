<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlatformNotification extends Model
{
    protected $table = 'platform_notifications';

    protected $fillable = [
        'restaurant_id', 'user_id', 'type', 'title', 'message',
        'data', 'read_at',
    ];

    protected $casts = [
        'data' => 'array',
        'read_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function restaurant()
    {
        return $this->belongsTo(Restaurant::class);
    }

    public function markAsRead()
    {
        $this->update(['read_at' => now()]);
    }
}
