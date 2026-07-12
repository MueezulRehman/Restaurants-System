<?php

namespace Tests\Feature;

use App\Models\MenuItem;
use App\Models\Medicine;
use App\Models\MedicineBatch;
use App\Models\ProductVariant;
use App\Models\Restaurant;
use App\Models\StockAdjustment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Tests\TestCase;

class StockManagementTest extends TestCase
{
    use RefreshDatabase;
    use WithoutMiddleware;

    public function test_menu_item_stock_adjustment(): void
    {
        $restaurant = Restaurant::create([
            'name' => 'Test Restaurant',
            'slug' => 'test-restaurant',
            'status' => 'active',
            'enabled_modules' => ['orders', 'stock'],
        ]);

        $user = User::create([
            'name' => 'Manager',
            'email' => 'manager@example.com',
            'phone' => '1234567890',
            'role' => 'manager',
            'restaurant_id' => $restaurant->id,
            'password' => bcrypt('password'),
            'module_access' => ['orders', 'stock'],
        ]);

        $category = \App\Models\Category::create([
            'restaurant_id' => $restaurant->id,
            'name' => 'Food',
            'is_active' => true,
        ]);

        $item = MenuItem::create([
            'restaurant_id' => $restaurant->id,
            'category_id' => $category->id,
            'name' => 'Burger',
            'price' => 500,
            'track_stock' => true,
            'stock_quantity' => 10,
            'is_available' => true,
        ]);

        $this->actingAs($user);

        $response = $this->post(route('manager.stock.adjust'), [
            'item_type' => 'menu_item',
            'item_id' => 'menu_item_' . $item->id,
            'quantity' => 5,
            'reason' => 'purchase',
            'notes' => 'Stock replenishment',
        ]);

        $response->assertRedirect();
        
        $item->refresh();
        $this->assertEquals(15, $item->stock_quantity);

        $this->assertDatabaseHas('stock_adjustments', [
            'restaurant_id' => $restaurant->id,
            'change_quantity' => 5,
            'reason' => 'purchase',
        ]);
    }

    public function test_product_variant_stock_adjustment(): void
    {
        $restaurant = Restaurant::create([
            'name' => 'Test Restaurant',
            'slug' => 'test-restaurant',
            'status' => 'active',
            'enabled_modules' => ['stock'],
        ]);

        $user = User::create([
            'name' => 'Manager',
            'email' => 'manager@example.com',
            'phone' => '1234567890',
            'role' => 'manager',
            'restaurant_id' => $restaurant->id,
            'password' => bcrypt('password'),
            'module_access' => ['stock'],
        ]);

        $category = \App\Models\Category::create([
            'restaurant_id' => $restaurant->id,
            'name' => 'Food',
            'is_active' => true,
        ]);

        $item = MenuItem::create([
            'restaurant_id' => $restaurant->id,
            'category_id' => $category->id,
            'name' => 'Pizza',
            'price' => 800,
            'is_available' => true,
        ]);

        $variant = ProductVariant::create([
            'restaurant_id' => $restaurant->id,
            'menu_item_id' => $item->id,
            'variant_name' => 'Large',
            'sku' => 'PIZZA-LG',
            'quantity_available' => 20,
            'is_available' => true,
        ]);

        $this->actingAs($user);

        $response = $this->post(route('manager.stock.adjust'), [
            'item_type' => 'variant',
            'item_id' => 'variant_' . $variant->id,
            'quantity' => -3,
            'reason' => 'sale',
            'notes' => 'POS sale adjustment',
        ]);

        $response->assertRedirect();
        
        $variant->refresh();
        $this->assertEquals(17, $variant->quantity_available);
    }

    public function test_medicine_batch_stock_adjustment(): void
    {
        $restaurant = Restaurant::create([
            'name' => 'Medical Store',
            'slug' => 'medical-store',
            'status' => 'active',
            'enabled_modules' => ['medical', 'stock'],
        ]);

        $user = User::create([
            'name' => 'Pharmacist',
            'email' => 'pharmacist@example.com',
            'phone' => '1234567890',
            'role' => 'manager',
            'restaurant_id' => $restaurant->id,
            'password' => bcrypt('password'),
            'module_access' => ['medical', 'stock'],
        ]);

        $medicine = Medicine::create([
            'restaurant_id' => $restaurant->id,
            'name' => 'Paracetamol',
            'sku' => 'PARA-500',
            'requires_prescription' => false,
            'track_stock' => true,
        ]);

        $batch = MedicineBatch::create([
            'restaurant_id' => $restaurant->id,
            'medicine_id' => $medicine->id,
            'batch_number' => 'BATCH001',
            'quantity' => 100,
            'purchase_price' => 10,
            'selling_price' => 15,
            'expiry_date' => now()->addMonths(12),
        ]);

        $this->actingAs($user);

        $response = $this->post(route('manager.stock.adjust'), [
            'item_type' => 'medicine_batch',
            'item_id' => 'medicine_batch_' . $batch->id,
            'quantity' => -10,
            'reason' => 'sale',
            'notes' => 'Manual stock count adjustment',
        ]);

        $response->assertRedirect();
        
        $batch->refresh();
        $this->assertEquals(90, $batch->quantity);

        $this->assertDatabaseHas('stock_adjustments', [
            'restaurant_id' => $restaurant->id,
            'change_quantity' => -10,
            'reason' => 'sale',
            'product_variant_id' => null,
        ]);
    }

    public function test_stock_adjustment_prevents_negative_inventory(): void
    {
        $restaurant = Restaurant::create([
            'name' => 'Test Restaurant',
            'slug' => 'test-restaurant',
            'status' => 'active',
            'enabled_modules' => ['stock'],
        ]);

        $user = User::create([
            'name' => 'Manager',
            'email' => 'manager@example.com',
            'phone' => '1234567890',
            'role' => 'manager',
            'restaurant_id' => $restaurant->id,
            'password' => bcrypt('password'),
            'module_access' => ['stock'],
        ]);

        $category = \App\Models\Category::create([
            'restaurant_id' => $restaurant->id,
            'name' => 'Food',
            'is_active' => true,
        ]);

        $item = MenuItem::create([
            'restaurant_id' => $restaurant->id,
            'category_id' => $category->id,
            'name' => 'Test Item',
            'price' => 100,
            'track_stock' => true,
            'stock_quantity' => 5,
            'is_available' => true,
        ]);

        $this->actingAs($user);

        // Try to reduce by more than available
        $this->post(route('manager.stock.adjust'), [
            'item_type' => 'menu_item',
            'item_id' => 'menu_item_' . $item->id,
            'quantity' => -10,
            'reason' => 'correction',
        ]);

        $item->refresh();
        // Should be 0, not negative
        $this->assertEquals(0, $item->stock_quantity);
    }

    public function test_stock_index_shows_correct_items_for_medical_mode(): void
    {
        // Create a business type that maps to medical mode
        $businessType = \App\Models\BusinessType::create([
            'name' => 'Pharmacy',
            'slug' => 'pharmacy',
            'description' => 'Medical/Pharmacy business',
            'is_active' => true,
        ]);

        $restaurant = Restaurant::create([
            'name' => 'Medical Store',
            'slug' => 'medical-store',
            'status' => 'active',
            'business_type_id' => $businessType->id,
            'enabled_modules' => ['medical', 'stock'],
        ]);

        $user = User::create([
            'name' => 'Manager',
            'email' => 'manager@example.com',
            'phone' => '1234567890',
            'role' => 'manager',
            'restaurant_id' => $restaurant->id,
            'password' => bcrypt('password'),
            'module_access' => ['medical', 'stock'],
        ]);

        $medicine = Medicine::create([
            'restaurant_id' => $restaurant->id,
            'name' => 'Aspirin',
            'sku' => 'ASP-100',
            'requires_prescription' => false,
            'track_stock' => true,
        ]);

        MedicineBatch::create([
            'restaurant_id' => $restaurant->id,
            'medicine_id' => $medicine->id,
            'batch_number' => 'BATCH001',
            'quantity' => 50,
            'purchase_price' => 5,
            'selling_price' => 10,
        ]);

        $this->actingAs($user);

        $response = $this->get(route('manager.stock.index'));

        $response->assertStatus(200);
        // Verify the view is rendering and has the expected data
        $response->assertViewHas('medicines');
        // Verify the page loads without errors
        $this->assertTrue(true);
    }
}
