<?php

/**
 * Codeibex multi-tenancy configuration (Hardened)
 *
 * Architecture:
 * - Central DB  : restaurants, users, subscriptions, business_types, modules, plans
 * - Tenant DB   : one MySQL/SQLite database per business with the FULL operational schema
 * - Modules     : access control only (UI + middleware) — tables always exist in tenant DB
 *
 * @author Mueez Ul Rehman
 */

return [

    /*
    |--------------------------------------------------------------------------
    | Auto-provision tenant database when a business is created
    |--------------------------------------------------------------------------
    */
    'auto_provision' => env('TENANCY_AUTO_PROVISION', true),

    /*
    |--------------------------------------------------------------------------
    | Database name pattern
    |--------------------------------------------------------------------------
    | Final name = prefix + restaurant id   e.g. codeibex_tenant_15
    */
    'database_prefix' => env('TENANCY_DB_PREFIX', 'codeibex_tenant_'),

    /*
    |--------------------------------------------------------------------------
    | Connection names
    |--------------------------------------------------------------------------
    */
    'connection' => 'tenant',                          // runtime tenant connection
    'central_connection' => env('DB_CONNECTION', 'mysql'), // platform / central

    /*
    |--------------------------------------------------------------------------
    | Path to tenant migrations (relative to base_path)
    |--------------------------------------------------------------------------
    */
    'migrations_path' => 'database/tenant_migrations',

    /*
    |--------------------------------------------------------------------------
    | Seed tenant after migrate
    |--------------------------------------------------------------------------
    */
    'seed_after_migrate' => env('TENANCY_SEED_AFTER_MIGRATE', true),

    /*
    |--------------------------------------------------------------------------
    | Filesystem isolation
    |--------------------------------------------------------------------------
    | When true, tenant files are stored under storage/app/tenants/{id}/
    */
    'filesystem_isolation' => env('TENANCY_FILESYSTEM_ISOLATION', true),

    /*
    |--------------------------------------------------------------------------
    | Driver defaults (merged with per-restaurant db_connection)
    |--------------------------------------------------------------------------
    */
    'template' => [
        'driver' => env('DB_CONNECTION', 'mysql'),
        'host' => env('DB_HOST', '127.0.0.1'),
        'port' => env('DB_PORT', '3306'),
        'username' => env('DB_USERNAME', 'root'),
        'password' => env('DB_PASSWORD', ''),
        'charset' => env('DB_CHARSET', 'utf8mb4'),
        'collation' => env('DB_COLLATION', 'utf8mb4_unicode_ci'),
        'prefix' => '',
        'prefix_indexes' => true,
        'strict' => true,
        'engine' => null,
    ],
];
