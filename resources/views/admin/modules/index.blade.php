@extends('layouts.admin')

@section('title', 'Modules')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-semibold text-hut-dark">Modules</h2>
            <p class="text-sm text-gray-500">Manage the platform modules available to restaurants.</p>
        </div>
        <a href="{{ route('admin.modules.create') }}" class="inline-flex items-center rounded-lg bg-hut-dark px-4 py-2 text-sm font-semibold text-white hover:bg-gray-800">+ Add Module</a>
    </div>

    <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Name</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Key</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Status</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($modules as $module)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 font-medium text-hut-dark">{{ $module->name }}</td>
                        <td class="px-4 py-3 text-sm text-gray-600">{{ $module->key }}</td>
                        <td class="px-4 py-3 text-sm">
                            <span class="rounded-full px-2.5 py-1 text-xs font-semibold {{ $module->is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-700' }}">{{ $module->is_active ? 'Active' : 'Inactive' }}</span>
                        </td>
                        <td class="px-4 py-3 text-sm">
                            <div class="flex items-center gap-3">
                                <a href="{{ route('admin.modules.edit', $module) }}" class="text-hut-yellow hover:text-amber-600">Edit</a>
                                <form action="{{ route('admin.modules.destroy', $module) }}" method="POST" onsubmit="return confirm('Delete this module?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="text-red-500 hover:text-red-700">Delete</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-4 py-6 text-center text-sm text-gray-500">No modules found yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
