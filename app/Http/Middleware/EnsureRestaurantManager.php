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

        // Allow platform super admins to reach their own account edit page
        // even when they see the manager navigation. This avoids confusing
        // 403s when a super admin is viewing manager screens but needs to
        // update their platform account details.
        if ($user->isSuperAdmin()) {
            $routeName = $request->route()?->getName() ?? '';
            if (str_starts_with($routeName, 'manager.account')) {
                return $next($request);
            }
        }

        abort(403, 'This area is restricted to restaurant managers. If you are a platform admin, use Admin → Restaurants to enter a restaurant, or visit Admin → My Account to update your platform account.');
    }
}
