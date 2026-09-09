<?php

namespace App\Models;

use App\Models\Concerns\BelongsToRestaurant;
use Illuminate\Database\Eloquent\Model;

class DeliveryZone extends Model
{
    use BelongsToRestaurant;

    protected $fillable = ['restaurant_id', 'name', 'area_pattern', 'fee', 'minimum_order', 'is_active'];
    protected $casts = ['fee' => 'decimal:2', 'minimum_order' => 'decimal:2', 'is_active' => 'boolean'];

    public function matchesAddress(string $address): bool
    {
        return str_contains(mb_strtolower($address), mb_strtolower($this->area_pattern));
    }
}
