<?php

namespace App\Models;

use App\Models\Concerns\BelongsToRestaurant;
use Illuminate\Database\Eloquent\Model;

class MedicalRecord extends Model
{
    use BelongsToRestaurant;

    protected $fillable = [
        'restaurant_id',
        'customer_id',
        'appointment_id',
        'patient_name',
        'medicine_name',
        'doctor_name',
        'diagnosis',
        'follow_up_at',
        'notes',
    ];

    protected $casts = ['follow_up_at' => 'datetime'];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function appointment()
    {
        return $this->belongsTo(Appointment::class);
    }

    public function followUpReminders()
    {
        return $this->hasMany(FollowUpReminder::class);
    }
}
