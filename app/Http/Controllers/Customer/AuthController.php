<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class AuthController extends Controller
{
    public function showRegister()
    {
        if (Auth::guard('customer')->check()) {
            return redirect()->route('account.dashboard');
        }
        return view('customer.auth.register');
    }

    public function register(Request $request)
    {
        $restaurant = app()->bound('restaurant') ? app('restaurant') : null;
        $restaurantId = $restaurant?->id;

        $phoneRule = Rule::unique('customers', 'phone');
        $emailRule = Rule::unique('customers', 'email');

        if ($restaurantId) {
            $phoneRule = $phoneRule->where(fn ($query) => $query->where('restaurant_id', $restaurantId));
            $emailRule = $emailRule->where(fn ($query) => $query->where('restaurant_id', $restaurantId));
        }

        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'phone' => ['required', 'string', 'max:20', $phoneRule],
            'email' => ['nullable', 'email', 'max:255', $emailRule],
            'password' => 'required|string|min:6|confirmed',
        ]);

        $customer = Customer::create([
            'restaurant_id' => $restaurantId,
            'name' => $validated['name'],
            'phone' => $validated['phone'],
            'email' => $validated['email'] ?? null,
            'password' => Hash::make($validated['password']),
        ]);

        Auth::guard('customer')->login($customer);
        $request->session()->regenerate();

        return redirect()->intended(route('account.dashboard'))
            ->with('success', 'Account created! You can now track all your orders here.');
    }

    public function showLogin()
    {
        if (Auth::guard('customer')->check()) {
            return redirect()->route('account.dashboard');
        }
        return view('customer.auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'phone' => 'required|string',
            'password' => 'required|string',
        ]);

        if (Auth::guard('customer')->attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();
            return redirect()->intended(route('account.dashboard'));
        }

        return back()->withErrors(['phone' => 'Invalid phone or password.'])->onlyInput('phone');
    }

    public function logout(Request $request)
    {
        Auth::guard('customer')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('home');
    }
}
