@extends('layouts.admin')

@section('title', 'Feedback')

@php
    $feedbackPrefix = auth()->user()?->isSuperAdmin() ? 'admin' : 'manager';
@endphp

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-semibold text-hut-dark">Feedback</h2>
            <p class="text-sm text-gray-500">Review customer and restaurant feedback.</p>
        </div>
    </div>

    <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Title</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Status</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Date</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($feedback as $item)
                    <tr class="hover:bg-gray-50 cursor-pointer" onclick="window.location='{{ route($feedbackPrefix . '.feedback.show', $item) }}'">
                        <td class="px-4 py-3 font-medium text-hut-dark">{{ $item->title ?? 'Feedback' }}</td>
                        <td class="px-4 py-3 text-sm">
                            <span class="rounded-full px-2.5 py-1 text-xs font-semibold {{ $item->status === 'resolved' ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700' }}">{{ ucfirst($item->status) }}</span>
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-600">{{ $item->created_at->format('M d, Y') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="px-4 py-6 text-center text-sm text-gray-500">No feedback found yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
