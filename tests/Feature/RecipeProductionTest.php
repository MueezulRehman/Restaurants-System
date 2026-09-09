<?php

namespace Tests\Feature;

use App\Http\Controllers\Admin\RecipeController;
use App\Models\Category;
use App\Models\MenuItem;
use App\Models\ProductionBatch;
use App\Models\Recipe;
use App\Models\RecipeIngredient;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class RecipeProductionTest extends TestCase
{
    use RefreshDatabase;

    public function test_production_consumes_ingredients_and_adds_finished_stock(): void
    {
        [$user, $ingredient, $product] = $this->makeKitchen();
        $recipe = Recipe::create(['restaurant_id' => $user->restaurant_id, 'menu_item_id' => $product->id, 'name' => 'Bread batch', 'yield_quantity' => 1, 'is_active' => true]);
        RecipeIngredient::create(['recipe_id' => $recipe->id, 'menu_item_id' => $ingredient->id, 'quantity' => 2]);
        $this->actingAs($user, 'web');

        $response = app(RecipeController::class)->produce(Request::create('/manager/recipes/produce', 'POST', ['recipe_id' => $recipe->id, 'quantity' => 2, 'notes' => 'Morning batch']));

        $this->assertTrue($response->isRedirect());
        $this->assertSame(6.0, (float) $ingredient->fresh()->stock_quantity);
        $this->assertSame(2.0, (float) $product->fresh()->stock_quantity);
        $this->assertSame(1, ProductionBatch::count());
    }

    public function test_production_rolls_back_when_ingredient_stock_is_insufficient(): void
    {
        [$user, $ingredient, $product] = $this->makeKitchen();
        $recipe = Recipe::create(['restaurant_id' => $user->restaurant_id, 'menu_item_id' => $product->id, 'name' => 'Limited batch', 'yield_quantity' => 1, 'is_active' => true]);
        RecipeIngredient::create(['recipe_id' => $recipe->id, 'menu_item_id' => $ingredient->id, 'quantity' => 20]);
        $this->actingAs($user, 'web');

        try {
            app(RecipeController::class)->produce(Request::create('/manager/recipes/produce', 'POST', ['recipe_id' => $recipe->id, 'quantity' => 1]));
            $this->fail('Insufficient ingredient stock should be rejected.');
        } catch (HttpException $exception) {
            $this->assertSame(422, $exception->getStatusCode());
        }

        $this->assertSame(10.0, (float) $ingredient->fresh()->stock_quantity);
        $this->assertSame(0, ProductionBatch::count());
    }

    public function test_wastage_reduces_finished_stock(): void
    {
        [$user, $ingredient, $product] = $this->makeKitchen();
        $product->update(['track_stock' => true, 'stock_quantity' => 5]);
        $this->actingAs($user, 'web');

        app(RecipeController::class)->wastage(Request::create('/manager/recipes/wastage', 'POST', ['menu_item_id' => $product->id, 'quantity' => 1.5, 'notes' => 'Burnt batch']));

        $this->assertSame(3.5, (float) $product->fresh()->stock_quantity);
    }

    private function makeKitchen(): array
    {
        $restaurant = \App\Models\Restaurant::create(['name' => 'Bakery Test', 'slug' => 'bakery-test-' . uniqid(), 'status' => 'active', 'plan' => 'basic']);
        $user = User::factory()->create(['role' => 'admin', 'restaurant_id' => $restaurant->id, 'phone' => '03000000026']);
        $category = Category::create(['restaurant_id' => $restaurant->id, 'name' => 'Bakery', 'slug' => 'bakery-' . uniqid(), 'is_active' => true]);
        $ingredient = MenuItem::create(['restaurant_id' => $restaurant->id, 'category_id' => $category->id, 'name' => 'Flour', 'price' => 100, 'stock_quantity' => 10, 'track_stock' => true, 'is_available' => true]);
        $product = MenuItem::create(['restaurant_id' => $restaurant->id, 'category_id' => $category->id, 'name' => 'Bread', 'price' => 250, 'stock_quantity' => 0, 'track_stock' => true, 'is_available' => true]);
        return [$user, $ingredient, $product];
    }
}
