@extends('manager.layout.master')
@section('title', 'Fitting Room')
@section('page-content')
    <div class="space-y-6">
        <div>
            <h1 class="text-2xl font-semibold text-hut-dark">Fitting-room sessions</h1>
            <p class="text-sm text-gray-500">Track rooms, customers, and checkout handoff.</p>
        </div>@if(session('success'))
        <div class="rounded-lg bg-green-50 px-4 py-3 text-sm text-green-700">{{ session('success') }}</div>@endif<div
            class="rounded-2xl border border-gray-200 bg-white p-4 shadow-sm">
            <form method="POST" action="{{ route('manager.fitting-room.store') }}" class="grid gap-3 md:grid-cols-4">
                @csrf<select name="customer_id" class="rounded-lg border-gray-300">
                    <option value="">Walk-in customer</option>@foreach($customers as $customer)
                    <option value="{{ $customer->id }}">{{ $customer->name }}</option>@endforeach
                </select><select name="staff_id" class="rounded-lg border-gray-300">
                    <option value="">Staff</option>@foreach($staff as $person)
                    <option value="{{ $person->id }}">{{ $person->name }}</option>@endforeach
                </select><input name="room_label" placeholder="Room / area" class="rounded-lg border-gray-300"><input
                    name="notes" placeholder="Notes" class="rounded-lg border-gray-300"><button
                    class="rounded-lg bg-hut-dark px-4 py-2 text-sm font-semibold text-white">Open session</button></form>
        </div>
        <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs uppercase text-gray-500">Room</th>
                        <th class="px-4 py-3 text-left text-xs uppercase text-gray-500">Customer</th>
                        <th class="px-4 py-3 text-left text-xs uppercase text-gray-500">Started</th>
                        <th class="px-4 py-3 text-left text-xs uppercase text-gray-500">Status</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">@forelse($sessions as $session)
                    <tr>
                        <td class="px-4 py-3">{{ $session->room_label ?: 'Unassigned' }}</td>
                        <td class="px-4 py-3">{{ $session->customer?->name ?: 'Walk-in' }}</td>
                        <td class="px-4 py-3 text-sm">{{ $session->started_at?->format('M d, g:i A') }}</td>
                        <td class="px-4 py-3">{{ ucfirst($session->status) }}</td>
                        <td class="px-4 py-3">
                            <form method="POST" action="{{ route('manager.fitting-room.status', $session) }}">@csrf
                                @method('PATCH')<select name="status" onchange="this.form.submit()"
                                    class="rounded border-gray-300 text-sm">
                                    <option value="open">Open</option>
                                    <option value="checkout">Checkout</option>
                                    <option value="closed">Closed</option>
                                    <option value="cancelled">Cancelled</option>
                                </select></form>
                        </td>
                </tr>@empty<tr>
                        <td colspan="5" class="px-4 py-8 text-center text-sm text-gray-500">No fitting-room sessions.</td>
                    </tr>@endforelse
                </tbody>
            </table>
            <div class="p-4">{{ $sessions->links() }}</div>
        </div>
</div>@endsection