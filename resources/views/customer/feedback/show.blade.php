<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Feedback Details</title>
    @vite(['resources/css/app.css'])
</head>
<body class="min-h-screen bg-gray-50 px-4 py-10">
    <div class="mx-auto max-w-3xl rounded-2xl border border-gray-200 bg-white p-8 shadow-sm">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-hut-dark">Feedback Details</h1>
                <p class="mt-2 text-sm text-gray-500">Your submission is visible only to the restaurant team and the platform admin.</p>
            </div>
            <a href="{{ route('customer.feedback.index') }}" class="text-sm font-semibold text-hut-dark">Back to list</a>
        </div>

        <div class="mt-6 space-y-4">
            <div>
                <p class="text-sm font-medium text-gray-500">Title</p>
                <p class="text-lg text-hut-dark">{{ $feedback->title }}</p>
            </div>
            <div>
                <p class="text-sm font-medium text-gray-500">Type</p>
                <p class="text-gray-700">{{ ucfirst($feedback->type) }}</p>
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
</body>
</html>
