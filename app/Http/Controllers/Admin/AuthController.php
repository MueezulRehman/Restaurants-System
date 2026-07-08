<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (Auth::check() && Auth::user()->role === 'super_admin') {
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

            // Only the platform super admin may use this login — restaurant
            // admins/managers must use /manager/login instead.
            if ($user->role !== 'super_admin') {
                Auth::logout();
                return back()->withErrors(['phone' => 'You do not have admin access. Restaurant managers should use the manager login.'])->onlyInput('phone');
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
