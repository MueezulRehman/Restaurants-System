<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubscriptionPlan extends Model
{
    protected $fillable = [
        'name', 'slug', 'description', 'price_monthly', 'price_yearly',
        'trial_days', 'max_staff', 'max_menu_items', 'max_modules', 'is_active', 'sort_order',
    ];

    protected $casts = [
        'price_monthly' => 'decimal:2',
        'price_yearly' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function features()
    {
        return $this->belongsToMany(
            SubscriptionFeature::class,
            'plan_features',
            'subscription_plan_id',
            'subscription_feature_id'
        );
    }

    public function restaurants()
    {
        return $this->hasMany(RestaurantSubscription::class, 'subscription_plan_id');
    }
}
