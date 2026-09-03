@extends('layouts.admin')

@section('title', 'Add Item Sale')

@section('content')
<div class="max-w-3xl rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
    <h2 class="mb-2 text-2xl font-semibold text-hut-dark">Add Item Sale</h2>
    <p class="mb-6 text-sm text-gray-500">Select one or more items, then set a percent or fixed (Rs) discount. It will show live on the menu when active.</p>

    <form action="{{ route('manager.item-sales.store') }}" method="POST" class="space-y-5">
        @csrf

        <div>
            <label class="mb-2 block text-sm font-medium text-gray-700">Select items</label>
            <div class="max-h-64 overflow-y-auto rounded-lg border border-gray-200 p-3 grid gap-2 sm:grid-cols-2">
                @forelse($items as $item)
                    <label class="inline-flex items-center gap-2 text-sm">
                        <input type="checkbox" name="menu_item_ids[]" value="{{ $item->id }}" class="form-checkbox h-4 w-4 text-hut-dark"
                            {{ in_array($item->id, old('menu_item_ids', [])) ? 'checked' : '' }} />
                        <span>{{ $item->name }} <span class="text-gray-400">(Rs {{ number_format($item->price, 0) }})</span></span>
                    </label>
                @empty
                    <p class="text-sm text-gray-500">No menu items found. Add items first.</p>
                @endforelse
            </div>
            @error('menu_item_ids') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
        </div>

        <div class="grid gap-4 sm:grid-cols-2">
            <div>
                <label class="mb-2 block text-sm font-medium text-gray-700">Label (optional)</label>
                <input type="text" name="label" value="{{ old('label', 'Sale') }}" class="w-full rounded-lg border border-gray-300 px-3 py-2" placeholder="Weekend Sale" />
            </div>
            <div>
                <label class="mb-2 block text-sm font-medium text-gray-700">Discount type</label>
                <select name="type" class="w-full rounded-lg border border-gray-300 px-3 py-2">
                    <option value="percent" {{ old('type') === 'percent' ? 'selected' : '' }}>Percent (%)</option>
                    <option value="fixed" {{ old('type') === 'fixed' ? 'selected' : '' }}>Fixed amount (Rs)</option>
                </select>
            </div>
            <div>
                <label class="mb-2 block text-sm font-medium text-gray-700">Value</label>
                <input type="number" step="0.01" min="0.01" name="value" value="{{ old('value') }}" required class="w-full rounded-lg border border-gray-300 px-3 py-2" placeholder="10 or 50" />
                @error('value') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="mb-2 block text-sm font-medium text-gray-700">Active</label>
                <label class="inline-flex items-center gap-2 mt-2">
                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }} class="form-checkbox h-4 w-4" />
                    <span class="text-sm">Sale is active</span>
                </label>
            </div>
            <div>
                <label class="mb-2 block text-sm font-medium text-gray-700">Starts at</label>
                <input type="datetime-local" name="starts_at" value="{{ old('starts_at') }}" class="w-full rounded-lg border border-gray-300 px-3 py-2" />
            </div>
            <div>
                <label class="mb-2 block text-sm font-medium text-gray-700">Ends at</label>
                <input type="datetime-local" name="ends_at" value="{{ old('ends_at') }}" class="w-full rounded-lg border border-gray-300 px-3 py-2" />
            </div>
        </div>

        <div class="flex gap-3">
            <button type="submit" class="rounded-lg bg-hut-dark px-4 py-2 font-semibold text-white">Save sale</button>
            <a href="{{ route('manager.item-sales.index') }}" class="text-sm text-gray-500 self-center">Cancel</a>
        </div>
    </form>
</div>
@endsection
