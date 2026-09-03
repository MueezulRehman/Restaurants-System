@extends('layouts.admin')

@section('title', 'Create Subscription Plan')

@section('content')
<div class="max-w-2xl rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
    <div class="mb-6">
        <h2 class="text-2xl font-semibold text-hut-dark">Create Subscription Plan</h2>
        <p class="text-sm text-gray-500">Add a new billing tier for restaurants (e.g. Basic, Standard, Premium).</p>
    </div>

    @if($errors->any())
        <div class="mb-4 rounded-lg border border-red-200 bg-red-50 p-3 text-sm text-red-700">
            <ul class="list-disc pl-5">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('admin.subscription-plans.store') }}" method="POST" class="space-y-4">
        @csrf
        <div class="grid gap-4 md:grid-cols-2">
            <div>
                <label class="mb-2 block text-sm font-medium text-gray-700">Name</label>
                <input type="text" name="name" value="{{ old('name') }}" required class="w-full rounded-lg border border-gray-300 px-3 py-2" placeholder="e.g. Basic, Standard, Premium" />
            </div>
            <div>
                <label class="mb-2 block text-sm font-medium text-gray-700">Slug</label>
                <input type="text" name="slug" value="{{ old('slug') }}" required class="w-full rounded-lg border border-gray-300 px-3 py-2" placeholder="e.g. basic, standard, premium" />
            </div>
        </div>

        <div>
            <label class="mb-2 block text-sm font-medium text-gray-700">Description</label>
            <textarea name="description" rows="2" class="w-full rounded-lg border border-gray-300 px-3 py-2">{{ old('description') }}</textarea>
        </div>

        <div class="grid gap-4 md:grid-cols-2">
            <div>
                <label class="mb-2 block text-sm font-medium text-gray-700">Price Monthly</label>
                <input type="number" step="0.01" min="0" name="price_monthly" value="{{ old('price_monthly') }}" required class="w-full rounded-lg border border-gray-300 px-3 py-2" />
            </div>
            <div>
                <label class="mb-2 block text-sm font-medium text-gray-700">Price Yearly</label>
                <input type="number" step="0.01" min="0" name="price_yearly" value="{{ old('price_yearly') }}" class="w-full rounded-lg border border-gray-300 px-3 py-2" />
            </div>
            <div>
                <label class="mb-2 block text-sm font-medium text-gray-700">Trial Days</label>
                <input type="number" min="0" max="365" name="trial_days" value="{{ old('trial_days', 14) }}" required class="w-full rounded-lg border border-gray-300 px-3 py-2" />
            </div>
            <div>
                <label class="mb-2 block text-sm font-medium text-gray-700">Max Staff</label>
                <input type="number" min="1" name="max_staff" value="{{ old('max_staff', 5) }}" required class="w-full rounded-lg border border-gray-300 px-3 py-2" />
            </div>
            <div>
                <label class="mb-2 block text-sm font-medium text-gray-700">Max Menu Items</label>
                <input type="number" min="1" name="max_menu_items" value="{{ old('max_menu_items', 100) }}" required class="w-full rounded-lg border border-gray-300 px-3 py-2" />
            </div>
            <div>
                <label class="mb-2 block text-sm font-medium text-gray-700">Max Modules</label>
                <input type="number" min="1" name="max_modules" value="{{ old('max_modules') }}" class="w-full rounded-lg border border-gray-300 px-3 py-2" placeholder="Leave blank for unlimited" />
                <p class="text-xs text-gray-500 mt-1">How many modules a business on this plan can have enabled. Leave blank for unlimited (e.g. a Premium tier).</p>
            </div>
        </div>

        @if($features->isNotEmpty())
            <div>
                <label class="mb-2 block text-sm font-medium text-gray-700">Features</label>
                <div class="grid gap-2 sm:grid-cols-2">
                    @foreach($features as $feature)
                        <label class="inline-flex items-center gap-2 rounded-lg border border-gray-200 bg-white px-3 py-2">
                            <input type="checkbox" name="features[]" value="{{ $feature->id }}" {{ in_array($feature->id, old('features', [])) ? 'checked' : '' }} class="form-checkbox h-4 w-4 text-hut-dark" />
                            <span class="text-sm text-gray-700">{{ $feature->name }}</span>
                        </label>
                    @endforeach
                </div>
            </div>
        @endif

        <div class="flex items-center gap-3">
            <button type="submit" class="rounded-lg bg-hut-dark px-4 py-2 font-semibold text-white hover:bg-gray-800">Create Plan</button>
            <a href="{{ route('admin.subscription-plans.index') }}" class="text-sm text-gray-500 hover:text-hut-dark">← Back</a>
        </div>
    </form>
</div>
@endsection
