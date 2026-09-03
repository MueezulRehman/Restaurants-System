<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Deal;
use App\Models\MenuItem;
use App\Models\Restaurant;
use App\Models\Table;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

/**
 * Seeds demo catalog into the tenant DB.
 * First syncs the business row into tenant.restaurants so FKs succeed.
 *
 * @author Mueez Ul Rehman
 */
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

        $connection = config('tenancy.connection', 'tenant');

        if (config("database.connections.{$connection}")) {
            DB::setDefaultConnection($connection);
        }

        // FK categories.restaurant_id → restaurants.id lives ON the tenant DB.
        // Central restaurant row is not visible there — sync a mirror row first.
        $this->syncRestaurantMirror($connection);

        $businessTypeName = $this->restaurant->businessType?->name ?? 'Restaurant';
        $prefix = $this->restaurant->slug ?: Str::slug($this->restaurant->name);
        $enabledModules = is_array($this->restaurant->enabled_modules)
            ? $this->restaurant->enabled_modules
            : [];

        $this->seedCoreCatalog($connection, $prefix, $businessTypeName, $enabledModules);
    }

    /**
     * Copy minimal restaurant fields into the tenant database so foreign keys work.
     */
    protected function syncRestaurantMirror(string $connection): void
    {
        if (! Schema::connection($connection)->hasTable('restaurants')) {
            return;
        }

        $r = $this->restaurant;
        $payload = [
            'id' => $r->id,
            'name' => $r->name,
            'slug' => $r->slug ?: Str::slug($r->name) . '-' . $r->id,
            'updated_at' => now(),
            'created_at' => $r->created_at ?? now(),
        ];

        $optional = [
            'email' => $r->email,
            'phone' => $r->phone,
            'address' => $r->address,
            'custom_domain' => $r->custom_domain,
            'domain' => $r->domain ?? null,
            'plan' => $r->plan ?? 'basic',
            'status' => $r->status ?? 'active',
            'logo_path' => $r->logo_path,
            'theme' => is_array($r->theme) ? json_encode($r->theme) : $r->theme,
            'trial_ends_at' => $r->trial_ends_at,
        ];

        foreach ($optional as $column => $value) {
            if (Schema::connection($connection)->hasColumn('restaurants', $column)) {
                $payload[$column] = $value;
            }
        }

        // Preserve existing created_at if row already there
        $existing = DB::connection($connection)->table('restaurants')->where('id', $r->id)->first();
        if ($existing && isset($existing->created_at)) {
            $payload['created_at'] = $existing->created_at;
        }

        DB::connection($connection)->table('restaurants')->updateOrInsert(
            ['id' => $r->id],
            $payload
        );
    }

    protected function seedCoreCatalog(
        string $connection,
        string $prefix,
        string $businessTypeName,
        array $enabledModules
    ): void {
        $shouldSeedMenu = in_array('menu', $enabledModules, true)
            || in_array('orders', $enabledModules, true)
            || in_array('pos', $enabledModules, true)
            || empty($enabledModules); // seed basic menu if modules unknown

        if ($shouldSeedMenu && Schema::connection($connection)->hasTable('categories')) {
            $categoryAttrs = [
                'name' => ucfirst($prefix) . ' Starters',
                'description' => 'Starter items for ' . $this->restaurant->name,
                'sort_order' => 1,
                'is_active' => true,
            ];

            if (Schema::connection($connection)->hasColumn('categories', 'restaurant_id')) {
                $categoryAttrs['restaurant_id'] = $this->restaurant->id;
            }
            if (Schema::connection($connection)->hasColumn('categories', 'slug')) {
                $categoryAttrs['slug'] = $prefix . '-starters';
            }
            if (Schema::connection($connection)->hasColumn('categories', 'icon')) {
                $categoryAttrs['icon'] = '🍽️';
            }

            $unique = [];
            if (isset($categoryAttrs['restaurant_id'])) {
                $unique['restaurant_id'] = $categoryAttrs['restaurant_id'];
            }
            $unique['name'] = $categoryAttrs['name'];

            $category = Category::on($connection)->firstOrCreate($unique, $categoryAttrs);

            if (Schema::connection($connection)->hasTable('menu_items')) {
                $itemAttrs = [
                    'category_id' => $category->id,
                    'name' => ucfirst($prefix) . ' Signature Item',
                    'description' => 'Seeded demo item for ' . $this->restaurant->name,
                    'price' => 199,
                    'is_available' => true,
                    'sort_order' => 1,
                ];

                if (Schema::connection($connection)->hasColumn('menu_items', 'restaurant_id')) {
                    $itemAttrs['restaurant_id'] = $this->restaurant->id;
                }
                if (Schema::connection($connection)->hasColumn('menu_items', 'slug')) {
                    $itemAttrs['slug'] = $prefix . '-signature-item';
                }
                if (Schema::connection($connection)->hasColumn('menu_items', 'cost_price')) {
                    $itemAttrs['cost_price'] = 120;
                }
                if (Schema::connection($connection)->hasColumn('menu_items', 'sku')) {
                    $itemAttrs['sku'] = strtoupper(Str::slug($prefix) . '-001');
                }

                $itemUnique = [];
                if (isset($itemAttrs['restaurant_id'])) {
                    $itemUnique['restaurant_id'] = $itemAttrs['restaurant_id'];
                }
                $itemUnique['name'] = $itemAttrs['name'];

                MenuItem::on($connection)->firstOrCreate($itemUnique, $itemAttrs);
            }
        }

        if (
            in_array('deals', $enabledModules, true)
            && in_array($businessTypeName, ['Restaurant', 'Cafe / Bakery', 'Fast Food'], true)
            && Schema::connection($connection)->hasTable('deals')
        ) {
            $dealAttrs = [
                'name' => ucfirst($prefix) . ' Combo',
                'price' => 299,
                'description' => 'Starter combo for ' . $this->restaurant->name,
                'is_active' => true,
            ];

            if (Schema::connection($connection)->hasColumn('deals', 'restaurant_id')) {
                $dealAttrs['restaurant_id'] = $this->restaurant->id;
            }
            if (Schema::connection($connection)->hasColumn('deals', 'deal_number')) {
                // Column is integer in schema — use a stable numeric code per business
                $dealAttrs['deal_number'] = (int) ($this->restaurant->id * 100 + 1);
            }

            $dealUnique = [];
            if (isset($dealAttrs['restaurant_id'])) {
                $dealUnique['restaurant_id'] = $dealAttrs['restaurant_id'];
            }
            $dealUnique['name'] = $dealAttrs['name'];

            Deal::on($connection)->firstOrCreate($dealUnique, $dealAttrs);
        }

        if (
            method_exists($this->restaurant, 'isModuleEnabled')
            && $this->restaurant->isModuleEnabled('tables')
            && Schema::connection($connection)->hasTable('tables')
        ) {
            $tableAttrs = [
                'capacity' => 4,
                'status' => 'available',
                'is_active' => true,
            ];

            if (Schema::connection($connection)->hasColumn('tables', 'restaurant_id')) {
                $tableAttrs['restaurant_id'] = $this->restaurant->id;
            }

            $tableNumberCol = Schema::connection($connection)->hasColumn('tables', 'table_number')
                ? 'table_number'
                : (Schema::connection($connection)->hasColumn('tables', 'number') ? 'number' : null);

            if ($tableNumberCol) {
                $tableAttrs[$tableNumberCol] = 'T1';
                $tableUnique = [];
                if (isset($tableAttrs['restaurant_id'])) {
                    $tableUnique['restaurant_id'] = $tableAttrs['restaurant_id'];
                }
                $tableUnique[$tableNumberCol] = 'T1';

                Table::on($connection)->firstOrCreate($tableUnique, $tableAttrs);
            }
        }
    }
}
