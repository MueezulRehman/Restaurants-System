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

        if (! $subscription) {
            return redirect()->route('manager.dashboard')
                ->with('info', 'No subscription data available. Please contact support.');
        }

        $billingCycles = $subscription->billingCycles()
            ->orderBy('period_start', 'desc')
            ->paginate(10);

        return view('admin.subscription.show', compact('subscription', 'billingCycles'));
    }

    public function pay(Request $request)
    {
        $user = auth()->user();
        $subscription = $user->effectiveRestaurant()->subscription;

        if (! $subscription) {
            return redirect()->route('manager.subscription.show')
                ->with('error', 'No active subscription to pay for.');
        }

        $validated = $request->validate([
            'payment_method' => 'required|in:manual,stripe,jazzcash,easypaisa',
            'payment_reference' => 'nullable|string|max:255',
        ]);

        $billingCycle = SubscriptionManager::processPayment($subscription, $validated['payment_method'], [
            'payment_reference' => $validated['payment_reference'] ?? null,
        ]);

        if ($billingCycle->status === 'paid') {
            return redirect()->route('manager.subscription.show')
                ->with('success', 'Payment completed and subscription has been reactivated.');
        }

        return redirect()->route('manager.subscription.show')
            ->with('success', 'Payment record created. Please follow up with the platform administrator if payment requires confirmation.');
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
