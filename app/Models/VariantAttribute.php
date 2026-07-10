<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\BelongsToRestaurant;

class VariantAttribute extends Model
{
    use BelongsToRestaurant;

    protected $fillable = [
        'restaurant_id', 'menu_item_id', 'name', 'sort_order',
    ];

    public function menuItem()
    {
        return $this->belongsTo(MenuItem::class);
    }

    public function restaurant()
    {
        return $this->belongsTo(Restaurant::class);
    }

    public function values()
    {
        return $this->hasMany(VariantAttributeValue::class)->orderBy('sort_order');
    }
}
