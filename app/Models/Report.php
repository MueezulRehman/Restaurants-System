<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\BelongsToRestaurant;

class Report extends Model
{
    use BelongsToRestaurant;

    protected $fillable = [
        'restaurant_id', 'user_id', 'type', 'name', 'filters',
        'data_snapshot', 'generated_at',
    ];

    protected $casts = [
        'filters' => 'json',
        'data_snapshot' => 'json',
        'generated_at' => 'datetime',
    ];

    public function restaurant()
    {
        return $this->belongsTo(Restaurant::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
