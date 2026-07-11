@extends('layouts.admin')
@section('title', 'Add Deal')

@section('content')

<div class="max-w-2xl">
    <a href="{{ route('manager.deals.index') }}" class="text-hut-green text-sm mb-4 inline-block hover:underline">← Back to Deals</a>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <h2 class="text-lg font-display font-bold text-hut-dark mb-6">New Deal</h2>

        @if ($errors->any())
        <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg mb-6 text-sm">
            <p class="font-medium mb-2">Please fix the following errors:</p>
            <ul class="list-disc list-inside space-y-1">
                @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <form action="{{ route('manager.deals.store') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
            @csrf

            <div>
                <label class="block text-sm font-medium text-hut-dark mb-1">Deal Name *</label>
                <input type="text" name="name" required class="w-full border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:border-hut-green" value="{{ old('name') }}" placeholder="e.g., Buy 2 Get 1 Free">
            </div>

            <div>
                <label class="block text-sm font-medium text-hut-dark mb-1">Price (Rs.) *</label>
                <input type="number" name="price" step="0.01" min="0" required class="w-full border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:border-hut-green" value="{{ old('price') }}" placeholder="0">
            </div>

            <div>
                <label class="block text-sm font-medium text-hut-dark mb-1">Description</label>
                <textarea name="description" rows="3" class="w-full border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:border-hut-green" placeholder="What's included in this deal?">{{ old('description') }}</textarea>
            </div>

            <div class="flex items-center gap-2">
                <input type="checkbox" name="active" id="active" value="1" checked class="rounded">
                <label for="active" class="text-sm text-hut-dark">Active (show on menu)</label>
            </div>

            <div>
                <label class="block text-sm font-medium text-hut-dark mb-1">Image (optional)</label>
                <input type="file" name="image" accept="image/*" class="w-full">
            </div>

            <div class="flex gap-3 pt-4">
                <button type="submit" class="bg-hut-green text-white px-6 py-2 rounded-lg font-medium hover:bg-hut-green/90">Create Deal</button>
                <a href="{{ route('manager.deals.index') }}" class="border border-gray-200 text-hut-dark px-6 py-2 rounded-lg font-medium hover:bg-gray-50">Cancel</a>
            </div>
        </form>
    </div>
</div>

@endsection
