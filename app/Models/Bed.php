<?php

namespace App\Models;

use App\Models\Concerns\BelongsToRestaurant;
use Illuminate\Database\Eloquent\Model;

class Bed extends Model
{
    use BelongsToRestaurant;

    protected $fillable = ['restaurant_id', 'ward_id', 'name', 'code', 'status'];
}
