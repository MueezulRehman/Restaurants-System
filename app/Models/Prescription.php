<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Prescription extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'restaurant_id',
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
    ];

    protected $casts = [
        'prescription_date' => 'date',
        'valid_until' => 'date',
        'medicines' => 'json',
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

    public function isExpired()
    {
        return $this->valid_until && $this->valid_until->isPast();
    }

    public function isActive()
    {
        return $this->status === 'verified' && !$this->isExpired();
    }
}
