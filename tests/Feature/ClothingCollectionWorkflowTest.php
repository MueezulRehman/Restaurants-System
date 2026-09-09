<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\MenuItem;
use App\Models\Restaurant;
use App\Models\RetailCollection;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClothingCollectionWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_manager_can_create_a_collection_and_assign_a_product(): void
    {
        $restaurant = Restaurant::create(['name' => 'Clothing Store', 'slug' => 'clothing-store', 'status' => 'active', 'enabled_modules' => ['collections']]);
        $restaurant->subscription()->create(['status' => 'active', 'billing_cycle' => 'monthly']);
        $manager = User::factory()->create(['role' => 'admin', 'restaurant_id' => $restaurant->id, 'phone' => '03000000036']);
        $category = Category::create(['restaurant_id' => $restaurant->id, 'name' => 'Garments', 'slug' => 'garments', 'is_active' => true]);
        $item = MenuItem::create(['restaurant_id' => $restaurant->id, 'category_id' => $category->id, 'name' => 'Summer Shirt', 'price' => 3000, 'is_available' => true]);

        $this->actingAs($manager, 'web')->post(route('manager.collections.store'), ['name' => 'Summer 2026', 'season' => 'summer', 'starts_at' => '2026-04-01', 'ends_at' => '2026-08-31'])->assertRedirect();
        $collection = RetailCollection::firstOrFail();
        $this->post(route('manager.collections.assign-item', $collection), ['menu_item_id' => $item->id])->assertRedirect();

        $this->assertSame($collection->id, $item->fresh()->collection_id);
        $this->assertSame(1, $collection->fresh()->menuItems()->count());
    }
}
