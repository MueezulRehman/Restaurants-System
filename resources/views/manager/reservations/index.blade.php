@extends('manager.layout.master')
@section('title', 'Reservations')
@section('page-content')
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-hut-dark">Reservations</h1>
                <p class="text-sm text-gray-500">Manage table bookings and guest arrivals.</p>
            </div><a href="{{ route('manager.reservations.create') }}"
                class="rounded-lg bg-hut-dark px-4 py-2 text-sm font-semibold text-white">New reservation</a>
        </div>
        @if(session('success'))
        <div class="rounded-lg bg-green-50 px-4 py-3 text-sm text-green-700">{{ session('success') }}</div>@endif
        <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs uppercase text-gray-500">Guest</th>
                        <th class="px-4 py-3 text-left text-xs uppercase text-gray-500">Time</th>
                        <th class="px-4 py-3 text-left text-xs uppercase text-gray-500">Party</th>
                        <th class="px-4 py-3 text-left text-xs uppercase text-gray-500">Table</th>
                        <th class="px-4 py-3 text-left text-xs uppercase text-gray-500">Status</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">@forelse($reservations as $reservation)
                    <tr>
                        <td class="px-4 py-3">
                            <div class="font-medium">{{ $reservation->guest_name }}</div>
                            <div class="text-xs text-gray-500">{{ $reservation->guest_phone }}</div>
                        </td>
                        <td class="px-4 py-3 text-sm">{{ $reservation->starts_at?->format('M d, Y g:i A') }}</td>
                        <td class="px-4 py-3 text-sm">{{ $reservation->party_size }}</td>
                        <td class="px-4 py-3 text-sm">
                            {{ $reservation->table?->label ?? $reservation->table?->number ?? 'Unassigned' }}
                        </td>
                        <td class="px-4 py-3 text-sm">{{ ucfirst(str_replace('_', ' ', $reservation->status)) }}</td>
                        <td class="px-4 py-3">
                            <form method="POST" action="{{ route('manager.reservations.status', $reservation) }}"
                                class="flex gap-2">@csrf @method('PATCH')<select name="status"
                                    class="rounded border-gray-300 text-sm">
                                    <option value="confirmed">Confirm</option>
                                    <option value="seated">Seat</option>
                                    <option value="completed">Complete</option>
                                    <option value="cancelled">Cancel</option>
                                    <option value="no_show">No show</option>
                                </select><button class="text-sm font-medium text-hut-green">Update</button></form>
                        </td>
                </tr>@empty<tr>
                        <td colspan="6" class="px-4 py-8 text-center text-sm text-gray-500">No reservations yet.</td>
                    </tr>@endforelse
                </tbody>
            </table>
            <div class="p-4">{{ $reservations->links() }}</div>
        </div>
    </div>
@endsection