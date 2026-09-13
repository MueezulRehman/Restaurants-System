@extends('manager.layout.master')

@section('title', 'Queue Notifications')

@section('page-content')
    <div class="max-w-3xl rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
        <h1 class="text-2xl font-semibold text-hut-dark">Medical Queue Notifications</h1>
        <p class="mt-1 text-sm text-gray-500">Notifications are off by default. Enable a channel only after confirming patient consent at check-in.</p>

        @if(session('success'))
            <div class="mt-4 rounded-lg bg-green-50 p-3 text-sm text-green-700">{{ session('success') }}</div>
        @endif

        <form method="POST" action="{{ route('manager.medical-notifications.update') }}" class="mt-6 space-y-5">
            @csrf
            @method('PATCH')
            <label class="flex items-center gap-2 text-sm font-medium">
                <input type="hidden" name="queue_notifications_enabled" value="0">
                <input type="checkbox" name="queue_notifications_enabled" value="1"
                    @checked(old('queue_notifications_enabled', $restaurant->queue_notifications_enabled ?? false))>
                Enable queue notifications for this business
            </label>
            <div class="grid gap-2 sm:grid-cols-2">
                @foreach(['sms' => 'SMS', 'whatsapp' => 'WhatsApp'] as $value => $label)
                    <label class="flex items-center gap-2 text-sm">
                        <input type="checkbox" name="queue_notification_channels[]" value="{{ $value }}"
                            @checked(in_array($value, old('queue_notification_channels', $restaurant->queue_notification_channels ?? []), true))>
                        {{ $label }}
                    </label>
                @endforeach
            </div>
            <button class="rounded-lg bg-hut-dark px-4 py-2 text-sm font-semibold text-white">Save settings</button>
        </form>
    </div>
@endsection
