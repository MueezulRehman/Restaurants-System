<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubscriptionFeature extends Model
{
    protected $fillable = [
        'name', 'description', 'icon', 'sort_order',
    ];

    public function plans()
    {
        return $this->belongsToMany(
            SubscriptionPlan::class,
            'plan_features',
            'subscription_feature_id',
            'subscription_plan_id'
        );
    }
}
