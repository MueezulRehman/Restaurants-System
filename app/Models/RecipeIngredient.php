<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RecipeIngredient extends Model
{
    protected $fillable = ['recipe_id', 'menu_item_id', 'quantity'];
    protected $casts = ['quantity' => 'decimal:3'];
    public function recipe()
    {
        return $this->belongsTo(Recipe::class);
    }
    public function item()
    {
        return $this->belongsTo(MenuItem::class, 'menu_item_id');
    }
}
