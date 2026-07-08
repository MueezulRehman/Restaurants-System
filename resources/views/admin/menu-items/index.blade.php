@extends('layouts.admin')
@section('title', 'Menu Items')

@section('content')

<div class="flex justify-between items-center mb-6">
    <h2 class="text-lg font-display font-bold text-hut-dark">Menu Items</h2>
    <a href="{{ route('admin.menu-items.create') }}" class="bg-hut-green text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-hut-green/90">+ Add Item</a>
</div>

<div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-gray-50 text-gray-500 text-xs uppercase border-b">
            <tr>
                <th class="px-4 py-3 text-left">Name</th>
                <th class="px-4 py-3 text-left">Category</th>
                <th class="px-4 py-3 text-right">Price</th>
                <th class="px-4 py-3 text-center">Status</th>
                <th class="px-4 py-3 text-right">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @forelse($items as $item)
            <tr class="hover:bg-gray-50 transition-colors">
                <td class="px-4 py-3 font-medium text-hut-dark">{{ $item->name }}</td>
                <td class="px-4 py-3 text-gray-600">{{ $item->category->name ?? '-' }}</td>
                <td class="px-4 py-3 text-right font-medium">Rs. {{ number_format($item->price) }}</td>
                <td class="px-4 py-3 text-center">
                    <span class="inline-flex items-center gap-1 text-xs font-medium">
                        @if($item->available)
                            <span class="w-2 h-2 bg-hut-green rounded-full"></span> Available
                        @else
                            <span class="w-2 h-2 bg-gray-300 rounded-full"></span> Unavailable
                        @endif
                    </span>
                </td>
                <td class="px-4 py-3 text-right space-x-2 flex justify-end">
                    <a href="{{ route('admin.menu-items.edit', $item) }}" class="text-hut-green hover:underline text-xs font-medium">Edit</a>
                    <form action="{{ route('admin.menu-items.destroy', $item) }}" method="POST" class="inline" onsubmit="return confirm('Delete this item?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-red-600 hover:underline text-xs font-medium">Delete</button>
                    </form>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="5" class="px-4 py-8 text-center text-gray-500">No menu items found.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-6">
    {{ $items->links() }}
</div>

@endsection
