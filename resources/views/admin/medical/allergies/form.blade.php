@extends('layouts.admin')

@section('title', isset($allergy->id) ? 'Edit Allergy' : 'Add Allergy')

@section('content')
<div class="container mx-auto px-4 py-8 max-w-2xl">
    <h1 class="text-3xl font-bold mb-6">
        {{ isset($allergy->id) ? 'Edit Allergy' : 'Add New Allergy' }}
    </h1>

    @if($errors->any())
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
            <ul>
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ isset($allergy->id) ? route('manager.customer-allergies.update', $allergy->id) : route('manager.customer-allergies.store') }}" 
          method="POST" class="bg-white rounded-lg shadow p-6">
        @csrf
        @if(isset($allergy->id))
            @method('PUT')
        @endif

        <div class="mb-4">
            <label for="customer_id" class="block text-sm font-medium text-gray-700 mb-2">
                Customer <span class="text-red-500">*</span>
            </label>
            <select name="customer_id" id="customer_id" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                <option value="">Select a customer</option>
                @foreach($customers as $customer)
                    <option value="{{ $customer->id }}" 
                        {{ (old('customer_id') ?? $allergy->customer_id ?? '') == $customer->id ? 'selected' : '' }}>
                        {{ $customer->name }}
                    </option>
                @endforeach
            </select>
            @error('customer_id')
                <span class="text-red-500 text-sm mt-1">{{ $message }}</span>
            @enderror
        </div>

        <div class="mb-4">
            <label for="allergy_name" class="block text-sm font-medium text-gray-700 mb-2">
                Allergy Name <span class="text-red-500">*</span>
            </label>
            <input type="text" name="allergy_name" id="allergy_name" 
                   class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                   placeholder="e.g., Penicillin, Lactose"
                   value="{{ old('allergy_name') ?? $allergy->allergy_name ?? '' }}" required>
            @error('allergy_name')
                <span class="text-red-500 text-sm mt-1">{{ $message }}</span>
            @enderror
        </div>

        <div class="mb-4">
            <label for="description" class="block text-sm font-medium text-gray-700 mb-2">
                Description
            </label>
            <textarea name="description" id="description" rows="3"
                      class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                      placeholder="Describe the allergy and symptoms...">{{ old('description') ?? $allergy->description ?? '' }}</textarea>
            @error('description')
                <span class="text-red-500 text-sm mt-1">{{ $message }}</span>
            @enderror
        </div>

        <div class="mb-4">
            <label for="severity" class="block text-sm font-medium text-gray-700 mb-2">
                Severity <span class="text-red-500">*</span>
            </label>
            <select name="severity" id="severity" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                <option value="mild" {{ (old('severity') ?? $allergy->severity ?? '') == 'mild' ? 'selected' : '' }}>Mild</option>
                <option value="moderate" {{ (old('severity') ?? $allergy->severity ?? '') == 'moderate' ? 'selected' : '' }}>Moderate</option>
                <option value="severe" {{ (old('severity') ?? $allergy->severity ?? '') == 'severe' ? 'selected' : '' }}>Severe</option>
            </select>
            @error('severity')
                <span class="text-red-500 text-sm mt-1">{{ $message }}</span>
            @enderror
        </div>

        <div class="mb-4">
            <label for="trigger_medicines" class="block text-sm font-medium text-gray-700 mb-2">
                Trigger Medicines
            </label>
            <select name="trigger_medicines[]" id="trigger_medicines" multiple
                    class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                @foreach($medicines as $medicine)
                    <option value="{{ $medicine->id }}"
                        {{ in_array($medicine->id, old('trigger_medicines') ?? ($allergy->trigger_medicines ?? [])) ? 'selected' : '' }}>
                        {{ $medicine->name }}
                    </option>
                @endforeach
            </select>
            <p class="text-sm text-gray-500 mt-2">Hold Ctrl/Cmd to select multiple medicines</p>
            @error('trigger_medicines')
                <span class="text-red-500 text-sm mt-1">{{ $message }}</span>
            @enderror
        </div>

        <div class="mb-6">
            <label class="inline-flex items-center">
                <input type="checkbox" name="is_active" value="1" 
                       {{ (old('is_active') ?? $allergy->is_active ?? true) ? 'checked' : '' }}
                       class="rounded border-gray-300">
                <span class="ml-2">Active</span>
            </label>
        </div>

        <div class="flex gap-2">
            <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white px-6 py-2 rounded">
                {{ isset($allergy->id) ? 'Update' : 'Add' }} Allergy
            </button>
            <a href="{{ route('manager.customer-allergies.index') }}" class="bg-gray-400 hover:bg-gray-500 text-white px-6 py-2 rounded">
                Cancel
            </a>
        </div>
    </form>
</div>
@endsection
