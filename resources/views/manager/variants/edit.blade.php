@extends('manager.layout.master')
@section('title', 'Edit Variant')

@section('page-content')

    <div class="max-w-2xl">
        <a href="{{ route('manager.menu-items.variants.index', $item) }}"
            class="mb-4 inline-flex h-10 w-10 items-center justify-center rounded-lg border border-slate-200 bg-white/75 text-hut-blue shadow-sm backdrop-blur transition hover:bg-white hover:text-hut-dark"
            aria-label="Back to variants" title="Back to variants">
            <i class="fas fa-arrow-left text-sm" aria-hidden="true"></i>
        </a>

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <h2 class="text-lg font-display font-bold text-hut-dark mb-6">Edit Variant for {{ $item->name }}</h2>

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

            <form action="{{ route('manager.menu-items.variants.update', [$item, $variant]) }}" method="POST"
                class="space-y-4">
                @csrf
                @method('PATCH')

                <div>
                    <label class="block text-sm font-medium text-hut-dark mb-1">SKU *</label>
                    <input type="text" name="sku" required
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:border-hut-green"
                        value="{{ old('sku', $variant->sku) }}">
                </div>

                <div>
                    <label class="block text-sm font-medium text-hut-dark mb-1">Variant Name *</label>
                    <input type="text" name="variant_name" required
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:border-hut-green"
                        value="{{ old('variant_name', $variant->variant_name) }}">
                </div>

                <div>
                    <label class="block text-sm font-medium text-hut-dark mb-1">Price Override</label>
                    <input type="number" step="0.01" min="0" name="price_override"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:border-hut-green"
                        value="{{ old('price_override', $variant->price_override) }}"
                        placeholder="Leave blank to use base price">
                </div>

                <div>
                    <label class="block text-sm font-medium text-hut-dark mb-1">Cost Price</label>
                    <input type="number" step="0.01" min="0" name="cost_price"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:border-hut-green"
                        value="{{ old('cost_price', $variant->cost_price) }}" placeholder="Optional cost price">
                </div>

                <div>
                    <label class="block text-sm font-medium text-hut-dark mb-1">Quantity Available</label>
                    <input type="number" min="0" name="quantity_available"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:border-hut-green"
                        value="{{ old('quantity_available', $variant->quantity_available) }}">
                </div>

                <div class="flex items-center gap-2">
                    <input type="checkbox" name="is_available" id="is_available" value="1" {{ old('is_available', $variant->is_available) ? 'checked' : '' }} class="rounded">
                    <label for="is_available" class="text-sm text-hut-dark">Available</label>
                </div>

                <div class="flex gap-3 pt-4">
                    <button type="submit" aria-label="Save variant" title="Save variant"
                        class="group relative inline-flex h-10 w-10 items-center justify-center rounded-lg bg-hut-green text-white shadow-sm transition duration-200 hover:-translate-y-0.5 hover:bg-hut-green/90">
                        <i class="fas fa-floppy-disk text-sm" aria-hidden="true"></i>
                        <span
                            class="pointer-events-none absolute bottom-full left-0 mb-2 whitespace-nowrap rounded-md bg-hut-dark px-2 py-1 text-[11px] font-medium text-white opacity-0 shadow-lg transition duration-200 group-hover:opacity-100">Save
                            variant</span>
                    </button>
                    <a href="{{ route('manager.menu-items.variants.index', $item) }}" aria-label="Cancel" title="Cancel"
                        class="group relative inline-flex h-10 w-10 items-center justify-center rounded-lg border border-slate-200 bg-white/80 text-slate-600 shadow-sm transition duration-200 hover:-translate-y-0.5 hover:bg-slate-50">
                        <i class="fas fa-xmark text-sm" aria-hidden="true"></i>
                        <span
                            class="pointer-events-none absolute bottom-full left-0 mb-2 whitespace-nowrap rounded-md bg-hut-dark px-2 py-1 text-[11px] font-medium text-white opacity-0 shadow-lg transition duration-200 group-hover:opacity-100">Cancel</span>
                    </a>
                </div>
            </form>
        </div>
    </div>

@endsection