@extends('layouts.admin')

@section('title', 'Business Types')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-semibold text-hut-dark">Business Types</h2>
            <p class="text-sm text-gray-500">Define the business categories used during restaurant onboarding.</p>
        </div>
        <a href="{{ route('admin.business-types.create') }}" class="inline-flex items-center rounded-lg bg-hut-dark px-4 py-2 text-sm font-semibold text-white hover:bg-gray-800">+ Add Business Type</a>
    </div>

    <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Name</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Modules</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Status</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($businessTypes as $businessType)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 font-medium text-hut-dark">{{ $businessType->name }}</td>
                        <td class="px-4 py-3 text-sm text-gray-600">{{ $businessType->modules_count }}</td>
                        <td class="px-4 py-3 text-sm">
                            <span class="rounded-full px-2.5 py-1 text-xs font-semibold {{ $businessType->is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-700' }}">{{ $businessType->is_active ? 'Active' : 'Inactive' }}</span>
                        </td>
                        <td class="px-4 py-3 text-sm">
                            <div class="flex items-center gap-3">
                                <a href="{{ route('admin.business-types.edit', $businessType) }}" class="text-hut-yellow hover:text-amber-600">Edit</a>
                                <form action="{{ route('admin.business-types.destroy', $businessType) }}" method="POST" onsubmit="return confirm('Delete this business type?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="text-red-500 hover:text-red-700">Delete</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-4 py-6 text-center text-sm text-gray-500">No business types found yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
