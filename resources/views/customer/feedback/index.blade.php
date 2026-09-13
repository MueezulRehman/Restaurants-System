@extends('customer.layout.master')

@section('title', 'My Feedback')

@section('page-content')
<div class="min-h-screen bg-gray-50 px-4 py-10">
    <div class="mx-auto max-w-4xl rounded-2xl border border-gray-200 bg-white p-8 shadow-sm">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-hut-dark">My Feedback</h1>
                <p class="mt-2 text-sm text-gray-500">View the feedback you have submitted.</p>
            </div>
            <a href="{{ route('customer.feedback.create') }}" class="rounded-lg bg-hut-dark px-4 py-2 text-sm font-semibold text-white">New feedback</a>
        </div>

        <div class="mt-6 space-y-4">
            @forelse($feedback as $item)
                <div class="rounded-xl border border-gray-200 p-4">
                    <div class="flex items-center justify-between gap-3">
                        <div>
                            <h2 class="font-semibold text-hut-dark">{{ $item->title }}</h2>
                            <p class="text-sm text-gray-500">{{ ucfirst($item->type) }} · {{ $item->created_at->format('M d, Y') }}</p>
                        </div>
                        <span class="rounded-full bg-gray-100 px-3 py-1 text-xs font-semibold text-gray-700">{{ ucfirst($item->status) }}</span>
                    </div>
                    <p class="mt-3 text-sm text-gray-700">{{ $item->message }}</p>
                    <a href="{{ route('customer.feedback.show', $item) }}"
                        title="View feedback details" aria-label="View feedback details"
                        class="mt-4 inline-flex h-8 w-8 items-center justify-center rounded-lg text-hut-dark hover:bg-gray-100">
                        <i class="fas fa-eye" aria-hidden="true"></i>
                    </a>
                </div>
            @empty
                <div class="rounded-xl border border-dashed border-gray-300 p-6 text-center text-sm text-gray-500">No feedback submitted yet.</div>
            @endforelse
        </div>
    </div>
</div>
@endsection
