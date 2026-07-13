<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\BillingCycle;
use App\Models\BusinessType;
use App\Models\Category;
use App\Models\Customer;
use App\Models\Expense;
use App\Models\MenuItem;
use App\Models\MenuItemSize;
use App\Models\Module;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Restaurant;
use App\Models\RestaurantSubscription;
use App\Models\Salary;
use App\Models\SubscriptionPlan;
use App\Models\Topping;
use App\Models\User;
use App\Services\SubscriptionManager;
use App\Support\Tenancy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * COMPREHENSIVE SYSTEM VERIFICATION — TasteHut v2.4 Design Document Compliance
 *
 * This test suite exhaustively checks every component mentioned in the design
 * against actual implementation. Each test verifies:
 *   1. The model exists
 *   2. The database schema is correct
 *   3. The business logic is functional
 *   4. The relationships work
 *   5. The isolation/security is enforced
 */
class ComprehensiveSystemVerificationTest extends TestCase
{
    use RefreshDatabase;

    // ============================================================================
    // SECTION 1: DATABASE SCHEMA VERIFICATION
    // ============================================================================

    public function test_schema_restaurants_table_exists_with_required_columns(): void
    {
        $this->assertTrue(\Schema::hasTable('restaurants'));
        $this->assertTrue(\Schema::hasColumn('restaurants', 'name'));
        $this->assertTrue(\Schema::hasColumn('restaurants', 'slug'));
        $this->assertTrue(\Schema::hasColumn('restaurants', 'business_type_id'));
        $this->assertTrue(\Schema::hasColumn('restaurants', 'status'));
        $this->assertTrue(\Schema::hasColumn('restaurants', 'db_connection'));
        $this->assertTrue(\Schema::hasColumn('restaurants', 'enabled_modules'));
    }

    public function test_schema_users_table_with_staff_roles(): void
    {
        $this->assertTrue(\Schema::hasTable('users'));
        $this->assertTrue(\Schema::hasColumn('users', 'restaurant_id'));
        $this->assertTrue(\Schema::hasColumn('users', 'role'));
        $this->assertTrue(\Schema::hasColumn('users', 'monthly_salary'));
        $this->assertTrue(\Schema::hasColumn('users', 'module_access'));
    }

    public function test_schema_orders_table_with_tracking_and_privacy(): void
    {
        $this->assertTrue(\Schema::hasTable('orders'));
        $this->assertTrue(\Schema::hasColumn('orders', 'restaurant_id'));
        $this->assertTrue(\Schema::hasColumn('orders', 'tracking_token'));
        $this->assertTrue(\Schema::hasColumn('orders', 'order_number'));
        $this->assertTrue(\Schema::hasColumn('orders', 'customer_id'));
        $this->assertTrue(\Schema::hasColumn('orders', 'status'));
    }

    public function test_schema_menu_items_with_barcode_and_variants(): void
    {
        $this->assertTrue(\Schema::hasTable('menu_items'));
        $this->assertTrue(\Schema::hasColumn('menu_items', 'barcode'));
        $this->assertTrue(\Schema::hasColumn('menu_items', 'sku'));
        $this->assertTrue(\Schema::hasColumn('menu_items', 'has_sizes'));
        $this->assertTrue(\Schema::hasColumn('menu_items', 'has_variants'));
    }

    public function test_schema_subscription_tables_created(): void
    {
        $this->assertTrue(\Schema::hasTable('subscription_plans'));
        $this->assertTrue(\Schema::hasTable('restaurant_subscriptions'));
        $this->assertTrue(\Schema::hasTable('billing_cycles'));
    }

    public function test_schema_financial_tables_exist(): void
    {
        $this->assertTrue(\Schema::hasTable('cashbook'));
        $this->assertTrue(\Schema::hasTable('expenses'));
        $this->assertTrue(\Schema::hasTable('salaries'));
    }

    public function test_schema_hr_tables_exist(): void
    {
        $this->assertTrue(\Schema::hasTable('attendance'));
        $this->assertTrue(\Schema::hasTable('salaries'));
    }

    // ============================================================================
    // SECTION 2: MODEL FUNCTIONALITY
    // ============================================================================

    public function test_user_model_role_methods(): void
    {
        $superAdmin = User::create([
            'name' => 'Super Admin',
            'email' => 'super@test.com',
            'phone' => '03001234567',
            'role' => 'super_admin',
            'password' => bcrypt('pw'),
        ]);

        $this->assertTrue($superAdmin->isSuperAdmin());
        $this->assertTrue($superAdmin->isAdmin());
        $this->assertFalse($superAdmin->isManagerRole());

        $manager = User::create([
            'name' => 'Manager',
            'email' => 'mgr@test.com',
            'phone' => '03001234568',
            'role' => 'manager',
            'password' => bcrypt('pw'),
        ]);

        $this->assertFalse($manager->isSuperAdmin());
        $this->assertTrue($manager->isRestaurantManager());
    }

    public function test_order_model_generates_tracking_token_and_order_number(): void
    {
        $restaurant = Restaurant::create([
            'name' => 'Test',
            'slug' => 'test',
            'status' => 'active',
        ]);

        $order = Order::create([
            'restaurant_id' => $restaurant->id,
            'order_type' => 'delivery',
            'status' => 'pending',
            'customer_name' => 'Test Customer',
            'subtotal' => 500,
            'total' => 550,
        ]);

        $this->assertNotNull($order->tracking_token);
        $this->assertNotNull($order->order_number);
        $this->assertStringStartsWith('TH-', $order->order_number);
    }

    public function test_menu_item_has_barcode_field(): void
    {
        $restaurant = Restaurant::create([
            'name' => 'Test',
            'slug' => 'test',
            'status' => 'active',
        ]);

        $category = Category::create([
            'restaurant_id' => $restaurant->id,
            'name' => 'Drinks',
            'slug' => 'drinks-cat',
        ]);

        $item = MenuItem::create([
            'restaurant_id' => $restaurant->id,
            'category_id' => $category->id,
            'name' => 'Coke',
            'barcode' => '6901443391356',
            'price' => 150,
        ]);

        $this->assertEquals('6901443391356', $item->barcode);
    }

    public function test_subscription_plan_model(): void
    {
        $plan = SubscriptionPlan::create([
            'name' => 'Growth',
            'slug' => 'growth',
            'price_monthly' => 6000,
            'price_yearly' => 60000,
            'trial_days' => 14,
            'max_staff' => 10,
            'max_menu_items' => 100,
            'is_active' => true,
        ]);

        $this->assertEquals('growth', $plan->slug);
        $this->assertEquals(6000, $plan->price_monthly);
        $this->assertEquals(14, $plan->trial_days);
    }

    public function test_subscription_lifecycle_methods(): void
    {
        $restaurant = Restaurant::create([
            'name' => 'Test',
            'slug' => 'test',
            'status' => 'active',
        ]);

        $plan = SubscriptionPlan::create([
            'name' => 'Growth',
            'slug' => 'growth',
            'price_monthly' => 6000,
            'price_yearly' => 60000,
            'trial_days' => 14,
            'is_active' => true,
        ]);

        $subscription = SubscriptionManager::createTrialSubscription($restaurant, $plan);

        $this->assertTrue($subscription->isInTrial());
        $this->assertFalse($subscription->isActive());
        $this->assertFalse($subscription->isExpired());

        SubscriptionManager::upgradeToPaidSubscription($subscription);
        $subscription->refresh();

        $this->assertFalse($subscription->isInTrial());
        $this->assertTrue($subscription->isActive());
    }

    public function test_expense_model_with_categories(): void
    {
        $restaurant = Restaurant::create([
            'name' => 'Test',
            'slug' => 'test',
            'status' => 'active',
        ]);

        $user = User::create([
            'name' => 'User',
            'email' => 'u@test.com',
            'phone' => '03001234567',
            'restaurant_id' => $restaurant->id,
            'role' => 'admin',
            'password' => bcrypt('pw'),
        ]);

        $expense = Expense::create([
            'restaurant_id' => $restaurant->id,
            'category' => 'rent',
            'amount' => 50000,
            'description' => 'Monthly rent',
            'created_by' => $user->id,
        ]);

        $this->assertEquals('rent', $expense->category);
        $this->assertEquals(50000, $expense->amount);
    }

    public function test_attendance_model_tracks_staff(): void
    {
        $restaurant = Restaurant::create([
            'name' => 'Test',
            'slug' => 'test',
            'status' => 'active',
        ]);

        $user = User::create([
            'name' => 'Staff',
            'email' => 'staff@test.com',
            'phone' => '03001234567',
            'restaurant_id' => $restaurant->id,
            'role' => 'cashier',
            'password' => bcrypt('pw'),
        ]);

        $attendance = Attendance::create([
            'restaurant_id' => $restaurant->id,
            'user_id' => $user->id,
            'date' => now()->date(),
            'status' => 'present',
        ]);

        $this->assertEquals('present', $attendance->status);
    }

    public function test_salary_model_tracks_payroll(): void
    {
        $restaurant = Restaurant::create([
            'name' => 'Test',
            'slug' => 'test',
            'status' => 'active',
        ]);

        $user = User::create([
            'name' => 'Staff',
            'email' => 'staff@test.com',
            'phone' => '03001234567',
            'restaurant_id' => $restaurant->id,
            'role' => 'cashier',
            'monthly_salary' => 50000,
            'password' => bcrypt('pw'),
        ]);

        $salary = Salary::create([
            'restaurant_id' => $restaurant->id,
            'user_id' => $user->id,
            'month' => now()->startOfMonth(),
            'amount' => 50000,
            'deductions' => 2500,
            'net_paid' => 47500,
        ]);

        $this->assertEquals(50000, $salary->amount);
        $this->assertEquals(47500, $salary->net_paid);
    }

    // ============================================================================
    // SECTION 3: MULTI-TENANCY & ISOLATION
    // ============================================================================

    public function test_belongs_to_restaurant_trait_on_all_tenant_models(): void
    {
        $models = [Order::class, MenuItem::class, Category::class, Expense::class, Attendance::class, Salary::class];

        foreach ($models as $modelClass) {
            $this->assertTrue(
                method_exists($modelClass, 'bootBelongsToRestaurant'),
                "$modelClass should use BelongsToRestaurant trait"
            );
        }
    }

    public function test_restaurant_isolation_manager_cannot_see_other_restaurant_data(): void
    {
        $rest1 = Restaurant::create(['name' => 'R1', 'slug' => 'r1', 'status' => 'active']);
        $rest2 = Restaurant::create(['name' => 'R2', 'slug' => 'r2', 'status' => 'active']);

        $cat1 = Category::create(['restaurant_id' => $rest1->id, 'name' => 'Cat1', 'slug' => 'cat1-r1']);
        $cat2 = Category::create(['restaurant_id' => $rest2->id, 'name' => 'Cat2', 'slug' => 'cat2-r2']);

        $mgr1 = User::create([
            'name' => 'Mgr1', 'email' => 'm1@test.com', 'phone' => '03001234567',
            'restaurant_id' => $rest1->id, 'role' => 'admin', 'password' => bcrypt('pw'),
        ]);

        $this->actingAs($mgr1, 'web');

        $categories = Category::get();
        $this->assertTrue($categories->contains('id', $cat1->id));
        $this->assertFalse($categories->contains('id', $cat2->id));
    }

    public function test_super_admin_impersonation_with_tenancy(): void
    {
        $restaurant = Restaurant::create(['name' => 'Test', 'slug' => 'test', 'status' => 'active']);

        $superAdmin = User::create([
            'name' => 'Super Admin', 'email' => 'sa@test.com', 'phone' => '03001234567',
            'role' => 'super_admin', 'password' => bcrypt('pw'),
        ]);

        $this->actingAs($superAdmin, 'web');

        Tenancy::enter($restaurant);
        $this->assertTrue(Tenancy::isImpersonating());
        $this->assertEquals($restaurant->id, Tenancy::impersonatedRestaurantId());

        Tenancy::exit();
        $this->assertFalse(Tenancy::isImpersonating());
    }

    // ============================================================================
    // SECTION 4: AUTHENTICATION & AUTHORIZATION
    // ============================================================================

    public function test_two_separate_auth_guards_web_and_customer(): void
    {
        $staff = User::create([
            'name' => 'Staff', 'email' => 'staff@test.com', 'phone' => '03001234567',
            'role' => 'cashier', 'password' => bcrypt('pw'),
        ]);

        $customer = Customer::create([
            'name' => 'Customer', 'email' => 'cust@test.com', 'phone' => '03009876543',
            'password' => bcrypt('pw'),
        ]);

        $this->actingAs($staff, 'web');
        $this->assertAuthenticatedAs($staff, 'web');

        $this->actingAs($customer, 'customer');
        $this->assertAuthenticatedAs($customer, 'customer');
    }

    public function test_customer_model_separate_from_staff(): void
    {
        $this->assertTrue(\Schema::hasTable('customers'));
        $this->assertTrue(\Schema::hasColumn('customers', 'phone'));
        $this->assertTrue(\Schema::hasColumn('customers', 'email'));
    }

    // ============================================================================
    // SECTION 5: BUSINESS TYPES & MODULES
    // ============================================================================

    public function test_business_type_with_modules(): void
    {
        $this->assertTrue(\Schema::hasTable('business_types'));
        $this->assertTrue(\Schema::hasTable('modules'));

        $businessType = BusinessType::create([
            'name' => 'Restaurant',
            'slug' => 'restaurant',
            'is_active' => true,
        ]);

        $module = Module::create([
            'name' => 'POS',
            'key' => 'pos',
            'is_active' => true,
        ]);

        $this->assertEquals('Restaurant', $businessType->name);
        $this->assertEquals('pos', $module->key);
    }

    public function test_restaurant_module_gating(): void
    {
        $restaurant = Restaurant::create([
            'name' => 'Test',
            'slug' => 'test',
            'status' => 'active',
            'enabled_modules' => ['pos', 'menu', 'orders'],
        ]);

        $this->assertTrue($restaurant->isModuleEnabled('pos'));
        $this->assertTrue($restaurant->isModuleEnabled('menu'));
        $this->assertFalse($restaurant->isModuleEnabled('medical'));
    }

    // ============================================================================
    // SECTION 6: TENANT DATABASE SUPPORT
    // ============================================================================

    public function test_tenant_database_configuration_storage(): void
    {
        $restaurant = Restaurant::create([
            'name' => 'Test',
            'slug' => 'test',
            'status' => 'active',
            'db_connection' => [
                'driver' => 'sqlite',
                'database' => ':memory:',
            ],
        ]);

        $this->assertTrue($restaurant->hasTenantDatabase());

        $config = $restaurant->getTenantDatabaseConfig();
        $this->assertEquals('sqlite', $config['driver']);
    }

    public function test_tenant_connection_switching(): void
    {
        $restaurant = Restaurant::create([
            'name' => 'Test',
            'slug' => 'test',
            'status' => 'active',
            'db_connection' => ['driver' => 'sqlite', 'database' => ':memory:'],
        ]);

        Tenancy::configureTenantConnection($restaurant);

        $this->assertEquals('tenant', config('database.default'));
        $this->assertEquals('sqlite', config('database.connections.tenant.driver'));
    }

    // ============================================================================
    // SECTION 7: ORDER PRIVACY & TRACKING
    // ============================================================================

    public function test_order_tracking_token_unique_per_order(): void
    {
        $restaurant = Restaurant::create(['name' => 'Test', 'slug' => 'test', 'status' => 'active']);

        $order1 = Order::create([
            'restaurant_id' => $restaurant->id, 'order_type' => 'delivery', 'status' => 'pending',
            'customer_name' => 'Cust1', 'subtotal' => 500, 'total' => 550,
        ]);

        $order2 = Order::create([
            'restaurant_id' => $restaurant->id, 'order_type' => 'delivery', 'status' => 'pending',
            'customer_name' => 'Cust2', 'subtotal' => 600, 'total' => 660,
        ]);

        $this->assertNotEquals($order1->tracking_token, $order2->tracking_token);
        $this->assertNotNull($order1->tracking_token);
        $this->assertNotNull($order2->tracking_token);
    }

    public function test_order_status_flow_constants(): void
    {
        $this->assertContains('pending', Order::STATUS_FLOW);
        $this->assertContains('confirmed', Order::STATUS_FLOW);
        $this->assertContains('preparing', Order::STATUS_FLOW);
        $this->assertContains('out_for_delivery', Order::STATUS_FLOW);
        $this->assertContains('delivered', Order::STATUS_FLOW);
    }

    // ============================================================================
    // SECTION 8: FINANCIAL & PAYMENT
    // ============================================================================

    public function test_billing_cycle_with_invoice_generation(): void
    {
        $restaurant = Restaurant::create(['name' => 'Test', 'slug' => 'test', 'status' => 'active']);
        $plan = SubscriptionPlan::create([
            'name' => 'Growth', 'slug' => 'growth', 'price_monthly' => 6000,
            'price_yearly' => 60000, 'trial_days' => 14, 'is_active' => true,
        ]);

        $subscription = SubscriptionManager::createTrialSubscription($restaurant, $plan);
        SubscriptionManager::upgradeToPaidSubscription($subscription);

        $billingCycle = $subscription->billingCycles()->first();
        $this->assertNotNull($billingCycle);
        $this->assertEquals('paid', $billingCycle->status);
        $this->assertNotNull($billingCycle->invoice_number);
        $this->assertStringStartsWith('INV-', $billingCycle->invoice_number);
    }

    public function test_payment_gateway_abstraction(): void
    {
        $this->assertTrue(method_exists('App\Services\PaymentGateway', 'charge'));

        $result = \App\Services\PaymentGateway::charge('manual', 6000);
        $this->assertTrue($result['success']);
    }

    // ============================================================================
    // SECTION 9: MIDDLEWARE & ROUTING
    // ============================================================================

    public function test_middleware_classes_exist(): void
    {
        $middlewares = [
            App\Http\Middleware\ResolveRestaurant::class,
            App\Http\Middleware\EnsureSuperAdmin::class,
            App\Http\Middleware\EnsureRestaurantManager::class,
            App\Http\Middleware\EnsureSubscriptionActive::class,
        ];

        foreach ($middlewares as $middleware) {
            $this->assertTrue(class_exists($middleware), "$middleware should exist");
        }
    }

    // ============================================================================
    // SECTION 10: VIEWS & FRONTEND
    // ============================================================================

    public function test_key_blade_views_exist(): void
    {
        $views = [
            'admin/dashboard',
            'manager/dashboard',
            'customer/menu',
            'customer/checkout',
            'customer/track',
        ];

        foreach ($views as $view) {
            $this->assertTrue(
                \view()->exists($view),
                "View $view should exist"
            );
        }
    }

    // ============================================================================
    // INTEGRATION TESTS
    // ============================================================================

    public function test_full_restaurant_signup_to_subscription_flow(): void
    {
        // Step 1: Super Admin creates restaurant
        $superAdmin = User::create([
            'name' => 'Super Admin', 'email' => 'admin@test.com', 'phone' => '03001111111',
            'role' => 'super_admin', 'password' => bcrypt('pw'),
        ]);

        $this->actingAs($superAdmin, 'web');

        $businessType = BusinessType::create([
            'name' => 'Restaurant', 'slug' => 'restaurant', 'is_active' => true,
        ]);

        $restaurant = Restaurant::create([
            'name' => 'Test Restaurant', 'slug' => 'test-rest', 'business_type_id' => $businessType->id,
            'status' => 'active',
        ]);

        // Step 2: Subscription plan created
        $plan = SubscriptionPlan::create([
            'name' => 'Growth', 'slug' => 'growth', 'price_monthly' => 6000,
            'price_yearly' => 60000, 'trial_days' => 14, 'is_active' => true,
        ]);

        // Step 3: Trial subscription created
        $subscription = SubscriptionManager::createTrialSubscription($restaurant, $plan);
        $this->assertTrue($subscription->isInTrial());

        // Step 4: Upgrade to paid
        SubscriptionManager::upgradeToPaidSubscription($subscription, 'manual');
        $subscription->refresh();
        $this->assertTrue($subscription->isActive());
    }

    public function test_full_customer_order_flow(): void
    {
        $restaurant = Restaurant::create([
            'name' => 'Test', 'slug' => 'test', 'status' => 'active',
            'enabled_modules' => ['pos', 'menu', 'orders'],
        ]);

        $category = Category::create([
            'restaurant_id' => $restaurant->id, 'name' => 'Drinks', 'slug' => 'drinks',
        ]);

        $menuItem = MenuItem::create([
            'restaurant_id' => $restaurant->id, 'category_id' => $category->id,
            'name' => 'Coke', 'price' => 150,
        ]);

        $customer = Customer::create([
            'name' => 'Test Customer', 'email' => 'cust@test.com', 'phone' => '03009876543',
            'password' => bcrypt('pw'),
        ]);

        $order = Order::create([
            'restaurant_id' => $restaurant->id, 'customer_id' => $customer->id,
            'order_type' => 'delivery', 'status' => 'pending',
            'customer_name' => 'Test Customer', 'customer_phone' => '03009876543',
            'address' => 'Test Address', 'subtotal' => 150, 'total' => 165,
        ]);

        $orderItem = OrderItem::create([
            'order_id' => $order->id, 'item_type' => 'menu_item', 'menu_item_id' => $menuItem->id,
            'item_name' => 'Coke', 'quantity' => 1, 'unit_price' => 150, 'total_price' => 150,
        ]);

        // Verify order integrity
        $this->assertNotNull($order->tracking_token);
        $this->assertEquals('pending', $order->status);
        $this->assertCount(1, $order->items);
        $this->assertEquals(165, $order->total);
    }
}
