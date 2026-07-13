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

        $restaurant = $user->effectiveRestaurant();

        if (! $restaurant) {
            return $next($request);
        }

        $subscriptionActive = $restaurant->subscription && ($restaurant->subscription->isActive() || $restaurant->subscription->isInTrial());

        $allowedRoutes = [
            'manager.subscription.expired',
            'manager.subscription.show',
            'manager.subscription.pay',
            'manager.subscription.cancel',
            'manager.subscription.reactivate',
            'manager.logout',
            'manager.login',
            'manager.login.attempt',
        ];

        if (! $subscriptionActive && ! $request->routeIs($allowedRoutes)) {
            return redirect()->route('manager.subscription.expired');
        }

        return $next($request);
    }
}
