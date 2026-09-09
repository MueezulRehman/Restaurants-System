<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\MenuItem;
use App\Models\Restaurant;
use App\Support\StorefrontPricing;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WeightedProductCheckoutTest extends TestCase
{
    use RefreshDatabase;

    public function test_fractional_online_quantity_uses_price_per_unit(): void
    {
        $restaurant = Restaurant::create([
            'name' => 'Grocery Test',
            'slug' => 'grocery-test-' . uniqid(),
            'status' => 'active',
            'plan' => 'basic',
            'enabled_modules' => ['orders', 'pos'],
            'accept_orders_when_closed' => true,
            'opening_hours' => array_fill_keys(['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'], ['open' => '00:00', 'close' => '23:59', 'closed' => false]),
        ]);
        $category = Category::create([
            'restaurant_id' => $restaurant->id,
            'name' => 'Produce',
            'slug' => 'produce-' . uniqid(),
            'is_active' => true,
        ]);
        $item = MenuItem::create([
            'restaurant_id' => $restaurant->id,
            'category_id' => $category->id,
            'name' => 'Rice',
            'price' => 300,
            'price_per_unit' => 280,
            'unit_type' => 'kg',
            'unit' => 'kg',
            'allow_fractional_qty' => true,
            'track_stock' => true,
            'stock_quantity' => 10,
            'is_available' => true,
        ]);
        $this->assertSame(280.0, StorefrontPricing::unitPriceForMenuItem($item));
        $this->assertTrue($item->allowsFractionalQty());
    }
}
