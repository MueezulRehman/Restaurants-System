<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OtpVerification extends Model
{
    protected $fillable = [
        'restaurant_id',
        'phone',
        'code_hash',
        'purpose',
        'channels',
        'attempts',
        'expires_at',
        'verified_at',
    ];

    protected $casts = [
        'channels' => 'array',
        'expires_at' => 'datetime',
        'verified_at' => 'datetime',
    ];
}
