<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Notification;
use App\Models\Order;
use App\Models\Restaurant;
use App\Models\RestaurantSubscription;
use App\Models\User;
use App\Services\NotificationService;
use App\Services\WhatsAppService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class CustomerBalanceReminderTest extends TestCase
{
    use RefreshDatabase;

    public function test_whatsapp_service_converts_local_phone_numbers_to_international_format(): void
    {
        Http::fake();

        $service = new WhatsAppService();
        config()->set('services.whatsapp.api_url', 'https://graph.facebook.com/v25.0');
        config()->set('services.whatsapp.token', 'test-token');
        config()->set('services.whatsapp.from', '123456789');

        $service->sendText('03001234567', 'Hello customer');

        Http::assertSent(function ($request) {
            $body = $request->data();
            return $body['to'] === '+923001234567';
        });
    }

    public function test_notification_service_sends_whatsapp_to_the_customer_phone_number(): void
    {
        Http::fake();

        config()->set('services.whatsapp.api_url', 'https://graph.facebook.com/v25.0');
        config()->set('services.whatsapp.token', 'test-token');
        config()->set('services.whatsapp.from', '123456789');

        $restaurant = Restaurant::create([
            'name' => 'Test Restaurant',
            'slug' => 'test-restaurant',
        ]);

        $notification = Notification::create([
            'restaurant_id' => $restaurant->id,
            'type' => 'custom',
            'title' => 'Reminder',
            'message' => 'Hello',
            'channels' => ['whatsapp'],
            'status' => 'pending',
        ]);

        NotificationService::sendWhatsApp($notification, '03001234567');

        Http::assertSent(function ($request) {
            $body = $request->data();
            return $body['to'] === '+923001234567';
        });
    }

    public function test_same_phone_can_be_registered_for_different_restaurants(): void
    {
        $restaurantOne = Restaurant::create([
            'name' => 'Restaurant One',
            'slug' => 'restaurant-one',
            'status' => 'active',
            'enabled_modules' => ['customers'],
        ]);
        $restaurantTwo = Restaurant::create([
            'name' => 'Restaurant Two',
            'slug' => 'restaurant-two',
            'status' => 'active',
            'enabled_modules' => ['customers'],
        ]);

        RestaurantSubscription::create([
            'restaurant_id' => $restaurantOne->id,
            'status' => 'active',
            'current_period_end' => now()->addMonth(),
        ]);
        RestaurantSubscription::create([
            'restaurant_id' => $restaurantTwo->id,
            'status' => 'active',
            'current_period_end' => now()->addMonth(),
        ]);

        $managerOne = User::create([
            'name' => 'Manager One',
            'phone' => '03001111111',
            'email' => 'manager-one@example.com',
            'role' => 'manager',
            'password' => bcrypt('password'),
            'restaurant_id' => $restaurantOne->id,
            'module_access' => ['customers'],
        ]);
        $managerTwo = User::create([
            'name' => 'Manager Two',
            'phone' => '03002222222',
            'email' => 'manager-two@example.com',
            'role' => 'manager',
            'password' => bcrypt('password'),
            'restaurant_id' => $restaurantTwo->id,
            'module_access' => ['customers'],
        ]);

        Customer::create([
            'restaurant_id' => $restaurantOne->id,
            'name' => 'First Customer',
            'phone' => '03001234567',
            'password' => bcrypt('password'),
        ]);

        $this->actingAs($managerTwo, 'web');

        $request = new \Illuminate\Http\Request([
            'name' => 'Second Customer',
            'phone' => '03001234567',
        ]);

        $response = app(\App\Http\Controllers\Admin\CustomerController::class)->store($request);

        $this->assertEquals(302, $response->getStatusCode());
        $this->assertDatabaseHas('customers', [
            'restaurant_id' => $restaurantOne->id,
            'phone' => '03001234567',
        ]);
        $this->assertDatabaseHas('customers', [
            'restaurant_id' => $restaurantTwo->id,
            'phone' => '03001234567',
        ]);
    }

    public function test_customer_registration_can_redirect_back_to_pos_with_cart_preserved(): void
    {
        $restaurant = Restaurant::create([
            'name' => 'Restaurant Three',
            'slug' => 'restaurant-three',
            'status' => 'active',
            'enabled_modules' => ['customers'],
        ]);
        RestaurantSubscription::create([
            'restaurant_id' => $restaurant->id,
            'status' => 'active',
            'current_period_end' => now()->addMonth(),
        ]);
        $manager = User::create([
            'name' => 'Manager Three',
            'phone' => '03003333333',
            'email' => 'manager-three@example.com',
            'role' => 'manager',
            'password' => bcrypt('password'),
            'restaurant_id' => $restaurant->id,
            'module_access' => ['customers'],
        ]);

        $this->actingAs($manager, 'web');

        $request = new \Illuminate\Http\Request([
            'name' => 'Cart Customer',
            'phone' => '03005555555',
            'redirect_to_pos' => '1',
            'cart' => [[
                'type' => 'menu_item',
                'id' => 1,
                'quantity' => 2,
                'name' => 'Burger',
                'price' => 10,
            ]],
        ]);

        $response = app(\App\Http\Controllers\Admin\CustomerController::class)->store($request);

        $this->assertEquals(route('manager.pos.index'), $response->getTargetUrl());
        $this->assertSame($request->input('cart'), session('pos_last_cart'));
    }

    public function test_reminder_message_includes_the_current_customer_balance(): void
    {
        $restaurant = Restaurant::create([
            'name' => 'Test Restaurant',
            'slug' => 'test-restaurant',
            'status' => 'active',
            'enabled_modules' => ['customers'],
        ]);
        RestaurantSubscription::create([
            'restaurant_id' => $restaurant->id,
            'status' => 'active',
            'current_period_end' => now()->addMonth(),
        ]);
        $manager = User::create([
            'name' => 'Manager One',
            'phone' => '03001111111',
            'email' => 'manager-one@example.com',
            'role' => 'manager',
            'password' => bcrypt('password'),
            'restaurant_id' => $restaurant->id,
            'module_access' => ['customers'],
        ]);
        $customer = Customer::create([
            'restaurant_id' => $restaurant->id,
            'name' => 'Aisha Khan',
            'phone' => '03001234567',
            'balance' => 125.50,
            'password' => bcrypt('password'),
        ]);
        Order::create([
            'restaurant_id' => $restaurant->id,
            'customer_id' => $customer->id,
            'customer_name' => $customer->name,
            'customer_phone' => $customer->phone,
            'status' => 'pending',
            'total' => 85.50,
            'subtotal' => 85.50,
            'payment_method' => 'cash',
        ]);

        $this->actingAs($manager, 'web');

        $response = $this->post(route('manager.customers.remind', $customer));

        $response->assertRedirect();
        $response->assertSessionHas('success', 'Balance reminder logged for Aisha Khan. Current balance: Rs. 125.50.');
        $this->assertDatabaseHas('notifications', [
            'customer_id' => $customer->id,
            'type' => 'custom',
        ]);
        $notification = Notification::where('customer_id', $customer->id)->where('type', 'custom')->latest()->first();
        $this->assertNotNull($notification);
        $this->assertStringContainsString('sales details', strtolower($notification->message));
        $this->assertStringContainsString('85.50', $notification->message);
        $this->assertNotNull($customer->fresh()->last_reminder_at);
        $this->assertTrue(Notification::where('customer_id', $customer->id)->where('type', 'custom')->exists());
        $this->assertDatabaseHas('notifications', [
            'customer_id' => $customer->id,
            'type' => 'custom',
        ]);
    }
}
