<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnsureSuperAdmin
{
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();

        if (! $user instanceof User || ! $user->isSuperAdmin()) {
            abort(403, 'This area is only accessible to the platform super admin.');
        }

        return $next($request);
    }
}
