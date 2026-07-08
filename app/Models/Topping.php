<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Topping extends Model
{
    protected $fillable = ['name', 'price', 'type'];

    protected $casts = ['price' => 'decimal:2'];
}
