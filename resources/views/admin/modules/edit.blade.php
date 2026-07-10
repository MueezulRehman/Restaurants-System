@extends('layouts.admin')

@section('title', 'Edit Module')

@section('content')
<div class="max-w-2xl rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
    <div class="mb-6">
        <h2 class="text-2xl font-semibold text-hut-dark">Edit Module</h2>
        <p class="text-sm text-gray-500">Update the available module configuration.</p>
    </div>

    <form action="{{ route('admin.modules.update', $module) }}" method="POST" class="space-y-4">
        @csrf
        @method('PATCH')

        <div>
            <label class="mb-2 block text-sm font-medium text-gray-700">Name</label>
            <input type="text" name="name" value="{{ old('name', $module->name) }}" required class="w-full rounded-lg border border-gray-300 px-3 py-2" />
        </div>

        <div>
            <label class="mb-2 block text-sm font-medium text-gray-700">Key</label>
            <input type="text" name="key" value="{{ old('key', $module->key) }}" required class="w-full rounded-lg border border-gray-300 px-3 py-2" />
        </div>

        <div>
            <label class="mb-2 block text-sm font-medium text-gray-700">Description</label>
            <textarea name="description" rows="4" class="w-full rounded-lg border border-gray-300 px-3 py-2">{{ old('description', $module->description) }}</textarea>
        </div>

        <div>
            <label class="mb-2 block text-sm font-medium text-gray-700">Icon</label>
            <input type="text" name="icon" value="{{ old('icon', $module->icon) }}" class="w-full rounded-lg border border-gray-300 px-3 py-2" />
        </div>

        <div class="flex items-center gap-2">
            <input type="checkbox" name="is_active" value="1" {{ old('is_active', $module->is_active) ? 'checked' : '' }} class="h-4 w-4 rounded border-gray-300 text-hut-dark" />
            <label class="text-sm text-gray-700">Active</label>
        </div>

        <div class="flex items-center gap-3">
            <button type="submit" class="rounded-lg bg-hut-dark px-4 py-2 font-semibold text-white hover:bg-gray-800">Update Module</button>
            <a href="{{ route('admin.modules.index') }}" class="text-sm text-gray-500 hover:text-hut-dark">← Back</a>
        </div>
    </form>
</div>
@endsection
