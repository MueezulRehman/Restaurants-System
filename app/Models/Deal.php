<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\BelongsToRestaurant;

class Deal extends Model
{
    use BelongsToRestaurant;

    protected $fillable = ['restaurant_id', 'name', 'deal_number', 'price', 'description', 'image', 'is_active'];

    protected $casts = [
        'price' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true)->orderBy('deal_number');
    }

    public function restaurant()
    {
        return $this->belongsTo(Restaurant::class);
    }
}
