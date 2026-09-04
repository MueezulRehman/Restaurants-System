@extends('layouts.admin')
@section('title', 'Menu Items')

@section('content')

    <div class="mb-4 flex flex-col gap-2 md:flex-row md:items-end md:justify-between">
        <form method="GET" class="flex flex-wrap gap-2 items-end">
            <div>
                <label class="text-xs text-gray-500">Search</label>
                <input type="text" name="q" value="{{ request('q') }}" placeholder="Name, SKU, barcode"
                    class="rounded-lg border border-gray-200 px-3 py-2 text-sm">
            </div>
            <div>
                <label class="text-xs text-gray-500">Category</label>
                <select name="category_id" class="rounded-lg border border-gray-200 px-3 py-2 text-sm">
                    <option value="">All</option>
                    @foreach(($categories ?? []) as $cat)
                        <option value="{{ $cat->id }}" {{ (string) request('category_id') === (string) $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                    @endforeach
                </select>
            </div>
            <button class="rounded-lg bg-hut-dark text-white px-4 py-2 text-sm font-semibold">Search</button>
            @if(request()->hasAny(['q', 'category_id']))
                <a href="{{ route('manager.menu-items.index') }}" class="text-sm text-gray-500 hover:underline py-2">Clear</a>
            @endif
        </form>
    </div>


    <div class="flex justify-between items-center mb-6">
        <h2 class="text-lg font-display font-bold text-hut-dark">Menu Items</h2>
        <a href="{{ route('manager.menu-items.create') }}"
            class="bg-hut-green text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-hut-green/90">+ Add Item</a>
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
                        <td class="px-4 py-3 text-gray-600">{{ $item->category?->name ?? 'Uncategorized' }}</td>
                        <td class="px-4 py-3 text-right font-medium">
                            @if($item->has_sizes && $item->sizes->isNotEmpty())
                                <span>From Rs. {{ number_format($item->display_price) }}</span>
                                <span class="block text-[11px] text-gray-400">{{ $item->sizes->count() }} sizes</span>
                            @else
                                Rs. {{ number_format($item->price) }}
                            @endif
                        </td>
                        <td class="px-4 py-3 text-center">
                            <span class="inline-flex items-center gap-1 text-xs font-medium">
                                @if($item->is_available)
                                    <span class="w-2 h-2 bg-hut-green rounded-full"></span> Available
                                @else
                                    <span class="w-2 h-2 bg-gray-300 rounded-full"></span> Unavailable
                                @endif
                            </span>
                        </td>
                        <td class="px-4 py-3 text-right space-x-2 flex justify-end flex-wrap gap-2">
                            <a href="{{ route('manager.menu-items.edit', $item) }}"
                                class="text-hut-green hover:underline text-xs font-medium">Edit</a>
                            @if(auth()->user()->hasModuleAccess('variants'))
                                <a href="{{ route('manager.menu-items.variants.index', $item) }}"
                                    class="text-hut-blue hover:underline text-xs font-medium">Variants</a>
                                <a href="{{ route('manager.menu-items.attributes.index', $item) }}"
                                    class="text-hut-blue hover:underline text-xs font-medium">Attributes</a>
                            @endif
                            <form action="{{ route('manager.menu-items.destroy', $item) }}" method="POST" class="inline"
                                data-confirm="Delete this item?">
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