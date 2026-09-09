<?php

namespace App\Console\Commands;

use App\Models\Category;
use App\Models\Deal;
use App\Models\MenuItem;
use App\Models\MenuItemSize;
use App\Models\Restaurant;
use App\Support\Tenancy;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class MigrateLegacyCatalogCommand extends Command
{
    protected $signature = 'tenants:migrate-legacy-catalog
        {restaurantId? : Central restaurant id to migrate}
        {--all : Migrate every restaurant with legacy catalog records}
        {--apply : Write records to tenant databases}';

    protected $description = 'Copy legacy central catalog records into tenant databases';

    public function handle(): int
    {
        if (! $this->argument('restaurantId') && ! $this->option('all')) {
            $this->error('Provide a restaurant id or use --all. No data was changed.');

            return self::FAILURE;
        }

        $central = config('tenancy.central_connection', config('database.default'));
        $query = Restaurant::on($central)->orderBy('id');

        if ($this->argument('restaurantId')) {
            $query->whereKey((int) $this->argument('restaurantId'));
        }

        foreach ($query->get() as $restaurant) {
            $this->migrateRestaurant($restaurant, $central);
        }

        return self::SUCCESS;
    }

    protected function migrateRestaurant(Restaurant $restaurant, string $central): void
    {
        $source = DB::connection($central);
        $categories = $source->table('categories')
            ->where('restaurant_id', $restaurant->id)
            ->orderBy('id')
            ->get();
        $menuItems = $source->table('menu_items')
            ->where('restaurant_id', $restaurant->id)
            ->orderBy('id')
            ->get();
        $menuItemIds = $menuItems->pluck('id');
        $sizes = $menuItemIds->isEmpty()
            ? collect()
            : $source->table('menu_item_sizes')->whereIn('menu_item_id', $menuItemIds)->orderBy('id')->get();
        $deals = $source->table('deals')
            ->where('restaurant_id', $restaurant->id)
            ->orderBy('id')
            ->get();

        $suffix = $this->option('apply') ? '' : ' (dry-run)';
        $this->line("{$restaurant->id} {$restaurant->name}: {$categories->count()} categories, {$menuItems->count()} items, {$sizes->count()} sizes, {$deals->count()} deals{$suffix}");

        if (! $this->option('apply')) {
            return;
        }

        Tenancy::runFor($restaurant, function () use ($categories, $menuItems, $sizes, $deals, $restaurant): void {
            $categoryIds = [];

            foreach ($categories as $sourceCategory) {
                $category = Category::updateOrCreate(
                    ['restaurant_id' => $restaurant->id, 'name' => $sourceCategory->name],
                    [
                        'slug' => $sourceCategory->slug,
                        'description' => $sourceCategory->description,
                        'icon' => $sourceCategory->icon,
                        'sort_order' => $sourceCategory->sort_order,
                        'is_active' => $sourceCategory->is_active,
                    ]
                );
                $categoryIds[$sourceCategory->id] = $category->id;
            }

            $menuItemIds = [];
            foreach ($menuItems as $sourceItem) {
                $categoryId = $categoryIds[$sourceItem->category_id] ?? null;
                if (! $categoryId) {
                    continue;
                }

                $item = MenuItem::updateOrCreate(
                    ['restaurant_id' => $restaurant->id, 'name' => $sourceItem->name],
                    [
                        'category_id' => $categoryId,
                        'sku' => $sourceItem->sku,
                        'barcode' => $sourceItem->barcode,
                        'description' => $sourceItem->description,
                        'price' => $sourceItem->price,
                        'cost_price' => $sourceItem->cost_price,
                        'unit' => $sourceItem->unit,
                        'unit_type' => $sourceItem->unit_type,
                        'price_per_unit' => $sourceItem->price_per_unit,
                        'allow_fractional_qty' => $sourceItem->allow_fractional_qty,
                        'has_sizes' => $sourceItem->has_sizes,
                        'has_variants' => $sourceItem->has_variants,
                        'image' => $sourceItem->image,
                        'is_available' => $sourceItem->is_available,
                        'allows_toppings' => $sourceItem->allows_toppings,
                        'sort_order' => $sourceItem->sort_order,
                        'track_stock' => $sourceItem->track_stock,
                        'stock_quantity' => $sourceItem->stock_quantity,
                        'low_stock_threshold' => $sourceItem->low_stock_threshold,
                    ]
                );
                $menuItemIds[$sourceItem->id] = $item->id;
            }

            foreach ($sizes as $sourceSize) {
                $menuItemId = $menuItemIds[$sourceSize->menu_item_id] ?? null;
                if ($menuItemId) {
                    MenuItemSize::updateOrCreate(
                        ['menu_item_id' => $menuItemId, 'size_label' => $sourceSize->size_label],
                        ['price' => $sourceSize->price, 'sort_order' => $sourceSize->sort_order]
                    );
                }
            }

            foreach ($deals as $sourceDeal) {
                Deal::updateOrCreate(
                    ['restaurant_id' => $restaurant->id, 'name' => $sourceDeal->name],
                    [
                        'deal_number' => $sourceDeal->deal_number,
                        'price' => $sourceDeal->price,
                        'start_date' => $sourceDeal->start_date,
                        'end_date' => $sourceDeal->end_date,
                        'description' => $sourceDeal->description,
                        'image' => $sourceDeal->image,
                        'is_active' => $sourceDeal->is_active,
                    ]
                );
            }
        });
    }
}
