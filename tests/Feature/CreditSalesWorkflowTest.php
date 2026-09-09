<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\CustomerBalanceTransaction;
use App\Models\Restaurant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CreditSalesWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_manager_can_record_a_wholesale_customer_payment(): void
    {
        $restaurant = Restaurant::create(['name' => 'Wholesale Store', 'slug' => 'wholesale-store', 'status' => 'active', 'enabled_modules' => ['credit-sales']]);
        $restaurant->subscription()->create(['status' => 'active', 'billing_cycle' => 'monthly']);
        $manager = User::factory()->create(['role' => 'admin', 'restaurant_id' => $restaurant->id, 'phone' => '03000000023']);
        $customer = Customer::create(['restaurant_id' => $restaurant->id, 'name' => 'Wholesale Buyer', 'phone' => '03000000024', 'balance' => 750, 'credit_limit' => 2000, 'password' => bcrypt('secret')]);

        $this->actingAs($manager, 'web')->post(route('manager.credit-sales.payment', $customer), ['amount' => 250, 'description' => 'Cash received'])->assertRedirect();

        $this->assertSame(500.0, (float) $customer->fresh()->balance);
        $transaction = CustomerBalanceTransaction::latest()->first();
        $this->assertSame('payment', $transaction->type);
        $this->assertSame(250.0, (float) $transaction->amount);
    }

    public function test_payment_cannot_exceed_outstanding_balance(): void
    {
        $restaurant = Restaurant::create(['name' => 'Wholesale Validation Store', 'slug' => 'wholesale-validation-store', 'status' => 'active', 'enabled_modules' => ['credit-sales']]);
        $restaurant->subscription()->create(['status' => 'active', 'billing_cycle' => 'monthly']);
        $manager = User::factory()->create(['role' => 'admin', 'restaurant_id' => $restaurant->id, 'phone' => '03000000025']);
        $customer = Customer::create(['restaurant_id' => $restaurant->id, 'name' => 'Wholesale Buyer', 'phone' => '03000000026', 'balance' => 100, 'credit_limit' => 500, 'password' => bcrypt('secret')]);

        $this->actingAs($manager, 'web')->from(route('manager.credit-sales.index'))
            ->post(route('manager.credit-sales.payment', $customer), ['amount' => 101])
            ->assertStatus(422);

        $this->assertSame(100.0, (float) $customer->fresh()->balance);
    }
}
