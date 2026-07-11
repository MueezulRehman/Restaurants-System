@extends('layouts.admin')
@section('title', 'Add Variant')

@section('content')

<div class="max-w-2xl">
    <a href="{{ route('manager.menu-items.variants.index', $item) }}" class="text-hut-green text-sm mb-4 inline-block hover:underline">← Back to Variants</a>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <h2 class="text-lg font-display font-bold text-hut-dark mb-6">Add Variant for {{ $item->name }}</h2>

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

        <form action="{{ route('manager.menu-items.variants.store', $item) }}" method="POST" class="space-y-4">
            @csrf

            <div>
                <label class="block text-sm font-medium text-hut-dark mb-1">SKU *</label>
                <input type="text" name="sku" required class="w-full border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:border-hut-green" value="{{ old('sku') }}" placeholder="Unique code for this variant">
            </div>

            <div>
                <label class="block text-sm font-medium text-hut-dark mb-1">Variant Name *</label>
                <input type="text" name="variant_name" required class="w-full border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:border-hut-green" value="{{ old('variant_name') }}" placeholder="e.g. Large Pepperoni">
            </div>

            <div>
                <label class="block text-sm font-medium text-hut-dark mb-1">Price Override</label>
                <input type="number" step="0.01" min="0" name="price_override" class="w-full border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:border-hut-green" value="{{ old('price_override') }}" placeholder="Leave blank to use base price">
            </div>

            <div>
                <label class="block text-sm font-medium text-hut-dark mb-1">Quantity Available</label>
                <input type="number" min="0" name="quantity_available" class="w-full border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:border-hut-green" value="{{ old('quantity_available', 0) }}">
            </div>

            <div class="flex items-center gap-2">
                <input type="checkbox" name="is_available" id="is_available" value="1" {{ old('is_available') ? 'checked' : '' }} class="rounded">
                <label for="is_available" class="text-sm text-hut-dark">Available</label>
            </div>

            <div class="flex gap-3 pt-4">
                <button type="submit" class="bg-hut-green text-white px-6 py-2 rounded-lg font-medium hover:bg-hut-green/90">Create Variant</button>
                <a href="{{ route('manager.menu-items.variants.index', $item) }}" class="border border-gray-200 text-hut-dark px-6 py-2 rounded-lg font-medium hover:bg-gray-50">Cancel</a>
            </div>
        </form>
    </div>
</div>

@endsection
