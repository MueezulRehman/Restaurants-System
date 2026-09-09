<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\InventoryPurchaseItem;
use App\Models\MenuItem;
use App\Models\Restaurant;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class GroceryInventoryWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_supplier_receiving_updates_stock_and_expiry_tracking_groups_items(): void
    {
        $restaurant = Restaurant::create(['name' => 'Grocery Test', 'slug' => 'grocery-test', 'status' => 'active', 'enabled_modules' => ['purchasing', 'expiry-tracking']]);
        $restaurant->subscription()->create(['status' => 'active', 'billing_cycle' => 'monthly']);
        $manager = User::factory()->create(['role' => 'admin', 'restaurant_id' => $restaurant->id, 'phone' => '03000000021']);
        $category = Category::create(['restaurant_id' => $restaurant->id, 'name' => 'Grocery', 'slug' => 'grocery', 'is_active' => true]);
        $item = MenuItem::create(['restaurant_id' => $restaurant->id, 'category_id' => $category->id, 'name' => 'Milk', 'price' => 250, 'stock_quantity' => 2, 'track_stock' => true, 'is_available' => true]);
        $supplier = Supplier::create(['restaurant_id' => $restaurant->id, 'name' => 'Fresh Supplier', 'phone' => '03000000022', 'is_active' => true]);

        $this->actingAs($manager, 'web')->post(route('manager.purchasing.store'), [
            'supplier_id' => $supplier->id,
            'invoice_no' => 'INV-1',
            'purchase_date' => now()->toDateString(),
            'menu_item_id' => $item->id,
            'quantity' => 10,
            'purchase_price' => 100,
            'expiry_date' => now()->addDays(10)->toDateString(),
        ])->assertRedirect();

        $this->assertSame(12.0, (float) $item->fresh()->stock_quantity);
        $this->assertSame(1, InventoryPurchaseItem::count());
        $this->get(route('manager.expiry-tracking.index'))->assertOk()->assertSee('Within 30 days');
    }
}
