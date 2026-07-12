<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\MenuItem;
use App\Models\Restaurant;
use Illuminate\Database\Seeder;

class PakistaniGeneralStoreSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $restaurant = Restaurant::where('slug', 'tastehut')->first() ?? Restaurant::first();

        if (! $restaurant) {
            return;
        }

        $categories = [
            'Groceries' => ['icon' => '🛒', 'sort_order' => 1],
            'Beverages' => ['icon' => '🥤', 'sort_order' => 2],
            'Frozen' => ['icon' => '🧊', 'sort_order' => 3],
            'Household' => ['icon' => '🏠', 'sort_order' => 4],
        ];

        foreach ($categories as $name => $config) {
            Category::firstOrCreate([
                'restaurant_id' => $restaurant->id,
                'name' => $name,
            ], [
                'icon' => $config['icon'],
                'sort_order' => $config['sort_order'],
                'is_active' => true,
            ]);
        }

        $items = [
            [
                'name' => 'Walls Ice Cream',
                'category' => 'Frozen',
                'price' => 350,
                'cost_price' => 250,
                'sku' => 'GS-001',
                'unit' => 'pcs',
                'track_stock' => true,
                'stock_quantity' => 40,
                'low_stock_threshold' => 10,
                'description' => 'Popular Walls ice cream available in Pakistan.',
            ],
            [
                'name' => 'Nido Milk Powder',
                'category' => 'Groceries',
                'price' => 1200,
                'cost_price' => 950,
                'sku' => 'GS-002',
                'unit' => 'pack',
                'track_stock' => true,
                'stock_quantity' => 25,
                'low_stock_threshold' => 5,
                'description' => 'Trusted milk powder for daily use.',
            ],
            [
                'name' => 'Tapal Tea',
                'category' => 'Groceries',
                'price' => 260,
                'cost_price' => 220,
                'sku' => 'GS-003',
                'unit' => 'pack',
                'track_stock' => true,
                'stock_quantity' => 60,
                'low_stock_threshold' => 10,
                'description' => 'Popular Pakistani tea brand.',
            ],
            [
                'name' => 'Nestle Milo',
                'category' => 'Beverages',
                'price' => 450,
                'cost_price' => 360,
                'sku' => 'GS-004',
                'unit' => 'jar',
                'track_stock' => true,
                'stock_quantity' => 30,
                'low_stock_threshold' => 8,
                'description' => 'Chocolate malt drink for families.',
            ],
            [
                'name' => 'Dettol Soap',
                'category' => 'Household',
                'price' => 120,
                'cost_price' => 90,
                'sku' => 'GS-005',
                'unit' => 'pcs',
                'track_stock' => true,
                'stock_quantity' => 50,
                'low_stock_threshold' => 10,
                'description' => 'Hygiene soap for everyday use.',
            ],
        ];

        foreach ($items as $itemData) {
            $category = Category::where('restaurant_id', $restaurant->id)
                ->where('name', $itemData['category'])
                ->first();

            MenuItem::updateOrCreate([
                'restaurant_id' => $restaurant->id,
                'sku' => $itemData['sku'],
            ], [
                'category_id' => $category?->id,
                'name' => $itemData['name'],
                'description' => $itemData['description'],
                'price' => $itemData['price'],
                'cost_price' => $itemData['cost_price'],
                'unit' => $itemData['unit'],
                'is_available' => true,
                'track_stock' => $itemData['track_stock'],
                'stock_quantity' => $itemData['stock_quantity'],
                'low_stock_threshold' => $itemData['low_stock_threshold'],
            ]);
        }
    }
}
