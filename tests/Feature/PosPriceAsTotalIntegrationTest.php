<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Medicine;
use App\Models\MedicineBatch;
use App\Models\Restaurant;
use App\Models\User;

class PosPriceAsTotalIntegrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_price_as_total_computes_quantity_and_decrements_stock()
    {
        // create restaurant & user (match existing tests' style)
        $restaurant = Restaurant::create([
            'name' => 'PriceAsTotal Restaurant',
            'slug' => 'price-total-restaurant',
            'status' => 'active',
            'plan' => 'basic',
        ]);

        $user = User::factory()->create([
            'name' => 'Price As Total User',
            'phone' => '7777777777',
            'role' => 'admin',
            'restaurant_id' => $restaurant->id,
        ]);

        // create medicine with a batch
        $medicine = Medicine::create([
            'restaurant_id' => $restaurant->id,
            'name' => 'Test Weight Item',
            'sku' => 'TW-1',
            'barcode' => '1234567890123',
            'track_stock' => true,
            'unit_type' => 'kg',
            'allow_fractional_qty' => true,
        ]);

        $batch = MedicineBatch::create([
            'medicine_id' => $medicine->id,
            'restaurant_id' => $restaurant->id,
            'batch_number' => 'B1',
            'selling_price' => 200.00,
            'quantity' => 10.000,
        ]);

        // login as user
        $this->actingAs($user, 'web');

        // prepare cart: simulate cashier selling Rs.100 worth of item priced Rs.200/kg
        $cart = [['type' => 'medicine_batch', 'id' => $batch->id, 'quantity' => 0.5, 'price' => 200.00]];

        $request = \Illuminate\Http\Request::create('/manager/pos/checkout', 'POST', [
            'order_type' => 'takeaway',
            'payment_method' => 'cash',
            'amount_received' => 100.00, // full payment for Rs.100 total
            'customer_name' => 'Walk-in Customer',
            'cart' => $cart,
        ]);

        $response = app(\App\Http\Controllers\Admin\PosController::class)->checkout($request);
        $this->assertTrue($response->isRedirect());

        // If checkout failed the controller flashes an error into session; surface it for debugging
        $err = session('pos_error_message');
        $this->assertNull($err, 'Checkout failed with message: ' . ($err ?? 'none'));

        // Ensure an order was created
        $this->assertDatabaseHas('orders', ['restaurant_id' => $restaurant->id]);

        // reload batch and assert stock decreased by 0.5
        $batch->refresh();
        $this->assertEquals(9.5, (float) $batch->quantity, 'Expected batch quantity to decrease by 0.5 after checkout');

        // assert order items recorded referencing this batch
        $this->assertDatabaseHas('order_items', [
            'medicine_batch_id' => $batch->id,
            'quantity' => 0.5,
        ]);
    }
}
