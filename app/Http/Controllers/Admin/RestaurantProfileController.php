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
            'hero_slides' => 'nullable|array|max:10',
            'hero_slides.*' => 'image|max:4096',
            'theme_primary' => 'nullable|string|max:20',
            'theme_secondary' => 'nullable|string|max:20',
            'theme_accent' => 'nullable|string|max:20',
            'theme_preset' => 'nullable|in:codeibex,emerald,royal,midnight,sunset',
            'theme_tab_background' => ['nullable', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'theme_tab_text' => ['nullable', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'theme_tab_active' => ['nullable', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'schedule' => 'nullable|array',
            'schedule.*.enabled' => 'nullable|boolean',
            'schedule.*.primary' => 'nullable|string|max:20',
            'schedule.*.secondary' => 'nullable|string|max:20',
            'schedule.*.accent' => 'nullable|string|max:20',
            'opening_hours' => 'nullable|array',
            'opening_hours.*.open' => 'nullable|date_format:H:i',
            'opening_hours.*.close' => 'nullable|date_format:H:i',
            'opening_hours.*.closed' => 'nullable|boolean',
            'is_closed_today' => 'nullable|boolean',
            'closed_message' => 'nullable|string|max:255',
            'accept_orders_when_closed' => 'nullable|boolean',
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
        $heroSlides = is_array($theme['hero_slides'] ?? null) ? $theme['hero_slides'] : [];
        $remainingSlides = max(0, 10 - count($heroSlides));

        if ($request->hasFile('hero_slides')) {
            $uploadedSlides = 0;
            foreach ($request->file('hero_slides') as $slide) {
                if ($uploadedSlides >= $remainingSlides) {
                    break;
                }
                $heroSlides[] = $slide->store('restaurant-hero', 'public');
                $uploadedSlides++;
            }
        }
        $theme['hero_slides'] = array_values(array_slice($heroSlides, 0, 10));
        $presets = [
            'codeibex' => ['primary' => '#2E5E99', 'secondary' => '#0D2440', 'accent' => '#7BA4D0', 'light' => '#E7F0FA'],
            'emerald' => ['primary' => '#166534', 'secondary' => '#052E16', 'accent' => '#4ADE80', 'light' => '#ECFDF5'],
            'royal' => ['primary' => '#6D28D9', 'secondary' => '#24104F', 'accent' => '#C4B5FD', 'light' => '#F5F3FF'],
            'midnight' => ['primary' => '#0F766E', 'secondary' => '#042F2E', 'accent' => '#5EEAD4', 'light' => '#F0FDFA'],
            'sunset' => ['primary' => '#C2410C', 'secondary' => '#431407', 'accent' => '#FDBA74', 'light' => '#FFF7ED'],
        ];
        $presetSelected = $request->filled('theme_preset');
        if ($presetSelected) {
            $preset = $presets[$request->input('theme_preset')] ?? $presets['codeibex'];
            $theme['preset'] = $request->input('theme_preset');
            $theme['primary'] = $preset['primary'];
            $theme['secondary'] = $preset['secondary'];
            $theme['accent'] = $preset['accent'];
            $theme['light'] = $preset['light'];
        }
        if (! $presetSelected) {
            $theme['primary'] = $validated['theme_primary'] ?? ($theme['primary'] ?? '#0f3d2e');
            $theme['secondary'] = $validated['theme_secondary'] ?? ($theme['secondary'] ?? '#c9a227');
            $theme['accent'] = $validated['theme_accent'] ?? ($theme['accent'] ?? '#16a34a');
        }
        $theme['tab_background'] = $validated['theme_tab_background'] ?? ($theme['tab_background'] ?? '#FFFFFF');
        $theme['tab_text'] = $validated['theme_tab_text'] ?? ($theme['tab_text'] ?? '#64748B');
        $theme['tab_active'] = $validated['theme_tab_active'] ?? ($theme['tab_active'] ?? $theme['primary']);

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

        $hours = [];
        foreach (['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'] as $day) {
            $row = $request->input("opening_hours.{$day}", []);
            $hours[$day] = [
                'open' => $row['open'] ?? '09:00',
                'close' => $row['close'] ?? '22:00',
                'closed' => ! empty($row['closed']),
            ];
        }

        unset($validated['theme_primary'], $validated['theme_secondary'], $validated['theme_accent'], $validated['theme_preset'], $validated['theme_tab_background'], $validated['theme_tab_text'], $validated['theme_tab_active'], $validated['schedule'], $validated['hero_slides'], $validated['opening_hours']);
        $validated['theme'] = $theme;
        $validated['opening_hours'] = $hours;
        $validated['is_closed_today'] = $request->boolean('is_closed_today');
        $validated['accept_orders_when_closed'] = $request->boolean('accept_orders_when_closed');
        $validated['closed_message'] = trim((string) $request->input('closed_message', '')) ?: null;

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
