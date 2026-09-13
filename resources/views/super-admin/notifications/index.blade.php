@extends('super-admin.layout.master')

@section('title', 'Notifications')

@section('page-content')
    <div class="space-y-6">
        <div>
            <h2 class="text-2xl font-semibold text-hut-dark">Notifications</h2>
            <p class="text-sm text-gray-500">Review platform and business notifications.</p>
        </div>

        <div class="grid gap-4 md:grid-cols-3">
            @foreach (['pending' => 'Pending', 'sent' => 'Sent', 'failed' => 'Failed'] as $key => $label)
                <div class="rounded-2xl border border-gray-200 bg-white p-4 shadow-sm">
                    <p class="text-sm text-gray-500">{{ $label }}</p>
                    <p class="text-2xl font-semibold text-hut-dark">{{ $stats[$key] }}</p>
                </div>
            @endforeach
        </div>

        <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Title</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Business</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Type</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Status</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($notifications as $notification)
                            <tr>
                                <td class="px-4 py-3 font-medium text-hut-dark">{{ $notification->title }}</td>
                                <td class="px-4 py-3 text-sm text-gray-600">{{ $notification->restaurant?->name ?? 'Platform' }}</td>
                                <td class="px-4 py-3 text-sm">{{ ucfirst(str_replace('_', ' ', $notification->type)) }}</td>
                                <td class="px-4 py-3 text-sm">{{ ucfirst($notification->status) }}</td>
                                <td class="px-4 py-3">
                                    @if (!$notification->read_at)
                                        <form method="POST" action="{{ route('admin.notifications.read', $notification) }}">
                                            @csrf
                                            <button class="rounded-lg bg-hut-yellow px-3 py-1 text-sm font-medium text-hut-dark">Mark read</button>
                                        </form>
                                    @else
                                        <span class="text-sm text-gray-500">Read</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="px-4 py-6 text-center text-sm text-gray-500">No notifications found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{ $notifications->links() }}
    </div>
@endsection
