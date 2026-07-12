<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Http\Controllers\Admin\PosController;
use App\Models\MenuItem;
use App\Models\Order;
use App\Models\Restaurant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Tests\TestCase;

class PosCashCheckoutTest extends TestCase
{
    use RefreshDatabase;

    public function test_pos_checkout_records_cash_tendered_and_change(): void
    {
        $restaurant = Restaurant::create([
            'name' => 'Cash Test Restaurant',
            'slug' => 'cash-test-restaurant',
            'status' => 'active',
            'plan' => 'basic',
        ]);

        $user = User::factory()->create([
            'name' => 'Cash POS User',
            'phone' => '2222222222',
            'role' => 'admin',
            'restaurant_id' => $restaurant->id,
        ]);

        $category = Category::create([
            'restaurant_id' => $restaurant->id,
            'name' => 'Drinks',
            'slug' => 'drinks',
            'is_active' => true,
        ]);

        $menuItem = MenuItem::create([
            'restaurant_id' => $restaurant->id,
            'category_id' => $category->id,
            'name' => 'Tea',
            'price' => 120,
            'is_available' => true,
            'track_stock' => false,
        ]);

        $this->actingAs($user, 'web');

        $request = Request::create('/manager/pos/checkout', 'POST', [
            'order_type' => 'takeaway',
            'payment_method' => 'cash',
            'amount_received' => 300,
            'customer_name' => 'Walk-in Customer',
            'cart' => [[
                'type' => 'menu_item',
                'id' => $menuItem->id,
                'quantity' => 1,
            ]],
        ]);

        $response = app(PosController::class)->checkout($request);

        $this->assertTrue($response->isRedirect());
        $this->assertStringContainsString('print=1', $response->getTargetUrl());

        $order = Order::where('restaurant_id', $restaurant->id)->latest()->first();
        $this->assertNotNull($order);
        $this->assertSame(300.0, (float) $order->amount_received);
        $this->assertSame(180.0, (float) $order->change_amount);
        $this->assertSame('cash', $order->payment_method);
    }
}
