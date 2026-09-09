<?php

namespace Tests\Feature;

use App\Models\Restaurant;
use App\Models\StockTransfer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StockTransferWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_manager_can_record_and_update_a_stock_transfer(): void
    {
        $restaurant = Restaurant::create([
            'name' => 'Transfer Store',
            'slug' => 'transfer-store',
            'status' => 'active',
            'enabled_modules' => ['stock-transfers'],
        ]);
        $restaurant->subscription()->create(['status' => 'active', 'billing_cycle' => 'monthly']);
        $manager = User::factory()->create(['role' => 'admin', 'restaurant_id' => $restaurant->id, 'phone' => '03000000012']);

        $this->actingAs($manager, 'web')
            ->post(route('manager.stock-transfers.store'), [
                'from_location' => 'Main store',
                'to_location' => 'Branch A',
                'item_name' => 'USB-C Charger',
                'quantity' => 12,
            ])
            ->assertRedirect();

        $transfer = StockTransfer::firstOrFail();
        $this->assertSame('Main store', $transfer->from_location);
        $this->assertSame(12.0, (float) $transfer->quantity);

        $this->patch(route('manager.stock-transfers.update', $transfer), ['status' => 'in_transit'])
            ->assertRedirect();

        $this->assertSame('in_transit', $transfer->fresh()->status);
    }

    public function test_transfer_rejects_the_same_source_and_destination(): void
    {
        $restaurant = Restaurant::create([
            'name' => 'Transfer Validation Store',
            'slug' => 'transfer-validation-store',
            'status' => 'active',
            'enabled_modules' => ['stock-transfers'],
        ]);
        $restaurant->subscription()->create(['status' => 'active', 'billing_cycle' => 'monthly']);
        $manager = User::factory()->create(['role' => 'admin', 'restaurant_id' => $restaurant->id, 'phone' => '03000000013']);

        $this->actingAs($manager, 'web')
            ->from(route('manager.stock-transfers.index'))
            ->post(route('manager.stock-transfers.store'), [
                'from_location' => 'Main store',
                'to_location' => 'Main store',
                'item_name' => 'USB-C Charger',
                'quantity' => 1,
            ])
            ->assertSessionHasErrors('to_location');

        $this->assertSame(0, StockTransfer::count());
    }
}
