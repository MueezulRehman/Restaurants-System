<?php

namespace Tests\Feature;

use App\Models\ProductDevice;
use App\Models\Restaurant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductDeviceWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_manager_can_register_and_update_a_device(): void
    {
        $restaurant = Restaurant::create(['name' => 'Mobile Store', 'slug' => 'mobile-store', 'status' => 'active', 'enabled_modules' => ['device-tracking']]);
        $restaurant->subscription()->create(['status' => 'active', 'billing_cycle' => 'monthly']);
        $manager = User::factory()->create(['role' => 'admin', 'restaurant_id' => $restaurant->id, 'phone' => '03000000028']);

        $this->actingAs($manager, 'web')->post(route('manager.product-devices.store'), [
            'identifier_type' => 'imei',
            'identifier_value' => 'IMEI-001',
            'warranty_until' => now()->addYear()->toDateString(),
            'status' => 'in_stock',
        ])->assertRedirect();

        $device = ProductDevice::firstOrFail();
        $this->assertSame('IMEI-001', $device->identifier_value);
        $this->assertSame('in_stock', $device->status);

        $this->patch(route('manager.product-devices.update', $device), ['status' => 'sold'])->assertRedirect();
        $this->assertSame('sold', $device->fresh()->status);
    }

    public function test_identifier_must_be_unique_per_restaurant(): void
    {
        $restaurant = Restaurant::create(['name' => 'Device Validation Store', 'slug' => 'device-validation-store', 'status' => 'active', 'enabled_modules' => ['device-tracking']]);
        $restaurant->subscription()->create(['status' => 'active', 'billing_cycle' => 'monthly']);
        $manager = User::factory()->create(['role' => 'admin', 'restaurant_id' => $restaurant->id, 'phone' => '03000000029']);
        ProductDevice::create(['restaurant_id' => $restaurant->id, 'identifier_type' => 'serial', 'identifier_value' => 'SERIAL-001', 'status' => 'in_stock']);

        $this->actingAs($manager, 'web')->from(route('manager.product-devices.index'))
            ->post(route('manager.product-devices.store'), ['identifier_type' => 'serial', 'identifier_value' => 'SERIAL-001', 'status' => 'in_stock'])
            ->assertSessionHasErrors('identifier_value');

        $this->assertSame(1, ProductDevice::count());
    }
}
