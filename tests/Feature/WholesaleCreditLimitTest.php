<?php

namespace Tests\Feature;

use App\Http\Controllers\Admin\PosController;
use App\Models\Category;
use App\Models\Customer;
use App\Models\MenuItem;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Tests\TestCase;

class WholesaleCreditLimitTest extends TestCase
{
    use RefreshDatabase;

    public function test_pos_rejects_a_sale_that_exceeds_customer_credit_limit(): void
    {
        $restaurant = \App\Models\Restaurant::create([
            'name' => 'Wholesale Test',
            'slug' => 'wholesale-test',
            'status' => 'active',
            'plan' => 'basic',
        ]);
        $user = User::factory()->create([
            'role' => 'admin',
            'restaurant_id' => $restaurant->id,
            'phone' => '03000000002',
        ]);
        $category = Category::create([
            'restaurant_id' => $restaurant->id,
            'name' => 'Bulk Goods',
            'slug' => 'bulk-goods',
            'is_active' => true,
        ]);
        $item = MenuItem::create([
            'restaurant_id' => $restaurant->id,
            'category_id' => $category->id,
            'name' => 'Bulk Flour',
            'price' => 150,
            'is_available' => true,
            'track_stock' => false,
        ]);
        $customer = Customer::create([
            'restaurant_id' => $restaurant->id,
            'name' => 'Wholesale Buyer',
            'phone' => '03000000001',
            'password' => bcrypt('secret'),
            'balance' => 75,
            'credit_limit' => 100,
        ]);

        $this->actingAs($user, 'web');
        $this->assertSame(100.0, (float) $customer->credit_limit);
        $this->assertSame(150.0, (float) $item->price);
        $request = Request::create('/manager/pos/checkout', 'POST', [
            'order_type' => 'takeaway',
            'payment_method' => 'cash',
            'amount_received' => 0,
            'customer_id' => $customer->id,
            'customer_name' => $customer->name,
            'customer_phone' => $customer->phone,
            'cart' => [['type' => 'menu_item', 'id' => $item->id, 'quantity' => 1]],
        ]);

        $response = app(PosController::class)->checkout($request);
        $this->assertTrue($response->isRedirect());
        $this->assertSame(0, Order::where('restaurant_id', $restaurant->id)->count());
        $this->assertSame(75.0, (float) $customer->fresh()->balance);
    }
}
