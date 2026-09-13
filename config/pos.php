<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Business type -> POS mode
    |--------------------------------------------------------------------------
    | Keys are lowercased BusinessType names. Whatever isn't listed here
    | falls back to 'default_mode' below (currently 'retail').
    */
    'business_type_modes' => [
        // Restaurant-style POS (menu, sizes, toppings, tables)
        'restaurant' => 'restaurant',
        'fast food' => 'restaurant',
        'cafe / bakery' => 'restaurant',
        'cafe' => 'restaurant',
        'bakery' => 'restaurant',
        'hotel' => 'restaurant',
        // Retail / general shop POS (barcode, discounts, debt)
        'retail / shop' => 'retail',
        'retail' => 'retail',
        'shop' => 'retail',
        'general business' => 'retail',
        'general store' => 'retail',
        'grocery' => 'retail',
        'grocery / supermarket' => 'retail',
        'supermarket' => 'retail',
        'wholesale' => 'retail',
        'wholesale / distributor' => 'retail',
        'electronics' => 'retail',
        'electronics store' => 'retail',
        'mobile shop' => 'retail',
        'mobile store' => 'retail',
        'clothing' => 'retail',
        'clothing store' => 'retail',
        'garments' => 'retail',
        'pharmacy' => 'medical',
        // Medical POS
        'medical store' => 'medical',
        'medical' => 'medical',
        'clinic' => 'medical',
        'clinic / doctor' => 'medical',
        'salon / beauty' => 'retail',
        'gym / fitness' => 'retail',
        'services / repair business' => 'retail',
        'online store' => 'retail',
    ],

    'default_mode' => 'retail',

    /*
    |--------------------------------------------------------------------------
    | Per-mode UI copy
    |--------------------------------------------------------------------------
    */
    'modes' => [
        'restaurant' => [
            'title' => 'Restaurant POS',
            'item_label' => 'Menu Item',
            'item_label_plural' => 'Menu',
            'search_placeholder' => 'Search the menu…',
            'view' => 'manager.pos.restaurant',
        ],
        'retail' => [
            'title' => 'Shop POS',
            'item_label' => 'Product',
            'item_label_plural' => 'Products',
            'search_placeholder' => 'Scan barcode or search by product name / SKU…',
            'view' => 'manager.pos.counter',
        ],
        'medical' => [
            'title' => 'Medical Store POS',
            'item_label' => 'Medicine',
            'item_label_plural' => 'Medicines',
            'search_placeholder' => 'Search by medicine name or code…',
            'view' => 'manager.pos.counter',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Short payment / rounding behaviour
    |--------------------------------------------------------------------------
    | If `allow_short_payment_without_debt` is true, cashiers can confirm
    | accepting a small underpayment (<= `short_payment_threshold` Rs)
    | without creating a customer debt. When false, any unpaid balance
    | will be recorded as customer debt (requires a customer selected).
    */
    'allow_short_payment_without_debt' => true,
    'short_payment_threshold' => 10,

];
