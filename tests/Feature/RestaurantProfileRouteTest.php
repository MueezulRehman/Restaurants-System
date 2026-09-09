<?php

namespace Tests\Feature;

use App\Models\Restaurant;
use App\Models\RestaurantSubscription;
use App\Models\SubscriptionPlan;
use App\Models\User;
use App\Models\Notification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RestaurantProfileRouteTest extends TestCase
{
    use RefreshDatabase;

    public function test_manager_can_view_their_restaurant_profile_page(): void
    {
        $restaurant = Restaurant::create([
            'name' => 'Test Restaurant',
            'slug' => 'test-restaurant',
            'status' => 'active',
            'enabled_modules' => ['theme', 'notifications'],
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

        RestaurantSubscription::create([
            'restaurant_id' => $restaurant->id,
            'subscription_plan_id' => $plan->id,
            'billing_cycle' => 'monthly',
            'status' => 'active',
            'auto_renew' => true,
            'current_period_start' => now(),
            'current_period_end' => now()->addMonth(),
        ]);

        $manager = User::create([
            'name' => 'Manager User',
            'email' => 'manager@example.com',
            'phone' => '10000000002',
            'role' => 'manager',
            'restaurant_id' => $restaurant->id,
            'password' => bcrypt('password'),
        ]);

        $response = $this->actingAs($manager, 'web')
            ->get('/manager/restaurant/profile');

        $response->assertOk();
        $response->assertSee('Business Profile');

        $this->actingAs($manager, 'web')
            ->patch('/manager/restaurant/profile', [
                'name' => 'Test Restaurant',
                'storefront_notice_enabled' => '1',
                'storefront_notice' => 'Delivery temporarily unavailable.',
            ])
            ->assertRedirect('/manager/restaurant/profile');
    }

    public function test_storefront_notice_is_visible_on_the_public_menu(): void
    {
        $restaurant = Restaurant::create([
            'name' => 'Rainy Restaurant',
            'slug' => 'rainy-restaurant',
            'status' => 'active',
            'show_on_homepage' => true,
            'enabled_modules' => ['theme', 'notifications'],
            'storefront_notice_enabled' => true,
            'storefront_notice' => 'Due to heavy rain, delivery is temporarily unavailable.',
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

        RestaurantSubscription::create([
            'restaurant_id' => $restaurant->id,
            'subscription_plan_id' => $plan->id,
            'billing_cycle' => 'monthly',
            'status' => 'active',
            'auto_renew' => true,
            'current_period_start' => now(),
            'current_period_end' => now()->addMonth(),
        ]);

        $response = $this->get('/rainy-restaurant');

        $response->assertOk();
        $response->assertSee('Due to heavy rain, delivery is temporarily unavailable.');
    }

    public function test_legacy_blade_template_name_still_uses_the_selected_menu_template(): void
    {
        $restaurant = new Restaurant(['customer_template' => 'modern.blade']);

        $this->assertSame('modern', $restaurant->getCustomerMenuTemplate());
    }

    public function test_manager_can_access_notification_center(): void
    {
        $restaurant = Restaurant::create([
            'name' => 'Kitchen Hub',
            'slug' => 'kitchen-hub',
            'status' => 'active',
            'enabled_modules' => ['theme', 'notifications'],
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

        RestaurantSubscription::create([
            'restaurant_id' => $restaurant->id,
            'subscription_plan_id' => $plan->id,
            'billing_cycle' => 'monthly',
            'status' => 'active',
            'auto_renew' => true,
            'current_period_start' => now(),
            'current_period_end' => now()->addMonth(),
        ]);

        $manager = User::create([
            'name' => 'Manager User',
            'email' => 'manager2@example.com',
            'phone' => '10000000003',
            'role' => 'manager',
            'restaurant_id' => $restaurant->id,
            'password' => bcrypt('password'),
        ]);

        $response = $this->actingAs($manager, 'web')->get('/manager/notifications');

        $response->assertOk();
        $response->assertSee('Notifications');

        $this->actingAs($manager, 'web')
            ->post('/manager/notifications', [
                'type' => 'order_update',
                'title' => 'No delivery due to rain',
                'message' => 'Delivery is temporarily unavailable.',
                'channels' => ['push'],
            ])
            ->assertRedirect('/manager/notifications');

        $notification = Notification::query()->latest('id')->firstOrFail();

        $this->actingAs($manager, 'web')
            ->get('/manager/notifications/' . $notification->id . '/read')
            ->assertRedirect('/manager/notifications');
    }
}
