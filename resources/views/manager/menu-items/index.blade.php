@extends('manager.layout.master')
@section('title', 'Menu Items')

@section('page-content')

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
            <div>
                <label class="text-xs text-gray-500">Season</label>
                <select name="season" class="rounded-lg border border-gray-200 px-3 py-2 text-sm">
                    <option value="">All seasons</option>
                    <option value="all-season" {{ request('season') === 'all-season' ? 'selected' : '' }}>All season</option>
                    <option value="summer" {{ request('season') === 'summer' ? 'selected' : '' }}>Summer</option>
                    <option value="winter" {{ request('season') === 'winter' ? 'selected' : '' }}>Winter</option>
                </select>
            </div>
            <button type="submit" aria-label="Search menu items" title="Search menu items"
                class="group relative inline-flex h-10 w-10 items-center justify-center rounded-lg bg-hut-dark text-white shadow-sm transition duration-200 hover:-translate-y-0.5 hover:bg-hut-blue">
                <i class="fas fa-magnifying-glass text-sm" aria-hidden="true"></i>
                <span
                    class="pointer-events-none absolute bottom-full left-0 mb-2 whitespace-nowrap rounded-md bg-hut-dark px-2 py-1 text-[11px] font-medium text-white opacity-0 shadow-lg transition duration-200 group-hover:opacity-100">Search</span>
            </button>
            @if(request()->hasAny(['q', 'category_id', 'season']))
                <a href="{{ route('manager.menu-items.index') }}" class="text-sm text-gray-500 hover:underline py-2">Clear</a>
            @endif
        </form>
    </div>


    <div class="flex justify-between items-center mb-6">
        <h2 class="text-lg font-display font-bold text-hut-dark">Menu Items</h2>
        <a href="{{ route('manager.menu-items.create') }}" aria-label="Add menu item" title="Add menu item"
            class="group relative inline-flex h-10 w-10 items-center justify-center rounded-lg bg-hut-green text-white shadow-sm transition duration-200 hover:-translate-y-0.5 hover:bg-hut-green/90">
            <x-icons.add class="h-5 w-5" />
            <span
                class="pointer-events-none absolute bottom-full right-0 mb-2 whitespace-nowrap rounded-md bg-hut-dark px-2 py-1 text-[11px] font-medium text-white opacity-0 shadow-lg transition duration-200 group-hover:opacity-100">Add Item</span>
        </a>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 text-gray-500 text-xs uppercase border-b">
                <tr>
                    <th class="px-4 py-3 text-left">Name</th>
                    <th class="px-4 py-3 text-left">Category</th>
                    <th class="px-4 py-3 text-left">Season</th>
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
                        <td class="px-4 py-3 text-gray-600">{{ ucfirst(str_replace('-', ' ', $item->season ?? 'all-season')) }}</td>
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
                        <td class="px-4 py-3 text-right">
                            <div class="flex justify-end flex-wrap gap-2">
                                <a href="{{ route('manager.menu-items.edit', $item) }}"
                                    class="group relative inline-flex h-9 w-9 items-center justify-center rounded-lg border border-emerald-100 bg-emerald-50 text-hut-green shadow-sm transition duration-200 hover:-translate-y-0.5 hover:bg-emerald-100"
                                    aria-label="Edit {{ $item->name }}" title="Edit menu item">
                                    <x-icons.edit class="h-4 w-4" />
                                    <span
                                        class="pointer-events-none absolute bottom-full left-1/2 mb-2 -translate-x-1/2 whitespace-nowrap rounded-md bg-hut-dark px-2 py-1 text-[11px] font-medium text-white opacity-0 shadow-lg transition duration-200 group-hover:opacity-100">Edit</span>
                                </a>
                                @if(auth()->user()->hasModuleAccess('variants'))
                                    <a href="{{ route('manager.menu-items.variants.index', $item) }}"
                                        class="group relative inline-flex h-9 w-9 items-center justify-center rounded-lg border border-blue-100 bg-blue-50 text-hut-blue shadow-sm transition duration-200 hover:-translate-y-0.5 hover:bg-blue-100"
                                        aria-label="Manage variants for {{ $item->name }}" title="Variants">
                                        <i class="fas fa-layer-group text-sm" aria-hidden="true"></i>
                                        <span
                                            class="pointer-events-none absolute bottom-full left-1/2 mb-2 -translate-x-1/2 whitespace-nowrap rounded-md bg-hut-dark px-2 py-1 text-[11px] font-medium text-white opacity-0 shadow-lg transition duration-200 group-hover:opacity-100">Variants</span>
                                    </a>
                                    <a href="{{ route('manager.menu-items.attributes.index', $item) }}"
                                        class="group relative inline-flex h-9 w-9 items-center justify-center rounded-lg border border-sky-100 bg-sky-50 text-sky-700 shadow-sm transition duration-200 hover:-translate-y-0.5 hover:bg-sky-100"
                                        aria-label="Manage attributes for {{ $item->name }}" title="Attributes">
                                        <i class="fas fa-sliders text-sm" aria-hidden="true"></i>
                                        <span
                                            class="pointer-events-none absolute bottom-full left-1/2 mb-2 -translate-x-1/2 whitespace-nowrap rounded-md bg-hut-dark px-2 py-1 text-[11px] font-medium text-white opacity-0 shadow-lg transition duration-200 group-hover:opacity-100">Attributes</span>
                                    </a>
                                @endif
                                <form action="{{ route('manager.menu-items.destroy', $item) }}" method="POST" class="inline"
                                    data-confirm="Delete this item?">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                        class="group relative inline-flex h-9 w-9 items-center justify-center rounded-lg border border-red-100 bg-red-50 text-red-600 shadow-sm transition duration-200 hover:-translate-y-0.5 hover:bg-red-100"
                                        aria-label="Delete {{ $item->name }}" title="Delete menu item">
                                        <x-icons.trash class="h-4 w-4" />
                                        <span
                                            class="pointer-events-none absolute bottom-full left-1/2 mb-2 -translate-x-1/2 whitespace-nowrap rounded-md bg-hut-dark px-2 py-1 text-[11px] font-medium text-white opacity-0 shadow-lg transition duration-200 group-hover:opacity-100">Delete</span>
                                    </button>
                                </form>
                            </div>
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