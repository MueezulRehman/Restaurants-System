@extends('customer.layout.master')

@section('title', 'Share Feedback')

@section('page-content')
<div class="min-h-screen bg-gray-50 px-4 py-10">
    <div class="mx-auto max-w-2xl rounded-2xl border border-gray-200 bg-white p-8 shadow-sm">
        <h1 class="text-2xl font-semibold text-hut-dark">Share Feedback</h1>
        <p class="mt-2 text-sm text-gray-500">Tell us about your
            experience{{ $restaurant ? ' at ' . $restaurant->name : '' }} and help us improve.</p>

        <form action="{{ route('customer.feedback.store') }}" method="POST" class="mt-6 space-y-5">
            @csrf
            <div>
                <label class="mb-2 block text-sm font-medium text-gray-700">Type</label>
                <select name="type" class="w-full rounded-lg border border-gray-300 px-3 py-2">
                    <option value="suggestion">Suggestion</option>
                    <option value="complaint">Complaint</option>
                    <option value="praise">Praise</option>
                    <option value="bug_report">Bug report</option>
                </select>
            </div>
            <div>
                <label class="mb-2 block text-sm font-medium text-gray-700">Title</label>
                <input type="text" name="title" required class="w-full rounded-lg border border-gray-300 px-3 py-2" />
            </div>
            <div>
                <label class="mb-2 block text-sm font-medium text-gray-700">Message</label>
                <textarea name="message" rows="5" required
                    class="w-full rounded-lg border border-gray-300 px-3 py-2"></textarea>
            </div>
            <div>
                <fieldset>
                    <legend class="mb-2 block text-sm font-medium text-gray-700">How would you rate your experience?
                    </legend>
                    <div class="flex flex-wrap gap-2">
                        @foreach(range(1, 5) as $rating)
                            <label
                                class="cursor-pointer rounded-lg border border-gray-200 px-3 py-2 text-sm has-[:checked]:border-hut-yellow has-[:checked]:bg-hut-yellow/20">
                                <input type="radio" name="rating" value="{{ $rating }}" class="sr-only">
                                <span aria-label="{{ $rating }} out of 5 stars">{{ str_repeat('★', $rating) }}</span>
                            </label>
                        @endforeach
                    </div>
                </fieldset>
            </div>
            <div class="flex items-center gap-3">
                <button type="submit" class="rounded-lg bg-hut-dark px-4 py-2 font-semibold text-white">Submit
                    Feedback</button>
                <a href="{{ route('customer.feedback.index') }}" class="text-sm text-gray-500 hover:text-hut-dark">View
                    my feedback</a>
            </div>
        </form>
    </div>
</div>
@endsection