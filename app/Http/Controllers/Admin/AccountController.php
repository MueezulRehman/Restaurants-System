<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class AccountController extends Controller
{
    /**
     * Show the logged-in manager/admin's own account settings form
     * (name, email, phone, password) — not the restaurant profile.
     */
    public function edit()
    {
        $user = Auth::user();

        abort_unless($user instanceof User, 403);

        return view('admin.account.edit', compact('user'));
    }

    public function update(Request $request)
    {
        $user = Auth::user();

        abort_unless($user instanceof User, 403);

        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'email' => [
                'nullable',
                'email',
                'max:150',
                Rule::unique('users', 'email')->ignore($user->id),
            ],
            'phone' => [
                'required',
                'string',
                'max:20',
                Rule::unique('users', 'phone')->ignore($user->id),
            ],
        ]);

        $user->update($validated);

        return back()->with('success', 'Account details updated successfully.');
    }

    public function updatePassword(Request $request)
    {
        $user = Auth::user();

        abort_unless($user instanceof User, 403);

        $validated = $request->validate([
            'current_password' => ['required'],
            'password' => ['required', 'confirmed', Password::min(8)],
        ]);

        if (! Hash::check($validated['current_password'], $user->password)) {
            return back()->withErrors(['current_password' => 'Your current password is incorrect.']);
        }

        $user->update([
            'password' => Hash::make($validated['password']),
        ]);

        return back()->with('success', 'Password changed successfully.');
    }
}
