<?php

namespace App\Models;

use App\Models\Concerns\BelongsToRestaurant;
use Illuminate\Database\Eloquent\Model;

class GymTrainerSchedule extends Model
{
    use BelongsToRestaurant;

    protected $fillable = ['restaurant_id', 'gym_trainer_id', 'day_of_week', 'starts_at', 'ends_at', 'capacity', 'is_active'];
    protected $casts = ['day_of_week' => 'integer', 'capacity' => 'integer', 'is_active' => 'boolean'];

    public function trainer()
    {
        return $this->belongsTo(GymTrainer::class, 'gym_trainer_id');
    }
}
