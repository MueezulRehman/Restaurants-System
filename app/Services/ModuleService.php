<?php

namespace App\Services;

use App\Models\Module;
use Illuminate\Support\Facades\Auth;

class ModuleService
{
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

    public static function ensureDefaults(): void
    {
        static::seedDefaultModules();
        static::seedDefaultBusinessTypes();
    }

    /**
     * Seed default modules.
     */
    public static function seedDefaultModules(): void
    {
        $modules = [
            ['name' => 'Orders', 'key' => 'orders', 'description' => 'Online order management', 'sort_order' => 1, 'is_active' => true],
            ['name' => 'POS', 'key' => 'pos', 'description' => 'Point of Sale register system', 'sort_order' => 2, 'is_active' => true],
            ['name' => 'Menu', 'key' => 'menu', 'description' => 'Menu item catalog', 'sort_order' => 3, 'is_active' => true],
            ['name' => 'Categories', 'key' => 'categories', 'description' => 'Menu categories', 'sort_order' => 4, 'is_active' => true],
            ['name' => 'Variants', 'key' => 'variants', 'description' => 'Item variants and options', 'sort_order' => 5, 'is_active' => true],
            ['name' => 'Deals', 'key' => 'deals', 'description' => 'Combo and deal management', 'sort_order' => 6, 'is_active' => true],
            ['name' => 'Cashbook', 'key' => 'cashbook', 'description' => 'Cashbook entries', 'sort_order' => 7, 'is_active' => true],
            ['name' => 'Expenses', 'key' => 'expenses', 'description' => 'Business expense tracking', 'sort_order' => 8, 'is_active' => true],
            ['name' => 'HR', 'key' => 'hr', 'description' => 'HR and staff administration', 'sort_order' => 9, 'is_active' => true],
            ['name' => 'Staff', 'key' => 'staff', 'description' => 'Staff management', 'sort_order' => 10, 'is_active' => true],
            ['name' => 'Attendance', 'key' => 'attendance', 'description' => 'Attendance tracking', 'sort_order' => 11, 'is_active' => true],
            ['name' => 'Salary', 'key' => 'salary', 'description' => 'Salary management', 'sort_order' => 12, 'is_active' => true],
            ['name' => 'Inventory', 'key' => 'inventory', 'description' => 'Inventory and stock management', 'sort_order' => 13, 'is_active' => true],
            ['name' => 'Delivery', 'key' => 'delivery', 'description' => 'Delivery tracking and management', 'sort_order' => 14, 'is_active' => true],
            ['name' => 'Reports', 'key' => 'reports', 'description' => 'Sales and inventory reports', 'sort_order' => 15, 'is_active' => true],
            ['name' => 'Feedback', 'key' => 'feedback', 'description' => 'Customer feedback and suggestions', 'sort_order' => 16, 'is_active' => true],
            ['name' => 'Customers', 'key' => 'customers', 'description' => 'Customer list and order history', 'sort_order' => 17, 'is_active' => true],
            ['name' => 'Notifications', 'key' => 'notifications', 'description' => 'Browser and WhatsApp notifications', 'sort_order' => 18, 'is_active' => true],
            ['name' => 'Tables', 'key' => 'tables', 'description' => 'Table management and table orders', 'sort_order' => 19, 'is_active' => true],
            ['name' => 'Stock', 'key' => 'stock', 'description' => 'Stock adjustment and history', 'sort_order' => 20, 'is_active' => true],
            ['name' => 'Medical', 'key' => 'medical', 'description' => 'Pharmacy and medical-store workflows', 'sort_order' => 21, 'is_active' => true],
            ['name' => 'Medical Records', 'key' => 'medical-records', 'description' => 'Prescription and medical record tracking', 'sort_order' => 22, 'is_active' => true],
            ['name' => 'General Store', 'key' => 'general_store', 'description' => 'Core modules for general-store workflows', 'sort_order' => 23, 'is_active' => true],
            ['name' => 'Pharmacy', 'key' => 'pharmacy', 'description' => 'Core modules for pharmacy workflows', 'sort_order' => 24, 'is_active' => true],
            ['name' => 'Allergies', 'key' => 'allergies', 'description' => 'Customer allergy tracking and warnings', 'sort_order' => 25, 'is_active' => true],
        ];

        // 'pos' already covers the register/checkout screen itself; the mode
        // (restaurant menu vs. barcode retail vs. medicine lookup) is decided
        // per business type in Restaurant::getPosMode(), not by a separate module.

        foreach ($modules as $module) {
            Module::updateOrCreate(['key' => $module['key']], $module);
        }
    }

    public static function getDefaultModuleKeysForBusinessType($businessType): array
    {
        if ($businessType instanceof \App\Models\BusinessType) {
            return $businessType->modules()->pluck('key')->toArray();
        }

        $typeName = trim((string) $businessType);
        $normalizedName = mb_strtolower($typeName);

        $moduleMap = [
            'restaurant' => ['orders', 'pos', 'menu', 'categories', 'variants', 'deals', 'cashbook', 'expenses', 'hr', 'staff', 'attendance', 'salary', 'reports', 'feedback', 'customers', 'tables', 'allergies'],
            'fast food' => ['orders', 'pos', 'menu', 'categories', 'cashbook', 'expenses', 'reports', 'feedback', 'customers', 'allergies'],
            'retail / shop' => ['pos', 'inventory', 'categories', 'variants', 'cashbook', 'expenses', 'hr', 'staff', 'attendance', 'salary', 'reports', 'feedback', 'customers', 'allergies'],
            'cafe / bakery' => ['orders', 'pos', 'menu', 'categories', 'variants', 'deals', 'cashbook', 'expenses', 'hr', 'staff', 'attendance', 'salary', 'reports', 'feedback', 'customers', 'tables', 'allergies'],
            'general business' => ['pos', 'inventory', 'categories', 'variants', 'stock', 'cashbook', 'expenses', 'hr', 'staff', 'attendance', 'salary', 'reports', 'feedback', 'customers', 'allergies', 'general_store'],
            'general store' => ['pos', 'inventory', 'categories', 'variants', 'stock', 'cashbook', 'expenses', 'hr', 'staff', 'attendance', 'salary', 'reports', 'feedback', 'customers', 'allergies', 'general_store'],
            'medical store' => ['pos', 'inventory', 'categories', 'cashbook', 'expenses', 'hr', 'staff', 'attendance', 'salary', 'reports', 'stock', 'customers', 'medical', 'medical-records', 'allergies', 'pharmacy'],
            'pharmacy' => ['pos', 'inventory', 'categories', 'cashbook', 'expenses', 'hr', 'staff', 'attendance', 'salary', 'reports', 'stock', 'customers', 'medical', 'medical-records', 'allergies', 'pharmacy'],
            'other / custom' => ['pos', 'inventory', 'categories', 'cashbook', 'expenses', 'hr', 'staff', 'attendance', 'salary', 'reports', 'customers', 'stock', 'allergies'],
        ];

        if (array_key_exists($normalizedName, $moduleMap)) {
            return $moduleMap[$normalizedName];
        }

        $businessTypeModel = \App\Models\BusinessType::where('name', $typeName)->first();

        return $businessTypeModel ? $businessTypeModel->modules()->pluck('key')->toArray() : [];
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
                'modules' => ['orders', 'pos', 'menu', 'categories', 'variants', 'deals', 'cashbook', 'expenses', 'hr', 'staff', 'attendance', 'salary', 'reports', 'feedback', 'customers', 'tables', 'allergies'],
            ],
            [
                'name' => 'Fast Food',
                'description' => 'Fast food kitchen for quick takeaway and delivery',
                'modules' => ['orders', 'pos', 'menu', 'categories', 'cashbook', 'expenses', 'reports', 'feedback', 'customers', 'allergies'],
            ],
            [
                'name' => 'Retail / Shop',
                'description' => 'Shop or retail business without a customer storefront',
                'modules' => ['pos', 'inventory', 'categories', 'variants', 'cashbook', 'expenses', 'hr', 'staff', 'attendance', 'salary', 'reports', 'feedback', 'customers', 'allergies'],
            ],
            [
                'name' => 'Cafe / Bakery',
                'description' => 'Cafe or bakery with optional storefront ordering',
                'modules' => ['orders', 'pos', 'menu', 'categories', 'variants', 'deals', 'cashbook', 'expenses', 'hr', 'staff', 'attendance', 'salary', 'reports', 'feedback', 'customers', 'tables', 'allergies'],
            ],
            [
                'name' => 'General Store',
                'description' => 'General store operations without a public storefront',
                'modules' => ['pos', 'inventory', 'categories', 'variants', 'stock', 'cashbook', 'expenses', 'hr', 'staff', 'attendance', 'salary', 'reports', 'feedback', 'customers', 'allergies', 'general_store'],
            ],
            [
                'name' => 'Pharmacy',
                'description' => 'Pharmacy / medical store with medicine lookup billing',
                'modules' => ['pos', 'inventory', 'categories', 'cashbook', 'expenses', 'hr', 'staff', 'attendance', 'salary', 'reports', 'stock', 'customers', 'medical', 'medical-records', 'allergies', 'pharmacy'],
            ],
            [
                'name' => 'Other / Custom',
                'description' => 'Flexible setup for a custom business workflow',
                'modules' => ['pos', 'inventory', 'categories', 'cashbook', 'expenses', 'hr', 'staff', 'attendance', 'salary', 'reports', 'customers', 'stock', 'allergies'],
            ],
        ];

        foreach ($businessTypes as $typeData) {
            $modules = $typeData['modules'];
            unset($typeData['modules']);

            $businessType = \App\Models\BusinessType::updateOrCreate(
                ['name' => $typeData['name']],
                array_merge($typeData, ['is_active' => true, 'sort_order' => 0])
            );

            $moduleIds = Module::whereIn('key', $modules)->pluck('id');
            $businessType->modules()->sync($moduleIds);
        }
    }
}
