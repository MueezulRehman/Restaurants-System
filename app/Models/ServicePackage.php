<?php

namespace App\Models;

use App\Models\Concerns\BelongsToRestaurant;
use Illuminate\Database\Eloquent\Model;

class ServicePackage extends Model
{
    use BelongsToRestaurant;
    protected $fillable = ['restaurant_id', 'name', 'description', 'included_visits', 'validity_days', 'price', 'is_active'];
    protected $casts = ['included_visits' => 'integer', 'validity_days' => 'integer', 'price' => 'decimal:2', 'is_active' => 'boolean'];
    public function purchases()
    {
        return $this->hasMany(ServicePackagePurchase::class);
    }
}
