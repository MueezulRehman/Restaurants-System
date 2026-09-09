@extends('layouts.admin')
@section('title', 'Staff Members')

@section('content')

    <div class="flex justify-between items-center mb-6">
        <h2 class="text-lg font-display font-bold text-hut-dark">Staff Members</h2>
        <a href="{{ route('manager.staff.create') }}" aria-label="Add staff" title="Add staff"
            class="inline-flex h-10 w-10 items-center justify-center rounded-lg bg-hut-green text-white shadow-sm transition hover:-translate-y-0.5 hover:bg-hut-green/90">
            <x-icons.add class="h-5 w-5" />
        </a>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 text-gray-500 text-xs uppercase border-b">
                <tr>
                    <th class="px-4 py-3 text-left">Name</th>
                    <th class="px-4 py-3 text-left">Email</th>
                    <th class="px-4 py-3 text-left">Phone</th>
                    <th class="px-4 py-3 text-left">Role</th>
                    <th class="px-4 py-3 text-left">Module Access</th>
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
                        <td class="px-4 py-3">
                            @if($member->role !== 'manager')
                                <span class="text-gray-400 text-xs">—</span>
                            @elseif(empty($member->getModuleAccessList()))
                                <span class="text-xs text-red-500">No modules granted</span>
                            @else
                                <div class="flex flex-wrap gap-1">
                                    @foreach($member->getModuleAccessList() as $key)
                                        <span
                                            class="inline-block bg-hut-green/10 text-hut-green text-[11px] font-medium px-2 py-0.5 rounded-full">{{ ucfirst($key) }}</span>
                                    @endforeach
                                </div>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-gray-500 text-xs">{{ $member->created_at->format('M d, Y') }}</td>
                        <td class="px-4 py-3 text-right space-x-2 flex justify-end">
                            <a href="{{ route('manager.staff.edit', $member) }}"
                                class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-emerald-100 bg-emerald-50 text-hut-green transition hover:-translate-y-0.5 hover:bg-emerald-100"
                                aria-label="Edit staff member" title="Edit">
                                <x-icons.edit class="h-4 w-4" />
                            </a>
                            <form action="{{ route('manager.staff.destroy', $member) }}" method="POST" class="inline"
                                data-confirm="Remove this staff member?">
                                @csrf
                                @method('DELETE')
                                <button type="submit" aria-label="Remove staff member" title="Remove"
                                    class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-red-100 bg-red-50 text-red-600 transition hover:-translate-y-0.5 hover:bg-red-100">
                                    <x-icons.trash class="h-4 w-4" />
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-4 py-8 text-center text-gray-500">No staff members found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-6">
        {{ $staff->links() }}
    </div>

@endsection