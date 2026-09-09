<?php

namespace App\Models;

use App\Models\Concerns\BelongsToRestaurant;
use Illuminate\Database\Eloquent\Model;

class CommissionEarning extends Model
{
    use BelongsToRestaurant;

    protected $fillable = ['restaurant_id', 'appointment_id', 'staff_id', 'base_amount', 'commission_amount', 'status', 'paid_at'];
    protected $casts = ['base_amount' => 'decimal:2', 'commission_amount' => 'decimal:2', 'paid_at' => 'datetime'];

    public function appointment()
    {
        return $this->belongsTo(Appointment::class);
    }

    public function staff()
    {
        return $this->belongsTo(User::class, 'staff_id');
    }
}
