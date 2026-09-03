<?php

namespace App\Http\Middleware;

use App\Models\User;
use App\Support\Tenancy;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Codeibex subscription gate with grace period (Hardened)
 *
 * Rules:
 * - Super Admin → always allowed
 * - Active / Trial → allowed (warning 7 days before end)
 * - Expired within 5-day grace → allowed + warning
 * - Expired past grace → only subscription + logout routes allowed
 *
 * @author Mueez Ul Rehman
 */
class EnsureSubscriptionActive
{
    public const GRACE_DAYS = 5;
    public const WARNING_DAYS = 7;

    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();

        // Super Admin or unauthenticated edge cases
        if (! $user instanceof User || $user->isSuperAdmin()) {
            return $next($request);
        }

        $restaurant = $user->effectiveRestaurant() ?? Tenancy::current();

        if (! $restaurant) {
            return $next($request);
        }

        $subscription = $restaurant->subscription;

        // No subscription record yet — allow (onboarding / trial creation can happen)
        if (! $subscription) {
            return $next($request);
        }

        $status = $subscription->status ?? 'active';
        $endsAt = $subscription->current_period_end
            ?? $subscription->trial_ends_at
            ?? $restaurant->trial_ends_at;

        $allowedWhenBlocked = $request->routeIs([
            'manager.subscription.*',
            'manager.logout',
            'manager.login',
            'logout',
        ]);

        // Active or still in trial
        if (in_array($status, ['active', 'trial'], true)) {
            if ($endsAt && now()->lt($endsAt) && now()->diffInDays($endsAt, false) <= self::WARNING_DAYS) {
                session()->flash(
                    'subscription_warning',
                    'Your subscription expires on ' . $endsAt->format('M d, Y') . '. Please renew to avoid interruption.'
                );
            }

            return $next($request);
        }

        // Expired / cancelled
        if (in_array($status, ['expired', 'cancelled'], true) || ($endsAt && now()->gt($endsAt))) {
            $graceEnd = $endsAt ? $endsAt->copy()->addDays(self::GRACE_DAYS) : null;

            // Still inside grace period
            if ($graceEnd && now()->lte($graceEnd)) {
                session()->flash(
                    'subscription_warning',
                    'Grace period active. Renew by ' . $graceEnd->format('M d, Y') . ' or access will be blocked.'
                );

                return $next($request);
            }

            // Past grace — only subscription + logout allowed
            if ($allowedWhenBlocked) {
                return $next($request);
            }

            return redirect()
                ->route('manager.subscription.show')
                ->with('error', 'Subscription expired. Please renew to continue using Codeibex.');
        }

        return $next($request);
    }
}
