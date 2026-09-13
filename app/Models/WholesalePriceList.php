<?php

namespace App\Models;

use App\Models\Concerns\BelongsToRestaurant;
use Illuminate\Database\Eloquent\Model;

class WholesalePriceList extends Model
{
    use BelongsToRestaurant;
    protected $fillable = ['restaurant_id', 'name', 'customer_group', 'is_active'];
    protected $casts = ['is_active' => 'boolean'];
    public function items()
    {
        return $this->hasMany(WholesalePriceListItem::class);
    }
}
