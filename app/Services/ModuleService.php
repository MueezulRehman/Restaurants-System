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
            ['name' => 'Business Theme & Settings', 'key' => 'theme', 'description' => 'Business profile, branding, and storefront appearance settings', 'sort_order' => 7, 'is_active' => true],
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
            ['name' => 'Storefront Notices', 'key' => 'storefront-notices', 'description' => 'Customer-facing storefront messages', 'sort_order' => 18, 'is_active' => true],
            ['name' => 'Tables', 'key' => 'tables', 'description' => 'Table management and table orders', 'sort_order' => 19, 'is_active' => true],
            ['name' => 'Stock', 'key' => 'stock', 'description' => 'Stock adjustment and history', 'sort_order' => 20, 'is_active' => true],
            ['name' => 'Item Sales', 'key' => 'item-sales', 'description' => 'Promotional sales and discounts for menu items', 'sort_order' => 21, 'is_active' => true],
            ['name' => 'Medical', 'key' => 'medical', 'description' => 'Pharmacy and medical-store workflows', 'sort_order' => 21, 'is_active' => true],
            ['name' => 'Medical Records', 'key' => 'medical-records', 'description' => 'Prescription and medical record tracking', 'sort_order' => 22, 'is_active' => true],
            ['name' => 'General Store', 'key' => 'general_store', 'description' => 'Core modules for general-store workflows', 'sort_order' => 23, 'is_active' => true],
            ['name' => 'Pharmacy', 'key' => 'pharmacy', 'description' => 'Core modules for pharmacy workflows', 'sort_order' => 24, 'is_active' => true],
            ['name' => 'Allergies', 'key' => 'allergies', 'description' => 'Customer allergy tracking and warnings', 'sort_order' => 25, 'is_active' => true],
            ['name' => 'Suppliers', 'key' => 'suppliers', 'description' => 'Supplier directory and supplier contacts', 'sort_order' => 26, 'is_active' => true],
            ['name' => 'Purchasing', 'key' => 'purchasing', 'description' => 'Purchase orders and receiving stock from suppliers', 'sort_order' => 27, 'is_active' => true],
            ['name' => 'Sales Returns', 'key' => 'sales-returns', 'description' => 'Customer returns, exchanges, refunds, and returned stock', 'sort_order' => 28, 'is_active' => true],
            ['name' => 'Warranty', 'key' => 'warranty', 'description' => 'Warranty registration, claims, and expiry tracking', 'sort_order' => 29, 'is_active' => true],
            ['name' => 'Repairs', 'key' => 'repairs', 'description' => 'Repair intake, service status, technician assignment, and collection', 'sort_order' => 30, 'is_active' => true],
            ['name' => 'Trade-ins', 'key' => 'trade-ins', 'description' => 'Used device trade-in valuation and customer credit', 'sort_order' => 31, 'is_active' => true],
            ['name' => 'Installments', 'key' => 'installments', 'description' => 'Installment plans, deposits, schedules, and outstanding payments', 'sort_order' => 32, 'is_active' => true],
            ['name' => 'Brands', 'key' => 'brands', 'description' => 'Product brands and brand-level catalog filtering', 'sort_order' => 33, 'is_active' => true],
            ['name' => 'Collections', 'key' => 'collections', 'description' => 'Clothing collections and seasonal product grouping', 'sort_order' => 34, 'is_active' => true],
            ['name' => 'Barcode Labels', 'key' => 'barcode-labels', 'description' => 'Generate and print product barcode labels', 'sort_order' => 35, 'is_active' => true],
            ['name' => 'Loyalty', 'key' => 'loyalty', 'description' => 'Customer points, rewards, and repeat-purchase benefits', 'sort_order' => 36, 'is_active' => true],
            ['name' => 'Stock Transfers', 'key' => 'stock-transfers', 'description' => 'Move stock between branches or storage locations', 'sort_order' => 37, 'is_active' => true],
            ['name' => 'Profit Margins', 'key' => 'profit-margins', 'description' => 'Cost, revenue, gross profit, and margin analysis', 'sort_order' => 38, 'is_active' => true],
            ['name' => 'Business Theme', 'key' => 'manager-theme', 'description' => 'Manager dashboard appearance and business branding controls', 'sort_order' => 39, 'is_active' => true],
            ['name' => 'Customer Storefront Theme', 'key' => 'customer-theme', 'description' => 'Customer-facing menu, storefront, and notice appearance controls', 'sort_order' => 40, 'is_active' => true],
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
            $keys = $businessType->modules()->pluck('key')->toArray();
            return in_array('theme', $keys, true) ? $keys : [...$keys, 'theme'];
        }

        $typeName = trim((string) $businessType);
        $normalizedName = mb_strtolower($typeName);

        $moduleMap = [
            'restaurant' => ['orders', 'pos', 'menu', 'categories', 'variants', 'deals', 'item-sales', 'cashbook', 'expenses', 'hr', 'staff', 'attendance', 'salary', 'reports', 'feedback', 'customers', 'tables', 'allergies'],
            'fast food' => ['orders', 'pos', 'menu', 'categories', 'item-sales', 'cashbook', 'expenses', 'reports', 'feedback', 'customers', 'allergies'],
            'retail / shop' => ['pos', 'inventory', 'categories', 'variants', 'item-sales', 'cashbook', 'expenses', 'hr', 'staff', 'attendance', 'salary', 'reports', 'feedback', 'customers'],
            'mobile shop' => ['pos', 'inventory', 'categories', 'variants', 'stock', 'item-sales', 'cashbook', 'expenses', 'hr', 'staff', 'attendance', 'salary', 'reports', 'feedback', 'customers', 'suppliers', 'purchasing', 'sales-returns', 'warranty', 'repairs', 'trade-ins', 'installments', 'brands', 'barcode-labels', 'loyalty', 'stock-transfers', 'profit-margins'],
            'mobile store' => ['pos', 'inventory', 'categories', 'variants', 'stock', 'item-sales', 'cashbook', 'expenses', 'hr', 'staff', 'attendance', 'salary', 'reports', 'feedback', 'customers', 'suppliers', 'purchasing', 'sales-returns', 'warranty', 'repairs', 'trade-ins', 'installments', 'brands', 'barcode-labels', 'loyalty', 'stock-transfers', 'profit-margins'],
            'clothing store' => ['pos', 'inventory', 'categories', 'variants', 'stock', 'item-sales', 'cashbook', 'expenses', 'hr', 'staff', 'attendance', 'salary', 'reports', 'feedback', 'customers', 'suppliers', 'purchasing', 'sales-returns', 'brands', 'collections', 'barcode-labels', 'loyalty', 'stock-transfers', 'profit-margins'],
            'clothing' => ['pos', 'inventory', 'categories', 'variants', 'stock', 'item-sales', 'cashbook', 'expenses', 'hr', 'staff', 'attendance', 'salary', 'reports', 'feedback', 'customers', 'suppliers', 'purchasing', 'sales-returns', 'brands', 'collections', 'barcode-labels', 'loyalty', 'stock-transfers', 'profit-margins'],
            'cafe / bakery' => ['orders', 'pos', 'menu', 'categories', 'variants', 'deals', 'item-sales', 'cashbook', 'expenses', 'hr', 'staff', 'attendance', 'salary', 'reports', 'feedback', 'customers', 'tables', 'allergies'],
            'general business' => ['pos', 'inventory', 'categories', 'variants', 'stock', 'cashbook', 'expenses', 'hr', 'staff', 'attendance', 'salary', 'reports', 'feedback', 'customers', 'general_store'],
            'general store' => ['pos', 'inventory', 'categories', 'variants', 'stock', 'item-sales', 'cashbook', 'expenses', 'hr', 'staff', 'attendance', 'salary', 'reports', 'feedback', 'customers', 'general_store'],
            'medical store' => ['pos', 'inventory', 'categories', 'cashbook', 'expenses', 'hr', 'staff', 'attendance', 'salary', 'reports', 'stock', 'customers', 'suppliers', 'medical', 'medical-records', 'allergies', 'pharmacy'],
            'pharmacy' => ['pos', 'inventory', 'categories', 'cashbook', 'expenses', 'hr', 'staff', 'attendance', 'salary', 'reports', 'stock', 'customers', 'suppliers', 'medical', 'medical-records', 'allergies', 'pharmacy'],
            'other / custom' => ['pos', 'inventory', 'categories', 'variants', 'cashbook', 'expenses', 'hr', 'staff', 'attendance', 'salary', 'reports', 'customers', 'stock', 'feedback'],
        ];

        if (array_key_exists($normalizedName, $moduleMap)) {
            return array_values(array_unique([...$moduleMap[$normalizedName], 'theme', 'manager-theme', 'customer-theme']));
        }

        $businessTypeModel = \App\Models\BusinessType::where('name', $typeName)->first();

        if (! $businessTypeModel) {
            return ['theme'];
        }

        $keys = $businessTypeModel->modules()->pluck('key')->toArray();
        return in_array('theme', $keys, true) ? $keys : [...$keys, 'theme'];
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
                'modules' => ['pos', 'inventory', 'categories', 'variants', 'cashbook', 'expenses', 'hr', 'staff', 'attendance', 'salary', 'reports', 'feedback', 'customers'],
            ],
            [
                'name' => 'Mobile Shop',
                'description' => 'Mobile phones, tablets, chargers, cases, accessories, and repair parts',
                'modules' => ['pos', 'inventory', 'categories', 'variants', 'stock', 'item-sales', 'cashbook', 'expenses', 'hr', 'staff', 'attendance', 'salary', 'reports', 'feedback', 'customers', 'suppliers', 'purchasing', 'sales-returns', 'warranty', 'repairs', 'trade-ins', 'installments', 'brands', 'barcode-labels', 'loyalty', 'stock-transfers', 'profit-margins'],
            ],
            [
                'name' => 'Clothing Store',
                'description' => 'Clothing and garments with size, color, stock, and summer/winter seasons',
                'modules' => ['pos', 'inventory', 'categories', 'variants', 'stock', 'item-sales', 'cashbook', 'expenses', 'hr', 'staff', 'attendance', 'salary', 'reports', 'feedback', 'customers', 'suppliers', 'purchasing', 'sales-returns', 'brands', 'collections', 'barcode-labels', 'loyalty', 'stock-transfers', 'profit-margins'],
            ],
            [
                'name' => 'Cafe / Bakery',
                'description' => 'Cafe or bakery with optional storefront ordering',
                'modules' => ['orders', 'pos', 'menu', 'categories', 'variants', 'deals', 'cashbook', 'expenses', 'hr', 'staff', 'attendance', 'salary', 'reports', 'feedback', 'customers', 'tables', 'allergies'],
            ],
            [
                'name' => 'General Store',
                'description' => 'General store operations without a public storefront',
                'modules' => ['pos', 'inventory', 'categories', 'variants', 'stock', 'cashbook', 'expenses', 'hr', 'staff', 'attendance', 'salary', 'reports', 'feedback', 'customers', 'general_store'],
            ],
            [
                'name' => 'General Business',
                'description' => 'Legacy name for general store operations',
                'modules' => ['pos', 'inventory', 'categories', 'variants', 'stock', 'cashbook', 'expenses', 'hr', 'staff', 'attendance', 'salary', 'reports', 'feedback', 'customers', 'general_store'],
            ],
            [
                'name' => 'Medical Store',
                'description' => 'Pharmacy / medical store with medicine lookup billing',
                'modules' => ['pos', 'inventory', 'categories', 'cashbook', 'expenses', 'hr', 'staff', 'attendance', 'salary', 'reports', 'stock', 'customers', 'suppliers', 'medical', 'medical-records', 'allergies', 'pharmacy'],
            ],
            [
                'name' => 'Other / Custom',
                'description' => 'Flexible setup for a custom business workflow',
                'modules' => ['pos', 'inventory', 'categories', 'variants', 'cashbook', 'expenses', 'hr', 'staff', 'attendance', 'salary', 'reports', 'customers', 'stock', 'feedback'],
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
            $themeId = Module::where('key', 'theme')->value('id');
            foreach (Module::whereIn('key', ['theme', 'manager-theme', 'customer-theme'])->pluck('id') as $themeModuleId) {
                if (! $moduleIds->contains($themeModuleId)) $moduleIds->push($themeModuleId);
            }
            $businessType->modules()->sync($moduleIds);
        }
    }
}
