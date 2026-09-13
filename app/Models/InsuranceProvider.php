<?php

namespace App\Models;

use App\Models\Concerns\BelongsToRestaurant;
use Illuminate\Database\Eloquent\Model;

class InsuranceProvider extends Model
{
    use BelongsToRestaurant;
    protected $fillable = ['restaurant_id', 'name', 'phone', 'email', 'is_active'];
    protected $casts = ['is_active' => 'boolean'];
    public function claims()
    {
        return $this->hasMany(InsuranceClaim::class, 'provider_id');
    }
}
