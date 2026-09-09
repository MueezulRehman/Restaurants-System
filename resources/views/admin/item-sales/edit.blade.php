@extends('layouts.admin')

@section('title', 'Edit Item Sale')

@section('content')
    <div class="max-w-xl rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
        <h2 class="mb-6 text-2xl font-semibold text-hut-dark">Edit Item Sale</h2>
        <form action="{{ route('manager.item-sales.update', $promotion) }}" method="POST" class="space-y-4">
            @csrf
            @method('PUT')
            <div>
                <label class="mb-2 block text-sm font-medium">Item</label>
                <select name="menu_item_id" class="w-full rounded-lg border border-gray-300 px-3 py-2">
                    @foreach($items as $item)
                        <option value="{{ $item->id }}" {{ (int) $promotion->menu_item_id === (int) $item->id ? 'selected' : '' }}>
                            {{ $item->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="mb-2 block text-sm font-medium">Label</label>
                <input type="text" name="label" value="{{ old('label', $promotion->label) }}"
                    class="w-full rounded-lg border border-gray-300 px-3 py-2" />
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="mb-2 block text-sm font-medium">Type</label>
                    <select name="type" class="w-full rounded-lg border border-gray-300 px-3 py-2">
                        <option value="percent" {{ $promotion->type === 'percent' ? 'selected' : '' }}>Percent</option>
                        <option value="fixed" {{ $promotion->type === 'fixed' ? 'selected' : '' }}>Fixed Rs</option>
                    </select>
                </div>
                <div>
                    <label class="mb-2 block text-sm font-medium">Value</label>
                    <input type="number" step="0.01" name="value" value="{{ old('value', $promotion->value) }}"
                        class="w-full rounded-lg border border-gray-300 px-3 py-2" />
                </div>
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="mb-2 block text-sm font-medium">Starts</label>
                    <input type="datetime-local" name="starts_at"
                        value="{{ old('starts_at', optional($promotion->starts_at)->format('Y-m-d\\TH:i')) }}"
                        class="w-full rounded-lg border border-gray-300 px-3 py-2" />
                </div>
                <div>
                    <label class="mb-2 block text-sm font-medium">Ends</label>
                    <input type="datetime-local" name="ends_at"
                        value="{{ old('ends_at', optional($promotion->ends_at)->format('Y-m-d\\TH:i')) }}"
                        class="w-full rounded-lg border border-gray-300 px-3 py-2" />
                </div>
            </div>
            <label class="inline-flex items-center gap-2">
                <input type="checkbox" name="is_active" value="1" {{ $promotion->is_active ? 'checked' : '' }} />
                <span class="text-sm">Active</span>
            </label>
            <div>
                <button type="submit" aria-label="Save sale" title="Save sale"
                    class="group relative inline-flex h-10 w-10 items-center justify-center rounded-lg bg-hut-green text-white shadow-sm transition hover:-translate-y-0.5 hover:bg-hut-green/90">
                    <i class="fas fa-floppy-disk text-sm" aria-hidden="true"></i>
                    <span
                        class="pointer-events-none absolute bottom-full left-0 mb-2 whitespace-nowrap rounded-md bg-hut-dark px-2 py-1 text-[11px] font-medium text-white opacity-0 shadow-lg transition group-hover:opacity-100">Save
                        sale</span>
                </button>
                <a href="{{ route('manager.item-sales.index') }}" aria-label="Cancel" title="Cancel"
                    class="group relative ml-2 inline-flex h-10 w-10 items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-600 shadow-sm transition hover:-translate-y-0.5 hover:bg-slate-50">
                    <i class="fas fa-xmark text-sm" aria-hidden="true"></i>
                    <span
                        class="pointer-events-none absolute bottom-full left-0 mb-2 whitespace-nowrap rounded-md bg-hut-dark px-2 py-1 text-[11px] font-medium text-white opacity-0 shadow-lg transition group-hover:opacity-100">Cancel</span>
                </a>
            </div>
        </form>
    </div>
@endsection