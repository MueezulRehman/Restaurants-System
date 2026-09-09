<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\MenuItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Restaurant;
use App\Models\StockAdjustment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Tests\TestCase;

class SalesReturnTest extends TestCase
{
    use RefreshDatabase;
    use WithoutMiddleware;

    public function test_cash_return_restores_tracked_stock_and_records_cashbook_entry(): void
    {
        [$user, $orderItem, $menuItem] = $this->salesFixture();

        $this->actingAs($user)->post(route('manager.sales-returns.store'), [
            'order_item_id' => $orderItem->id,
            'quantity' => 2,
            'refund_method' => 'cash',
            'reason' => 'Damaged item',
        ])->assertRedirect();

        $this->assertEquals(7, (float) $menuItem->fresh()->stock_quantity);
        $this->assertDatabaseHas('sales_returns', [
            'order_item_id' => $orderItem->id,
            'quantity' => 2,
            'amount' => 200,
            'refund_method' => 'cash',
        ]);
        $this->assertDatabaseHas('cashbook', [
            'order_id' => $orderItem->order_id,
            'type' => 'out',
            'amount' => 200,
            'source' => 'sales_return',
        ]);
        $this->assertDatabaseHas('stock_adjustments', [
            'reason' => 'return',
            'change_quantity' => 2,
        ]);
    }

    public function test_return_cannot_exceed_quantity_sold(): void
    {
        [$user, $orderItem] = $this->salesFixture();

        $this->actingAs($user)->post(route('manager.sales-returns.store'), [
            'order_item_id' => $orderItem->id,
            'quantity' => 99,
            'refund_method' => 'cash',
        ])->assertStatus(422);

        $this->assertDatabaseCount('sales_returns', 0);
    }

    private function salesFixture(): array
    {
        $restaurant = Restaurant::create([
            'name' => 'Return Shop',
            'slug' => 'return-shop',
            'status' => 'active',
            'enabled_modules' => ['pos', 'sales-returns', 'stock', 'cashbook'],
        ]);
        $user = User::create([
            'name' => 'Manager',
            'email' => 'returns@example.com',
            'phone' => '03001234567',
            'role' => 'manager',
            'restaurant_id' => $restaurant->id,
            'password' => bcrypt('password'),
            'module_access' => ['pos', 'sales-returns', 'stock', 'cashbook'],
        ]);
        $category = Category::create(['restaurant_id' => $restaurant->id, 'name' => 'Accessories', 'is_active' => true]);
        $menuItem = MenuItem::create([
            'restaurant_id' => $restaurant->id,
            'category_id' => $category->id,
            'name' => 'Phone Case',
            'price' => 100,
            'track_stock' => true,
            'stock_quantity' => 5,
            'is_available' => true,
        ]);
        $order = Order::create([
            'restaurant_id' => $restaurant->id,
            'order_number' => 'RET-0001',
            'tracking_token' => (string) \Illuminate\Support\Str::uuid(),
            'order_type' => 'takeaway',
            'status' => 'delivered',
            'customer_name' => 'Walk-in',
            'customer_phone' => '03000000000',
            'subtotal' => 300,
            'total' => 300,
            'payment_method' => 'cash',
        ]);
        $orderItem = OrderItem::create([
            'order_id' => $order->id,
            'item_type' => 'menu_item',
            'menu_item_id' => $menuItem->id,
            'item_name' => 'Phone Case',
            'quantity' => 3,
            'unit_price' => 100,
            'total_price' => 300,
        ]);
        StockAdjustment::create([
            'restaurant_id' => $restaurant->id,
            'menu_item_id' => $menuItem->id,
            'user_id' => $user->id,
            'quantity_before' => 8,
            'quantity_after' => 5,
            'change_quantity' => -3,
            'reason' => 'sale',
            'reference_id' => $order->id,
        ]);

        return [$user, $orderItem, $menuItem];
    }
}
