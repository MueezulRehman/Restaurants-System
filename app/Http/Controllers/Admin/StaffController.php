<?php

namespace App\Http\Controllers\Admin;

use App\Models\Module;
use App\Models\User;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class StaffController extends Controller
{
    /**
     * Modules the currently logged-in admin's restaurant actually has
     * turned on. Only these are offered as grantable checkboxes — there's
     * no point letting an admin grant a manager a module the restaurant
     * itself doesn't have.
     */
    protected function grantableModules()
    {
        $restaurant = Auth::user()->restaurant;

        return $restaurant ? $restaurant->getEnabledModules() : collect();
    }

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
        $modules = $this->grantableModules();
        return view('admin.staff.create', compact('modules'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'phone' => 'nullable|string|max:20',
            'role' => 'required|in:staff,manager',
            'password' => 'required|string|min:8',
            'module_access' => 'nullable|array',
            'module_access.*' => 'string|in:' . $this->grantableModules()->pluck('key')->implode(','),
        ]);

        $validated['password'] = Hash::make($validated['password']);
        $validated['restaurant_id'] = Auth::user()->restaurant_id;

        // Module access only makes sense for managers — plain "staff"
        // accounts don't use the admin/manager panel modules at all.
        if ($validated['role'] === 'manager') {
            $requestedAccess = array_values($request->input('module_access', []));
            $validated['module_access'] = ! empty($requestedAccess)
                ? $requestedAccess
                : $this->grantableModules()->pluck('key')->toArray();
        } else {
            $validated['module_access'] = [];
        }

        User::create($validated);

        return redirect()->route('manager.staff.index')
            ->with('success', 'Staff member added successfully.');
    }

    public function edit(User $staff)
    {
        abort_unless($staff->restaurant_id === Auth::user()->restaurant_id, 403);

        $modules = $this->grantableModules();
        return view('admin.staff.edit', compact('staff', 'modules'));
    }

    public function update(Request $request, User $staff)
    {
        abort_unless($staff->restaurant_id === Auth::user()->restaurant_id, 403);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $staff->id,
            'phone' => 'nullable|string|max:20',
            'role' => 'required|in:staff,manager',
            'module_access' => 'nullable|array',
            'module_access.*' => 'string|in:' . $this->grantableModules()->pluck('key')->implode(','),
        ]);

        $validated['module_access'] = $validated['role'] === 'manager'
            ? array_values($request->input('module_access', []))
            : [];

        $staff->update($validated);

        return redirect()->route('manager.staff.index')
            ->with('success', 'Staff member updated successfully.');
    }

    public function destroy(User $staff)
    {
        abort_unless($staff->restaurant_id === Auth::user()->restaurant_id, 403);

        $staff->delete();
        return redirect()->route('manager.staff.index')
            ->with('success', 'Staff member removed successfully.');
    }
}
