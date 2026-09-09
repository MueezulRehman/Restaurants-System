<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\LoyaltyAccount;
use App\Models\Restaurant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoyaltyWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_manager_can_add_points_to_a_customer_account(): void
    {
        $restaurant = Restaurant::create(['name' => 'Loyalty Store', 'slug' => 'loyalty-store', 'status' => 'active', 'enabled_modules' => ['loyalty']]);
        $restaurant->subscription()->create(['status' => 'active', 'billing_cycle' => 'monthly']);
        $manager = User::factory()->create(['role' => 'admin', 'restaurant_id' => $restaurant->id, 'phone' => '03000000019']);
        $customer = Customer::create(['restaurant_id' => $restaurant->id, 'name' => 'Loyal Customer', 'phone' => '03000000020', 'password' => bcrypt('secret')]);

        $this->actingAs($manager, 'web')->post(route('manager.loyalty.store'), ['customer_id' => $customer->id, 'points' => 100])->assertRedirect();
        $this->assertSame(100, (int) LoyaltyAccount::firstOrFail()->points);

        $this->post(route('manager.loyalty.store'), ['customer_id' => $customer->id, 'points' => 25])->assertRedirect();
        $this->assertSame(125, (int) LoyaltyAccount::firstOrFail()->fresh()->points);

        $account = LoyaltyAccount::firstOrFail();
        $this->post(route('manager.loyalty.redeem', $account), ['points' => 40])->assertRedirect();
        $this->assertSame(85, (int) $account->fresh()->points);
    }
}
