@extends('layouts.admin')
@section('title', 'Add Category')

@section('content')

    <div class="max-w-2xl">
        <x-back-link href="{{ route('manager.categories.index') }}" label="Back to Categories" />

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <h2 class="text-lg font-display font-bold text-hut-dark mb-6">New Category</h2>

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

            <form action="{{ route('manager.categories.store') }}" method="POST" class="space-y-4">
                @csrf

                <div>
                    <label class="block text-sm font-medium text-hut-dark mb-1">Category Name *</label>
                    <input type="text" name="name" required
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:border-hut-green"
                        value="{{ old('name') }}" placeholder="e.g., Pizza, Burgers, Desserts">
                </div>

                <div>
                    <label class="block text-sm font-medium text-hut-dark mb-1">Description</label>
                    <textarea name="description" rows="3"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:border-hut-green"
                        placeholder="Brief description of this category">{{ old('description') }}</textarea>
                </div>

                <div class="grid md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-hut-dark mb-1">Icon</label>
                        <input type="text" name="icon"
                            class="w-full border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:border-hut-green"
                            value="{{ old('icon') }}" placeholder="e.g. pizza">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-hut-dark mb-1">Sort Order</label>
                        <input type="number" name="sort_order" min="0"
                            class="w-full border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:border-hut-green"
                            value="{{ old('sort_order', 0) }}">
                    </div>
                    <div class="flex items-center gap-2 pt-7">
                        <input type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }} class="rounded">
                        <label for="is_active" class="text-sm text-hut-dark">Active</label>
                    </div>
                </div>

                <div class="flex items-center gap-2 pt-3">
                    <input type="checkbox" name="pos_show_line_edit" id="pos_show_line_edit" value="1" {{ old('pos_show_line_edit') ? 'checked' : '' }} class="rounded">
                    <label for="pos_show_line_edit" class="text-sm text-hut-dark">Show weight/line-edit modal for items in
                        this category</label>
                </div>
                <p class="text-xs text-gray-500">New items added to this category will use this POS setting.</p>

                <div class="flex gap-3 pt-4">
                    <button type="submit" aria-label="Save category" title="Save category"
                        class="group relative inline-flex h-10 w-10 items-center justify-center rounded-lg bg-hut-green text-white shadow-sm transition hover:-translate-y-0.5 hover:bg-hut-green/90">
                        <i class="fas fa-floppy-disk text-sm" aria-hidden="true"></i>
                        <span
                            class="pointer-events-none absolute bottom-full left-0 mb-2 whitespace-nowrap rounded-md bg-hut-dark px-2 py-1 text-[11px] font-medium text-white opacity-0 shadow-lg transition group-hover:opacity-100">Save
                            category</span>
                    </button>
                    <a href="{{ route('manager.categories.index') }}" aria-label="Cancel" title="Cancel"
                        class="group relative inline-flex h-10 w-10 items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-600 shadow-sm transition hover:-translate-y-0.5 hover:bg-slate-50">
                        <i class="fas fa-xmark text-sm" aria-hidden="true"></i>
                        <span
                            class="pointer-events-none absolute bottom-full left-0 mb-2 whitespace-nowrap rounded-md bg-hut-dark px-2 py-1 text-[11px] font-medium text-white opacity-0 shadow-lg transition group-hover:opacity-100">Cancel</span>
                    </a>
                </div>
            </form>
        </div>
    </div>

@endsection