@extends('manager.layout.master')
@section('title', 'Categories')

@section('page-content')

    <div class="flex justify-between items-center mb-6">
        <h2 class="text-lg font-display font-bold text-hut-dark">Categories</h2>
        <a href="{{ route('manager.categories.create') }}" aria-label="Add category" title="Add category"
            class="inline-flex h-10 w-10 items-center justify-center rounded-lg bg-hut-green text-white shadow-sm transition hover:-translate-y-0.5 hover:bg-hut-green/90">
            <x-icons.add class="h-5 w-5" />
        </a>
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
                            <a href="{{ route('manager.menu-items.create', ['category_id' => $category->id]) }}"
                                class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-blue-100 bg-blue-50 text-hut-blue transition hover:-translate-y-0.5 hover:bg-blue-100"
                                aria-label="Add item to category" title="Add item">
                                <x-icons.add class="h-4 w-4" />
                            </a>
                            <a href="{{ route('manager.categories.edit', $category) }}"
                                class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-emerald-100 bg-emerald-50 text-hut-green transition hover:-translate-y-0.5 hover:bg-emerald-100"
                                aria-label="Edit category" title="Edit">
                                <x-icons.edit class="h-4 w-4" />
                            </a>
                            <form action="{{ route('manager.categories.destroy', $category) }}" method="POST" class="inline"
                                data-confirm="Delete this category?">
                                @csrf
                                @method('DELETE')
                                <button type="submit" aria-label="Delete category" title="Delete"
                                    class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-red-100 bg-red-50 text-red-600 transition hover:-translate-y-0.5 hover:bg-red-100">
                                    <x-icons.trash class="h-4 w-4" />
                                </button>
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