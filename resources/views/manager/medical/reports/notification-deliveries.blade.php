@extends('manager.layout.master')

@section('title', 'Notification Delivery History')

@section('page-content')
    <div class="space-y-6">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h1 class="text-2xl font-semibold text-hut-dark">Notification Delivery History</h1>
                <p class="text-sm text-gray-500">Masked recipients and queue status for this business only.</p>
            </div>
            <a href="{{ route('manager.medical-notifications.edit') }}" class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-semibold text-hut-dark">
                Notification settings
            </a>
        </div>

        <form method="GET" class="flex flex-wrap items-end gap-3 rounded-2xl border border-gray-200 bg-white p-4">
            <label class="text-sm font-medium">Status
                <select name="status" class="mt-1 block rounded-lg border px-3 py-2">
                    <option value="">All statuses</option>
                    @foreach(['queued', 'sent', 'failed'] as $option)
                        <option value="{{ $option }}" @selected($status === $option)>{{ ucfirst($option) }}</option>
                    @endforeach
                </select>
            </label>
            <button class="rounded-lg bg-hut-dark px-4 py-2 text-sm font-semibold text-white">Filter</button>
        </form>

        <div class="overflow-x-auto rounded-2xl border border-gray-200 bg-white shadow-sm">
            <table class="min-w-full text-left text-sm">
                <thead class="border-b bg-gray-50 text-xs uppercase tracking-wide text-gray-500">
                    <tr>
                        <th class="px-5 py-3">Queue</th>
                        <th class="px-5 py-3">Doctor</th>
                        <th class="px-5 py-3">Channel</th>
                        <th class="px-5 py-3">Recipient</th>
                        <th class="px-5 py-3">Status</th>
                        <th class="px-5 py-3">Sent at</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($deliveries as $delivery)
                        <tr>
                            <td class="px-5 py-3 font-medium">Token {{ $delivery->queueEntry?->token_number ?? '—' }}</td>
                            <td class="px-5 py-3">{{ $delivery->queueEntry?->doctor?->name ?? '—' }}</td>
                            <td class="px-5 py-3">{{ strtoupper($delivery->channel) }}</td>
                            <td class="px-5 py-3 font-mono text-xs">{{ $delivery->recipient_masked }}</td>
                            <td class="px-5 py-3">
                                <span class="rounded-full px-2 py-1 text-xs font-semibold {{ $delivery->status === 'sent' ? 'bg-green-100 text-green-700' : ($delivery->status === 'failed' ? 'bg-red-100 text-red-700' : 'bg-gray-100 text-gray-700') }}">
                                    {{ ucfirst($delivery->status) }}
                                </span>
                            </td>
                            <td class="px-5 py-3 text-gray-500">{{ $delivery->sent_at?->format('Y-m-d H:i') ?? '—' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-5 py-8 text-center text-gray-500">No delivery attempts recorded yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $deliveries->links() }}
    </div>
@endsection
