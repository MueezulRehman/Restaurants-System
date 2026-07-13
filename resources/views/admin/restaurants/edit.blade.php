@extends('layouts.admin')

@section('title', 'Edit Restaurant')

@section('content')
<div class="max-w-4xl rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
    <div class="mb-6">
        <h2 class="text-2xl font-semibold text-hut-dark">Edit Restaurant</h2>
        <p class="text-sm text-gray-500">Update the restaurant profile, plan, and custom domain.</p>
    </div>

    <form action="{{ route('admin.restaurants.update', $restaurant) }}" method="POST" enctype="multipart/form-data" class="space-y-6">
        @csrf
        @method('PATCH')

        <div class="grid gap-6 md:grid-cols-2">
            <div>
                <label class="mb-2 block text-sm font-medium text-gray-700">Restaurant Name</label>
                <input type="text" name="name" value="{{ old('name', $restaurant->name) }}" required class="w-full rounded-lg border border-gray-300 px-3 py-2" />
            </div>
            <div>
                <label class="mb-2 block text-sm font-medium text-gray-700">Slug</label>
                <input type="text" name="slug" value="{{ old('slug', $restaurant->slug) }}" required class="w-full rounded-lg border border-gray-300 px-3 py-2" />
            </div>
            <div>
                <label class="mb-2 block text-sm font-medium text-gray-700">Business Type</label>
                <select name="business_type_id" class="w-full rounded-lg border border-gray-300 px-3 py-2">
                    @foreach($businessTypes as $businessType)
                        <option value="{{ $businessType->id }}" {{ old('business_type_id', $restaurant->business_type_id) == $businessType->id ? 'selected' : '' }}>{{ $businessType->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="md:col-span-2">
                <label class="mb-2 block text-sm font-medium text-gray-700">Modules</label>
                <div class="grid gap-3 sm:grid-cols-2">
                    @foreach($modules as $module)
                        <label class="inline-flex items-center gap-2 rounded-lg border border-gray-200 bg-white px-3 py-2">
                            <input type="checkbox" name="enabled_modules[]" value="{{ $module->id }}" data-module-key="{{ $module->key }}" {{ in_array($module->id, old('enabled_modules', $selectedModules)) ? 'checked' : '' }} class="form-checkbox h-4 w-4 text-hut-dark" />
                            <span class="text-sm text-gray-700">{{ $module->name }}</span>
                        </label>
                    @endforeach
                </div>
                <p class="text-xs text-gray-500 mt-2">Select modules to keep enabled for this restaurant.</p>
            </div>
            <div>
                <label class="mb-2 block text-sm font-medium text-gray-700">Email</label>
                <input type="email" name="email" value="{{ old('email', $restaurant->email) }}" class="w-full rounded-lg border border-gray-300 px-3 py-2" />
            </div>
            <div>
                <label class="mb-2 block text-sm font-medium text-gray-700">Phone</label>
                <input type="text" name="phone" value="{{ old('phone', $restaurant->phone) }}" class="w-full rounded-lg border border-gray-300 px-3 py-2" />
            </div>
            <div>
                <label class="mb-2 block text-sm font-medium text-gray-700">Custom Domain</label>
                <input type="text" name="custom_domain" value="{{ old('custom_domain', $restaurant->custom_domain) }}" placeholder="restaurant.com" class="w-full rounded-lg border border-gray-300 px-3 py-2" />
            </div>
            <div>
                <label class="mb-2 block text-sm font-medium text-gray-700">Restaurant Domain</label>
                <input type="text" name="domain" value="{{ old('domain', $restaurant->domain) }}" placeholder="example.com" class="w-full rounded-lg border border-gray-300 px-3 py-2" />
            </div>
            <div class="md:col-span-2">
                <label class="mb-2 block text-sm font-medium text-gray-700">Tenant Database Configuration</label>
                <textarea name="db_connection" rows="4" class="w-full rounded-lg border border-gray-300 px-3 py-2" placeholder='{"driver":"mysql","host":"127.0.0.1","database":"tenant_db","username":"tenant_user","password":"secret"}'>{{ old('db_connection', json_encode($restaurant->db_connection ?? [])) }}</textarea>
                <p class="text-xs text-gray-500 mt-2">Optional JSON config for a dedicated tenant database. When provided, the platform will provision tenant schema and enable selected modules for this business. Leave blank to use the shared platform database.</p>
            </div>
            <div>
                <label class="mb-2 block text-sm font-medium text-gray-700">Plan</label>
                <select name="plan" class="w-full rounded-lg border border-gray-300 px-3 py-2">
                    @forelse($subscriptionPlans as $plan)
                        <option value="{{ $plan->slug }}" {{ old('plan', $selectedPlanSlug) === $plan->slug ? 'selected' : '' }}>{{ $plan->name }}</option>
                    @empty
                        <option value="" selected>No subscription plans available yet</option>
                    @endforelse
                </select>
            </div>
            <div>
                <label class="mb-2 block text-sm font-medium text-gray-700">Status</label>
                <select name="status" class="w-full rounded-lg border border-gray-300 px-3 py-2">
                    <option value="trial" {{ old('status', $restaurant->status) === 'trial' ? 'selected' : '' }}>Trial</option>
                    <option value="active" {{ old('status', $restaurant->status) === 'active' ? 'selected' : '' }}>Active</option>
                    <option value="suspended" {{ old('status', $restaurant->status) === 'suspended' ? 'selected' : '' }}>Suspended</option>
                    <option value="cancelled" {{ old('status', $restaurant->status) === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                </select>
            </div>
            <div>
                <label class="mb-2 block text-sm font-medium text-gray-700">Address</label>
                <input type="text" name="address" value="{{ old('address', $restaurant->address) }}" class="w-full rounded-lg border border-gray-300 px-3 py-2" />
            </div>
            <div>
                <label class="mb-2 block text-sm font-medium text-gray-700">Restaurant Logo</label>
                <input type="file" name="logo_path" accept="image/*" class="w-full rounded-lg border border-gray-300 px-3 py-2" />
                @if($restaurant->logo_path)
                    <p class="text-xs text-gray-500 mt-2">Current logo: <span class="font-medium">{{ basename($restaurant->logo_path) }}</span></p>
                @endif
            </div>
        </div>

        <div class="flex items-center gap-3">
            <button type="submit" class="rounded-lg bg-hut-dark px-4 py-2 font-semibold text-white hover:bg-gray-800">Update Restaurant</button>
            <a href="{{ route('admin.restaurants.index') }}" class="text-sm text-gray-500 hover:text-hut-dark">← Back</a>
        </div>
    </form>
</div>

<script>
    const businessTypeSelect = document.querySelector('select[name="business_type_id"]');
    const moduleCheckboxes = Array.from(document.querySelectorAll('input[name="enabled_modules[]"]'));

    const defaultModuleMap = {
        restaurant: ['orders', 'pos', 'menu', 'categories', 'variants', 'deals', 'cashbook', 'expenses', 'hr', 'staff', 'attendance', 'salary', 'reports', 'feedback', 'customers', 'tables', 'allergies'],
        general_store: ['pos', 'inventory', 'categories', 'variants', 'stock', 'cashbook', 'expenses', 'hr', 'staff', 'attendance', 'salary', 'reports', 'feedback', 'customers', 'allergies', 'general_store'],
        pharmacy: ['pos', 'inventory', 'categories', 'cashbook', 'expenses', 'hr', 'staff', 'attendance', 'salary', 'reports', 'stock', 'customers', 'medical', 'medical-records', 'allergies', 'pharmacy'],
        retail_shop: ['pos', 'inventory', 'categories', 'variants', 'cashbook', 'expenses', 'hr', 'staff', 'attendance', 'salary', 'reports', 'feedback', 'customers', 'allergies'],
        cafe_bakery: ['orders', 'pos', 'menu', 'categories', 'variants', 'deals', 'cashbook', 'expenses', 'hr', 'staff', 'attendance', 'salary', 'reports', 'feedback', 'customers', 'tables', 'allergies'],
        fast_food: ['orders', 'pos', 'menu', 'categories', 'cashbook', 'expenses', 'reports', 'feedback', 'customers', 'allergies'],
        other_custom: ['pos', 'inventory', 'categories', 'cashbook', 'expenses', 'hr', 'staff', 'attendance', 'salary', 'reports', 'customers', 'stock', 'allergies'],
    };

    const getBusinessTypeKey = () => {
        const selectedText = businessTypeSelect?.options[businessTypeSelect.selectedIndex]?.text?.trim().toLowerCase() ?? '';

        if (selectedText.includes('restaurant')) return 'restaurant';
        if (selectedText.includes('general store') || selectedText.includes('general business')) return 'general_store';
        if (selectedText.includes('pharmacy') || selectedText.includes('medical store')) return 'pharmacy';
        if (selectedText.includes('retail')) return 'retail_shop';
        if (selectedText.includes('cafe') || selectedText.includes('bakery')) return 'cafe_bakery';
        if (selectedText.includes('fast food')) return 'fast_food';
        return 'other_custom';
    };

    const applyBusinessTypeDefaults = () => {
        const keys = defaultModuleMap[getBusinessTypeKey()] ?? [];
        moduleCheckboxes.forEach((checkbox) => {
            checkbox.checked = keys.includes(checkbox.dataset.moduleKey);
        });
    };

    if (businessTypeSelect) {
        businessTypeSelect.addEventListener('change', applyBusinessTypeDefaults);
        if (!moduleCheckboxes.some((checkbox) => checkbox.checked)) {
            applyBusinessTypeDefaults();
        }
    }
</script>
@endsection
