@extends('manager.layout.master')
@section('title', 'Doctors')

@section('page-content')
<div class="rounded-xl border border-gray-200 bg-white shadow-sm">
    <div class="flex items-center justify-between border-b border-gray-100 px-5 py-4">
        <div>
            <h1 class="font-display text-base font-bold text-hut-dark">Doctors</h1>
            <p class="text-xs text-gray-500">Manage doctors for this clinic.</p>
        </div>
        <a href="{{ route('manager.doctors.create') }}" class="btn rounded-lg bg-hut-yellow px-3.5 py-2 text-xs font-semibold text-hut-dark">Add Doctor</a>
    </div>
    @if(session('success'))
        <div class="mx-5 mt-4 rounded-lg bg-green-50 px-4 py-3 text-sm text-green-700">{{ session('success') }}</div>
    @endif
    <div class="overflow-x-auto">
        <table class="w-full text-left text-sm">
            <thead class="border-b border-gray-100 bg-gray-50 text-xs uppercase text-gray-500">
                <tr><th class="px-5 py-3">Doctor</th><th class="px-3 py-3">Contact</th><th class="px-3 py-3">Status</th><th class="px-5 py-3 text-right">Actions</th></tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($doctors as $doctor)
                    <tr>
                        <td class="px-5 py-3"><div class="font-semibold text-hut-dark">{{ $doctor->name }}</div><div class="text-xs text-gray-500">{{ $doctor->specialty }}</div></td>
                        <td class="px-3 py-3 text-gray-600">{{ $doctor->phone ?: 'No phone' }}<br>{{ $doctor->email ?: 'No email' }}</td>
                        <td class="px-3 py-3"><span class="rounded-full bg-gray-100 px-2.5 py-1 text-xs font-medium capitalize">{{ $doctor->status }}</span></td>
                        <td class="px-5 py-3 text-right"><a href="{{ route('manager.doctors.edit', $doctor) }}" class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-hut-green hover:bg-hut-green/10" aria-label="Edit doctor" title="Edit doctor"><x-icons.edit class="h-4 w-4" /></a>
                            <form class="ml-3 inline" method="POST" action="{{ route('manager.doctors.destroy', $doctor) }}" onsubmit="return confirm('Delete this doctor?')">@csrf @method('DELETE')<button type="submit" class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-red-600 hover:bg-red-50" aria-label="Delete doctor" title="Delete doctor"><x-icons.trash class="h-4 w-4" /></button></form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="px-5 py-10 text-center text-sm text-gray-500">No doctors found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="px-5 py-4">{{ $doctors->links() }}</div>
</div>
@endsection
