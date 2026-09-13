@extends('manager.layout.master')
@section('title', 'Kitchen Display')
@section('page-content')
    <div class="space-y-6">
        <div>
            <h1 class="text-2xl font-semibold text-hut-dark">Kitchen display</h1>
            <p class="text-sm text-gray-500">Queue, prepare, and complete kitchen tickets.</p>
        </div>@if(session('success'))
        <div class="rounded-lg bg-green-50 px-4 py-3 text-sm text-green-700">{{ session('success') }}</div>@endif<div
            class="grid gap-6 lg:grid-cols-3">
            @foreach(['queued' => 'Queued', 'preparing' => 'Preparing', 'ready' => 'Ready'] as $status => $label)
                <section class="rounded-2xl border border-gray-200 bg-white p-4 shadow-sm">
                    <h2 class="mb-4 font-semibold">{{ $label }}</h2>@forelse($tickets->where('status', $status) as $ticket)
                        <div class="mb-3 rounded-xl border border-gray-200 p-3">
                            <div class="flex justify-between"><strong>{{ $ticket->ticket_number }}</strong><span
                                    class="text-xs text-gray-500">P{{ $ticket->priority }}</span></div>
                            <p class="text-sm text-gray-600">{{ $ticket->station ?: 'Kitchen' }} ·
                                {{ $ticket->order?->order_number ?: 'Manual ticket' }}
                            </p>
                            <form method="POST" action="{{ route('manager.kitchen-display.tickets.status', $ticket) }}"
                                class="mt-2 flex gap-2">@csrf @method('PATCH')<select name="status"
                                    class="w-full rounded border-gray-300 text-xs">
                                    <option value="preparing">Preparing</option>
                                    <option value="ready">Ready</option>
                                    <option value="completed">Complete</option>
                                    <option value="cancelled">Cancel</option>
                                </select><button class="text-xs font-medium text-hut-green">Save</button></form>
                    </div>@empty<p class="text-sm text-gray-500">No tickets.</p>@endforelse
            </section>@endforeach
        </div>
        <div class="rounded-2xl border border-gray-200 bg-white p-4 shadow-sm">
            <h2 class="mb-3 font-semibold">Queue a ticket</h2>
            <form method="POST" action="{{ route('manager.kitchen-display.tickets.store') }}" class="flex flex-wrap gap-3">
                @csrf<select name="order_id" class="rounded-lg border-gray-300">
                    <option value="">Manual ticket</option>@foreach($orders as $order)
                    <option value="{{ $order->id }}">{{ $order->order_number }}</option>@endforeach
                </select><input name="station" placeholder="Station" class="rounded-lg border-gray-300"><input type="number"
                    name="priority" min="0" max="10" value="0" class="w-24 rounded-lg border-gray-300"><input name="notes"
                    placeholder="Notes" class="flex-1 rounded-lg border-gray-300"><button
                    class="rounded-lg bg-hut-dark px-4 py-2 text-sm font-semibold text-white">Queue</button></form>
        </div>
</div>@endsection