@extends('layouts.admin')

@section('title', 'Opening Hours')

@section('content')
<div class="mx-auto max-w-3xl space-y-8">
    <div class="flex flex-wrap items-start justify-between gap-3">
        <div>
            <h2 class="text-2xl font-semibold text-hut-dark">Opening hours</h2>
            <p class="text-sm text-gray-500">Weekly schedule and same-day overrides. Online orders follow these rules.</p>
        </div>
        <div class="rounded-xl border px-4 py-2 text-sm {{ $accepting ? 'border-emerald-200 bg-emerald-50 text-emerald-800' : 'border-slate-200 bg-slate-100 text-slate-700' }}">
            <span class="font-semibold">Now:</span> {{ $statusLabel }}
            <div class="text-xs mt-0.5">{{ $accepting ? 'Accepting online orders' : 'Not accepting online orders' }}</div>
        </div>
    </div>

    {{-- Same-day quick controls --}}
    <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm space-y-4">
        <h3 class="text-lg font-semibold text-hut-dark">Today’s controls</h3>
        <p class="text-sm text-gray-500">Use these when you close early, stay open late, or take a day off. Flags reset automatically after midnight.</p>

        <div class="grid gap-3 sm:grid-cols-2">
            <form action="{{ route('manager.hours.today') }}" method="POST" class="rounded-xl border border-amber-200 bg-amber-50 p-4 space-y-2">
                @csrf
                <input type="hidden" name="action" value="closed_today" />
                <div class="font-medium text-amber-900 text-sm">Closed today</div>
                <input type="text" name="closed_message" placeholder="Message for customers" class="w-full rounded border border-amber-200 px-2 py-1.5 text-sm" value="{{ $restaurant->closed_message }}" />
                <button class="rounded-lg bg-amber-600 px-3 py-1.5 text-sm font-semibold text-white">Mark closed today</button>
            </form>

            <form action="{{ route('manager.hours.today') }}" method="POST" class="rounded-xl border border-emerald-200 bg-emerald-50 p-4 space-y-2">
                @csrf
                <input type="hidden" name="action" value="open_today" />
                <div class="font-medium text-emerald-900 text-sm">Re-open today</div>
                <p class="text-xs text-emerald-800">Clears the “closed today” flag.</p>
                <button class="rounded-lg bg-emerald-600 px-3 py-1.5 text-sm font-semibold text-white">Open today</button>
            </form>

            <form action="{{ route('manager.hours.today') }}" method="POST" class="rounded-xl border border-gray-200 p-4 space-y-2">
                @csrf
                <input type="hidden" name="action" value="early_close" />
                <div class="font-medium text-sm">Close early today</div>
                <input type="time" name="early_close_time" value="{{ optional($restaurant->early_close_at)->format('H:i') ?? '18:00' }}" class="w-full rounded border px-2 py-1.5 text-sm" required />
                <input type="text" name="closed_message" placeholder="Optional message" class="w-full rounded border px-2 py-1.5 text-sm" />
                <button class="rounded-lg bg-hut-dark px-3 py-1.5 text-sm font-semibold text-white">Set early close</button>
            </form>

            <form action="{{ route('manager.hours.today') }}" method="POST" class="rounded-xl border border-gray-200 p-4 space-y-2">
                @csrf
                <input type="hidden" name="action" value="extend" />
                <div class="font-medium text-sm">Stay open later today</div>
                <input type="time" name="extend_close_time" value="{{ optional($restaurant->extend_close_at)->format('H:i') ?? '23:30' }}" class="w-full rounded border px-2 py-1.5 text-sm" required />
                <button class="rounded-lg bg-hut-dark px-3 py-1.5 text-sm font-semibold text-white">Extend hours</button>
            </form>
        </div>

        <form action="{{ route('manager.hours.today') }}" method="POST">
            @csrf
            <input type="hidden" name="action" value="clear_overrides" />
            <button class="text-sm text-gray-500 underline">Clear all same-day overrides</button>
        </form>
    </div>

    {{-- Weekly schedule --}}
    <form action="{{ route('manager.hours.weekly') }}" method="POST" class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm space-y-4">
        @csrf
        <h3 class="text-lg font-semibold text-hut-dark">Weekly schedule</h3>
        @foreach(['monday','tuesday','wednesday','thursday','friday','saturday','sunday'] as $day)
            @php $row = $hours[$day] ?? ['open'=>'09:00','close'=>'22:00','closed'=>false]; @endphp
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 items-center border-b border-gray-50 py-2">
                <span class="text-sm font-medium capitalize">{{ $day }}</span>
                <label class="text-xs inline-flex items-center gap-1">
                    <input type="checkbox" name="opening_hours[{{ $day }}][closed]" value="1" {{ !empty($row['closed']) ? 'checked' : '' }} />
                    Day off
                </label>
                <input type="time" name="opening_hours[{{ $day }}][open]" value="{{ $row['open'] ?? '09:00' }}" class="rounded border px-2 py-1 text-sm" />
                <input type="time" name="opening_hours[{{ $day }}][close]" value="{{ $row['close'] ?? '22:00' }}" class="rounded border px-2 py-1 text-sm" />
            </div>
        @endforeach

        <label class="inline-flex items-center gap-2 text-sm">
            <input type="hidden" name="accept_orders_when_closed" value="0" />
            <input type="checkbox" name="accept_orders_when_closed" value="1" {{ $restaurant->accept_orders_when_closed ? 'checked' : '' }} />
            Accept online orders even when closed (pre-orders)
        </label>

        <button type="submit" class="rounded-lg bg-hut-dark px-4 py-2 text-sm font-semibold text-white">Save weekly hours</button>
    </form>
</div>
@endsection
