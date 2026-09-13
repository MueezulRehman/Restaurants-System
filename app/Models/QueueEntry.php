<?php

namespace App\Models;

use App\Models\Concerns\BelongsToRestaurant;
use Illuminate\Database\Eloquent\Model;

class QueueEntry extends Model
{
    use BelongsToRestaurant;

    protected $fillable = [
        'restaurant_id', 'doctor_id', 'patient_id', 'visit_id', 'queue_date',
        'token_number', 'public_token', 'status', 'called_at', 'started_at', 'completed_at',
        'no_show_at',
    ];

    protected $casts = [
        'queue_date' => 'date',
        'called_at' => 'datetime',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'no_show_at' => 'datetime',
    ];

    public function doctor() { return $this->belongsTo(Doctor::class); }
    public function patient() { return $this->belongsTo(Patient::class); }
    public function visit() { return $this->belongsTo(Visit::class); }
}
