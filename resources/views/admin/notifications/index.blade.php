@extends('layouts.admin')

@section('title', 'Notifications')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-semibold text-hut-dark">Notifications</h2>
            <p class="text-sm text-gray-500">Keep staff and customers informed about important events.</p>
        </div>
    </div>

    <div class="grid md:grid-cols-3 gap-4">
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-4">
            <p class="text-sm text-gray-500">Pending</p>
            <p class="text-2xl font-semibold text-hut-dark">{{ $stats['pending'] }}</p>
        </div>
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-4">
            <p class="text-sm text-gray-500">Sent</p>
            <p class="text-2xl font-semibold text-hut-dark">{{ $stats['sent'] }}</p>
        </div>
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-4">
            <p class="text-sm text-gray-500">Failed</p>
            <p class="text-2xl font-semibold text-hut-dark">{{ $stats['failed'] }}</p>
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-4">
        <form method="POST" action="{{ route('manager.notifications.store') }}" class="grid md:grid-cols-2 gap-4">
            @csrf
            <div>
                <label class="block text-sm font-medium text-gray-700">Type</label>
                <select name="type" class="mt-1 w-full border rounded-lg px-3 py-2">
                    @foreach(['order_update','feedback_reply','low_stock','delivery_update','custom'] as $type)
                        <option value="{{ $type }}">{{ ucfirst(str_replace('_',' ',$type)) }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Title</label>
                <input type="text" name="title" class="mt-1 w-full border rounded-lg px-3 py-2" placeholder="New message" required>
            </div>
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700">Message</label>
                <textarea name="message" rows="3" class="mt-1 w-full border rounded-lg px-3 py-2" required></textarea>
            </div>
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700">Channels</label>
                <div class="flex gap-3 mt-2">
                    @foreach(['email','whatsapp','push'] as $channel)
                        <label class="flex items-center gap-2 text-sm">
                            <input type="checkbox" name="channels[]" value="{{ $channel }}" {{ $channel === 'email' ? 'checked' : '' }}>
                            {{ ucfirst($channel) }}
                        </label>
                    @endforeach
                </div>
            </div>
            <div class="md:col-span-2">
                <button class="px-4 py-2 rounded-lg bg-hut-dark text-white">Create notification</button>
            </div>
        </form>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Title</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Type</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Status</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Channels</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($notifications as $notification)
                    <tr>
                        <td class="px-4 py-3">{{ $notification->title }}</td>
                        <td class="px-4 py-3">{{ ucfirst(str_replace('_',' ',$notification->type)) }}</td>
                        <td class="px-4 py-3">{{ ucfirst($notification->status) }}</td>
                        <td class="px-4 py-3">{{ implode($notification->channels ?? [], ', ') }}</td>
                        <td class="px-4 py-3">
                            <form method="POST" action="{{ route('manager.notifications.read', $notification) }}">
                                @csrf
                                <button class="px-3 py-1 rounded-lg bg-hut-yellow text-hut-dark font-medium">Mark read</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-4 py-6 text-center text-gray-500">No notifications found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $notifications->links() }}
    </div>
</div>
@endsection
