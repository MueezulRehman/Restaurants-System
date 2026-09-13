<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\RestaurantTheme;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RestaurantThemeController extends Controller
{
    public function edit()
    {
        $restaurant = Auth::user()->effectiveRestaurant();
        abort_unless($restaurant, 403);

        $theme = $restaurant->restaurantTheme ?: new RestaurantTheme(RestaurantTheme::defaults());
        $customer = $theme->customerTheme();
        $schedule = is_array($customer['schedule'] ?? null) ? $customer['schedule'] : [];
        $presets = RestaurantTheme::customerPresets();

        return view('manager.restaurant-theme.edit', compact('restaurant', 'theme', 'customer', 'schedule', 'presets'));
    }

    public function update(Request $request)
    {
        $restaurant = Auth::user()->effectiveRestaurant();
        abort_unless($restaurant, 403);

        $hex = ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'];
        $validated = $request->validate([
            'manager_light_surface' => $hex,
            'manager_dark_surface' => $hex,
            'theme_primary' => $hex,
            'theme_secondary' => $hex,
            'theme_accent' => $hex,
            'theme_light' => $hex,
            'theme_preset' => 'nullable|in:custom,codeibex,emerald,royal,midnight,sunset',
            'hero_slides' => 'nullable|array|max:10',
            'hero_slides.*' => 'image|max:4096',
            'schedule' => 'nullable|array',
            'schedule.*.enabled' => 'nullable|boolean',
            'schedule.*.primary' => $hex,
            'schedule.*.secondary' => $hex,
            'schedule.*.accent' => $hex,
        ]);

        $record = $restaurant->restaurantTheme ?: new RestaurantTheme(['restaurant_id' => $restaurant->id]);
        $customer = $record->customerTheme();
        $heroSlides = is_array($customer['hero_slides'] ?? null) ? $customer['hero_slides'] : [];
        $remainingSlides = max(0, 10 - count($heroSlides));
        if ($request->hasFile('hero_slides')) {
            foreach (array_slice($request->file('hero_slides'), 0, $remainingSlides) as $slide) {
                $heroSlides[] = $slide->store('restaurant-hero', 'public');
            }
        }

        $presets = collect(RestaurantTheme::customerPresets())->mapWithKeys(fn($preset, $key) => [$key => [
            'primary' => $preset[1][0],
            'secondary' => $preset[1][1],
            'accent' => $preset[1][2],
            'light' => $preset[1][3],
        ]])->all();
        $managerPresets = RestaurantTheme::managerPresets();
        $presetKey = $request->input('theme_preset');
        $presetSelected = isset($presets[$presetKey]);
        if ($presetSelected) {
            $customer = array_merge($customer, $presets[$presetKey]);
            $customer['preset'] = $presetKey;
        } else {
            foreach (['primary', 'secondary', 'accent', 'light'] as $key) {
                $customer[$key] = $validated['theme_' . $key] ?? ($customer[$key] ?? RestaurantTheme::defaults()['customer'][$key]);
            }
            $customer['preset'] = 'custom';
        }

        $schedule = [];
        foreach (['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday', 'weekend'] as $day) {
            if (! empty($request->input("schedule.{$day}.enabled"))) {
                $schedule[$day] = [
                    'primary' => $request->input("schedule.{$day}.primary", $customer['primary']),
                    'secondary' => $request->input("schedule.{$day}.secondary", $customer['secondary']),
                    'accent' => $request->input("schedule.{$day}.accent", $customer['accent']),
                ];
            }
        }
        $customer['schedule'] = $schedule;
        $customer['hero_slides'] = array_values(array_slice($heroSlides, 0, 10));

        $record->restaurant_id = $restaurant->id;
        if (isset($managerPresets[$presetKey])) {
            $managerLight = $managerPresets[$presetKey]['light'];
            $managerDark = $managerPresets[$presetKey]['dark'];
            $managerLight['surface'] = $managerLight['background'];
            $managerDark['surface'] = $managerDark['background'];
        } else {
            $managerLight = $record->managerPalette('light');
            $managerDark = $record->managerPalette('dark');
            $managerLight['surface'] = $validated['manager_light_surface'];
            $managerDark['surface'] = $validated['manager_dark_surface'];
        }
        $record->manager_light = $managerLight;
        $record->manager_dark = $managerDark;
        $record->customer = $customer;
        $record->save();

        return redirect()->route('manager.business.theme.edit')->with('success', 'Theme settings updated successfully.');
    }
}
