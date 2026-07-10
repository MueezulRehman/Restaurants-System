<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnsureModuleEnabled
{
    public function handle(Request $request, Closure $next, string ...$moduleKeys)
    {
        $user = Auth::user();

        if (! $user instanceof User || ! $user->isRestaurantManager()) {
            return $next($request);
        }

        $restaurant = $user->restaurant;

        if (! $restaurant) {
            abort(403, 'No restaurant is linked to this manager account.');
        }

        foreach ($moduleKeys as $moduleKey) {
            if ($restaurant->isModuleEnabled($moduleKey)) {
                return $next($request);
            }
        }

        abort(403, 'This module is not enabled for your restaurant.');
    }
}
