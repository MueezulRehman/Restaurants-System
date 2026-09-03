<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ManagerAuthController extends Controller
{
    public function showLogin()
    {
        if (Auth::check() && in_array(Auth::user()->role, ['admin', 'manager'], true)) {
            return redirect()->route('manager.dashboard');
        }
        return view('admin.manager-login');
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

            if (! in_array($user->role, ['admin', 'manager'], true)) {
                Auth::logout();
                return back()->withErrors(['phone' => 'Only restaurant managers may login here.'])->onlyInput('phone');
            }

            if ($user->restaurant && $user->restaurant->status !== 'active') {
                Auth::logout();
                return back()->withErrors(['phone' => 'This restaurant account is currently inactive.'])->onlyInput('phone');
            }

            if ($user->restaurant && ($user->restaurant->restricted ?? false)) {
                Auth::logout();
                return back()->withErrors(['phone' => 'Manager logins for this restaurant have been restricted by the platform administrator.'])->onlyInput('phone');
            }

            $user->forceFill(['last_login_at' => now()])->save();

            return redirect()->intended(route('manager.dashboard'));
        }

        return back()->withErrors(['phone' => 'Invalid phone or password.'])->onlyInput('phone');
    }

    public function logout(Request $request)
    {
        if (Auth::user()) {
            Auth::user()->forceFill(['last_logout_at' => now()])->save();
        }
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('manager.login');
    }
}
