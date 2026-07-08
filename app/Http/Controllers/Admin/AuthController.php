<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (Auth::check()) {
            return redirect()->route('admin.dashboard');
        }
        return view('admin.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'phone'    => 'required|string',
            'password' => 'required|string',
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();

            $user = Auth::user();

            // Allow both 'super_admin' and 'admin' roles into the admin panel
            if (! in_array($user->role, ['super_admin', 'admin'], true)) {
                Auth::logout();
                return back()->withErrors(['phone' => 'You do not have admin access.'])->onlyInput('phone');
            }

            if ($user->role === 'admin' && $user->restaurant && $user->restaurant->status !== 'active') {
                Auth::logout();
                return back()->withErrors(['phone' => 'This restaurant account is currently inactive.'])->onlyInput('phone');
            }

            return redirect()->intended(route('admin.dashboard'));
        }

        return back()->withErrors(['phone' => 'Invalid phone or password.'])->onlyInput('phone');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('admin.login');
    }
}
