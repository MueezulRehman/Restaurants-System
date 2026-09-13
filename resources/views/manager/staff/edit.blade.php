@extends('manager.layout.master')
@section('title', 'Edit Staff Member')

@section('page-content')

    <div class="max-w-2xl">
        <x-back-link href="{{ route('manager.staff.index') }}" label="Back to Staff" />

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
                    <input type="text" name="name" required
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:border-hut-green"
                        value="{{ old('name', $staff->name) }}">
                </div>

                <div>
                    <label class="block text-sm font-medium text-hut-dark mb-1">Email *</label>
                    <input type="email" name="email" required
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:border-hut-green"
                        value="{{ old('email', $staff->email) }}">
                </div>

                <div>
                    <label class="block text-sm font-medium text-hut-dark mb-1">Phone</label>
                    <input type="tel" name="phone"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:border-hut-green"
                        value="{{ old('phone', $staff->phone) }}">
                </div>

                <div>
                    <label class="block text-sm font-medium text-hut-dark mb-1">Role *</label>
                    <select name="role" id="role-select" required
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:border-hut-green"
                        onchange="toggleModuleAccess(this.value)">
                        <option value="staff" {{ old('role', $staff->role) === 'staff' ? 'selected' : '' }}>Staff</option>
                        <option value="manager" {{ old('role', $staff->role) === 'manager' ? 'selected' : '' }}>Manager
                        </option>
                    </select>
                </div>

                @if($staffTypes)
                    <div>
                        <label class="block text-sm font-medium text-hut-dark mb-1">Staff Type</label>
                        <select name="staff_type"
                            class="w-full border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:border-hut-green">
                            <option value="">Select a staff type</option>
                            @foreach($staffTypes as $staffType)
                                <option value="{{ $staffType }}" @selected(old('staff_type', $staff->staff_type) === $staffType)>
                                    {{ ucfirst($staffType) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                @endif

                @if(($branches ?? collect())->isNotEmpty())
                    <div>
                        <label class="block text-sm font-medium text-hut-dark mb-1">Assigned Branch</label>
                        <select name="branch_id"
                            class="w-full border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:border-hut-green">
                            <option value="">All branches / unassigned</option>
                            @foreach($branches as $branch)
                                <option value="{{ $branch->id }}" @selected(old('branch_id', $staff->branch_id) == $branch->id)>
                            {{ $branch->name }} ({{ $branch->code }})</option>@endforeach
                        </select>
                    </div>
                @endif

                @php
                    $currentAccess = old('module_access', $staff->getModuleAccessList());
                @endphp

                <div id="module-access-panel"
                    class="{{ old('role', $staff->role) === 'manager' ? '' : 'hidden' }} border border-gray-200 rounded-lg p-4 bg-gray-50">
                    <label class="block text-sm font-medium text-hut-dark mb-1">Module Access</label>
                    <p class="text-xs text-gray-500 mb-3">Managers inherit every module enabled for this business. These
                        legacy selections are retained for compatibility and do not hide enabled business modules.</p>

                    @if($modules->isEmpty())
                        <p class="text-sm text-gray-500">No modules are enabled for this business yet. The Super Admin must
                            enable a business module before it can be delegated.</p>
                    @else
                        <div class="mb-3 grid gap-2 sm:grid-cols-2 lg:grid-cols-5">
                            <button type="button" onclick="applyPreset('all')"
                                class="rounded-lg border border-hut-dark/30 bg-hut-dark/10 px-3 py-2 text-left text-sm font-medium text-hut-dark">All
                                enabled</button>
                            @if($presetKey === 'restaurant')
                                <button type="button" onclick="applyPreset('restaurant')"
                                    class="rounded-lg border border-hut-green/30 bg-hut-green/10 px-3 py-2 text-left text-sm font-medium text-hut-dark">Restaurant
                                    preset</button>
                            @elseif($presetKey === 'pharmacy')
                                <button type="button" onclick="applyPreset('pharmacy')"
                                    class="rounded-lg border border-hut-green/30 bg-hut-green/10 px-3 py-2 text-left text-sm font-medium text-hut-dark">Pharmacy
                                    preset</button>
                            @else
                                <button type="button" onclick="applyPreset('general_store')"
                                    class="rounded-lg border border-hut-yellow/30 bg-hut-yellow/10 px-3 py-2 text-left text-sm font-medium text-hut-dark">General
                                    store preset</button>
                            @endif
                            <button type="button" onclick="applyPreset('none')"
                                class="rounded-lg border border-gray-300 bg-white px-3 py-2 text-left text-sm font-medium text-gray-600">Clear
                                all</button>
                        </div>
                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-2">
                            @foreach($modules as $module)
                                <label
                                    class="flex items-center gap-2 text-sm bg-white border border-gray-200 rounded-lg px-3 py-2 cursor-pointer hover:border-hut-green">
                                    <input type="checkbox" name="module_access[]" value="{{ $module->key }}" {{ in_array($module->key, $currentAccess) ? 'checked' : '' }}
                                        class="rounded border-gray-300 text-hut-green focus:ring-hut-green">
                                    <span>{{ $module->name }}</span>
                                </label>
                            @endforeach
                        </div>
                    @endif
                </div>

                <div class="flex gap-3 pt-4">
                    <button type="submit"
                        class="bg-hut-green text-white px-6 py-2 rounded-lg font-medium hover:bg-hut-green/90">Save
                        Changes</button>
                    <a href="{{ route('manager.staff.index') }}"
                        class="border border-gray-200 text-hut-dark px-6 py-2 rounded-lg font-medium hover:bg-gray-50">Cancel</a>
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
                all: Array.from(checks).map((box) => box.value),
                none: [],
                restaurant: ['orders', 'pos', 'menu', 'categories', 'variants', 'deals', 'customers', 'cashbook', 'expenses', 'reports', 'tables', 'feedback', 'allergies', 'theme'],
                pharmacy: ['medical', 'inventory', 'stock', 'pos', 'medical-records', 'customers', 'cashbook', 'expenses', 'reports', 'allergies', 'pharmacy', 'theme'],
                general_store: ['inventory', 'stock', 'pos', 'categories', 'variants', 'customers', 'cashbook', 'expenses', 'reports', 'allergies', 'general_store', 'theme'],
            };

            checks.forEach((box) => {
                box.checked = (keys[preset] ?? []).includes(box.value);
            });
        }
    </script>

@endsection