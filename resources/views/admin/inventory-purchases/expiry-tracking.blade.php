@extends('layouts.admin')
@section('title', 'Expiry Tracking')
@section('content')
    <div class="mb-6 flex items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-display font-bold text-hut-dark">Expiry tracking</h1>
            <p class="mt-1 text-sm text-gray-500">Monitor date-sensitive inventory received through purchasing.</p>
        </div>
        <a href="{{ route('manager.purchasing.create') }}"
            class="rounded-lg bg-hut-dark px-4 py-2 text-sm font-semibold text-white">Receive stock</a>
    </div>

    <div class="mb-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        @foreach([
                ['expired', 'Expired', 'text-red-700', 'bg-red-50', 'border-red-200'],
                ['within_30_days', 'Within 30 days', 'text-orange-700', 'bg-orange-50', 'border-orange-200'],
                ['within_90_days', 'Within 90 days', 'text-yellow-700', 'bg-yellow-50', 'border-yellow-200'],
                ['good', 'More than 90 days', 'text-green-700', 'bg-green-50', 'border-green-200'],
            ] as [$key, $label, $text, $background, $border])
            <div class="rounded-xl border {{ $border }} {{ $background }} p-4">
                <p class="text-sm {{ $text }}">{{ $label }}</p>
                <p class="mt-1 text-3xl font-bold {{ $text }}">{{ $buckets[$key]->count() }}</p>
            </div>
        @endforeach
    </div>

    <div class="overflow-hidden rounded-xl border border-gray-100 bg-white shadow-sm">
        <div class="border-b border-gray-100 px-5 py-4">
            <h2 class="font-semibold text-hut-dark">Tracked inventory</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full min-w-full text-left text-sm">
                <thead class="bg-gray-50 text-xs uppercase text-gray-500">
                    <tr>
                        <th class="px-4 py-3">Product</th>
                        <th class="px-4 py-3">Expiry date</th>
                        <th class="px-4 py-3">Quantity received</th>
                        <th class="px-4 py-3">Purchase date</th>
                        <th class="px-4 py-3">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($items as $item)
                        @php
                            $status = $item->expiry_date->lt(now()->startOfDay()) ? 'Expired' : ($item->expiry_date->lte(now()->addDays(30)) ? 'Within 30 days' : ($item->expiry_date->lte(now()->addDays(90)) ? 'Within 90 days' : 'Good'));
                            $statusClass = $status === 'Expired' ? 'text-red-700 bg-red-50' : ($status === 'Within 30 days' ? 'text-orange-700 bg-orange-50' : ($status === 'Within 90 days' ? 'text-yellow-700 bg-yellow-50' : 'text-green-700 bg-green-50'));
                        @endphp
                        <tr>
                            <td class="px-4 py-3 font-medium text-gray-800">
                                {{ $item->menuItem?->name ?? $item->variant?->menuItem?->name ?? 'Product' }}</td>
                            <td class="px-4 py-3">{{ $item->expiry_date->format('d M Y') }}</td>
                            <td class="px-4 py-3">{{ $item->quantity }}</td>
                            <td class="px-4 py-3">{{ $item->purchase?->purchase_date?->format('d M Y') ?? '—' }}</td>
                            <td class="px-4 py-3"><span
                                    class="rounded-full px-2.5 py-1 text-xs font-semibold {{ $statusClass }}">{{ $status }}</span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-12 text-center text-gray-500">No expiry dates have been recorded yet.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection