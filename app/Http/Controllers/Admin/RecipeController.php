<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MenuItem;
use App\Models\ProductionBatch;
use App\Models\Recipe;
use App\Models\RecipeIngredient;
use App\Models\StockAdjustment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class RecipeController extends Controller
{
    private function restaurantId(): int
    {
        $user = Auth::user();
        abort_unless($user instanceof User, 403);
        $id = $user->effectiveRestaurantId();
        abort_unless($id !== null, 403);
        return (int) $id;
    }

    public function index()
    {
        $id = $this->restaurantId();
        $items = MenuItem::where('restaurant_id', $id)->orderBy('name')->get();
        $recipes = Recipe::with(['product', 'ingredients.item'])->where('restaurant_id', $id)->latest()->get();
        return view('admin.recipes.index', compact('items', 'recipes'));
    }

    public function store(Request $request)
    {
        $id = $this->restaurantId();
        $data = $request->validate([
            'menu_item_id' => ['required', 'integer', Rule::exists('menu_items', 'id')->where(fn($q) => $q->where('restaurant_id', $id))],
            'name' => 'required|string|max:150',
            'yield_quantity' => 'required|numeric|min:0.001',
        ]);
        Recipe::create([...$data, 'restaurant_id' => $id]);
        return back()->with('success', 'Recipe created.');
    }

    public function storeIngredient(Request $request)
    {
        $id = $this->restaurantId();
        $data = $request->validate([
            'recipe_id' => ['required', 'integer', Rule::exists('recipes', 'id')->where(fn($q) => $q->where('restaurant_id', $id))],
            'menu_item_id' => ['required', 'integer', Rule::exists('menu_items', 'id')->where(fn($q) => $q->where('restaurant_id', $id))],
            'quantity' => 'required|numeric|min:0.001',
        ]);
        RecipeIngredient::updateOrCreate(['recipe_id' => $data['recipe_id'], 'menu_item_id' => $data['menu_item_id']], ['quantity' => $data['quantity']]);
        return back()->with('success', 'Recipe ingredient saved.');
    }

    public function produce(Request $request)
    {
        $id = $this->restaurantId();
        $data = $request->validate(['recipe_id' => ['required', 'integer', Rule::exists('recipes', 'id')->where(fn($q) => $q->where('restaurant_id', $id))], 'quantity' => 'required|numeric|min:0.001', 'notes' => 'nullable|string|max:500']);
        DB::transaction(function () use ($data, $id): void {
            $recipe = Recipe::with('ingredients')->where('restaurant_id', $id)->findOrFail($data['recipe_id']);
            $quantity = (float) $data['quantity'];
            foreach ($recipe->ingredients as $ingredient) {
                $item = MenuItem::where('restaurant_id', $id)->findOrFail($ingredient->menu_item_id);
                $needed = (float) $ingredient->quantity * $quantity;
                if ((float) $item->stock_quantity < $needed) abort(422, "Not enough ingredient stock for {$item->name}.");
                $before = (float) $item->stock_quantity;
                $item->update(['track_stock' => true, 'stock_quantity' => $before - $needed]);
                StockAdjustment::create(['restaurant_id' => $id, 'menu_item_id' => $item->id, 'product_variant_id' => null, 'user_id' => Auth::id(), 'quantity_before' => $before, 'quantity_after' => $before - $needed, 'change_quantity' => -$needed, 'reason' => 'sale', 'reference_id' => 'production', 'notes' => "Ingredient consumed for {$recipe->name}"]);
            }
            $product = MenuItem::where('restaurant_id', $id)->findOrFail($recipe->menu_item_id);
            $before = (float) $product->stock_quantity;
            $produced = $quantity * (float) $recipe->yield_quantity;
            $product->update(['track_stock' => true, 'stock_quantity' => $before + $produced]);
            ProductionBatch::create(['restaurant_id' => $id, 'recipe_id' => $recipe->id, 'batch_number' => 'PB-' . now()->format('YmdHis') . '-' . random_int(100, 999), 'quantity' => $produced, 'produced_at' => now(), 'notes' => $data['notes'] ?? null]);
            StockAdjustment::create(['restaurant_id' => $id, 'menu_item_id' => $product->id, 'product_variant_id' => null, 'user_id' => Auth::id(), 'quantity_before' => $before, 'quantity_after' => $before + $produced, 'change_quantity' => $produced, 'reason' => 'purchase', 'reference_id' => 'production', 'notes' => "Production batch for {$recipe->name}"]);
        });
        return back()->with('success', 'Production batch created and stock updated.');
    }

    public function wastage(Request $request)
    {
        $id = $this->restaurantId();
        $data = $request->validate(['menu_item_id' => ['required', 'integer', Rule::exists('menu_items', 'id')->where(fn($q) => $q->where('restaurant_id', $id))], 'quantity' => 'required|numeric|min:0.001', 'notes' => 'nullable|string|max:500']);
        $item = MenuItem::where('restaurant_id', $id)->findOrFail($data['menu_item_id']);
        $before = (float) $item->stock_quantity;
        $quantity = (float) $data['quantity'];
        abort_if($before < $quantity, 422, "Not enough stock for {$item->name}.");
        $item->update(['stock_quantity' => $before - $quantity]);
        StockAdjustment::create(['restaurant_id' => $id, 'menu_item_id' => $item->id, 'product_variant_id' => null, 'user_id' => Auth::id(), 'quantity_before' => $before, 'quantity_after' => $before - $quantity, 'change_quantity' => -$quantity, 'reason' => 'damage', 'reference_id' => 'wastage', 'notes' => $data['notes'] ?? 'Production wastage']);
        return back()->with('success', 'Wastage recorded.');
    }
}
