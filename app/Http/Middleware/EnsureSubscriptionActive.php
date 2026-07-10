<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnsureSubscriptionActive
{
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();

        if (! $user instanceof User || ! $user->isRestaurantManager()) {
            return $next($request);
        }

        $restaurant = $user->restaurant;

        if (! $restaurant) {
            return $next($request);
        }

        $subscriptionActive = $restaurant->subscription && ($restaurant->subscription->isActive() || $restaurant->subscription->isInTrial());

        if (! $subscriptionActive && ! $request->routeIs('manager.subscription.expired') && ! $request->routeIs('manager.logout') && ! $request->routeIs('manager.login') && ! $request->routeIs('manager.login.attempt')) {
            return redirect()->route('manager.subscription.expired');
        }

        return $next($request);
    }
}
