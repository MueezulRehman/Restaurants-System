<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

/**
 * Manager business settings: contact, logo, base theme, theme-by-day schedule.
 *
 * @author Mueez Ul Rehman
 */
class RestaurantProfileController extends Controller
{
    public function edit()
    {
        $restaurant = Auth::user()->effectiveRestaurant();
        if (! $restaurant) {
            abort(403);
        }

        $theme = is_array($restaurant->theme) ? $restaurant->theme : [];
        $schedule = is_array($theme['schedule'] ?? null) ? $theme['schedule'] : [];

        return view('admin.restaurant-profile.edit', compact('restaurant', 'theme', 'schedule'));
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
            'theme_primary' => 'nullable|string|max:20',
            'theme_secondary' => 'nullable|string|max:20',
            'theme_accent' => 'nullable|string|max:20',
            'schedule' => 'nullable|array',
            'schedule.*.enabled' => 'nullable|boolean',
            'schedule.*.primary' => 'nullable|string|max:20',
            'schedule.*.secondary' => 'nullable|string|max:20',
            'schedule.*.accent' => 'nullable|string|max:20',
            'pos_allow_short_payment_without_debt' => 'nullable|boolean',
            'pos_short_payment_threshold' => 'nullable|integer|min:0',
        ]);

        if ($request->hasFile('logo_path')) {
            if ($restaurant->logo_path) {
                Storage::disk('public')->delete($restaurant->logo_path);
            }
            $validated['logo_path'] = $request->file('logo_path')->store('restaurant-logos', 'public');
        }

        $theme = is_array($restaurant->theme) ? $restaurant->theme : [];
        $theme['primary'] = $validated['theme_primary'] ?? ($theme['primary'] ?? '#0f3d2e');
        $theme['secondary'] = $validated['theme_secondary'] ?? ($theme['secondary'] ?? '#c9a227');
        $theme['accent'] = $validated['theme_accent'] ?? ($theme['accent'] ?? '#16a34a');

        // Day-based theme schedule (monday … sunday + weekend shortcut)
        $scheduleIn = $request->input('schedule', []);
        $days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday', 'weekend'];
        $schedule = [];
        foreach ($days as $day) {
            if (empty($scheduleIn[$day]['enabled'])) {
                continue;
            }
            $schedule[$day] = [
                'primary' => $scheduleIn[$day]['primary'] ?? $theme['primary'],
                'secondary' => $scheduleIn[$day]['secondary'] ?? $theme['secondary'],
                'accent' => $scheduleIn[$day]['accent'] ?? $theme['accent'],
            ];
        }
        $theme['schedule'] = $schedule;

        unset($validated['theme_primary'], $validated['theme_secondary'], $validated['theme_accent'], $validated['schedule']);
        $validated['theme'] = $theme;

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
