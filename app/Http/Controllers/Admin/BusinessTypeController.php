<?php

namespace App\Http\Controllers\Admin;

use App\Models\BusinessType;
use App\Models\Module;
use App\Http\Controllers\Controller;
use App\Services\ModuleService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BusinessTypeController extends Controller
{
    protected function ensureSuperAdmin(): void
    {
        $user = Auth::user();

        abort_unless($user && $user->isSuperAdmin(), 403, 'This area is only accessible to the platform super admin.');
    }

    /**
     * Show all business types (super admin only).
     */
    public function index()
    {
        $this->ensureSuperAdmin();

        $businessTypes = BusinessType::withCount('restaurants', 'modules')
            ->orderBy('sort_order')
            ->paginate(20);

        return view('admin.business-types.index', compact('businessTypes'));
    }

    /**
     * Show create business type form.
     */
    public function create()
    {
        $this->ensureSuperAdmin();

        ModuleService::ensureDefaults();

        $modules = Module::where('is_active', true)->orderBy('sort_order')->get();
        return view('admin.business-types.create', compact('modules'));
    }

    /**
     * Store new business type.
     */
    public function store(Request $request)
    {
        $this->ensureSuperAdmin();

        ModuleService::ensureDefaults();

        $validated = $request->validate([
            'name' => 'required|string|max:100|unique:business_types',
            'description' => 'nullable|string|max:500',
            'icon' => 'nullable|string|max:50',
            'modules' => 'nullable|array',
            'modules.*' => 'exists:modules,id',
        ]);

        $modules = $validated['modules'] ?? [];
        unset($validated['modules']);

        $businessType = BusinessType::create($validated);

        if (!empty($modules)) {
            $businessType->modules()->attach($modules);
        }

        return redirect()->route('admin.business-types.index')
            ->with('success', 'Business type created successfully.');
    }

    /**
     * Show edit business type form.
     */
    public function edit(BusinessType $businessType)
    {
        $this->ensureSuperAdmin();

        ModuleService::ensureDefaults();

        $modules = Module::where('is_active', true)->orderBy('sort_order')->get();
        $selectedModules = $businessType->modules()->pluck('id')->toArray();

        return view('admin.business-types.edit', compact('businessType', 'modules', 'selectedModules'));
    }

    /**
     * Update business type.
     */
    public function update(Request $request, BusinessType $businessType)
    {
        $this->ensureSuperAdmin();

        $validated = $request->validate([
            'name' => 'required|string|max:100|unique:business_types,name,' . $businessType->id,
            'description' => 'nullable|string|max:500',
            'icon' => 'nullable|string|max:50',
            'is_active' => 'boolean',
            'modules' => 'nullable|array',
            'modules.*' => 'exists:modules,id',
        ]);

        $modules = $validated['modules'] ?? [];
        unset($validated['modules']);

        $businessType->update($validated);
        $businessType->modules()->sync($modules);

        return redirect()->route('admin.business-types.index')
            ->with('success', 'Business type updated successfully.');
    }

    /**
     * Delete business type.
     */
    public function destroy(BusinessType $businessType)
    {
        $this->ensureSuperAdmin();

        // Prevent deletion if restaurants are using this type
        if ($businessType->restaurants()->exists()) {
            return redirect()->route('admin.business-types.index')
                ->with('error', 'Cannot delete: restaurants are using this business type.');
        }

        $businessType->delete();

        return redirect()->route('admin.business-types.index')
            ->with('success', 'Business type deleted successfully.');
    }
}
