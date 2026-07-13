<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Deal;
use App\Models\MenuItem;
use App\Models\Restaurant;
use App\Models\Table;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class TenantDatabaseSeeder extends Seeder
{
    protected ?Restaurant $restaurant = null;

    public function setRestaurant(Restaurant $restaurant): self
    {
        $this->restaurant = $restaurant;

        return $this;
    }

    public function run(): void
    {
        if (! $this->restaurant) {
            return;
        }

        $businessTypeName = $this->restaurant->businessType?->name ?? 'Restaurant';
        $prefix = $this->restaurant->slug ?: Str::slug($this->restaurant->name);
        $enabledModules = is_array($this->restaurant->enabled_modules) ? $this->restaurant->enabled_modules : [];

        $this->seedCoreCatalog($prefix, $businessTypeName, $enabledModules);
    }

    protected function seedCoreCatalog(string $prefix, string $businessTypeName, array $enabledModules): void
    {
        $shouldSeedMenu = in_array('menu', $enabledModules, true)
            || in_array('orders', $enabledModules, true)
            || in_array('pos', $enabledModules, true);

        if ($shouldSeedMenu) {
            $category = Category::firstOrCreate([
                'restaurant_id' => $this->restaurant->id,
                'slug' => $prefix . '-starters',
            ], [
                'name' => ucfirst($prefix) . ' Starters',
                'description' => 'Starter items for ' . $this->restaurant->name,
                'icon' => '🍽️',
                'sort_order' => 1,
                'is_active' => true,
            ]);

            MenuItem::firstOrCreate([
                'restaurant_id' => $this->restaurant->id,
                'slug' => $prefix . '-signature-item',
            ], [
                'category_id' => $category->id,
                'name' => ucfirst($prefix) . ' Signature Item',
                'description' => 'Seeded demo item for ' . $this->restaurant->name,
                'price' => 199,
                'cost_price' => 120,
                'is_available' => true,
                'sort_order' => 1,
                'sku' => strtoupper(Str::slug($prefix) . '-001'),
            ]);
        }

        if (in_array('deals', $enabledModules, true) && in_array($businessTypeName, ['Restaurant', 'Cafe / Bakery', 'Fast Food'], true)) {
            Deal::firstOrCreate([
                'restaurant_id' => $this->restaurant->id,
                'name' => ucfirst($prefix) . ' Combo',
            ], [
                'deal_number' => 'DEAL-' . strtoupper(Str::slug($prefix)),
                'price' => 299,
                'description' => 'Starter combo for ' . $this->restaurant->name,
                'is_active' => true,
            ]);
        }

        if ($this->restaurant->isModuleEnabled('tables')) {
            Table::firstOrCreate([
                'restaurant_id' => $this->restaurant->id,
                'table_number' => 'T1',
            ], [
                'capacity' => 4,
                'status' => 'available',
                'is_active' => true,
            ]);
        }
    }
}
