<?php

namespace App\Services;

use App\Models\Restaurant;
use App\Models\RestaurantSubscription;
use App\Models\SubscriptionPlan;
use App\Models\BillingCycle;
use Carbon\Carbon;

class SubscriptionManager
{
    /**
     * Create a new subscription for a restaurant (typically at signup with trial).
     */
    public static function createTrialSubscription(Restaurant $restaurant, SubscriptionPlan $plan): RestaurantSubscription
    {
        $trialEnds = Carbon::now()->addDays($plan->trial_days);

        return RestaurantSubscription::create([
            'restaurant_id' => $restaurant->id,
            'subscription_plan_id' => $plan->id,
            'billing_cycle' => 'monthly',
            'trial_ends_at' => $trialEnds,
            'status' => 'trial',
            'auto_renew' => true,
        ]);
    }

    /**
     * Upgrade to a paid subscription after trial expires.
     * NOTE: Payment gateway integration is deferred — this is placeholder logic.
     */
    public static function upgradeToPaidSubscription(RestaurantSubscription $subscription): bool
    {
        // TODO: Integrate payment gateway (JazzCash, EasyPaisa, Stripe) here
        // For now, just mark as active with a new billing period

        $periodStart = Carbon::now();
        $periodEnd = $subscription->billing_cycle === 'yearly'
            ? $periodStart->clone()->addYear()
            : $periodStart->clone()->addMonth();

        $subscription->update([
            'status' => 'active',
            'trial_ends_at' => null,
            'current_period_start' => $periodStart,
            'current_period_end' => $periodEnd,
        ]);

        // Create a billing cycle record
        BillingCycle::create([
            'restaurant_subscription_id' => $subscription->id,
            'period_start' => $periodStart,
            'period_end' => $periodEnd,
            'amount' => $subscription->billing_cycle === 'yearly'
                ? $subscription->plan->price_yearly
                : $subscription->plan->price_monthly,
            'status' => 'pending', // Would be 'paid' after payment gateway processes it
        ]);

        return true;
    }

    /**
     * Check and expire subscriptions that have passed their trial or period end dates.
     * Call this daily via a scheduled command.
     */
    public static function checkExpiredSubscriptions(): array
    {
        $now = Carbon::now();
        $expiredCount = 0;
        $stats = ['expired_trials' => 0, 'expired_periods' => 0, 'total' => 0];

        // Expire trials
        $trialExpired = RestaurantSubscription::where('status', 'trial')
            ->where('trial_ends_at', '<', $now)
            ->update(['status' => 'expired']);

        $stats['expired_trials'] = $trialExpired;

        // Expire active subscriptions whose period has ended
        $periodExpired = RestaurantSubscription::where('status', 'active')
            ->where('current_period_end', '<', $now)
            ->where('auto_renew', false)
            ->update(['status' => 'expired']);

        $stats['expired_periods'] = $periodExpired;
        $stats['total'] = $trialExpired + $periodExpired;

        return $stats;
    }

    /**
     * Change subscription plan (usually with prorated credits).
     */
    public static function upgradePlan(RestaurantSubscription $subscription, SubscriptionPlan $newPlan): RestaurantSubscription
    {
        // TODO: Handle proration logic here if needed
        $subscription->update([
            'subscription_plan_id' => $newPlan->id,
        ]);

        return $subscription->fresh();
    }

    /**
     * Cancel a subscription.
     */
    public static function cancel(RestaurantSubscription $subscription): RestaurantSubscription
    {
        $subscription->update([
            'status' => 'cancelled',
            'auto_renew' => false,
        ]);

        return $subscription->fresh();
    }

    /**
     * Reactivate a cancelled subscription.
     */
    public static function reactivate(RestaurantSubscription $subscription): RestaurantSubscription
    {
        $periodStart = Carbon::now();
        $periodEnd = $subscription->billing_cycle === 'yearly'
            ? $periodStart->clone()->addYear()
            : $periodStart->clone()->addMonth();

        $subscription->update([
            'status' => 'active',
            'auto_renew' => true,
            'current_period_start' => $periodStart,
            'current_period_end' => $periodEnd,
        ]);

        return $subscription->fresh();
    }
}
