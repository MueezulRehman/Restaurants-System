@extends('layouts.admin')

@section('title', 'Feedback Details')

@section('content')
<div class="space-y-6">
    <div class="max-w-3xl rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
        <div class="mb-6">
            <h2 class="text-2xl font-semibold text-hut-dark">Feedback Details</h2>
            <p class="text-sm text-gray-500">Review the feedback content and update its handling status.</p>
        </div>

        <div class="space-y-4">
            <div>
                <p class="text-sm font-medium text-gray-500">Title</p>
                <p class="text-lg text-hut-dark">{{ $feedback->title ?? 'Feedback' }}</p>
            </div>
            <div>
                <p class="text-sm font-medium text-gray-500">Message</p>
                <p class="whitespace-pre-wrap text-gray-700">{{ $feedback->message }}</p>
            </div>
            <div>
                <p class="text-sm font-medium text-gray-500">Status</p>
                <p class="text-gray-700">{{ ucfirst($feedback->status) }}</p>
            </div>
        </div>
    </div>

    <div class="grid gap-6 lg:grid-cols-2">
        <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
            <h3 class="text-lg font-semibold text-hut-dark">Update status</h3>
            <form action="{{ route('admin.feedback.update-status', $feedback) }}" method="POST" class="mt-4 space-y-4">
                @csrf
                @method('PATCH')
                <select name="status" class="w-full rounded-lg border border-gray-300 px-3 py-2">
                    <option value="open" {{ $feedback->status === 'open' ? 'selected' : '' }}>Open</option>
                    <option value="in_progress" {{ $feedback->status === 'in_progress' ? 'selected' : '' }}>In progress</option>
                    <option value="resolved" {{ $feedback->status === 'resolved' ? 'selected' : '' }}>Resolved</option>
                    <option value="closed" {{ $feedback->status === 'closed' ? 'selected' : '' }}>Closed</option>
                </select>
                <button type="submit" class="rounded-lg bg-hut-dark px-4 py-2 font-semibold text-white">Save</button>
            </form>
        </div>

        <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
            <h3 class="text-lg font-semibold text-hut-dark">Reply</h3>
            <form action="{{ route('admin.feedback.reply', $feedback) }}" method="POST" class="mt-4 space-y-4">
                @csrf
                <textarea name="message" rows="4" class="w-full rounded-lg border border-gray-300 px-3 py-2" placeholder="Write a reply to the customer or manager"></textarea>
                <label class="flex items-center gap-2 text-sm text-gray-700">
                    <input type="checkbox" name="is_internal" value="1" class="rounded border-gray-300" />
                    Internal note only
                </label>
                <button type="submit" class="rounded-lg bg-hut-dark px-4 py-2 font-semibold text-white">Send reply</button>
            </form>
        </div>
    </div>
</div>
@endsection
