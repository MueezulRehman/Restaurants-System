<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnsureModuleEnabled
{
    /**
     * Gate a route group behind one or more module keys, e.g.
     * `middleware('module:menu,categories')`.
     *
     * Super admins always pass through untouched (they don't belong to a
     * restaurant). Restaurant admins and Managers pass as long as the
     * restaurant itself has the module enabled. User::hasModuleAccess() is
     * the shared source of truth for every login path.
     */
    public function handle(Request $request, Closure $next, string ...$moduleKeys)
    {
        $user = Auth::user();

        if (! $user instanceof User || ! $user->isRestaurantManager()) {
            return $next($request);
        }

        if (! $user->restaurant) {
            abort(403, 'No restaurant is linked to this account.');
        }

        foreach ($moduleKeys as $moduleKey) {
            if ($user->hasModuleAccess($moduleKey)) {
                return $next($request);
            }
        }

        abort(403, 'This module is not enabled for your restaurant.');
    }
}
