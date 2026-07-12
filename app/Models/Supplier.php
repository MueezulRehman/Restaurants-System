<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Supplier extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'restaurant_id',
        'name',
        'contact_person',
        'email',
        'phone',
        'address',
        'city',
        'state',
        'gstin',
        'average_delivery_days',
        'payment_terms',
        'notes',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'average_delivery_days' => 'float',
    ];

    public function restaurant()
    {
        return $this->belongsTo(Restaurant::class);
    }

    public function purchaseHeaders()
    {
        return $this->hasMany(PurchaseHeader::class);
    }
}
