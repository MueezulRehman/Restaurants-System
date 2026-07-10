<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class RestaurantProfileController extends Controller
{
    public function edit()
    {
        $restaurant = Auth::user()->restaurant;

        if (! $restaurant) {
            abort(403);
        }

        return view('admin.restaurant-profile.edit', compact('restaurant'));
    }

    public function update(Request $request)
    {
        $restaurant = Auth::user()->restaurant;

        if (! $restaurant) {
            abort(403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:25',
            'address' => 'nullable|string|max:500',
            'logo_path' => 'nullable|image|max:2048',
        ]);

        if ($request->hasFile('logo_path')) {
            if ($restaurant->logo_path) {
                Storage::disk('public')->delete($restaurant->logo_path);
            }
            $validated['logo_path'] = $request->file('logo_path')->store('restaurant-logos', 'public');
        }

        $restaurant->update($validated);

        return redirect()->route('manager.restaurant.profile.edit')
            ->with('success', 'Restaurant profile updated successfully.');
    }
}
