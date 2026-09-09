<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\InstallmentPlan;
use App\Models\Restaurant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InstallmentWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_manager_can_create_and_complete_an_installment_plan(): void
    {
        $restaurant = Restaurant::create(['name' => 'Installment Store', 'slug' => 'installment-store', 'status' => 'active', 'enabled_modules' => ['installments']]);
        $restaurant->subscription()->create(['status' => 'active', 'billing_cycle' => 'monthly']);
        $manager = User::factory()->create(['role' => 'admin', 'restaurant_id' => $restaurant->id, 'phone' => '03000000016']);
        $customer = Customer::create(['restaurant_id' => $restaurant->id, 'name' => 'Plan Customer', 'phone' => '03000000017', 'password' => bcrypt('secret')]);

        $this->actingAs($manager, 'web')
            ->post(route('manager.installments.store'), ['customer_id' => $customer->id, 'item_name' => 'Laptop', 'total_amount' => 120000, 'deposit' => 20000, 'months' => 10])
            ->assertRedirect();

        $plan = InstallmentPlan::firstOrFail();
        $this->assertSame('active', $plan->status);
        $this->assertSame(10, $plan->months);

        $this->patch(route('manager.installments.update', $plan), ['status' => 'paid'])->assertRedirect();
        $this->assertSame('paid', $plan->fresh()->status);
    }

    public function test_deposit_cannot_exceed_total_amount(): void
    {
        $restaurant = Restaurant::create(['name' => 'Installment Validation Store', 'slug' => 'installment-validation-store', 'status' => 'active', 'enabled_modules' => ['installments']]);
        $restaurant->subscription()->create(['status' => 'active', 'billing_cycle' => 'monthly']);
        $manager = User::factory()->create(['role' => 'admin', 'restaurant_id' => $restaurant->id, 'phone' => '03000000018']);

        $this->actingAs($manager, 'web')->from(route('manager.installments.index'))
            ->post(route('manager.installments.store'), ['item_name' => 'Laptop', 'total_amount' => 100, 'deposit' => 101, 'months' => 2])
            ->assertSessionHasErrors('deposit');

        $this->assertSame(0, InstallmentPlan::count());
    }
}
