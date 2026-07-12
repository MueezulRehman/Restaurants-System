<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Restaurant;
use App\Models\RestaurantSubscription;
use App\Models\SubscriptionPlan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MenuItemAdminFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_manager_can_create_menu_item_with_cost_price_unit_and_variants_flag(): void
    {
        $restaurant = Restaurant::create([
            'name' => 'TasteHut Test',
            'slug' => 'tastehut-test',
            'status' => 'active',
            'enabled_modules' => ['menu'],
        ]);

        $user = User::create([
            'name' => 'Manager',
            'phone' => '1234567890',
            'email' => 'manager@example.com',
            'role' => 'manager',
            'password' => bcrypt('password'),
            'restaurant_id' => $restaurant->id,
            'module_access' => ['menu'],
            'is_active' => true,
        ]);

        $category = Category::create([
            'restaurant_id' => $restaurant->id,
            'name' => 'Pizza',
            'slug' => 'pizza',
            'is_active' => true,
        ]);

        $plan = SubscriptionPlan::create([
            'name' => 'Basic',
            'slug' => 'basic',
            'price_monthly' => 0,
            'price_yearly' => 0,
            'trial_days' => 30,
            'is_active' => true,
            'sort_order' => 1,
        ]);

        RestaurantSubscription::create([
            'restaurant_id' => $restaurant->id,
            'subscription_plan_id' => $plan->id,
            'billing_cycle' => 'monthly',
            'trial_ends_at' => now()->addDays(30),
            'current_period_start' => now(),
            'current_period_end' => now()->addMonth(),
            'status' => 'active',
            'auto_renew' => true,
        ]);

        $response = $this->actingAs($user)
            ->post(route('manager.menu-items.store'), [
                'name' => 'Margherita Pizza',
                'category_id' => $category->id,
                'price' => '12.50',
                'cost_price' => '8.25',
                'unit' => 'piece',
                'available' => '1',
                'has_variants' => '1',
                'track_stock' => '0',
                'stock_quantity' => '0',
                'low_stock_threshold' => '5',
            ]);

        $response->assertRedirect(route('manager.menu-items.index'));
        $this->assertDatabaseHas('menu_items', [
            'name' => 'Margherita Pizza',
            'restaurant_id' => $restaurant->id,
            'cost_price' => '8.25',
            'unit' => 'piece',
            'has_variants' => true,
            'is_available' => true,
        ]);
    }
}
