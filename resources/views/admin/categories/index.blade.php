@extends('layouts.admin')
@section('title', 'Categories')

@section('content')

<div class="flex justify-between items-center mb-6">
    <h2 class="text-lg font-display font-bold text-hut-dark">Categories</h2>
    <a href="{{ route('admin.categories.create') }}" class="bg-hut-green text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-hut-green/90">+ Add Category</a>
</div>

<div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-gray-50 text-gray-500 text-xs uppercase border-b">
            <tr>
                <th class="px-4 py-3 text-left">Name</th>
                <th class="px-4 py-3 text-left">Description</th>
                <th class="px-4 py-3 text-left">Items</th>
                <th class="px-4 py-3 text-right">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @forelse($categories as $category)
            <tr class="hover:bg-gray-50 transition-colors">
                <td class="px-4 py-3 font-medium text-hut-dark">{{ $category->name }}</td>
                <td class="px-4 py-3 text-gray-600">{{ Str::limit($category->description, 50) }}</td>
                <td class="px-4 py-3">
                    <span class="bg-hut-green/10 text-hut-green text-xs font-medium px-2 py-1 rounded">
                        {{ $category->items_count ?? 0 }}
                    </span>
                </td>
                <td class="px-4 py-3 text-right space-x-2 flex justify-end">
                    <a href="{{ route('admin.categories.edit', $category) }}" class="text-hut-green hover:underline text-xs font-medium">Edit</a>
                    <form action="{{ route('admin.categories.destroy', $category) }}" method="POST" class="inline" onsubmit="return confirm('Delete this category?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-red-600 hover:underline text-xs font-medium">Delete</button>
                    </form>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="4" class="px-4 py-8 text-center text-gray-500">No categories found.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

@endsection
