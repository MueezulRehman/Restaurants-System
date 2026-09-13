<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\Tenancy;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function showLogin()
    {
        return redirect()->route('manager.login');
    }

    public function login(Request $request)
    {
        return app(ManagerAuthController::class)->login($request);
    }

    public function logout(Request $request)
    {
        Auth::logout();
        Tenancy::end();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('admin.login');
    }
}
