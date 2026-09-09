<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StorefrontNotice extends Model
{
    protected $fillable = [
        'restaurant_id',
        'title',
        'message',
        'is_active',
        'show_as_modal',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'show_as_modal' => 'boolean',
    ];

    public function restaurant()
    {
        return $this->belongsTo(Restaurant::class);
    }
}