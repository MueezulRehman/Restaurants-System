@extends('manager.layout.master')
@section('title', 'Departments')

@section('page-content')
<div class="rounded-xl border border-gray-200 bg-white shadow-sm">
    <div class="flex items-center justify-between border-b border-gray-100 px-5 py-4">
        <div>
            <h1 class="font-display text-base font-bold text-hut-dark">Hospital Departments</h1>
            <p class="text-xs text-gray-500">Manage departments used by Professional Hospital workflows.</p>
        </div>
        <a href="{{ route('manager.departments.create') }}" class="btn rounded-lg bg-hut-yellow px-3.5 py-2 text-xs font-semibold text-hut-dark">Add Department</a>
    </div>
    @if(session('success'))
        <div class="mx-5 mt-4 rounded-lg bg-green-50 px-4 py-3 text-sm text-green-700">{{ session('success') }}</div>
    @endif
    <div class="overflow-x-auto">
        <table class="w-full text-left text-sm">
            <thead class="border-b border-gray-100 bg-gray-50 text-xs uppercase text-gray-500">
                <tr><th class="px-5 py-3">Department</th><th class="px-3 py-3">Code</th><th class="px-3 py-3">Status</th><th class="px-5 py-3 text-right">Actions</th></tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($departments as $department)
                    <tr>
                        <td class="px-5 py-3"><div class="font-semibold text-hut-dark">{{ $department->name }}</div><div class="text-xs text-gray-500">{{ $department->description ?: 'No description' }}</div></td>
                        <td class="px-3 py-3 font-mono text-xs text-gray-600">{{ $department->code }}</td>
                        <td class="px-3 py-3"><span class="rounded-full px-2.5 py-1 text-xs font-medium {{ $department->is_active ? 'bg-green-50 text-green-700' : 'bg-gray-100 text-gray-500' }}">{{ $department->is_active ? 'Active' : 'Inactive' }}</span></td>
                        <td class="px-5 py-3 text-right"><a href="{{ route('manager.departments.edit', $department) }}" class="text-sm font-semibold text-hut-green">Edit</a>
                            <form class="ml-3 inline" method="POST" action="{{ route('manager.departments.destroy', $department) }}" onsubmit="return confirm('Delete this department?')">@csrf @method('DELETE')<button type="submit" class="text-sm font-semibold text-red-600">Delete</button></form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="px-5 py-10 text-center text-sm text-gray-500">No departments found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="px-5 py-4">{{ $departments->links() }}</div>
</div>
@endsection
