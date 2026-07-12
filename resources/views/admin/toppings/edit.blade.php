@extends('layouts.admin')

@section('title', 'Edit Topping')

@section('content')
<div class="mx-auto max-w-2xl rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
    <div class="mb-6">
        <a href="{{ route('manager.toppings.index') }}" class="text-sm text-gray-500 hover:text-hut-dark">← Back</a>
        <h2 class="mt-2 text-xl font-semibold text-hut-dark">Edit Topping</h2>
    </div>

    <form action="{{ route('manager.toppings.update', $topping) }}" method="POST" class="space-y-4">
        @csrf
        @method('PATCH')

        <div>
            <label class="mb-1 block text-sm font-medium text-gray-700">Name</label>
            <input type="text" name="name" value="{{ old('name', $topping->name) }}" required class="w-full rounded-lg border border-gray-300 px-3 py-2" />
        </div>

        <div class="grid gap-4 md:grid-cols-2">
            <div>
                <label class="mb-1 block text-sm font-medium text-gray-700">Price</label>
                <input type="number" step="0.01" min="0" name="price" value="{{ old('price', $topping->price) }}" required class="w-full rounded-lg border border-gray-300 px-3 py-2" />
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium text-gray-700">Type</label>
                <input type="text" name="type" value="{{ old('type', $topping->type) }}" placeholder="e.g. cheese" class="w-full rounded-lg border border-gray-300 px-3 py-2" />
            </div>
        </div>

        <div class="flex justify-end gap-3">
            <a href="{{ route('manager.toppings.index') }}" class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">Cancel</a>
            <button type="submit" class="rounded-lg bg-hut-dark px-4 py-2 text-sm font-semibold text-white hover:bg-gray-800">Update Topping</button>
        </div>
    </form>
</div>
@endsection
