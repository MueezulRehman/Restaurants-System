<?php

namespace App\Support;

use App\Models\Restaurant;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

/**
 * Lets the platform super admin temporarily "enter" a specific restaurant —
 * everything they do afterward (POS, menu, cashbook, staff, etc.) is scoped
 * to that restaurant exactly as if they were its own admin/manager, until
 * they explicitly exit back to the platform-wide view.
 *
 * State lives in the session only — it's per-browser-tab-session, never
 * persisted, and never applies to anyone but a super_admin (see
 * BelongsToRestaurant and EnsureRestaurantManager, which are the only two
 * places that consult this).
 */
class Tenancy
{
    protected const SESSION_KEY = 'super_admin_managing_restaurant_id';

    public static function enter(Restaurant $restaurant): void
    {
        Session::put(self::SESSION_KEY, $restaurant->id);
    }

    public static function exit(): void
    {
        Session::forget(self::SESSION_KEY);
    }

    public static function isImpersonating(): bool
    {
        return Session::has(self::SESSION_KEY);
    }

    public static function impersonatedRestaurantId(): ?int
    {
        return Session::get(self::SESSION_KEY);
    }

    public static function impersonatedRestaurant(): ?Restaurant
    {
        $id = self::impersonatedRestaurantId();

        return $id ? Restaurant::find($id) : null;
    }

    public static function configureTenantConnection(Restaurant $restaurant): void
    {
        if (! $restaurant->hasTenantDatabase()) {
            return;
        }

        $tenantConfig = $restaurant->getTenantDatabaseConfig();
        config(['database.connections.tenant' => $tenantConfig]);
        config(['database.default' => 'tenant']);

        DB::purge('tenant');
        DB::reconnect('tenant');
    }
}
