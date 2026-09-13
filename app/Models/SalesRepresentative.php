<?php

namespace App\Models;

use App\Models\Concerns\BelongsToRestaurant;
use Illuminate\Database\Eloquent\Model;

class SalesRepresentative extends Model
{
    use BelongsToRestaurant;
    protected $fillable = ['restaurant_id', 'user_id', 'name', 'phone', 'commission_rate', 'is_active'];
    protected $casts = ['commission_rate' => 'decimal:2', 'is_active' => 'boolean'];
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function customers()
    {
        return $this->hasMany(Customer::class);
    }
}
