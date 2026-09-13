<?php

namespace App\Models;

use App\Models\Concerns\BelongsToRestaurant;
use App\Models\GymCheckIn;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class GymMembership extends Model
{
    use BelongsToRestaurant;

    protected $fillable = ['restaurant_id', 'customer_id', 'gym_plan_id', 'gym_trainer_id', 'starts_at', 'ends_at', 'status', 'amount_paid', 'notes'];
    protected $casts = ['starts_at' => 'date', 'ends_at' => 'date', 'amount_paid' => 'decimal:2'];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function plan()
    {
        return $this->belongsTo(GymPlan::class, 'gym_plan_id');
    }

    public function trainer()
    {
        return $this->belongsTo(GymTrainer::class, 'gym_trainer_id');
    }

    public function checkIns()
    {
        return $this->hasMany(GymCheckIn::class);
    }

    public function isActive(): bool
    {
        return $this->status === 'active' && Carbon::parse($this->ends_at)->isFuture();
    }
}
