<?php

namespace Tests\Feature;

use App\Http\Controllers\Admin\PosController;
use App\Models\Category;
use App\Models\Customer;
use App\Models\CustomerBalanceTransaction;
use App\Models\MenuItem;
use App\Models\Order;
use App\Models\Restaurant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Tests\TestCase;

class PosShortPaymentBehaviorTest extends TestCase
{
    use RefreshDatabase;

    public function test_accept_short_payment_without_creating_debt_when_allowed_and_confirmed(): void
    {
        $restaurant = Restaurant::create([
            'name' => 'ShortPay Allowed',
            'slug' => 'shortpay-allowed',
            'status' => 'active',
            'plan' => 'basic',
            'pos_allow_short_payment_without_debt' => true,
            'pos_short_payment_threshold' => 10,
        ]);

        $user = User::factory()->create([
            'name' => 'POS User',
            'phone' => '7777777777',
            'role' => 'admin',
            'restaurant_id' => $restaurant->id,
        ]);

        $category = Category::create(['restaurant_id' => $restaurant->id, 'name' => 'Misc', 'slug' => 'misc', 'is_active' => true]);

        $item = MenuItem::create(['restaurant_id' => $restaurant->id, 'category_id' => $category->id, 'name' => 'Item A', 'price' => 110, 'is_available' => true, 'track_stock' => false]);

        $this->actingAs($user, 'web');

        $request = Request::create('/manager/pos/checkout', 'POST', [
            'order_type' => 'takeaway',
            'payment_method' => 'cash',
            'amount_received' => 100,
            // cashier confirms accepting short payment without debt
            'accept_short_payment_without_debt' => 1,
            'customer_name' => 'Walk-in',
            'cart' => [['type' => 'menu_item', 'id' => $item->id, 'quantity' => 1]],
        ]);

        $response = app(PosController::class)->checkout($request);

        $this->assertTrue($response->isRedirect());

        $order = Order::where('restaurant_id', $restaurant->id)->latest()->first();
        $this->assertNotNull($order);
        $this->assertSame(100.0, (float) $order->amount_received);

        $this->assertSame(0, CustomerBalanceTransaction::count());
    }

    public function test_shortfall_is_recorded_as_customer_debt_when_setting_off(): void
    {
        $restaurant = Restaurant::create([
            'name' => 'ShortPay Disabled',
            'slug' => 'shortpay-disabled',
            'status' => 'active',
            'plan' => 'basic',
            'pos_allow_short_payment_without_debt' => false,
            'pos_short_payment_threshold' => 10,
        ]);

        $user = User::factory()->create([
            'name' => 'POS User 2',
            'phone' => '8888888888',
            'role' => 'admin',
            'restaurant_id' => $restaurant->id,
        ]);

        $category = Category::create(['restaurant_id' => $restaurant->id, 'name' => 'Meals', 'slug' => 'meals', 'is_active' => true]);

        $item = MenuItem::create(['restaurant_id' => $restaurant->id, 'category_id' => $category->id, 'name' => 'Burger', 'price' => 110, 'is_available' => true, 'track_stock' => false]);

        $customer = Customer::create(['restaurant_id' => $restaurant->id, 'name' => 'Bilal', 'phone' => '9999999999', 'password' => bcrypt('secret'), 'balance' => 0]);

        $this->actingAs($user, 'web');

        $request = Request::create('/manager/pos/checkout', 'POST', [
            'order_type' => 'takeaway',
            'payment_method' => 'cash',
            'amount_received' => 100,
            'customer_name' => $customer->name,
            'customer_phone' => $customer->phone,
            'cart' => [['type' => 'menu_item', 'id' => $item->id, 'quantity' => 1]],
        ]);

        $response = app(PosController::class)->checkout($request);

        $this->assertTrue($response->isRedirect());

        $customer->refresh();
        $this->assertSame(10.0, (float) $customer->balance);
        $this->assertSame(1, $customer->balanceTransactions()->count());
    }
}
