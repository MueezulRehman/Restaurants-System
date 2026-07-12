@extends('layouts.admin')
@section('title', 'Add Medicine')

@section('content')
<div class="max-w-4xl">
    <a href="{{ route('manager.medicines.index') }}" class="text-hut-green text-sm mb-4 inline-block hover:underline">← Back to Medicines</a>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <h2 class="text-lg font-display font-bold text-hut-dark mb-6">New Medicine</h2>

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

        <form action="{{ route('manager.medicines.store') }}" method="POST" class="space-y-6">
            @csrf

            <div class="grid gap-4 md:grid-cols-2">
                <div>
                    <label class="block text-sm font-medium text-hut-dark mb-1">Medicine Name *</label>
                    <input type="text" name="name" required value="{{ old('name') }}" class="w-full rounded-lg border border-gray-200 px-3 py-2 focus:outline-none focus:border-hut-green" placeholder="e.g. Paracetamol">
                </div>
                <div>
                    <label class="block text-sm font-medium text-hut-dark mb-1">Generic Name</label>
                    <input type="text" name="generic_name" value="{{ old('generic_name') }}" class="w-full rounded-lg border border-gray-200 px-3 py-2 focus:outline-none focus:border-hut-green" placeholder="e.g. Acetaminophen">
                </div>
                <div>
                    <label class="block text-sm font-medium text-hut-dark mb-1">Image URL</label>
                    <input type="text" name="image" value="{{ old('image') }}" class="w-full rounded-lg border border-gray-200 px-3 py-2 focus:outline-none focus:border-hut-green" placeholder="Optional image URL">
                </div>
                <div>
                    <label class="block text-sm font-medium text-hut-dark mb-1">Tax (%)</label>
                    <input type="number" name="tax" value="{{ old('tax') }}" step="0.01" min="0" class="w-full rounded-lg border border-gray-200 px-3 py-2 focus:outline-none focus:border-hut-green" placeholder="e.g. 5.00">
                </div>
                <div>
                    <label class="block text-sm font-medium text-hut-dark mb-1">Dosage Form</label>
                    <input type="text" name="dosage_form" value="{{ old('dosage_form') }}" class="w-full rounded-lg border border-gray-200 px-3 py-2 focus:outline-none focus:border-hut-green" placeholder="Tablet, Syrup, Capsule">
                </div>
                <div>
                    <label class="block text-sm font-medium text-hut-dark mb-1">Strength</label>
                    <input type="text" name="strength" value="{{ old('strength') }}" class="w-full rounded-lg border border-gray-200 px-3 py-2 focus:outline-none focus:border-hut-green" placeholder="500 mg">
                </div>
                <div>
                    <label class="block text-sm font-medium text-hut-dark mb-1">Category</label>
                    <select name="category_id" class="w-full rounded-lg border border-gray-200 px-3 py-2 focus:outline-none focus:border-hut-green">
                        <option value="">Select category</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-hut-dark mb-1">New Category</label>
                    <input type="text" name="new_category_name" value="{{ old('new_category_name') }}" class="w-full rounded-lg border border-gray-200 px-3 py-2 focus:outline-none focus:border-hut-green" placeholder="Create new category">
                </div>
                <div>
                    <label class="block text-sm font-medium text-hut-dark mb-1">SKU</label>
                    <input type="text" name="sku" value="{{ old('sku') }}" class="w-full rounded-lg border border-gray-200 px-3 py-2 focus:outline-none focus:border-hut-green" placeholder="Stock keeping unit">
                </div>
                <div>
                    <label class="block text-sm font-medium text-hut-dark mb-1">Barcode</label>
                    <input type="text" name="barcode" value="{{ old('barcode') }}" class="w-full rounded-lg border border-gray-200 px-3 py-2 focus:outline-none focus:border-hut-green" placeholder="EAN / UPC code">
                </div>
                <div>
                    <label class="block text-sm font-medium text-hut-dark mb-1">Minimum Stock</label>
                    <input type="number" name="min_stock" value="{{ old('min_stock', 0) }}" min="0" class="w-full rounded-lg border border-gray-200 px-3 py-2 focus:outline-none focus:border-hut-green" placeholder="Safety stock level">
                </div>
                <div>
                    <label class="block text-sm font-medium text-hut-dark mb-1">Tax (%)</label>
                    <input type="number" name="tax" value="{{ old('tax') }}" step="0.01" min="0" class="w-full rounded-lg border border-gray-200 px-3 py-2 focus:outline-none focus:border-hut-green" placeholder="e.g. 5.00">
                </div>
            </div>

            <div class="grid gap-4 md:grid-cols-2">
                <div class="flex items-center gap-3">
                    <input type="checkbox" id="requires_prescription" name="requires_prescription" value="1" {{ old('requires_prescription') ? 'checked' : '' }} class="h-4 w-4 rounded border-gray-300 text-hut-green focus:ring-hut-green">
                    <label for="requires_prescription" class="text-sm text-hut-dark">Requires Prescription</label>
                </div>
                <div class="flex items-center gap-3">
                    <input type="checkbox" id="track_stock" name="track_stock" value="1" {{ old('track_stock', true) ? 'checked' : '' }} class="h-4 w-4 rounded border-gray-300 text-hut-green focus:ring-hut-green">
                    <label for="track_stock" class="text-sm text-hut-dark">Track Stock</label>
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-hut-dark mb-1">Description</label>
                <textarea name="description" rows="4" class="w-full rounded-lg border border-gray-200 px-3 py-2 focus:outline-none focus:border-hut-green" placeholder="Details, instructions, use cases">{{ old('description') }}</textarea>
            </div>

            <div class="flex gap-3 pt-3">
                <button type="submit" class="bg-hut-green text-white px-6 py-2 rounded-lg font-medium hover:bg-hut-green/90">Create Medicine</button>
                <a href="{{ route('manager.medicines.index') }}" class="border border-gray-200 text-hut-dark px-6 py-2 rounded-lg font-medium hover:bg-gray-50">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
