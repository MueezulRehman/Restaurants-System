@extends('layouts.admin')
@section('title', 'Staff Members')

@section('content')

<div class="flex justify-between items-center mb-6">
    <h2 class="text-lg font-display font-bold text-hut-dark">Staff Members</h2>
    <a href="{{ route('admin.staff.create') }}" class="bg-hut-green text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-hut-green/90">+ Add Staff</a>
</div>

<div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-gray-50 text-gray-500 text-xs uppercase border-b">
            <tr>
                <th class="px-4 py-3 text-left">Name</th>
                <th class="px-4 py-3 text-left">Email</th>
                <th class="px-4 py-3 text-left">Phone</th>
                <th class="px-4 py-3 text-left">Role</th>
                <th class="px-4 py-3 text-left">Joined</th>
                <th class="px-4 py-3 text-right">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @forelse($staff as $member)
            <tr class="hover:bg-gray-50 transition-colors">
                <td class="px-4 py-3 font-medium text-hut-dark">{{ $member->name }}</td>
                <td class="px-4 py-3 text-gray-600">{{ $member->email }}</td>
                <td class="px-4 py-3 text-gray-600">{{ $member->phone ?? '-' }}</td>
                <td class="px-4 py-3">
                    <span class="inline-flex items-center gap-1 text-xs font-medium">
                        @if($member->role === 'manager')
                            <span class="w-2 h-2 bg-hut-yellow rounded-full"></span> Manager
                        @else
                            <span class="w-2 h-2 bg-gray-300 rounded-full"></span> Staff
                        @endif
                    </span>
                </td>
                <td class="px-4 py-3 text-gray-500 text-xs">{{ $member->created_at->format('M d, Y') }}</td>
                <td class="px-4 py-3 text-right space-x-2 flex justify-end">
                    <a href="{{ route('admin.staff.edit', $member) }}" class="text-hut-green hover:underline text-xs font-medium">Edit</a>
                    <form action="{{ route('admin.staff.destroy', $member) }}" method="POST" class="inline" onsubmit="return confirm('Remove this staff member?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-red-600 hover:underline text-xs font-medium">Remove</button>
                    </form>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="px-4 py-8 text-center text-gray-500">No staff members found.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-6">
    {{ $staff->links() }}
</div>

@endsection
