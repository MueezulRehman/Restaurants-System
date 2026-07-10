<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PushSubscription extends Model
{
    protected $fillable = [
        'subscriber_type', 'subscriber_id', 'endpoint', 'keys',
    ];

    protected $casts = [
        'keys' => 'json',
    ];

    public function subscriber()
    {
        return $this->morphTo();
    }
}
