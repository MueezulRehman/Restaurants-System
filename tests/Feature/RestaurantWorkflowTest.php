<?php

namespace Tests\Feature;

use App\Models\MenuItem;
use App\Models\Order;
use App\Models\Category;
use App\Models\Restaurant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class RestaurantWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_manager_login_is_blocked_for_inactive_restaurant(): void
    {
        $restaurant = Restaurant::create([
            'name' => 'Pizza Club',
            'slug' => 'pizza-club',
            'status' => 'suspended',
        ]);

        User::create([
            'name' => 'Manager',
            'phone' => '03000000000',
            'email' => 'manager@example.com',
            'role' => 'manager',
            'password' => Hash::make('password123'),
            'restaurant_id' => $restaurant->id,
            'is_active' => true,
        ]);

        $response = $this->from('/manager/login')->post('/manager/login', [
            'phone' => '03000000000',
            'password' => 'password123',
        ]);

        $response->assertRedirect('/manager/login');
        $response->assertSessionHasErrors('phone');
        $this->assertGuest();
    }

    public function test_checkout_uses_the_selected_restaurant_id_for_the_order(): void
    {
        $restaurant = Restaurant::create([
            'name' => 'Pizza Club',
            'slug' => 'pizza-club',
            'status' => 'active',
        ]);

        $category = Category::create([
            'restaurant_id' => $restaurant->id,
            'name' => 'Pizza',
            'slug' => 'pizza',
            'is_active' => true,
            'sort_order' => 1,
        ]);

        $item = MenuItem::create([
            'restaurant_id' => $restaurant->id,
            'category_id' => $category->id,
            'name' => 'Margherita',
            'price' => 500,
            'is_available' => true,
            'has_sizes' => false,
        ]);

        $response = $this->withSession(['current_restaurant_id' => $restaurant->id])
            ->post('/checkout', [
                'order_type' => 'takeaway',
                'customer_name' => 'Ali',
                'customer_phone' => '03460000000',
                'payment_method' => 'cash',
                'notes' => 'none',
                'restaurant_id' => $restaurant->id,
                'cart' => [[
                    'type' => 'menu_item',
                    'id' => $item->id,
                    'quantity' => 1,
                    'size_label' => null,
                    'topping_ids' => [],
                    'special_request' => null,
                ]],
            ]);

        $response->assertRedirect();
        $order = Order::latest()->first();
        $this->assertNotNull($order);
        $this->assertSame($restaurant->id, $order->restaurant_id);
    }
}
