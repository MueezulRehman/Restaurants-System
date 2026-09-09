<?php

namespace App\Models;

use App\Models\Concerns\BelongsToRestaurant;
use App\Models\GymMembership;
use Illuminate\Database\Eloquent\Model;

class GymPlan extends Model
{
    use BelongsToRestaurant;

    protected $fillable = ['restaurant_id', 'name', 'duration_days', 'price', 'is_active'];
    protected $casts = ['duration_days' => 'integer', 'price' => 'decimal:2', 'is_active' => 'boolean'];

    public function memberships()
    {
        return $this->hasMany(GymMembership::class);
    }
}
