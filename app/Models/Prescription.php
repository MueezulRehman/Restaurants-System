<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Concerns\BelongsToRestaurant;

class Prescription extends Model
{
    use SoftDeletes;
    use BelongsToRestaurant;

    protected $fillable = [
        'restaurant_id',
        'visit_id',
        'patient_id',
        'doctor_id',
        'order_id',
        'prescription_number',
        'customer_id',
        'patient_name',
        'doctor_name',
        'prescription_date',
        'valid_until',
        'medicines',
        'image_path',
        'status',
        'verification_notes',
        'dispensed_by',
        'dispensed_at',
    ];

    protected $casts = [
        'prescription_date' => 'date',
        'valid_until' => 'date',
        'medicines' => 'json',
        'dispensed_at' => 'datetime',
    ];

    public function restaurant()
    {
        return $this->belongsTo(Restaurant::class);
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function visit() { return $this->belongsTo(Visit::class); }
    public function patient() { return $this->belongsTo(Patient::class); }
    public function doctor() { return $this->belongsTo(Doctor::class); }
    public function dispenser() { return $this->belongsTo(User::class, 'dispensed_by'); }

    public function isExpired()
    {
        return $this->valid_until && $this->valid_until->isPast();
    }

    public function isActive()
    {
        return $this->status === 'verified' && !$this->isExpired();
    }
}
