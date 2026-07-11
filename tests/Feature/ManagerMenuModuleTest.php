<?php

namespace Tests\Feature;

use App\Models\MenuItem;
use App\Models\Category;
use App\Models\Restaurant;
use App\Models\User;
use App\Services\ModuleService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ManagerMenuModuleTest extends TestCase
{
    use RefreshDatabase;

    public function test_manager_with_menu_access_can_view_menu_items_index(): void
    {
        ModuleService::seedDefaultModules();

        $restaurant = Restaurant::create([
            'name' => 'Test Restaurant',
            'slug' => 'test-restaurant',
            'status' => 'active',
            'enabled_modules' => ['menu'],
        ]);

        $restaurant->subscription()->create([
            'subscription_plan_id' => null,
            'status' => 'active',
            'billing_cycle' => 'monthly',
        ]);

        $manager = User::create([
            'name' => 'Manager',
            'email' => 'manager@example.com',
            'phone' => '1234567890',
            'role' => 'manager',
            'restaurant_id' => $restaurant->id,
            'password' => bcrypt('password'),
            'module_access' => ['menu'],
        ]);

        $response = $this->actingAs($manager)->get('/manager/menu-items');

        $response->assertStatus(200);
        $response->assertSee('Menu Items');
        $response->assertSee('Add Item');
    }

    public function test_manager_without_menu_access_is_blocked_from_menu_module(): void
    {
        ModuleService::seedDefaultModules();

        $restaurant = Restaurant::create([
            'name' => 'Test Restaurant',
            'slug' => 'test-restaurant',
            'status' => 'active',
            'enabled_modules' => ['menu'],
        ]);

        $restaurant->subscription()->create([
            'subscription_plan_id' => null,
            'status' => 'active',
            'billing_cycle' => 'monthly',
        ]);

        $manager = User::create([
            'name' => 'Manager',
            'email' => 'manager2@example.com',
            'phone' => '1234567891',
            'role' => 'manager',
            'restaurant_id' => $restaurant->id,
            'password' => bcrypt('password'),
            'module_access' => [],
        ]);

        $response = $this->actingAs($manager)->get('/manager/menu-items');

        $response->assertStatus(403);
    }

    public function test_manager_with_menu_access_can_create_menu_item(): void
    {
        ModuleService::seedDefaultModules();

        $restaurant = Restaurant::create([
            'name' => 'Test Restaurant',
            'slug' => 'test-restaurant',
            'status' => 'active',
            'enabled_modules' => ['menu'],
        ]);

        $restaurant->subscription()->create([
            'subscription_plan_id' => null,
            'status' => 'active',
            'billing_cycle' => 'monthly',
        ]);

        $category = Category::create([
            'restaurant_id' => $restaurant->id,
            'name' => 'Pizza',
            'slug' => 'pizza',
            'is_active' => true,
            'sort_order' => 1,
        ]);

        $manager = User::create([
            'name' => 'Manager',
            'email' => 'manager3@example.com',
            'phone' => '1234567892',
            'role' => 'manager',
            'restaurant_id' => $restaurant->id,
            'password' => bcrypt('password'),
            'module_access' => ['menu'],
        ]);

        $response = $this->actingAs($manager)->post('/manager/menu-items', [
            'name' => 'New Pizza',
            'category_id' => $category->id,
            'price' => 500,
            'available' => '1',
            'track_stock' => '1',
            'stock_quantity' => 20,
            'low_stock_threshold' => 5,
        ]);

        $response->assertRedirect('/manager/menu-items');
        $this->assertDatabaseHas('menu_items', [
            'name' => 'New Pizza',
            'category_id' => $category->id,
            'restaurant_id' => $restaurant->id,
        ]);
    }
}
