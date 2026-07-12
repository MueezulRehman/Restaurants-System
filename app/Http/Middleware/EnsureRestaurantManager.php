<?php

namespace App\Http\Middleware;

use App\Models\User;
use App\Support\Tenancy;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnsureRestaurantManager
{
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();

        if (! $user instanceof User) {
            abort(403, 'This area is only accessible to restaurant managers.');
        }

        if ($user->isRestaurantManager()) {
            return $next($request);
        }

        // A super admin who has "entered" a restaurant via the Restaurants
        // list is allowed through too — everything downstream (the
        // BelongsToRestaurant trait, module checks, etc.) treats them as
        // that restaurant's manager for the duration.
        if ($user->isSuperAdmin() && Tenancy::isImpersonating()) {
            return $next($request);
        }

        abort(403, 'This area is only accessible to restaurant managers.');
    }
}
