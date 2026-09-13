<?php

namespace App\Models;

use App\Models\Concerns\BelongsToRestaurant;
use Illuminate\Database\Eloquent\Model;

class Visit extends Model
{
    use BelongsToRestaurant;

    protected $fillable = [
        'restaurant_id',
        'patient_id',
        'doctor_id',
        'appointment_id',
        'reason',
        'diagnosis',
        'notes',
        'status',
        'checked_in_at',
        'started_at',
        'completed_at',
    ];

    protected $casts = [
        'checked_in_at' => 'datetime',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function patient() { return $this->belongsTo(Patient::class); }
    public function doctor() { return $this->belongsTo(Doctor::class); }
    public function appointment() { return $this->belongsTo(Appointment::class); }
    public function queueEntry() { return $this->hasOne(QueueEntry::class); }
    public function prescriptions() { return $this->hasMany(Prescription::class); }
}
