<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Share Feedback</title>
    @vite(['resources/css/app.css'])
</head>
<body class="min-h-screen bg-gray-50 px-4 py-10">
    <div class="mx-auto max-w-2xl rounded-2xl border border-gray-200 bg-white p-8 shadow-sm">
        <h1 class="text-2xl font-semibold text-hut-dark">Share Feedback</h1>
        <p class="mt-2 text-sm text-gray-500">Tell us about your experience and help us improve.</p>

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
                <textarea name="message" rows="5" required class="w-full rounded-lg border border-gray-300 px-3 py-2"></textarea>
            </div>
            <div>
                <label class="mb-2 block text-sm font-medium text-gray-700">Rating</label>
                <select name="rating" class="w-full rounded-lg border border-gray-300 px-3 py-2">
                    <option value="">No rating</option>
                    <option value="1">1 star</option>
                    <option value="2">2 stars</option>
                    <option value="3">3 stars</option>
                    <option value="4">4 stars</option>
                    <option value="5">5 stars</option>
                </select>
            </div>
            <div class="flex items-center gap-3">
                <button type="submit" class="rounded-lg bg-hut-dark px-4 py-2 font-semibold text-white">Submit Feedback</button>
                <a href="{{ route('customer.feedback.index') }}" class="text-sm text-gray-500 hover:text-hut-dark">View my feedback</a>
            </div>
        </form>
    </div>
</body>
</html>
