<?php

namespace App\Http\Controllers\Admin;

use App\Models\User;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class StaffController extends Controller
{
    public function index()
    {
        $staff = User::whereNotIn('role', ['super_admin', 'admin'])
            ->where('restaurant_id', Auth::user()->restaurant_id)
            ->orderBy('created_at', 'desc')
            ->paginate(15);
        return view('admin.staff.index', compact('staff'));
    }

    public function create()
    {
        return view('admin.staff.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'phone' => 'nullable|string|max:20',
            'role' => 'required|in:staff,manager',
            'password' => 'required|string|min:8',
        ]);

        $validated['password'] = Hash::make($validated['password']);
        $validated['restaurant_id'] = Auth::user()->restaurant_id;

        User::create($validated);

        return redirect()->route('admin.staff.index')
            ->with('success', 'Staff member added successfully.');
    }

    public function edit(User $staff)
    {
        abort_unless($staff->restaurant_id === Auth::user()->restaurant_id, 403);

        return view('admin.staff.edit', compact('staff'));
    }

    public function update(Request $request, User $staff)
    {
        abort_unless($staff->restaurant_id === Auth::user()->restaurant_id, 403);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $staff->id,
            'phone' => 'nullable|string|max:20',
            'role' => 'required|in:staff,manager',
        ]);

        $staff->update($validated);

        return redirect()->route('admin.staff.index')
            ->with('success', 'Staff member updated successfully.');
    }

    public function destroy(User $staff)
    {
        abort_unless($staff->restaurant_id === Auth::user()->restaurant_id, 403);

        $staff->delete();
        return redirect()->route('admin.staff.index')
            ->with('success', 'Staff member removed successfully.');
    }
}
