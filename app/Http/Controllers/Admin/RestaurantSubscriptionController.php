<?php

namespace App\Http\Controllers\Admin;

use App\Models\RestaurantSubscription;
use App\Services\SubscriptionManager;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class RestaurantSubscriptionController extends Controller
{
    /**
     * Show subscription details (for restaurant admin).
     */
    public function show()
    {
        $user = auth()->user();
        $subscription = $user->effectiveRestaurant()->subscription;

        if (!$subscription) {
            return redirect()->route('manager.dashboard')
                ->with('info', 'No active subscription. Please contact support.');
        }

        $billingCycles = $subscription->billingCycles()
            ->orderBy('period_start', 'desc')
            ->paginate(10);

        return view('admin.subscription.show', compact('subscription', 'billingCycles'));
    }

    /**
     * Cancel subscription.
     */
    public function cancel(Request $request)
    {
        $user = auth()->user();
        $subscription = $user->effectiveRestaurant()->subscription;

        if (!$subscription) {
            return redirect()->route('manager.subscription.show')
                ->with('error', 'No active subscription.');
        }

        SubscriptionManager::cancel($subscription);

        return redirect()->route('manager.subscription.show')
            ->with('success', 'Subscription cancelled successfully.');
    }

    /**
     * Reactivate subscription.
     */
    public function reactivate(Request $request)
    {
        $user = auth()->user();
        $subscription = $user->effectiveRestaurant()->subscription;

        if (!$subscription || $subscription->status !== 'cancelled') {
            return redirect()->route('manager.subscription.show')
                ->with('error', 'Cannot reactivate this subscription.');
        }

        SubscriptionManager::reactivate($subscription);

        return redirect()->route('manager.subscription.show')
            ->with('success', 'Subscription reactivated successfully. Payment processing initiated.');
    }
}
