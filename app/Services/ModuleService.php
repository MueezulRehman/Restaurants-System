<?php

namespace App\Services;

use App\Models\Module;
use Illuminate\Support\Facades\Auth;

class ModuleService
{
    /**
     * Check if current restaurant has a module enabled.
     */
    public static function isEnabled(string $moduleKey): bool
    {
        $user = Auth::user();

        if (!$user || !$user->restaurant_id) {
            return false;
        }

        $restaurant = $user->restaurant;
        if (!$restaurant) {
            return false;
        }

        return $restaurant->isModuleEnabled($moduleKey);
    }

    /**
     * Get all enabled modules for current restaurant.
     */
    public static function getEnabled()
    {
        $user = Auth::user();

        if (!$user || !$user->restaurant_id) {
            return collect();
        }

        $restaurant = $user->restaurant;
        if (!$restaurant) {
            return collect();
        }

        return $restaurant->getEnabledModules();
    }

    /**
     * Seed default modules.
     */
    public static function seedDefaultModules(): void
    {
        $modules = [
            ['name' => 'Orders', 'key' => 'orders', 'description' => 'Online order management', 'sort_order' => 1],
            ['name' => 'POS', 'key' => 'pos', 'description' => 'Point of Sale register system', 'sort_order' => 2],
            ['name' => 'Menu', 'key' => 'menu', 'description' => 'Menu item catalog', 'sort_order' => 3],
            ['name' => 'Categories', 'key' => 'categories', 'description' => 'Menu categories', 'sort_order' => 4],
            ['name' => 'Variants', 'key' => 'variants', 'description' => 'Item variants and options', 'sort_order' => 5],
            ['name' => 'Deals', 'key' => 'deals', 'description' => 'Combo and deal management', 'sort_order' => 6],
            ['name' => 'Cashbook', 'key' => 'cashbook', 'description' => 'Cashbook entries', 'sort_order' => 7],
            ['name' => 'Expenses', 'key' => 'expenses', 'description' => 'Business expense tracking', 'sort_order' => 8],
            ['name' => 'Staff', 'key' => 'staff', 'description' => 'Staff management', 'sort_order' => 9],
            ['name' => 'Attendance', 'key' => 'attendance', 'description' => 'Attendance tracking', 'sort_order' => 10],
            ['name' => 'Salary', 'key' => 'salary', 'description' => 'Salary management', 'sort_order' => 11],
            ['name' => 'Inventory', 'key' => 'inventory', 'description' => 'Inventory and stock management', 'sort_order' => 12],
            ['name' => 'Delivery', 'key' => 'delivery', 'description' => 'Delivery tracking and management', 'sort_order' => 13],
            ['name' => 'Reports', 'key' => 'reports', 'description' => 'Sales and inventory reports', 'sort_order' => 14],
            ['name' => 'Feedback', 'key' => 'feedback', 'description' => 'Customer feedback and suggestions', 'sort_order' => 15],
            ['name' => 'Notifications', 'key' => 'notifications', 'description' => 'Browser and WhatsApp notifications', 'sort_order' => 16],
            ['name' => 'Stock', 'key' => 'stock', 'description' => 'Stock adjustment and history', 'sort_order' => 17],
        ];

        foreach ($modules as $module) {
            Module::firstOrCreate(['key' => $module['key']], $module);
        }
    }

    /**
     * Seed default business types with modules.
     */
    public static function seedDefaultBusinessTypes(): void
    {
        $businessTypes = [
            [
                'name' => 'Restaurant',
                'description' => 'Full-service restaurant with dine-in and delivery',
                'modules' => ['orders', 'pos', 'menu', 'categories', 'variants', 'deals', 'cashbook', 'expenses', 'staff', 'attendance', 'salary', 'reports', 'feedback'],
            ],
            [
                'name' => 'Retail / Shop',
                'description' => 'Shop or retail business without a customer storefront',
                'modules' => ['pos', 'inventory', 'categories', 'variants', 'cashbook', 'expenses', 'staff', 'attendance', 'salary', 'reports', 'feedback'],
            ],
            [
                'name' => 'Cafe / Bakery',
                'description' => 'Cafe or bakery with optional storefront ordering',
                'modules' => ['orders', 'pos', 'menu', 'categories', 'variants', 'deals', 'cashbook', 'expenses', 'staff', 'attendance', 'salary', 'reports', 'feedback'],
            ],
            [
                'name' => 'General Business',
                'description' => 'General business operations without a public storefront',
                'modules' => ['pos', 'inventory', 'variants', 'cashbook', 'expenses', 'staff', 'attendance', 'salary', 'reports', 'feedback'],
            ],
        ];

        foreach ($businessTypes as $typeData) {
            $modules = $typeData['modules'];
            unset($typeData['modules']);

            $businessType = \App\Models\BusinessType::firstOrCreate(
                ['name' => $typeData['name']],
                $typeData
            );

            // Attach modules
            $moduleIds = Module::whereIn('key', $modules)->pluck('id');
            $businessType->modules()->sync($moduleIds);
        }
    }
}
