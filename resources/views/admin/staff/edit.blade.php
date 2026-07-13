@extends('layouts.admin')
@section('title', 'Edit Staff Member')

@section('content')

<div class="max-w-2xl">
    <a href="{{ route('manager.staff.index') }}" class="text-hut-green text-sm mb-4 inline-block hover:underline">← Back to Staff</a>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <h2 class="text-lg font-display font-bold text-hut-dark mb-6">Edit Staff Member</h2>

        @if ($errors->any())
        <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg mb-6 text-sm">
            <p class="font-medium mb-2">Please fix the following errors:</p>
            <ul class="list-disc list-inside space-y-1">
                @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <form action="{{ route('manager.staff.update', $staff) }}" method="POST" class="space-y-4">
            @csrf
            @method('PATCH')

            <div>
                <label class="block text-sm font-medium text-hut-dark mb-1">Full Name *</label>
                <input type="text" name="name" required class="w-full border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:border-hut-green" value="{{ old('name', $staff->name) }}">
            </div>

            <div>
                <label class="block text-sm font-medium text-hut-dark mb-1">Email *</label>
                <input type="email" name="email" required class="w-full border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:border-hut-green" value="{{ old('email', $staff->email) }}">
            </div>

            <div>
                <label class="block text-sm font-medium text-hut-dark mb-1">Phone</label>
                <input type="tel" name="phone" class="w-full border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:border-hut-green" value="{{ old('phone', $staff->phone) }}">
            </div>

            <div>
                <label class="block text-sm font-medium text-hut-dark mb-1">Role *</label>
                <select name="role" id="role-select" required class="w-full border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:border-hut-green" onchange="toggleModuleAccess(this.value)">
                    <option value="staff" {{ old('role', $staff->role) === 'staff' ? 'selected' : '' }}>Staff</option>
                    <option value="manager" {{ old('role', $staff->role) === 'manager' ? 'selected' : '' }}>Manager</option>
                </select>
            </div>

            @php
                $currentAccess = old('module_access', $staff->getModuleAccessList());
            @endphp

            <div id="module-access-panel" class="{{ old('role', $staff->role) === 'manager' ? '' : 'hidden' }} border border-gray-200 rounded-lg p-4 bg-gray-50">
                <label class="block text-sm font-medium text-hut-dark mb-1">Module Access</label>
                <p class="text-xs text-gray-500 mb-3">Pick which parts of the manager panel this manager is allowed to open and manage.</p>

                @if($modules->isEmpty())
                    <p class="text-sm text-gray-500">No modules are enabled for your restaurant yet. Enable modules on your restaurant first, then come back here to grant them.</p>
                @else
                    <div class="mb-3 grid gap-2 sm:grid-cols-3">
                        <button type="button" onclick="applyPreset('restaurant')" class="rounded-lg border border-hut-green/30 bg-hut-green/10 px-3 py-2 text-left text-sm font-medium text-hut-dark">Restaurant preset</button>
                        <button type="button" onclick="applyPreset('pharmacy')" class="rounded-lg border border-hut-green/30 bg-hut-green/10 px-3 py-2 text-left text-sm font-medium text-hut-dark">Pharmacy preset</button>
                        <button type="button" onclick="applyPreset('general_store')" class="rounded-lg border border-hut-yellow/30 bg-hut-yellow/10 px-3 py-2 text-left text-sm font-medium text-hut-dark">General store preset</button>
                    </div>
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-2">
                        @foreach($modules as $module)
                        <label class="flex items-center gap-2 text-sm bg-white border border-gray-200 rounded-lg px-3 py-2 cursor-pointer hover:border-hut-green">
                            <input type="checkbox" name="module_access[]" value="{{ $module->key }}"
                                {{ in_array($module->key, $currentAccess) ? 'checked' : '' }}
                                class="rounded border-gray-300 text-hut-green focus:ring-hut-green">
                            <span>{{ $module->name }}</span>
                        </label>
                        @endforeach
                    </div>
                @endif
            </div>

            <div class="flex gap-3 pt-4">
                <button type="submit" class="bg-hut-green text-white px-6 py-2 rounded-lg font-medium hover:bg-hut-green/90">Save Changes</button>
                <a href="{{ route('manager.staff.index') }}" class="border border-gray-200 text-hut-dark px-6 py-2 rounded-lg font-medium hover:bg-gray-50">Cancel</a>
            </div>
        </form>
    </div>
</div>

<script>
    function toggleModuleAccess(role) {
        document.getElementById('module-access-panel').classList.toggle('hidden', role !== 'manager');
    }

    function applyPreset(preset) {
        const checks = document.querySelectorAll('input[name="module_access[]"]');
        const keys = {
            restaurant: ['orders', 'pos', 'menu', 'categories', 'variants', 'deals', 'customers', 'cashbook', 'expenses', 'reports', 'tables', 'feedback', 'allergies'],
            pharmacy: ['medical', 'inventory', 'stock', 'pos', 'medical-records', 'customers', 'cashbook', 'expenses', 'reports', 'allergies', 'pharmacy'],
            general_store: ['inventory', 'stock', 'pos', 'categories', 'variants', 'customers', 'cashbook', 'expenses', 'reports', 'allergies', 'general_store'],
        };

        checks.forEach((box) => {
            box.checked = (keys[preset] ?? []).includes(box.value);
        });
    }
</script>

@endsection
