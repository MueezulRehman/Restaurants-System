<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Restaurant;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class RestaurantController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        abort_unless($user instanceof User && $user->isSuperAdmin(), 403);

        $restaurants = Restaurant::withCount('users')->latest()->get();

        return view('admin.restaurants.index', compact('restaurants'));
    }

    public function create()
    {
        $user = Auth::user();
        abort_unless($user instanceof User && $user->isSuperAdmin(), 403);

        return view('admin.restaurants.create');
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        abort_unless($user instanceof User && $user->isSuperAdmin(), 403);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:100|unique:restaurants,slug',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:25',
            'address' => 'nullable|string|max:500',
            'custom_domain' => 'nullable|string|max:255|unique:restaurants,custom_domain',
            'domain' => 'nullable|string|max:255|unique:restaurants,domain',
            'plan' => 'nullable|string|max:50',
            'status' => 'nullable|in:trial,active,suspended,cancelled',
            'logo_path' => 'nullable|image|max:2048',
            'owner_name' => 'nullable|string|max:255',
            'owner_email' => 'nullable|email|max:255',
            'owner_phone' => 'nullable|string|max:25',
            'owner_password' => 'nullable|string|min:8',
        ]);

        $validated['slug'] = Str::slug($validated['slug']);

        if ($request->hasFile('logo_path')) {
            $validated['logo_path'] = $request->file('logo_path')->store('restaurant-logos', 'public');
        }

        $restaurant = Restaurant::create($validated);

        $ownerCredentials = null;

        if ($request->filled('owner_email')) {
            $password = $request->input('owner_password') ?: Str::random(10);

            $owner = User::create([
                'name' => $request->input('owner_name') ?: $validated['name'] . ' Owner',
                'email' => $request->input('owner_email'),
                'phone' => $request->input('owner_phone'),
                'role' => 'admin',
                'restaurant_id' => $restaurant->id,
                'password' => Hash::make($password),
            ]);

            $ownerCredentials = [
                'email' => $owner->email,
                'password' => $password,
            ];
        }

        $message = 'Restaurant created successfully.';

        if ($ownerCredentials) {
            $message .= ' Owner login: ' . $ownerCredentials['email'] . ' / ' . $ownerCredentials['password'];
        }

        return redirect()->route('admin.restaurants.index')->with('success', $message);
    }

    public function edit(Restaurant $restaurant)
    {
        $user = Auth::user();
        abort_unless($user instanceof User && $user->isSuperAdmin(), 403);

        return view('admin.restaurants.edit', compact('restaurant'));
    }

    public function update(Request $request, Restaurant $restaurant)
    {
        $user = Auth::user();
        abort_unless($user instanceof User && $user->isSuperAdmin(), 403);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:100|unique:restaurants,slug,' . $restaurant->id,
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:25',
            'address' => 'nullable|string|max:500',
            'custom_domain' => 'nullable|string|max:255|unique:restaurants,custom_domain,' . $restaurant->id,
            'domain' => 'nullable|string|max:255|unique:restaurants,domain,' . $restaurant->id,
            'plan' => 'nullable|string|max:50',
            'status' => 'nullable|in:trial,active,suspended,cancelled',
            'logo_path' => 'nullable|image|max:2048',
        ]);

        $validated['slug'] = Str::slug($validated['slug']);

        if ($request->hasFile('logo_path')) {
            if ($restaurant->logo_path) {
                Storage::disk('public')->delete($restaurant->logo_path);
            }
            $validated['logo_path'] = $request->file('logo_path')->store('restaurant-logos', 'public');
        }

        $restaurant->update($validated);

        return redirect()->route('admin.restaurants.index')->with('success', 'Restaurant updated successfully.');
    }

    public function destroy(Restaurant $restaurant)
    {
        $user = Auth::user();
        abort_unless($user instanceof User && $user->isSuperAdmin(), 403);

        $restaurant->delete();

        return redirect()->route('admin.restaurants.index')->with('success', 'Restaurant deleted successfully.');
    }
}
