<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnsureRestaurantManager
{
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();

        if (! $user instanceof User || ! $user->isRestaurantManager()) {
            abort(403, 'This area is only accessible to restaurant managers.');
        }

        return $next($request);
    }
}
