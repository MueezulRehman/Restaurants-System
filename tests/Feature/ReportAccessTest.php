<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Category;
use App\Models\MenuItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Report;
use App\Models\Restaurant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_manager_can_view_a_report_for_a_module_they_have_access_to(): void
    {
        $restaurant = Restaurant::create([
            'name' => 'Report Access Test',
            'slug' => 'report-access-test',
            'status' => 'active',
            'enabled_modules' => ['reports', 'orders'],
        ]);
        $restaurant->subscription()->create(['status' => 'active', 'billing_cycle' => 'monthly']);

        $user = User::create([
            'name' => 'Report Manager',
            'email' => 'report-manager@example.com',
            'phone' => '1234567892',
            'role' => 'manager',
            'restaurant_id' => $restaurant->id,
            'password' => bcrypt('password'),
            'module_access' => ['reports', 'orders'],
        ]);

        $this->actingAs($user);

        $report = Report::create([
            'restaurant_id' => $restaurant->id,
            'user_id' => $user->id,
            'type' => 'orders',
            'name' => 'Orders summary',
            'filters' => ['date_from' => '2026-07-01', 'date_to' => '2026-07-12'],
            'data_snapshot' => ['orders' => 5],
            'generated_at' => now(),
        ]);

        $response = $this->get(route('manager.reports.show', $report));

        $response->assertOk();
    }

    public function test_report_creation_page_lists_types_for_enabled_business_modules(): void
    {
        $restaurant = Restaurant::create([
            'name' => 'Report Types Test',
            'slug' => 'report-types-test',
            'status' => 'active',
            'enabled_modules' => ['reports', 'orders', 'stock'],
        ]);
        $restaurant->subscription()->create(['status' => 'active', 'billing_cycle' => 'monthly']);

        $user = User::create([
            'name' => 'Limited Manager',
            'email' => 'limited-manager@example.com',
            'phone' => '1234567893',
            'role' => 'manager',
            'restaurant_id' => $restaurant->id,
            'password' => bcrypt('password'),
            'module_access' => ['reports', 'orders'],
        ]);

        $this->actingAs($user);

        $response = $this->get(route('manager.reports.create'));

        $response->assertOk();
        $response->assertSee('Orders');
        $response->assertSee('Sales');
        $response->assertSee('Inventory');
        $response->assertDontSee('Financial');
    }

    public function test_sales_report_can_be_scoped_to_a_branch(): void
    {
        $restaurant = Restaurant::create([
            'name' => 'Branch Report Test',
            'slug' => 'branch-report-test',
            'status' => 'active',
            'enabled_modules' => ['reports', 'orders', 'pos'],
        ]);
        $restaurant->subscription()->create(['status' => 'active', 'billing_cycle' => 'monthly']);

        $branchA = Branch::create(['restaurant_id' => $restaurant->id, 'name' => 'Downtown', 'code' => 'DT', 'is_active' => true]);
        $branchB = Branch::create(['restaurant_id' => $restaurant->id, 'name' => 'Uptown', 'code' => 'UT', 'is_active' => true]);

        $user = User::create([
            'name' => 'Branch Manager',
            'email' => 'branch-manager@example.com',
            'phone' => '1234567894',
            'role' => 'manager',
            'restaurant_id' => $restaurant->id,
            'branch_id' => $branchA->id,
            'password' => bcrypt('password'),
            'module_access' => ['reports', 'orders', 'pos'],
        ]);

        $category = Category::create(['restaurant_id' => $restaurant->id, 'name' => 'Meals', 'slug' => 'meals', 'is_active' => true]);
        $menuItem = MenuItem::create(['restaurant_id' => $restaurant->id, 'category_id' => $category->id, 'name' => 'Burger', 'price' => 220, 'is_available' => true, 'track_stock' => false]);

        $branchOrder = Order::create([
            'restaurant_id' => $restaurant->id,
            'branch_id' => $branchA->id,
            'customer_name' => 'Branch A Customer',
            'customer_phone' => '1111111111',
            'total' => 220,
            'status' => 'delivered',
            'payment_method' => 'cash',
            'subtotal' => 220,
        ]);
        OrderItem::create([
            'order_id' => $branchOrder->id,
            'item_type' => 'menu_item',
            'menu_item_id' => $menuItem->id,
            'item_name' => $menuItem->name,
            'quantity' => 1,
            'unit_price' => 220,
            'total_price' => 220,
        ]);

        $otherOrder = Order::create([
            'restaurant_id' => $restaurant->id,
            'branch_id' => $branchB->id,
            'customer_name' => 'Branch B Customer',
            'customer_phone' => '2222222222',
            'total' => 400,
            'status' => 'delivered',
            'payment_method' => 'cash',
            'subtotal' => 400,
        ]);
        OrderItem::create([
            'order_id' => $otherOrder->id,
            'item_type' => 'menu_item',
            'menu_item_id' => $menuItem->id,
            'item_name' => $menuItem->name,
            'quantity' => 2,
            'unit_price' => 200,
            'total_price' => 400,
        ]);

        $this->actingAs($user);

        $response = $this->post(route('manager.reports.store'), [
            'type' => 'sales',
            'name' => 'Branch A sales',
            'date_from' => now()->subDay()->format('Y-m-d'),
            'date_to' => now()->addDay()->format('Y-m-d'),
            'branch_id' => $branchA->id,
        ]);

        $response->assertRedirect();

        $report = Report::latest()->first();
        $this->assertNotNull($report);
        $this->assertSame((string) $branchA->id, (string) ($report->filters['branch_id'] ?? null));
        $this->assertSame(1, (int) ($report->data_snapshot['order_count'] ?? 0));
        $this->assertSame(220.0, (float) ($report->data_snapshot['total_sales'] ?? 0));
    }

    public function test_branch_can_be_created_for_the_current_restaurant(): void
    {
        $restaurant = Restaurant::create([
            'name' => 'Branch CRUD Test',
            'slug' => 'branch-crud-test',
            'status' => 'active',
            'enabled_modules' => ['stock-transfers'],
        ]);
        $restaurant->subscription()->create(['status' => 'active', 'billing_cycle' => 'monthly']);

        $user = User::create([
            'name' => 'Branch Admin',
            'email' => 'branch-admin@example.com',
            'phone' => '1234567895',
            'role' => 'admin',
            'restaurant_id' => $restaurant->id,
            'password' => bcrypt('password'),
        ]);

        $this->actingAs($user);

        $response = $this->post(route('manager.branches.store'), [
            'name' => 'Downtown',
            'code' => 'DT',
            'address' => 'Main Street',
            'phone' => '03001234567',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('branches', [
            'restaurant_id' => $restaurant->id,
            'name' => 'Downtown',
            'code' => 'DT',
            'is_active' => true,
        ]);
    }

    public function test_manager_cannot_open_report_pages_when_business_module_is_disabled(): void
    {
        $restaurant = Restaurant::create([
            'name' => 'Report Access Guard',
            'slug' => 'report-access-guard',
            'status' => 'active',
            'enabled_modules' => ['orders'],
        ]);
        $restaurant->subscription()->create(['status' => 'active', 'billing_cycle' => 'monthly']);

        $user = User::create([
            'name' => 'Restricted Manager',
            'email' => 'restricted-manager@example.com',
            'phone' => '1234567896',
            'role' => 'manager',
            'restaurant_id' => $restaurant->id,
            'password' => bcrypt('password'),
            'module_access' => ['orders'],
        ]);

        $this->actingAs($user);

        $response = $this->get(route('manager.reports.create'));

        $response->assertStatus(403);
    }
}
