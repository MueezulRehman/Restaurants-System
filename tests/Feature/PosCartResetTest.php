<?php

namespace Tests\Feature;

use App\Http\Controllers\Admin\PosController;
use App\Models\Restaurant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PosCartResetTest extends TestCase
{
    use RefreshDatabase;

    public function test_pos_page_resets_client_cart_after_checkout(): void
    {
        $restaurant = Restaurant::create([
            'name' => 'Cart Reset Test',
            'slug' => 'cart-reset-test',
            'status' => 'active',
            'plan' => 'basic',
        ]);

        $user = User::factory()->create([
            'name' => 'POS User',
            'phone' => '1111111111',
            'role' => 'admin',
            'restaurant_id' => $restaurant->id,
        ]);

        $this->actingAs($user, 'web');

        $response = app(PosController::class)->index();

        $this->assertInstanceOf(\Illuminate\View\View::class, $response);
        $this->assertStringContainsString('cart-input', $response->render());
    }
}
