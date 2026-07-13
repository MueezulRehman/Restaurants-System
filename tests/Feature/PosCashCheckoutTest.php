<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Http\Controllers\Admin\PosController;
use App\Models\Customer;
use App\Models\Medicine;
use App\Models\MedicineBatch;
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

    public function test_pos_checkout_adds_partial_payment_to_customer_balance(): void
    {
        $restaurant = Restaurant::create([
            'name' => 'Credit POS Restaurant',
            'slug' => 'credit-pos-restaurant',
            'status' => 'active',
            'plan' => 'basic',
        ]);

        $user = User::factory()->create([
            'name' => 'Credit POS User',
            'phone' => '3333333333',
            'role' => 'admin',
            'restaurant_id' => $restaurant->id,
        ]);

        $category = Category::create([
            'restaurant_id' => $restaurant->id,
            'name' => 'Meals',
            'slug' => 'meals',
            'is_active' => true,
        ]);

        $menuItem = MenuItem::create([
            'restaurant_id' => $restaurant->id,
            'category_id' => $category->id,
            'name' => 'Burger',
            'price' => 250,
            'is_available' => true,
            'track_stock' => false,
        ]);

        $customer = Customer::create([
            'restaurant_id' => $restaurant->id,
            'name' => 'Ayesha',
            'phone' => '4444444444',
            'password' => bcrypt('secret'),
            'balance' => 0,
        ]);

        $this->actingAs($user, 'web');

        $request = Request::create('/manager/pos/checkout', 'POST', [
            'order_type' => 'takeaway',
            'payment_method' => 'cash',
            'amount_received' => 100,
            'customer_name' => 'Ayesha',
            'customer_phone' => $customer->phone,
            'cart' => [[
                'type' => 'menu_item',
                'id' => $menuItem->id,
                'quantity' => 1,
            ]],
        ]);

        $response = app(PosController::class)->checkout($request);

        $this->assertTrue($response->isRedirect());

        $customer->refresh();
        $this->assertSame(150.0, (float) $customer->balance);
        $this->assertSame(1, $customer->balanceTransactions()->count());
    }

    public function test_pos_checkout_preserves_cart_when_batch_is_expired(): void
    {
        $restaurant = Restaurant::create([
            'name' => 'Expired Batch POS',
            'slug' => 'expired-batch-pos',
            'status' => 'active',
            'plan' => 'basic',
        ]);

        $user = User::factory()->create([
            'name' => 'Expired Batch User',
            'phone' => '5555555555',
            'role' => 'admin',
            'restaurant_id' => $restaurant->id,
        ]);

        $medicine = Medicine::create([
            'restaurant_id' => $restaurant->id,
            'name' => 'Amox',
            'sku' => 'AMX-001',
            'requires_prescription' => false,
            'track_stock' => false,
        ]);

        $batch = MedicineBatch::create([
            'restaurant_id' => $restaurant->id,
            'medicine_id' => $medicine->id,
            'batch_number' => 'EXP-1',
            'expiry_date' => now()->subDay(),
            'selling_price' => 90,
            'quantity' => 5,
        ]);

        $this->actingAs($user, 'web');

        $request = Request::create('/manager/pos/checkout', 'POST', [
            'order_type' => 'takeaway',
            'payment_method' => 'cash',
            'amount_received' => 100,
            'customer_name' => 'Walk-in Customer',
            'cart' => [[
                'type' => 'medicine_batch',
                'id' => $batch->id,
                'quantity' => 1,
            ]],
        ]);

        $response = app(PosController::class)->checkout($request);

        $this->assertTrue($response->isRedirect());
        $this->assertSame([[
            'type' => 'medicine_batch',
            'id' => $batch->id,
            'quantity' => 1,
        ]], session('pos_last_cart'));
        $this->assertSame(['type' => 'medicine_batch', 'id' => $batch->id], session('pos_error_highlight'));
    }
}
