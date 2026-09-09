<?php

namespace App\Models;

use App\Models\Concerns\BelongsToRestaurant;
use App\Models\RecipeIngredient;
use Illuminate\Database\Eloquent\Model;

class Recipe extends Model
{
    use BelongsToRestaurant;

    protected $fillable = ['restaurant_id', 'menu_item_id', 'name', 'yield_quantity', 'is_active'];
    protected $casts = ['yield_quantity' => 'decimal:3', 'is_active' => 'boolean'];

    public function product()
    {
        return $this->belongsTo(MenuItem::class, 'menu_item_id');
    }
    public function ingredients()
    {
        return $this->hasMany(RecipeIngredient::class);
    }
}
