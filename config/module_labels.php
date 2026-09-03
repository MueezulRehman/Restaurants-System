<?php

/**
 * Module display labels / icons by business type.
 * Same module key, different wording per business type.
 *
 * @author Mueez Ul Rehman
 */

return [

    /*
    |--------------------------------------------------------------------------
    | Default labels (fallback for any business type)
    |--------------------------------------------------------------------------
    */
    'default' => [
        'orders' => ['label' => 'Orders', 'icon' => 'fa-receipt'],
        'pos' => ['label' => 'POS', 'icon' => 'fa-cash-register'],
        'menu' => ['label' => 'Menu Items', 'icon' => 'fa-utensils'],
        'categories' => ['label' => 'Categories', 'icon' => 'fa-folder'],
        'variants' => ['label' => 'Variants', 'icon' => 'fa-layer-group'],
        'deals' => ['label' => 'Deals', 'icon' => 'fa-tags'],
        'inventory' => ['label' => 'Inventory', 'icon' => 'fa-boxes'],
        'stock' => ['label' => 'Stock', 'icon' => 'fa-warehouse'],
        'cashbook' => ['label' => 'Cashbook', 'icon' => 'fa-book'],
        'expenses' => ['label' => 'Expenses', 'icon' => 'fa-money-bill-wave'],
        'hr' => ['label' => 'HR', 'icon' => 'fa-user-tie'],
        'staff' => ['label' => 'Staff', 'icon' => 'fa-users'],
        'attendance' => ['label' => 'Attendance', 'icon' => 'fa-check-circle'],
        'salary' => ['label' => 'Salary', 'icon' => 'fa-wallet'],
        'customers' => ['label' => 'Customers', 'icon' => 'fa-user-friends'],
        'reports' => ['label' => 'Reports', 'icon' => 'fa-chart-line'],
        'feedback' => ['label' => 'Feedback', 'icon' => 'fa-comments'],
        'delivery' => ['label' => 'Delivery', 'icon' => 'fa-motorcycle'],
        'tables' => ['label' => 'Tables', 'icon' => 'fa-chair'],
        'medical' => ['label' => 'Medicines', 'icon' => 'fa-pills'],
        'medical-records' => ['label' => 'Medical Records', 'icon' => 'fa-notes-medical'],
        'allergies' => ['label' => 'Allergies', 'icon' => 'fa-allergies'],
        'pharmacy' => ['label' => 'Pharmacy', 'icon' => 'fa-prescription-bottle'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Per business-type overrides (matched on business type name, case-insensitive)
    |--------------------------------------------------------------------------
    */
    'types' => [

        'restaurant' => [
            'menu' => ['label' => 'Menu Items', 'icon' => 'fa-utensils'],
            'categories' => ['label' => 'Menu Categories', 'icon' => 'fa-list'],
            'pos' => ['label' => 'Restaurant POS', 'icon' => 'fa-cash-register'],
            'orders' => ['label' => 'Kitchen Orders', 'icon' => 'fa-receipt'],
            'deals' => ['label' => 'Combos & Deals', 'icon' => 'fa-tags'],
            'tables' => ['label' => 'Dining Tables', 'icon' => 'fa-chair'],
        ],

        'fast food' => [
            'menu' => ['label' => 'Menu Items', 'icon' => 'fa-hamburger'],
            'categories' => ['label' => 'Categories', 'icon' => 'fa-list'],
            'pos' => ['label' => 'Counter POS', 'icon' => 'fa-cash-register'],
            'deals' => ['label' => 'Combo Deals', 'icon' => 'fa-tags'],
        ],

        'cafe' => [
            'menu' => ['label' => 'Menu', 'icon' => 'fa-coffee'],
            'categories' => ['label' => 'Categories', 'icon' => 'fa-list'],
        ],

        'cafe / bakery' => [
            'menu' => ['label' => 'Bakery Menu', 'icon' => 'fa-bread-slice'],
            'categories' => ['label' => 'Categories', 'icon' => 'fa-list'],
        ],

        'bakery' => [
            'menu' => ['label' => 'Products', 'icon' => 'fa-bread-slice'],
            'categories' => ['label' => 'Categories', 'icon' => 'fa-list'],
        ],

        'retail / shop' => [
            'menu' => ['label' => 'Products', 'icon' => 'fa-box'],
            'categories' => ['label' => 'Product Categories', 'icon' => 'fa-folder'],
            'inventory' => ['label' => 'Inventory Items', 'icon' => 'fa-boxes'],
            'stock' => ['label' => 'Stock Levels', 'icon' => 'fa-warehouse'],
            'pos' => ['label' => 'Shop POS', 'icon' => 'fa-cash-register'],
            'orders' => ['label' => 'Sales Orders', 'icon' => 'fa-receipt'],
        ],

        'general store' => [
            'menu' => ['label' => 'Inventory Items', 'icon' => 'fa-boxes'],
            'categories' => ['label' => 'Item Categories', 'icon' => 'fa-folder'],
            'inventory' => ['label' => 'Inventory Items', 'icon' => 'fa-boxes'],
            'stock' => ['label' => 'Stock Tracking', 'icon' => 'fa-warehouse'],
            'pos' => ['label' => 'Store POS', 'icon' => 'fa-cash-register'],
            'orders' => ['label' => 'Bills / Sales', 'icon' => 'fa-receipt'],
            'variants' => ['label' => 'Units & Variants', 'icon' => 'fa-balance-scale'],
        ],

        'general business' => [
            'menu' => ['label' => 'Products', 'icon' => 'fa-box'],
            'categories' => ['label' => 'Categories', 'icon' => 'fa-folder'],
            'pos' => ['label' => 'Billing POS', 'icon' => 'fa-cash-register'],
        ],

        'medical store' => [
            'menu' => ['label' => 'Products', 'icon' => 'fa-pills'],
            'categories' => ['label' => 'Categories', 'icon' => 'fa-folder'],
            'medical' => ['label' => 'Medicines', 'icon' => 'fa-pills'],
            'inventory' => ['label' => 'Medicine Stock', 'icon' => 'fa-warehouse'],
            'stock' => ['label' => 'Stock', 'icon' => 'fa-warehouse'],
            'pos' => ['label' => 'Pharmacy POS', 'icon' => 'fa-cash-register'],
            'orders' => ['label' => 'Prescriptions / Sales', 'icon' => 'fa-file-medical'],
            'customers' => ['label' => 'Patients / Customers', 'icon' => 'fa-user-injured'],
        ],

        'pharmacy' => [
            'menu' => ['label' => 'Products', 'icon' => 'fa-pills'],
            'categories' => ['label' => 'Categories', 'icon' => 'fa-folder'],
            'medical' => ['label' => 'Medicines', 'icon' => 'fa-pills'],
            'inventory' => ['label' => 'Medicine Stock', 'icon' => 'fa-warehouse'],
            'stock' => ['label' => 'Stock', 'icon' => 'fa-warehouse'],
            'pos' => ['label' => 'Pharmacy POS', 'icon' => 'fa-cash-register'],
            'orders' => ['label' => 'Sales', 'icon' => 'fa-receipt'],
            'customers' => ['label' => 'Customers', 'icon' => 'fa-user-friends'],
        ],
    ],
];
