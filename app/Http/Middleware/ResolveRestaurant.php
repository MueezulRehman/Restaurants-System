<?php

namespace App\Http\Middleware;

use App\Models\Restaurant;
use Closure;
use Illuminate\Http\Request;

class ResolveRestaurant
{
    public function handle(Request $request, Closure $next)
    {
        if ($request->is('admin/*') || $request->is('admin') || $request->is('manager/*') || $request->is('manager') || $request->is('login')) {
            return $next($request);
        }

        if ($request->is('/')
            || $request->is('checkout')
            || $request->is('checkout/*')
            || $request->is('track')
            || $request->is('track/*')
            || $request->is('feedback')
            || $request->is('feedback/*')
            || $request->is('account')
            || $request->is('account/*')
            || $request->is('register')
            || $request->is('login')
            || $request->is('logout')) {
            return $next($request);
        }

        $host = strtolower($request->getHost());

        $restaurant = Restaurant::where('custom_domain', $host)
            ->orWhere('domain', $host)
            ->orWhereHas('domains', fn ($query) => $query->where('domain', $host))
            ->first();

        $hostIsLocal = str_contains($host, 'localhost')
            || $host === '127.0.0.1'
            || str_ends_with($host, '.test')
            || str_contains($host, '.local');

        if (! $restaurant && $hostIsLocal) {
            $subdomain = explode('.', $host)[0] ?? null;
            if ($subdomain && $subdomain !== 'localhost' && $subdomain !== '127') {
                $restaurant = Restaurant::where('slug', $subdomain)->first();
            }
        }

        // If still not found, attempt to resolve by the first path segment (slug)
        // This allows the main platform domain to serve restaurant storefronts
        // at /{slug} when a restaurant does not have a custom domain configured.
        if (! $restaurant) {
            $slug = $request->segment(1);
            if ($slug && ! in_array($slug, ['admin', 'manager', 'track', 'checkout', 'login'], true)) {
                $restaurant = Restaurant::where('slug', $slug)
                    ->where('status', 'active')
                    ->first();
            }
        }

        if (! $restaurant) {
            if ($hostIsLocal && $request->path() === '') {
                return $next($request);
            }

            abort(404, 'Restaurant not found');
        }

        $request->attributes->set('restaurant', $restaurant);
        app()->instance('restaurant', $restaurant);
        view()->share('currentRestaurant', $restaurant);

        if ($restaurant->hasTenantDatabase()) {
            \App\Support\Tenancy::configureTenantConnection($restaurant);
        }

        return $next($request);
    }
}
