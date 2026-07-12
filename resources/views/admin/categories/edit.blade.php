@extends('layouts.admin')
@section('title', 'Edit Category')

@section('content')

<div class="max-w-2xl">
    <a href="{{ route('manager.categories.index') }}" class="text-hut-green text-sm mb-4 inline-block hover:underline">← Back to Categories</a>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <h2 class="text-lg font-display font-bold text-hut-dark mb-6">Edit Category</h2>

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

        <form action="{{ route('manager.categories.update', $category) }}" method="POST" class="space-y-4">
            @csrf
            @method('PATCH')

            <div>
                <label class="block text-sm font-medium text-hut-dark mb-1">Category Name *</label>
                <input type="text" name="name" required class="w-full border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:border-hut-green" value="{{ old('name', $category->name) }}">
            </div>

            <div>
                <label class="block text-sm font-medium text-hut-dark mb-1">Description</label>
                <textarea name="description" rows="3" class="w-full border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:border-hut-green">{{ old('description', $category->description) }}</textarea>
            </div>

            <div class="grid md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-hut-dark mb-1">Icon</label>
                    <input type="text" name="icon" class="w-full border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:border-hut-green" value="{{ old('icon', $category->icon) }}" placeholder="e.g. pizza">
                </div>
                <div>
                    <label class="block text-sm font-medium text-hut-dark mb-1">Sort Order</label>
                    <input type="number" name="sort_order" min="0" class="w-full border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:border-hut-green" value="{{ old('sort_order', $category->sort_order) }}">
                </div>
                <div class="flex items-center gap-2 pt-7">
                    <input type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', $category->is_active) ? 'checked' : '' }} class="rounded">
                    <label for="is_active" class="text-sm text-hut-dark">Active</label>
                </div>
            </div>

            <div class="flex gap-3 pt-4">
                <button type="submit" class="bg-hut-green text-white px-6 py-2 rounded-lg font-medium hover:bg-hut-green/90">Save Changes</button>
                <a href="{{ route('manager.categories.index') }}" class="border border-gray-200 text-hut-dark px-6 py-2 rounded-lg font-medium hover:bg-gray-50">Cancel</a>
            </div>
        </form>
    </div>
</div>

@endsection
