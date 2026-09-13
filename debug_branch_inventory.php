<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Restaurant;
use App\Models\Branch;
use App\Models\User;
use App\Models\Category;
use App\Models\MenuItem;
use App\Models\BranchInventory;
use Illuminate\Support\Facades\Auth;

$app->make('db')->connection()->beginTransaction();

$restaurant = Restaurant::create(['name' => 'Debug Branch', 'slug' => 'debug-branch', 'status' => 'active', 'plan' => 'basic']);
$branch = Branch::create(['restaurant_id' => $restaurant->id, 'name' => 'Downtown', 'code' => 'DT', 'is_active' => true]);
$user = User::factory()->create(['name' => 'B', 'phone' => '9999999999', 'role' => 'admin', 'restaurant_id' => $restaurant->id, 'branch_id' => $branch->id]);
$category = Category::create(['restaurant_id' => $restaurant->id, 'name' => 'Meals', 'slug' => 'meals', 'is_active' => true]);
$menuItem = MenuItem::create(['restaurant_id' => $restaurant->id, 'category_id' => $category->id, 'name' => 'Sandwich', 'price' => 180, 'is_available' => true, 'track_stock' => true, 'stock_quantity' => 10]);
$inventory = BranchInventory::create(['restaurant_id' => $restaurant->id, 'branch_id' => $branch->id, 'item_type' => 'menu_item', 'item_id' => $menuItem->id, 'quantity' => 10]);
Auth::login($user);

$branchStock = BranchInventory::withoutGlobalScope('restaurant')
    ->where('restaurant_id', $restaurant->id)
    ->where('branch_id', $branch->id)
    ->where('item_type', 'menu_item')
    ->where('item_id', $menuItem->id)
    ->first();

echo "before=" . ($branchStock ? $branchStock->quantity : 'NULL') . "\n";

$branchStock->quantity = 8;
$branchStock->save();

echo "after=" . BranchInventory::withoutGlobalScope('restaurant')->where('restaurant_id', $restaurant->id)->where('branch_id', $branch->id)->where('item_type', 'menu_item')->where('item_id', $menuItem->id)->first()->quantity . "\n";

$app->make('db')->connection()->rollBack();
