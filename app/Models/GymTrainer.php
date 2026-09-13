<?php

namespace App\Models;

use App\Models\Concerns\BelongsToRestaurant;
use Illuminate\Database\Eloquent\Model;

class GymTrainer extends Model
{
    use BelongsToRestaurant;
    protected $fillable = ['restaurant_id', 'user_id', 'name', 'specialty', 'phone', 'is_active'];
    protected $casts = ['is_active' => 'boolean'];
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function memberships()
    {
        return $this->hasMany(GymMembership::class);
    }

    public function schedules()
    {
        return $this->hasMany(GymTrainerSchedule::class);
    }
}
