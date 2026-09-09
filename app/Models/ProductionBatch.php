<?php

namespace App\Models;

use App\Models\Concerns\BelongsToRestaurant;
use Illuminate\Database\Eloquent\Model;

class ProductionBatch extends Model
{
    use BelongsToRestaurant;
    protected $fillable = ['restaurant_id', 'recipe_id', 'batch_number', 'quantity', 'produced_at', 'notes'];
    protected $casts = ['quantity' => 'decimal:3', 'produced_at' => 'datetime'];
    public function recipe()
    {
        return $this->belongsTo(Recipe::class);
    }
}
