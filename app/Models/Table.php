<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Table extends Model
{
    use HasFactory;

    protected $fillable = [
        'restaurant_id', 'label', 'number', 'seats', 'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function restaurant()
    {
        return $this->belongsTo(Restaurant::class);
    }
}
