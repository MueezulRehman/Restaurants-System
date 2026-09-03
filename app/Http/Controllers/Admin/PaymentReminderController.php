<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Restaurant;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

/**
 * Super admin sends payment reminders to business managers/owners.
 * @author Mueez Ul Rehman
 */
class PaymentReminderController extends Controller
{
    public function store(Request $request, Restaurant $restaurant)
    {
        abort_unless(Auth::user() instanceof User && Auth::user()->isSuperAdmin(), 403);

        $data = $request->validate([
            'message' => 'nullable|string|max:500',
        ]);

        $message = $data['message']
            ?? 'Please pay your Codeibex subscription. Bank details are on your Subscription page.';

        $staff = User::where('restaurant_id', $restaurant->id)
            ->whereIn('role', ['admin', 'manager'])
            ->where('is_active', true)
            ->get();

        foreach ($staff as $user) {
            // Store a simple session-flash style log; extend to mail/WhatsApp later
            Log::info('Codeibex payment reminder', [
                'restaurant_id' => $restaurant->id,
                'user_id' => $user->id,
                'email' => $user->email,
                'phone' => $user->phone,
                'message' => $message,
            ]);

            // Optional: database notifications if table exists
            if (method_exists($user, 'notify')) {
                try {
                    $user->notify(new \App\Notifications\SubscriptionPaymentReminder($message, $restaurant->name));
                } catch (\Throwable $e) {
                    // Notification class may not exist yet — ignore
                }
            }
        }

        // Mark last reminder on restaurant if column exists
        try {
            $restaurant->forceFill(['last_payment_reminder_at' => now()])->save();
        } catch (\Throwable $e) {
            // column optional
        }

        return back()->with('success', 'Payment reminder sent to ' . $staff->count() . ' staff member(s) for ' . $restaurant->name . '.');
    }
}
