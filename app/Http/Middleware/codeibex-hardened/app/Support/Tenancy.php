<?php

namespace App\Support;

use App\Models\Restaurant;
use App\Services\TenantProvisioner;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;

/**
 * Codeibex Tenancy Helper (Hardened)
 *
 * Responsibilities:
 * - Super Admin impersonation (session-based)
 * - Safe switching of the default DB connection to a tenant database
 * - Tracking of current tenant context for the request
 * - Safe restore of previous connection state
 *
 * Architecture:
 * - Central connection remains the app default
 * - Tenant connection name is configurable (default: "tenant")
 * - When a tenant is active, database.default is temporarily pointed at the tenant connection
 *
 * @author Mueez Ul Rehman
 */
class Tenancy
{
    protected const SESSION_KEY = 'super_admin_managing_restaurant_id';

    /** @var int|null Currently active tenant restaurant id (request lifetime) */
    protected static ?int $currentRestaurantId = null;

    /** @var string|null Previous default connection before we switched */
    protected static ?string $previousDefaultConnection = null;

    /** @var bool Whether we currently have a tenant connection as default */
    protected static bool $tenantIsDefault = false;

    /**
     * Super Admin "enters" a business (impersonation + optional DB switch).
     */
    public static function enter(Restaurant $restaurant): void
    {
        Session::put(self::SESSION_KEY, $restaurant->id);
        self::setCurrent($restaurant);

        if ($restaurant->hasTenantDatabase()) {
            self::configureTenantConnection($restaurant);
        }

        Log::debug('Codeibex Tenancy: entered business', [
            'restaurant_id' => $restaurant->id,
            'has_tenant_db' => $restaurant->hasTenantDatabase(),
        ]);
    }

    /**
     * Exit impersonation and restore central connection.
     */
    public static function exit(): void
    {
        $id = self::impersonatedRestaurantId();

        Session::forget(self::SESSION_KEY);
        self::end();

        Log::debug('Codeibex Tenancy: exited business', [
            'restaurant_id' => $id,
        ]);
    }

    public static function isImpersonating(): bool
    {
        return Session::has(self::SESSION_KEY);
    }

    public static function impersonatedRestaurantId(): ?int
    {
        $id = Session::get(self::SESSION_KEY);

        return $id !== null ? (int) $id : null;
    }

    public static function impersonatedRestaurant(): ?Restaurant
    {
        $id = self::impersonatedRestaurantId();

        return $id ? Restaurant::on(config('database.default') === config('tenancy.connection')
            ? env('DB_CONNECTION', 'mysql')
            : config('database.default'))->find($id) : null;
    }

    /**
     * Mark a restaurant as the current tenant for this request
     * without necessarily switching the database yet.
     */
    public static function setCurrent(Restaurant $restaurant): void
    {
        self::$currentRestaurantId = $restaurant->id;
        app()->instance('restaurant', $restaurant);
        app()->instance('currentTenant', $restaurant);
    }

    /**
     * Get the current restaurant id (impersonation or resolved storefront).
     */
    public static function currentId(): ?int
    {
        if (self::$currentRestaurantId !== null) {
            return self::$currentRestaurantId;
        }

        if (self::isImpersonating()) {
            return self::impersonatedRestaurantId();
        }

        if (app()->bound('restaurant')) {
            $r = app('restaurant');

            return $r instanceof Restaurant ? $r->id : null;
        }

        return null;
    }

    /**
     * Get the current Restaurant model (central connection).
     */
    public static function current(): ?Restaurant
    {
        $id = self::currentId();

        if (! $id) {
            return null;
        }

        // Always read Restaurant from central connection
        $central = config('tenancy.central_connection', env('DB_CONNECTION', 'mysql'));

        return Restaurant::on($central)->find($id);
    }

    /**
     * Whether we are currently inside a tenant database context.
     */
    public static function isTenantContext(): bool
    {
        return self::$tenantIsDefault;
    }

    /**
     * Point the "tenant" connection at this restaurant's database
     * and make it the default for the rest of the request.
     * Stores previous default so we can restore safely.
     */
    public static function configureTenantConnection(Restaurant $restaurant): void
    {
        if (! $restaurant->hasTenantDatabase()) {
            return;
        }

        // Remember previous default only once per request
        if (! self::$tenantIsDefault) {
            self::$previousDefaultConnection = config('database.default');
        }

        app(TenantProvisioner::class)->useAsDefault($restaurant);

        self::$currentRestaurantId = $restaurant->id;
        self::$tenantIsDefault = true;

        // Make restaurant available to the container
        app()->instance('restaurant', $restaurant);
        app()->instance('currentTenant', $restaurant);
    }

    /**
     * End tenant context and restore the previous default connection.
     * Safe to call multiple times.
     */
    public static function end(): void
    {
        if (! self::$tenantIsDefault) {
            self::$currentRestaurantId = null;

            return;
        }

        $tenantConnection = config('tenancy.connection', 'tenant');
        $restoreTo = self::$previousDefaultConnection
            ?? config('tenancy.central_connection', env('DB_CONNECTION', 'mysql'));

        // Purge tenant connection to avoid stale PDO
        try {
            DB::purge($tenantConnection);
        } catch (\Throwable $e) {
            // ignore
        }

        config(['database.default' => $restoreTo]);

        self::$tenantIsDefault = false;
        self::$previousDefaultConnection = null;
        self::$currentRestaurantId = null;
    }

    /**
     * Run a callback inside a specific tenant context and always restore.
     *
     * Example:
     * Tenancy::runFor($restaurant, function () {
     *     Order::create([...]);
     * });
     */
    public static function runFor(Restaurant $restaurant, callable $callback): mixed
    {
        $wasTenant = self::$tenantIsDefault;
        $previousId = self::$currentRestaurantId;

        try {
            if ($restaurant->hasTenantDatabase()) {
                self::configureTenantConnection($restaurant);
            } else {
                self::setCurrent($restaurant);
            }

            return $callback($restaurant);
        } finally {
            if (! $wasTenant) {
                self::end();
            } else {
                // Restore previous tenant if we were already in one
                self::$currentRestaurantId = $previousId;
            }
        }
    }

    /**
     * Helper used by queue jobs / commands.
     */
    public static function forRestaurantId(int $restaurantId, callable $callback): mixed
    {
        $central = config('tenancy.central_connection', env('DB_CONNECTION', 'mysql'));
        $restaurant = Restaurant::on($central)->findOrFail($restaurantId);

        return self::runFor($restaurant, $callback);
    }
}
