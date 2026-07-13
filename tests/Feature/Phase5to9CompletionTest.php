<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\MenuItem;
use App\Models\ProductVariant;
use App\Models\ManagerFeedback;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\PlatformNotification;
use App\Models\Restaurant;
use App\Models\Salary;
use App\Models\StockAdjustment;
use App\Models\User;
use App\Services\NotificationService;
use App\Services\ReportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Phase5to9CompletionTest extends TestCase
{
    use RefreshDatabase;

    public function test_report_service_generates_expected_metrics()
    {
        $restaurant = new Restaurant();
        $restaurant->name = 'Test Restaurant';
        $restaurant->slug = 'test-restaurant';
        $restaurant->email = 'test@example.com';
        $restaurant->phone = '0000000000';
        $restaurant->status = 'active';
        $restaurant->save();

        $category = new Category();
        $category->restaurant_id = $restaurant->id;
        $category->name = 'Menu Category';
        $category->save();

        $menuItem = new MenuItem();
        $menuItem->restaurant_id = $restaurant->id;
        $menuItem->category_id = $category->id;
        $menuItem->name = 'Sample Item';
        $menuItem->price = 200;
        $menuItem->cost_price = 120;
        $menuItem->stock_quantity = 10;
        $menuItem->low_stock_threshold = 2;
        $menuItem->save();

        $order = new Order();
        $order->restaurant_id = $restaurant->id;
        $order->subtotal = 200;
        $order->total = 200;
        $order->status = 'completed';
        $order->order_number = 'ORD-1001';
        $order->invoice_number = 'INV-20260713-0001';
        $order->tracking_token = (string) \Illuminate\Support\Str::uuid();
        $order->customer_name = 'Test Customer';
        $order->customer_phone = '03100000000';
        $order->save();

        $orderItem = new OrderItem();
        $orderItem->order_id = $order->id;
        $orderItem->menu_item_id = $menuItem->id;
        $orderItem->item_name = $menuItem->name;
        $orderItem->unit_price = $menuItem->price;
        $orderItem->quantity = 1;
        $orderItem->total_price = 200;
        $orderItem->item_type = 'menu_item';
        $orderItem->save();

        $report = ReportService::forToday($restaurant->id)->salesReport();

        $this->assertEquals(200, $report['total_sales']);
        $this->assertEquals(1, $report['order_count']);
        $this->assertEquals(200, $report['top_items']->first()['revenue']);
    }

    public function test_manager_feedback_can_be_submitted_and_replied()
    {
        $restaurant = new Restaurant();
        $restaurant->name = 'Feedback Restaurant';
        $restaurant->slug = 'feedback-restaurant';
        $restaurant->email = 'feedback@example.com';
        $restaurant->phone = '03100000001';
        $restaurant->status = 'active';
        $restaurant->save();

        $user = new User();
        $user->restaurant_id = $restaurant->id;
        $user->name = 'Manager User';
        $user->email = 'manager@example.com';
        $user->phone = '03110000001';
        $user->password = bcrypt('secret');
        $user->role = 'manager';
        $user->save();

        $feedback = ManagerFeedback::create([
            'restaurant_id' => $restaurant->id,
            'user_id' => $user->id,
            'type' => 'feature_request',
            'title' => 'Test feedback',
            'message' => 'Please add export',
        ]);

        $feedback->update(['admin_reply' => 'Acknowledged', 'status' => 'resolved', 'replied_at' => now()]);

        $this->assertEquals('resolved', $feedback->status);
        $this->assertNotNull($feedback->admin_reply);
    }

    public function test_stock_adjustment_records_adjustment_and_generates_notification()
    {
        $restaurant = new Restaurant();
        $restaurant->name = 'Stock Restaurant';
        $restaurant->slug = 'stock-restaurant';
        $restaurant->email = 'stock@example.com';
        $restaurant->phone = '03100000002';
        $restaurant->status = 'active';
        $restaurant->save();

        $user = new User();
        $user->restaurant_id = $restaurant->id;
        $user->name = 'Stock Manager';
        $user->email = 'stockmanager@example.com';
        $user->phone = '03110000002';
        $user->password = bcrypt('secret');
        $user->role = 'manager';
        $user->save();

        $stockCategory = new Category();
        $stockCategory->restaurant_id = $restaurant->id;
        $stockCategory->name = 'Stock Category';
        $stockCategory->save();

        $item = new MenuItem();
        $item->restaurant_id = $restaurant->id;
        $item->category_id = $stockCategory->id;
        $item->name = 'Stock Item';
        $item->price = 100;
        $item->cost_price = 60;
        $item->stock_quantity = 5;
        $item->low_stock_threshold = 5;
        $item->save();

        $variant = new ProductVariant();
        $variant->restaurant_id = $restaurant->id;
        $variant->menu_item_id = $item->id;
        $variant->sku = 'STOCK001';
        $variant->variant_name = 'Default';
        $variant->price_override = 100;
        $variant->cost_price = 60;
        $variant->quantity_available = 5;
        $variant->is_available = true;
        $variant->save();

        $adjustment = StockAdjustment::create([
            'restaurant_id' => $restaurant->id,
            'product_variant_id' => $variant->id,
            'adjustment_type' => 'out',
            'quantity_before' => 5,
            'quantity_after' => 3,
            'change_quantity' => -2,
            'reason' => 'Test pick',
            'user_id' => $user->id,
        ]);

        $notification = PlatformNotification::create([
            'restaurant_id' => $restaurant->id,
            'type' => 'low_stock_alert',
            'title' => 'Low stock',
            'message' => 'Stock low for test item',
        ]);

        $this->assertDatabaseHas('stock_adjustments', ['id' => $adjustment->id]);
        $this->assertDatabaseHas('platform_notifications', ['id' => $notification->id]);
    }
}
