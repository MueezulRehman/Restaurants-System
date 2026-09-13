<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

/**
 * Manager business settings: contact, logo, hours, and operational controls.
 *
 * @author Mueez Ul Rehman
 */
class RestaurantProfileController extends Controller
{
    public function editMedicalNotifications()
    {
        $restaurant = Auth::user()->effectiveRestaurant();
        abort_unless($restaurant, 403);

        return view('manager.medical.notifications', compact('restaurant'));
    }

    public function updateMedicalNotifications(Request $request)
    {
        $restaurant = Auth::user()->effectiveRestaurant();
        abort_unless($restaurant, 403);

        $data = $request->validate([
            'queue_notifications_enabled' => 'nullable|boolean',
            'queue_notification_channels' => 'nullable|array',
            'queue_notification_channels.*' => 'string|in:sms,whatsapp',
        ]);
        $restaurant->update([
            'queue_notifications_enabled' => $request->boolean('queue_notifications_enabled'),
            'queue_notification_channels' => $request->boolean('queue_notifications_enabled')
                ? array_values(array_unique($data['queue_notification_channels'] ?? []))
                : [],
        ]);

        return back()->with('success', 'Medical queue notification settings updated.');
    }

    public function edit()
    {
        $restaurant = Auth::user()->effectiveRestaurant();
        if (! $restaurant) {
            abort(403);
        }

        return view('manager.restaurant-profile.edit', compact('restaurant'));
    }

    public function update(Request $request)
    {
        $restaurant = Auth::user()->effectiveRestaurant();
        if (! $restaurant) {
            abort(403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:25',
            'address' => 'nullable|string|max:500',
            'logo_path' => 'nullable|image|max:2048',
            'opening_hours' => 'nullable|array',
            'opening_hours.*.open' => 'nullable|date_format:H:i',
            'opening_hours.*.close' => 'nullable|date_format:H:i',
            'opening_hours.*.closed' => 'nullable|boolean',
            'is_closed_today' => 'nullable|boolean',
            'closed_message' => 'nullable|string|max:255',
            'accept_orders_when_closed' => 'nullable|boolean',
            'pos_allow_short_payment_without_debt' => 'nullable|boolean',
            'pos_short_payment_threshold' => 'nullable|integer|min:0',
            'queue_notifications_enabled' => 'nullable|boolean',
            'queue_notification_channels' => 'nullable|array',
            'queue_notification_channels.*' => 'string|in:sms,whatsapp',
        ]);

        if ($request->hasFile('logo_path')) {
            if ($restaurant->logo_path) {
                Storage::disk('public')->delete($restaurant->logo_path);
            }
            $validated['logo_path'] = $request->file('logo_path')->store('restaurant-logos', 'public');
        }

        $hours = [];
        foreach (['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'] as $day) {
            $row = $request->input("opening_hours.{$day}", []);
            $hours[$day] = [
                'open' => $row['open'] ?? '09:00',
                'close' => $row['close'] ?? '22:00',
                'closed' => ! empty($row['closed']),
            ];
        }

        unset($validated['opening_hours']);
        $validated['opening_hours'] = $hours;
        $validated['is_closed_today'] = $request->boolean('is_closed_today');
        $validated['accept_orders_when_closed'] = $request->boolean('accept_orders_when_closed');
        $validated['closed_message'] = trim((string) $request->input('closed_message', '')) ?: null;
        $validated['queue_notifications_enabled'] = $request->boolean('queue_notifications_enabled');
        $validated['queue_notification_channels'] = $request->boolean('queue_notifications_enabled')
            ? array_values(array_unique($request->input('queue_notification_channels', [])))
            : [];

        if ($request->has('pos_allow_short_payment_without_debt')) {
            $validated['pos_allow_short_payment_without_debt'] = $request->boolean('pos_allow_short_payment_without_debt');
        }
        if ($request->filled('pos_short_payment_threshold')) {
            $validated['pos_short_payment_threshold'] = (int) $request->input('pos_short_payment_threshold');
        }
        $restaurant->update($validated);

        return redirect()->route('manager.restaurant.profile.edit')
            ->with('success', 'Business profile updated successfully.');
    }
}
