<?php

namespace Tests\Feature;

use App\Models\BusinessType;
use App\Models\Restaurant;
use App\Models\SubscriptionPlan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RestaurantSubscriptionPlanTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_can_create_restaurant_with_subscription_plan(): void
    {
        $superAdmin = User::factory()->create([
            'role' => 'super_admin',
            'email' => 'admin@example.com',
            'phone' => '10000000001',
        ]);

        $businessType = BusinessType::create([
            'name' => 'Cafe',
            'slug' => 'cafe',
            'is_active' => true,
            'sort_order' => 1,
        ]);

        $plan = SubscriptionPlan::create([
            'name' => 'Starter',
            'slug' => 'starter',
            'description' => 'Starter plan',
            'price_monthly' => 15,
            'price_yearly' => 150,
            'trial_days' => 14,
            'max_staff' => 5,
            'max_menu_items' => 100,
            'is_active' => true,
            'sort_order' => 1,
        ]);

        $response = $this->actingAs($superAdmin)->post(route('admin.restaurants.store'), [
            'name' => 'Plan Test Restaurant',
            'slug' => 'plan-test-restaurant',
            'business_type_id' => $businessType->id,
            'status' => 'active',
            'plan' => $plan->slug,
            'enabled_modules' => [],
        ]);

        $response->assertRedirect(route('admin.restaurants.index'));

        $restaurant = Restaurant::where('slug', 'plan-test-restaurant')->firstOrFail();

        $this->assertNotNull($restaurant->subscription);
        $this->assertEquals($plan->id, $restaurant->subscription->subscription_plan_id);
        $this->assertEquals('active', $restaurant->subscription->status);
    }
}
