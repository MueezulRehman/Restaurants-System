<?php

namespace App\Http\Controllers\Ceo;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('ceo.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'phone' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            return back()->withErrors(['phone' => 'The phone number or password is incorrect.'])->onlyInput('phone');
        }

        if (! Auth::user()->isCeo()) {
            Auth::logout();

            return back()->withErrors(['phone' => 'Only CEO accounts may use this login.'])->onlyInput('phone');
        }

        $request->session()->regenerate();

        return redirect()->intended(route('ceo.dashboard'));
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('ceo.login');
    }
}
