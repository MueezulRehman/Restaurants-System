<?php

namespace App\Http\Middleware;

use App\Models\Restaurant;
use App\Support\Tenancy;
use Closure;
use Illuminate\Http\Request;

/**
 * Force tenant DB switch on public business routes (menu, checkout, track context).
 * Uses bound restaurant, session current_restaurant_id, or request restaurant_id.
 *
 * Register on web routes that need business operational data:
 *   Route::middleware([EnsurePublicTenantConnection::class])->group(...)
 *
 * @author Mueez Ul Rehman
 */
class EnsurePublicTenantConnection
{
    public function handle(Request $request, Closure $next)
    {
        $restaurant = $this->resolve($request);

        if ($restaurant) {
            app()->instance('restaurant', $restaurant);
            view()->share('currentRestaurant', $restaurant);
            $request->session()->put('current_restaurant_id', $restaurant->id);

            if ($restaurant->hasTenantDatabase()) {
                Tenancy::configureTenantConnection($restaurant);
            } else {
                Tenancy::setCurrent($restaurant);
            }
        }

        return $next($request);
    }

    protected function resolve(Request $request): ?Restaurant
    {
        if (app()->bound('restaurant')) {
            $r = app('restaurant');
            if ($r instanceof Restaurant) {
                return $r;
            }
        }

        $id = $request->input('restaurant_id')
            ?? $request->session()->get('current_restaurant_id');

        if (! $id) {
            return null;
        }

        $central = config('tenancy.central_connection', env('DB_CONNECTION', 'mysql'));

        return Restaurant::on($central)
            ->where('id', $id)
            ->where('status', 'active')
            ->first();
    }
}
