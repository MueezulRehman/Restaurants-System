<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;

class EnsureCeo
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        if (! $user instanceof User || ! $user->isCeo()) {
            abort(403, 'This area is only accessible to a CEO.');
        }

        return $next($request);
    }
}
