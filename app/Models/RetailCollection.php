<?php

namespace App\Models;

use App\Models\Concerns\BelongsToRestaurant;
use Illuminate\Database\Eloquent\Model;

class RetailCollection extends Model
{
    use BelongsToRestaurant;
    protected $table = 'retail_collections';
    protected $fillable = ['restaurant_id', 'name', 'season', 'starts_at', 'ends_at'];
    protected $casts = ['starts_at' => 'date', 'ends_at' => 'date'];
}
