<?php

namespace Tests\Unit;

use App\Models\BillingCycle;
use App\Models\Restaurant;
use App\Models\RestaurantSubscription;
use App\Models\SubscriptionPlan;
use App\Services\SubscriptionManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SubscriptionManagerTest extends TestCase
{
    use RefreshDatabase;

    public function test_upgrade_to_paid_subscription_creates_paid_billing_cycle(): void
    {
        $restaurant = Restaurant::create([
            'name' => 'Paid Hut',
            'slug' => 'paid-hut',
            'status' => 'active',
        ]);

        $plan = SubscriptionPlan::create([
            'name' => 'Growth',
            'slug' => 'growth',
            'description' => 'Growth plan',
            'price_monthly' => 1200,
            'price_yearly' => 12000,
            'trial_days' => 14,
            'max_staff' => 10,
            'max_menu_items' => 100,
            'is_active' => true,
            'sort_order' => 1,
        ]);

        $subscription = RestaurantSubscription::create([
            'restaurant_id' => $restaurant->id,
            'subscription_plan_id' => $plan->id,
            'billing_cycle' => 'monthly',
            'trial_ends_at' => now()->subDays(1),
            'status' => 'trial',
            'auto_renew' => true,
        ]);

        $result = SubscriptionManager::upgradeToPaidSubscription($subscription);

        $subscription->refresh();
        $billingCycle = BillingCycle::where('restaurant_subscription_id', $subscription->id)->latest('period_start')->first();

        $this->assertTrue($result);
        $this->assertEquals('active', $subscription->status);
        $this->assertNotNull($subscription->current_period_start);
        $this->assertNotNull($subscription->current_period_end);
        $this->assertNotNull($billingCycle);
        $this->assertEquals('paid', $billingCycle->status);
        $this->assertNotNull($billingCycle->invoice_number);
        $this->assertNotNull($billingCycle->paid_at);
    }

    public function test_process_payment_returns_pending_billing_cycle_for_manual_method(): void
    {
        $restaurant = Restaurant::create([
            'name' => 'Manual Hut',
            'slug' => 'manual-hut',
            'status' => 'active',
        ]);

        $plan = SubscriptionPlan::create([
            'name' => 'Starter',
            'slug' => 'starter',
            'description' => 'Starter plan',
            'price_monthly' => 2500,
            'price_yearly' => 25000,
            'trial_days' => 14,
            'max_staff' => 3,
            'max_menu_items' => 50,
            'is_active' => true,
            'sort_order' => 1,
        ]);

        $subscription = RestaurantSubscription::create([
            'restaurant_id' => $restaurant->id,
            'subscription_plan_id' => $plan->id,
            'billing_cycle' => 'monthly',
            'status' => 'expired',
            'auto_renew' => false,
        ]);

        $billingCycle = SubscriptionManager::processPayment($subscription, 'manual', ['payment_reference' => 'BANK-REF-123']);

        $this->assertInstanceOf(BillingCycle::class, $billingCycle);
        $this->assertEquals('pending', $billingCycle->status);
        $this->assertNull($billingCycle->invoice_number);
        $this->assertNull($billingCycle->paid_at);
        $this->assertEquals($subscription->id, $billingCycle->restaurant_subscription_id);
    }
}
