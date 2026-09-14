@extends('super-admin.layout.master')

@section('title', 'Manager Access')

@section('page-content')
    <div class="mb-6 flex items-center justify-between gap-4">
        <div>
            <h2 class="text-2xl font-semibold text-hut-dark">Manager Access</h2>
            <p class="text-sm text-gray-500">{{ $restaurant->name }} · managers inherit the modules enabled for this business.</p>
        </div>
        <a href="{{ route('admin.restaurants.edit', $restaurant) }}"
            class="rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-hut-dark shadow-sm hover:bg-gray-50">Back
            to Business</a>
    </div>

    @if(session('success'))
        <div class="mb-6 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700">
            {{ session('success') }}
        </div>
    @endif

    <div class="mb-6 rounded-xl border border-blue-100 bg-blue-50 px-4 py-3 text-sm text-blue-800">
        Business modules are enabled on the Business edit page and are the single source of truth for every Manager,
        including direct logins. Legacy per-manager selections shown below are retained for compatibility and do not hide
        modules that are enabled for the business.
    </div>

    <div class="space-y-6">
        @foreach($owners as $owner)
            <div class="rounded-2xl border border-blue-200 bg-blue-50 p-6 shadow-sm">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <h3 class="font-semibold text-hut-dark">{{ $owner->name }}</h3>
                        <p class="text-xs text-gray-600">{{ $owner->email ?: $owner->phone }}</p>
                    </div>
                    <span class="rounded-full bg-white px-3 py-1 text-xs font-semibold text-blue-700">Business owner</span>
                </div>
                <p class="mt-3 text-sm text-blue-800">
                    This is the owner login created during business registration. Owner accounts inherit all enabled
                    business modules and are not managed by per-manager access grants.
                </p>
            </div>
        @endforeach

        @forelse($managers as $manager)
            <form action="{{ route('admin.restaurants.manager-access.update', [$restaurant, $manager]) }}" method="POST"
                class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
                @csrf
                @method('PATCH')
                <div class="mb-4 flex items-center justify-between gap-4">
                    <div>
                        <h3 class="font-semibold text-hut-dark">{{ $manager->name }}</h3>
                        <p class="text-xs text-gray-500">{{ $manager->email }}</p>
                    </div>
                    <div class="flex items-center gap-3">
                        @if($manager->getModuleAccessList())
                            <span class="rounded-full bg-green-50 px-3 py-1 text-xs font-semibold text-green-700">
                                {{ count($manager->getModuleAccessList()) }} granted
                            </span>
                        @else
                            <span class="rounded-full bg-blue-50 px-3 py-1 text-xs font-semibold text-blue-700">All enabled</span>
                        @endif
                        <button type="submit"
                            class="rounded-lg bg-hut-dark px-4 py-2 text-sm font-semibold text-white hover:bg-gray-800">Save
                            Access</button>
                    </div>
                </div>

                @if($modules->isEmpty())
                    <p class="text-sm text-gray-500">No modules are enabled for this business. Enable modules from the Business edit
                        page first.</p>
                @else
                    <div class="grid gap-2 sm:grid-cols-2 lg:grid-cols-3">
                        @foreach($modules as $module)
                            <label
                                class="flex items-center gap-2 rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 text-sm text-gray-700 hover:border-hut-green">
                                <input type="checkbox" name="module_access[]" value="{{ $module->key }}" {{ in_array($module->key, $manager->getModuleAccessList(), true) ? 'checked' : '' }}
                                    class="rounded border-gray-300 text-hut-green focus:ring-hut-green">
                                <span>{{ $module->name }}</span>
                            </label>
                        @endforeach
                    </div>
                @endif
            </form>
        @empty
            <div class="rounded-2xl border border-gray-200 bg-white p-8 text-center text-sm text-gray-500 shadow-sm">
                No separate manager accounts exist for this business yet. The owner login is shown above when one was
                created during registration; create a staff account with the Manager role to configure per-manager
                access here.
            </div>
        @endforelse
    </div>
@endsection