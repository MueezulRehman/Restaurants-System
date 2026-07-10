<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RestaurantSubscription extends Model
{
    protected $fillable = [
        'restaurant_id', 'subscription_plan_id', 'billing_cycle',
        'trial_ends_at', 'current_period_start', 'current_period_end',
        'payment_method', 'status', 'auto_renew',
    ];

    protected $casts = [
        'trial_ends_at' => 'datetime',
        'current_period_start' => 'datetime',
        'current_period_end' => 'datetime',
        'auto_renew' => 'boolean',
    ];

    public function restaurant()
    {
        return $this->belongsTo(Restaurant::class);
    }

    public function plan()
    {
        return $this->belongsTo(SubscriptionPlan::class, 'subscription_plan_id');
    }

    public function billingCycles()
    {
        return $this->hasMany(BillingCycle::class);
    }

    /**
     * Check if subscription is in trial period.
     */
    public function isInTrial(): bool
    {
        return $this->trial_ends_at && $this->trial_ends_at->isFuture();
    }

    /**
     * Check if subscription is active.
     */
    public function isActive(): bool
    {
        return $this->status === 'active' && 
               (!$this->current_period_end || $this->current_period_end->isFuture());
    }

    /**
     * Check if subscription is expired.
     */
    public function isExpired(): bool
    {
        return $this->current_period_end && $this->current_period_end->isPast();
    }
}
