@extends('layouts.admin')
@section('title', 'Appointments')
@section('content')
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-display font-bold text-hut-dark">Appointments</h1>
            <p class="mt-1 text-sm text-gray-500">Manage salon bookings, staff schedules, and service status.</p>
        </div>
        <a href="{{ route('manager.appointments.create') }}"
            class="rounded-lg bg-hut-dark px-4 py-2 text-sm font-semibold text-white">Book appointment</a>
    </div>

    <form method="GET" class="mb-5 grid gap-3 rounded-xl border border-gray-100 bg-white p-4 shadow-sm sm:grid-cols-3">
        <input type="date" name="date" value="{{ request('date') }}"
            class="rounded-lg border border-gray-200 px-3 py-2 text-sm">
        <select name="status" class="rounded-lg border border-gray-200 px-3 py-2 text-sm">
            <option value="">All statuses</option>
            @foreach(['scheduled', 'confirmed', 'completed', 'cancelled'] as $status)
                <option value="{{ $status }}" @selected(request('status') === $status)>{{ ucfirst($status) }}</option>
            @endforeach
        </select>
        <button class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700">Filter
            appointments</button>
    </form>

    <div class="overflow-hidden rounded-xl border border-gray-100 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="min-w-full text-left text-sm">
                <thead class="bg-gray-50 text-xs uppercase text-gray-500">
                    <tr>
                        <th class="px-4 py-3">Date & time</th>
                        <th class="px-4 py-3">Customer</th>
                        <th class="px-4 py-3">Service</th>
                        <th class="px-4 py-3">Staff</th>
                        <th class="px-4 py-3">Price</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($appointments as $appointment)
                        @php
                            $statusClass = match ($appointment->status) {
                                'completed' => 'bg-green-50 text-green-700',
                                'cancelled' => 'bg-red-50 text-red-700',
                                'confirmed' => 'bg-blue-50 text-blue-700',
                                default => 'bg-yellow-50 text-yellow-700',
                            };
                        @endphp
                        <tr>
                            <td class="px-4 py-3 whitespace-nowrap">
                                <div class="font-medium text-gray-800">{{ $appointment->starts_at->format('d M Y') }}</div>
                                <div class="text-xs text-gray-500">{{ $appointment->starts_at->format('H:i') }} -
                                    {{ $appointment->ends_at->format('H:i') }}</div>
                            </td>
                            <td class="px-4 py-3">{{ $appointment->customer->name }}
                                <div class="text-xs text-gray-500">{{ $appointment->customer->phone }}</div>
                            </td>
                            <td class="px-4 py-3 font-medium">{{ $appointment->service_name }}</td>
                            <td class="px-4 py-3">{{ $appointment->staff?->name ?? 'Unassigned' }}</td>
                            <td class="px-4 py-3">
                                {{ $appointment->price !== null ? 'Rs. ' . number_format($appointment->price, 2) : '—' }}</td>
                            <td class="px-4 py-3"><span
                                    class="rounded-full px-2.5 py-1 text-xs font-semibold {{ $statusClass }}">{{ ucfirst($appointment->status) }}</span>
                            </td>
                            <td class="px-4 py-3">
                                <form method="POST" action="{{ route('manager.appointments.status', $appointment) }}"
                                    class="flex items-center gap-2">
                                    @csrf @method('PATCH')
                                    <select name="status" class="rounded border border-gray-200 px-2 py-1 text-xs">
                                        @foreach(['scheduled', 'confirmed', 'completed', 'cancelled'] as $status)
                                            <option value="{{ $status }}" @selected($appointment->status === $status)>
                                                {{ ucfirst($status) }}</option>
                                        @endforeach
                                    </select>
                                    <button class="text-xs font-semibold text-hut-dark hover:underline">Save</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-12 text-center text-gray-500">No appointments found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="border-t border-gray-100 px-5 py-4">{{ $appointments->links() }}</div>
    </div>
@endsection