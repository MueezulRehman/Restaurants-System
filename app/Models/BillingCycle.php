<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BillingCycle extends Model
{
    protected $fillable = [
        'restaurant_subscription_id', 'period_start', 'period_end',
        'amount', 'status', 'paid_at', 'invoice_number',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'period_start' => 'datetime',
        'period_end' => 'datetime',
        'paid_at' => 'datetime',
    ];

    public function subscription()
    {
        return $this->belongsTo(RestaurantSubscription::class, 'restaurant_subscription_id');
    }

    /**
     * Check if billing cycle is paid.
     */
    public function isPaid(): bool
    {
        return $this->status === 'paid';
    }

    /**
     * Check if billing cycle is pending.
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }
}
