@extends('layouts.admin')
@section('title', 'Edit Menu Item')

@section('content')

<div class="max-w-2xl">
    <a href="{{ route('manager.menu-items.index') }}" class="text-hut-green text-sm mb-4 inline-block hover:underline">← Back to Menu Items</a>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <h2 class="text-lg font-display font-bold text-hut-dark mb-6">Edit Menu Item</h2>

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

        <form action="{{ route('manager.menu-items.update', $item) }}" method="POST" enctype="multipart/form-data" class="space-y-4">
            @csrf
            @method('PATCH')

            <div>
                <label class="block text-sm font-medium text-hut-dark mb-1">Item Name *</label>
                <input type="text" name="name" required class="w-full border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:border-hut-green" value="{{ old('name', $item->name) }}">
            </div>

            <div>
                <label class="block text-sm font-medium text-hut-dark mb-1">Category *</label>
                <select name="category_id" required class="w-full border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:border-hut-green">
                    @foreach($categories as $category)
                    <option value="{{ $category->id }}" {{ old('category_id', $item->category_id) == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-hut-dark mb-1">Barcode / SKU / Code</label>
                <input type="text" name="sku" class="w-full border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:border-hut-green" value="{{ old('sku', $item->sku) }}" placeholder="Used by POS barcode scan / search (optional)">
            </div>

            <div>
                <label class="block text-sm font-medium text-hut-dark mb-1">Price (Rs.) *</label>
                <input type="number" name="price" step="0.01" min="0" required class="w-full border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:border-hut-green" value="{{ old('price', $item->price) }}">
            </div>

            <div>
                <label class="block text-sm font-medium text-hut-dark mb-1">Description</label>
                <textarea name="description" rows="3" class="w-full border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:border-hut-green">{{ old('description', $item->description) }}</textarea>
            </div>

            <div>
                <label class="block text-sm font-medium text-hut-dark mb-1">Food Image</label>
                <input type="file" name="image" accept="image/*" class="w-full rounded-lg border border-gray-200 px-3 py-2" />
                @if($item->image)
                    <p class="text-xs text-gray-500 mt-2">Current image: {{ basename($item->image) }}</p>
                @endif
            </div>

            <div class="grid sm:grid-cols-3 gap-4">
                <label class="flex items-center gap-2 text-sm">
                    <input type="checkbox" name="available" id="available" value="1" {{ old('available', $item->is_available) ? 'checked' : '' }} class="rounded">
                    <span>Available for order</span>
                </label>
                <label class="flex items-center gap-2 text-sm">
                    <input type="checkbox" name="has_sizes" id="has_sizes" value="1" {{ old('has_sizes', $item->has_sizes) ? 'checked' : '' }} class="rounded">
                    <span>Has sizes</span>
                </label>
                <label class="flex items-center gap-2 text-sm">
                    <input type="checkbox" name="allows_toppings" id="allows_toppings" value="1" {{ old('allows_toppings', $item->allows_toppings) ? 'checked' : '' }} class="rounded">
                    <span>Allows toppings</span>
                </label>
            </div>

            <div>
                <label class="block text-sm font-medium text-hut-dark mb-1">Sort Order</label>
                <input type="number" name="sort_order" min="0" class="w-full border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:border-hut-green" value="{{ old('sort_order', $item->sort_order) }}">
            </div>

            <div class="border border-gray-100 rounded-lg p-3 space-y-3">
                <div class="flex items-center gap-2">
                    <input type="checkbox" name="track_stock" id="track_stock" value="1" {{ old('track_stock', $item->track_stock) ? 'checked' : '' }} class="rounded">
                    <label for="track_stock" class="text-sm text-hut-dark">Track stock quantity (for Shop / Medical POS)</label>
                </div>
                <div>
                    <label class="block text-sm font-medium text-hut-dark mb-1">Stock Quantity</label>
                    <input type="number" name="stock_quantity" min="0" class="w-full border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:border-hut-green" value="{{ old('stock_quantity', $item->stock_quantity) }}">
                </div>
                <div>
                    <label class="block text-sm font-medium text-hut-dark mb-1">Low Stock Alert Threshold</label>
                    <input type="number" name="low_stock_threshold" min="0" class="w-full border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:border-hut-green" value="{{ old('low_stock_threshold', $item->low_stock_threshold) }}">
                </div>
            </div>

            <div class="flex gap-3 pt-4">
                <button type="submit" class="bg-hut-green text-white px-6 py-2 rounded-lg font-medium hover:bg-hut-green/90">Save Changes</button>
                <a href="{{ route('manager.menu-items.index') }}" class="border border-gray-200 text-hut-dark px-6 py-2 rounded-lg font-medium hover:bg-gray-50">Cancel</a>
            </div>
        </form>
    </div>
</div>

@endsection
