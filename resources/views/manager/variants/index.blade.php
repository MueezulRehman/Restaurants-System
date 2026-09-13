@extends('manager.layout.master')
@section('title', 'Variants')

@section('page-content')

<a href="{{ route('manager.menu-items.index') }}"
    class="mb-4 inline-flex h-10 w-10 items-center justify-center rounded-lg border border-slate-200 bg-white/75 text-hut-blue shadow-sm backdrop-blur transition hover:bg-white hover:text-hut-dark"
    aria-label="Back to menu items" title="Back to menu items">
    <i class="fas fa-arrow-left text-sm" aria-hidden="true"></i>
</a>

<div class="flex justify-between items-center mb-6">
    <div>
        <h2 class="text-lg font-display font-bold text-hut-dark">Variants for {{ $item->name }}</h2>
        <p class="text-sm text-gray-500">Manage the variants available for this menu item.</p>
    </div>
    <a href="{{ route('manager.menu-items.variants.create', $item) }}"
        class="group relative inline-flex h-10 w-10 items-center justify-center rounded-lg bg-hut-green text-white shadow-sm transition duration-200 hover:-translate-y-0.5 hover:bg-hut-green/90"
        aria-label="Add variant" title="Add variant">
        <x-icons.add class="h-5 w-5" />
        <span
            class="pointer-events-none absolute bottom-full right-0 mb-2 whitespace-nowrap rounded-md bg-hut-dark px-2 py-1 text-[11px] font-medium text-white opacity-0 shadow-lg transition duration-200 group-hover:opacity-100">Add
            variant</span>
    </a>
</div>

<div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-gray-50 text-gray-500 text-xs uppercase border-b">
            <tr>
                <th class="px-4 py-3 text-left">Type</th>
                <th class="px-4 py-3 text-left">SKU</th>
                <th class="px-4 py-3 text-left">Name</th>
                <th class="px-4 py-3 text-right">Price</th>
                <th class="px-4 py-3 text-center">Qty</th>
                <th class="px-4 py-3 text-center">Status</th>
                <th class="px-4 py-3 text-right">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @php($hasRows = $sizes->isNotEmpty() || $variants->isNotEmpty())
            @if($hasRows)
                @foreach($sizes as $size)
                    <tr class="hover:bg-blue-50/40 transition-colors">
                        <td class="px-4 py-3 text-xs font-semibold uppercase tracking-wide text-blue-700">Size</td>
                        <td class="px-4 py-3 text-gray-400">-</td>
                        <td class="px-4 py-3 font-medium text-hut-dark">{{ $size->size_label }}</td>
                        <td class="px-4 py-3 text-right font-medium">Rs. {{ number_format($size->price) }}</td>
                        <td class="px-4 py-3 text-center text-gray-400">-</td>
                        <td class="px-4 py-3 text-center">
                            <span class="inline-flex items-center gap-1 text-xs font-medium">
                                <span class="w-2 h-2 bg-hut-green rounded-full"></span> Available
                            </span>
                        </td>
                        <td class="px-4 py-3 text-right">
                            <div class="flex justify-end gap-2">
                                <form action="{{ route('manager.menu-items.variants.sizes.update', [$item, $size]) }}"
                                    method="POST" class="flex items-center gap-2">
                                    @csrf
                                    @method('PATCH')
                                    <input type="text" name="size_label" value="{{ $size->size_label }}" required
                                        class="w-24 rounded border border-gray-200 px-2 py-1 text-xs">
                                    <input type="number" name="price" value="{{ $size->price }}" min="0" step="0.01" required
                                        class="w-24 rounded border border-gray-200 px-2 py-1 text-xs">
                                    <button type="submit"
                                        class="group relative inline-flex h-8 w-8 items-center justify-center rounded-lg border border-emerald-100 bg-emerald-50 text-hut-green transition duration-200 hover:-translate-y-0.5 hover:bg-emerald-100"
                                        aria-label="Save size option" title="Save size option">
                                        <i class="fas fa-check text-xs" aria-hidden="true"></i>
                                        <span
                                            class="pointer-events-none absolute bottom-full right-0 mb-2 whitespace-nowrap rounded-md bg-hut-dark px-2 py-1 text-[11px] font-medium text-white opacity-0 shadow-lg transition duration-200 group-hover:opacity-100">Save</span>
                                    </button>
                                </form>
                                <form action="{{ route('manager.menu-items.variants.sizes.destroy', [$item, $size]) }}"
                                    method="POST" class="inline" data-confirm="Delete this size option?">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                        class="group relative inline-flex h-8 w-8 items-center justify-center rounded-lg border border-red-100 bg-red-50 text-red-600 transition duration-200 hover:-translate-y-0.5 hover:bg-red-100"
                                        aria-label="Delete size option" title="Delete size option">
                                        <x-icons.trash class="h-4 w-4" />
                                        <span
                                            class="pointer-events-none absolute bottom-full right-0 mb-2 whitespace-nowrap rounded-md bg-hut-dark px-2 py-1 text-[11px] font-medium text-white opacity-0 shadow-lg transition duration-200 group-hover:opacity-100">Delete</span>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @endforeach
                @foreach($variants as $variant)
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-4 py-3 text-xs font-semibold uppercase tracking-wide text-hut-blue">Variant</td>
                        <td class="px-4 py-3 font-medium text-hut-dark">{{ $variant->sku }}</td>
                        <td class="px-4 py-3 text-gray-600">{{ $variant->variant_name }}</td>
                        <td class="px-4 py-3 text-right font-medium">
                            {{ $variant->price_override !== null ? 'Rs. ' . number_format($variant->price_override) : 'Base price' }}
                        </td>
                        <td class="px-4 py-3 text-center text-gray-600">{{ $variant->quantity_available }}</td>
                        <td class="px-4 py-3 text-center">
                            <span class="inline-flex items-center gap-1 text-xs font-medium">
                                @if($variant->is_available)
                                    <span class="w-2 h-2 bg-hut-green rounded-full"></span> Available
                                @else
                                    <span class="w-2 h-2 bg-gray-300 rounded-full"></span> Hidden
                                @endif
                            </span>
                        </td>
                        <td class="px-4 py-3 text-right space-x-2 flex justify-end">
                            <a href="{{ route('manager.menu-items.variants.edit', [$item, $variant]) }}"
                                class="group relative inline-flex h-8 w-8 items-center justify-center rounded-lg border border-emerald-100 bg-emerald-50 text-hut-green transition duration-200 hover:-translate-y-0.5 hover:bg-emerald-100"
                                aria-label="Edit variant" title="Edit variant">
                                <x-icons.edit class="h-4 w-4" />
                                <span
                                    class="pointer-events-none absolute bottom-full right-0 mb-2 whitespace-nowrap rounded-md bg-hut-dark px-2 py-1 text-[11px] font-medium text-white opacity-0 shadow-lg transition duration-200 group-hover:opacity-100">Edit</span>
                            </a>
                            <form action="{{ route('manager.menu-items.variants.destroy', [$item, $variant]) }}" method="POST"
                                class="inline" data-confirm="Delete this variant?">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                    class="group relative inline-flex h-8 w-8 items-center justify-center rounded-lg border border-red-100 bg-red-50 text-red-600 transition duration-200 hover:-translate-y-0.5 hover:bg-red-100"
                                    aria-label="Delete variant" title="Delete variant">
                                    <x-icons.trash class="h-4 w-4" />
                                    <span
                                        class="pointer-events-none absolute bottom-full right-0 mb-2 whitespace-nowrap rounded-md bg-hut-dark px-2 py-1 text-[11px] font-medium text-white opacity-0 shadow-lg transition duration-200 group-hover:opacity-100">Delete</span>
                                </button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            @else
                <tr>
                    <td colspan="7" class="px-4 py-8 text-center text-gray-500">No sizes or variants found.</td>
                </tr>
            @endif
        </tbody>
    </table>
</div>

<div class="mt-6">
    {{ $variants->links() }}
</div>

@endsection