<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Restricts a route to the restaurant's own admin/owner account. Managers
 * are deliberately excluded even though they share the /manager panel —
 * granting module access and managing other staff accounts is an
 * admin-only privilege, otherwise a manager could grant themselves (or
 * another manager) access they weren't supposed to have.
 */
class EnsureRestaurantAdmin
{
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();

        if (! $user instanceof User || ! in_array($user->role, ['super_admin', 'admin'], true)) {
            abort(403, 'Only the restaurant admin can manage staff and module access.');
        }

        return $next($request);
    }
}
