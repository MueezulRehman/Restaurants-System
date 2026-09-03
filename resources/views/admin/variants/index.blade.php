@extends('layouts.admin')
@section('title', 'Variants')

@section('content')

    <div class="flex justify-between items-center mb-6">
        <div>
            <h2 class="text-lg font-display font-bold text-hut-dark">Variants for {{ $item->name }}</h2>
            <p class="text-sm text-gray-500">Manage the variants available for this menu item.</p>
        </div>
        <a href="{{ route('manager.menu-items.variants.create', $item) }}"
            class="bg-hut-green text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-hut-green/90">+ Add Variant</a>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 text-gray-500 text-xs uppercase border-b">
                <tr>
                    <th class="px-4 py-3 text-left">SKU</th>
                    <th class="px-4 py-3 text-left">Name</th>
                    <th class="px-4 py-3 text-right">Price</th>
                    <th class="px-4 py-3 text-center">Qty</th>
                    <th class="px-4 py-3 text-center">Status</th>
                    <th class="px-4 py-3 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($variants as $variant)
                    <tr class="hover:bg-gray-50 transition-colors">
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
                                class="text-hut-green hover:underline text-xs font-medium">Edit</a>
                            <form action="{{ route('manager.menu-items.variants.destroy', [$item, $variant]) }}" method="POST"
                                class="inline" data-confirm="Delete this variant?">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 hover:underline text-xs font-medium">Delete</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-8 text-center text-gray-500">No variants found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-6">
        {{ $variants->links() }}
    </div>

    <div class="mt-6">
        <a href="{{ route('manager.menu-items.index') }}" class="text-hut-green text-sm hover:underline">← Back to Menu
            Items</a>
    </div>

@endsection