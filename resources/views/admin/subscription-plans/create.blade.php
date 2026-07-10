@extends('layouts.admin')

@section('title', 'Create Subscription Plan')

@section('content')
<div class="max-w-2xl rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
    <div class="mb-6">
        <h2 class="text-2xl font-semibold text-hut-dark">Create Subscription Plan</h2>
        <p class="text-sm text-gray-500">Add a new billing tier for restaurants.</p>
    </div>

    <form action="{{ route('admin.subscription-plans.store') }}" method="POST" class="space-y-4">
        @csrf
        <div>
            <label class="mb-2 block text-sm font-medium text-gray-700">Name</label>
            <input type="text" name="name" required class="w-full rounded-lg border border-gray-300 px-3 py-2" />
        </div>
        <div>
            <label class="mb-2 block text-sm font-medium text-gray-700">Slug</label>
            <input type="text" name="slug" required class="w-full rounded-lg border border-gray-300 px-3 py-2" />
        </div>
        <div>
            <label class="mb-2 block text-sm font-medium text-gray-700">Price Monthly</label>
            <input type="number" step="0.01" name="price_monthly" class="w-full rounded-lg border border-gray-300 px-3 py-2" />
        </div>
        <div>
            <label class="mb-2 block text-sm font-medium text-gray-700">Price Yearly</label>
            <input type="number" step="0.01" name="price_yearly" class="w-full rounded-lg border border-gray-300 px-3 py-2" />
        </div>
        <div class="flex items-center gap-3">
            <button type="submit" class="rounded-lg bg-hut-dark px-4 py-2 font-semibold text-white hover:bg-gray-800">Create Plan</button>
            <a href="{{ route('admin.subscription-plans.index') }}" class="text-sm text-gray-500 hover:text-hut-dark">← Back</a>
        </div>
    </form>
</div>
@endsection
