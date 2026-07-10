@extends('layouts.admin')

@section('title', 'Create Module')

@section('content')
<div class="max-w-2xl rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
    <div class="mb-6">
        <h2 class="text-2xl font-semibold text-hut-dark">Create Module</h2>
        <p class="text-sm text-gray-500">Add a new platform module that can be enabled for restaurants.</p>
    </div>

    <form action="{{ route('admin.modules.store') }}" method="POST" class="space-y-4">
        @csrf

        <div>
            <label class="mb-2 block text-sm font-medium text-gray-700">Name</label>
            <input type="text" name="name" value="{{ old('name') }}" required class="w-full rounded-lg border border-gray-300 px-3 py-2" />
        </div>

        <div>
            <label class="mb-2 block text-sm font-medium text-gray-700">Key</label>
            <input type="text" name="key" value="{{ old('key') }}" required class="w-full rounded-lg border border-gray-300 px-3 py-2" />
        </div>

        <div>
            <label class="mb-2 block text-sm font-medium text-gray-700">Description</label>
            <textarea name="description" rows="4" class="w-full rounded-lg border border-gray-300 px-3 py-2">{{ old('description') }}</textarea>
        </div>

        <div>
            <label class="mb-2 block text-sm font-medium text-gray-700">Icon</label>
            <input type="text" name="icon" value="{{ old('icon') }}" class="w-full rounded-lg border border-gray-300 px-3 py-2" />
        </div>

        <div class="flex items-center gap-3">
            <button type="submit" class="rounded-lg bg-hut-dark px-4 py-2 font-semibold text-white hover:bg-gray-800">Create Module</button>
            <a href="{{ route('admin.modules.index') }}" class="text-sm text-gray-500 hover:text-hut-dark">← Back</a>
        </div>
    </form>
</div>
@endsection
