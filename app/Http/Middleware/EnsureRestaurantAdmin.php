<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Restricts staff-management routes to restaurant accounts. Module grants
 * are still limited by StaffController to the current user's own enabled
 * access, so a manager cannot grant a module they do not have.
 */
class EnsureRestaurantAdmin
{
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();

        if (! $user instanceof User || ! in_array($user->role, ['super_admin', 'admin', 'manager'], true)) {
            abort(403, 'Only a restaurant account can manage staff.');
        }

        return $next($request);
    }
}
