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
        'restaurant' => 'restaurant',
        'cafe / bakery' => 'restaurant',
        'retail / shop' => 'retail',
        'medical store' => 'medical',
        'general business' => 'retail',
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
            'view' => 'admin.pos.restaurant',
        ],
        'retail' => [
            'title' => 'Shop POS',
            'item_label' => 'Product',
            'item_label_plural' => 'Products',
            'search_placeholder' => 'Scan barcode or search by product name / SKU…',
            'view' => 'admin.pos.counter',
        ],
        'medical' => [
            'title' => 'Medical Store POS',
            'item_label' => 'Medicine',
            'item_label_plural' => 'Medicines',
            'search_placeholder' => 'Search by medicine name or code…',
            'view' => 'admin.pos.counter',
        ],
    ],

];
