<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Restaurant;
use App\Models\TradeIn;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TradeInWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_manager_can_record_and_update_a_trade_in(): void
    {
        $restaurant = Restaurant::create(['name' => 'Device Store', 'slug' => 'device-store', 'status' => 'active', 'enabled_modules' => ['trade-ins']]);
        $restaurant->subscription()->create(['status' => 'active', 'billing_cycle' => 'monthly']);
        $manager = User::factory()->create(['role' => 'admin', 'restaurant_id' => $restaurant->id, 'phone' => '03000000014']);
        $customer = Customer::create(['restaurant_id' => $restaurant->id, 'name' => 'Device Customer', 'phone' => '03000000015', 'password' => bcrypt('secret')]);

        $this->actingAs($manager, 'web')
            ->post(route('manager.trade-ins.store'), [
                'customer_id' => $customer->id,
                'item_name' => 'Used Phone',
                'serial_number' => 'IMEI-123',
                'estimated_value' => 25000,
                'condition' => 'Good',
            ])
            ->assertRedirect();

        $tradeIn = TradeIn::firstOrFail();
        $this->assertSame('received', $tradeIn->status);
        $this->assertSame(25000.0, (float) $tradeIn->estimated_value);

        $this->patch(route('manager.trade-ins.update', $tradeIn), ['status' => 'credited'])
            ->assertRedirect();

        $this->assertSame('credited', $tradeIn->fresh()->status);
    }
}
