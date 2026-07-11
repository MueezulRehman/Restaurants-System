<?php

namespace App\Http\Controllers\Admin;

use App\Models\Module;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ModuleController extends Controller
{
    protected function ensureSuperAdmin(): void
    {
        $user = Auth::user();

        abort_unless($user instanceof \App\Models\User && $user->isSuperAdmin(), 403, 'This area is only accessible to the platform super admin.');
    }

    /**
     * Show all modules (super admin only).
     */
    public function index()
    {
        $this->ensureSuperAdmin();

        $modules = Module::withCount('businessTypes')
            ->orderBy('sort_order')
            ->paginate(20);

        return view('admin.modules.index', compact('modules'));
    }

    /**
     * Show create module form.
     */
    public function create()
    {
        $this->ensureSuperAdmin();

        return view('admin.modules.create');
    }

    /**
     * Store new module.
     */
    public function store(Request $request)
    {
        $this->ensureSuperAdmin();

        $validated = $request->validate([
            'name' => 'required|string|max:100|unique:modules',
            'key' => 'required|string|max:50|unique:modules|regex:/^[a-z\-]+$/',
            'description' => 'nullable|string|max:500',
            'icon' => 'nullable|string|max:50',
        ]);

        Module::create($validated);

        return redirect()->route('admin.modules.index')
            ->with('success', 'Module created successfully.');
    }

    /**
     * Show edit module form.
     */
    public function edit(Module $module)
    {
        $this->ensureSuperAdmin();

        return view('admin.modules.edit', compact('module'));
    }

    /**
     * Update module.
     */
    public function update(Request $request, Module $module)
    {
        $this->ensureSuperAdmin();

        $validated = $request->validate([
            'name' => 'required|string|max:100|unique:modules,name,' . $module->id,
            'key' => 'required|string|max:50|unique:modules,key,' . $module->id . '|regex:/^[a-z\-]+$/',
            'description' => 'nullable|string|max:500',
            'icon' => 'nullable|string|max:50',
            'is_active' => 'boolean',
        ]);

        $module->update($validated);

        return redirect()->route('admin.modules.index')
            ->with('success', 'Module updated successfully.');
    }

    /**
     * Delete module.
     */
    public function destroy(Module $module)
    {
        $this->ensureSuperAdmin();

        $module->delete();

        return redirect()->route('admin.modules.index')
            ->with('success', 'Module deleted successfully.');
    }
}
