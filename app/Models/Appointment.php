<?php

namespace App\Models;

use App\Models\Concerns\BelongsToRestaurant;
use Illuminate\Database\Eloquent\Model;

class Appointment extends Model
{
    use BelongsToRestaurant;

    protected $fillable = [
        'restaurant_id',
        'customer_id',
        'patient_id',
        'service_package_purchase_id',
        'staff_id',
        'service_name',
        'starts_at',
        'ends_at',
        'status',
        'price',
        'notes',
    ];

    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'price' => 'decimal:2',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    public function staff()
    {
        return $this->belongsTo(User::class, 'staff_id');
    }

    public function servicePackagePurchase()
    {
        return $this->belongsTo(ServicePackagePurchase::class);
    }

    public function scopeUpcoming($query)
    {
        return $query->where('starts_at', '>=', now())->orderBy('starts_at');
    }
}
