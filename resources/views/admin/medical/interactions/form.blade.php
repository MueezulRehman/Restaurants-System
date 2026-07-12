@extends('layouts.admin')

@section('title', isset($interaction->id) ? 'Edit Interaction' : 'Add Interaction')

@section('content')
<div class="container mx-auto px-4 py-8 max-w-2xl">
    <h1 class="text-3xl font-bold mb-6">
        {{ isset($interaction->id) ? 'Edit Interaction' : 'Add New Interaction' }}
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

    <form action="{{ isset($interaction->id) ? route('manager.medicine-interactions.update', $interaction->id) : route('manager.medicine-interactions.store') }}" 
          method="POST" class="bg-white rounded-lg shadow p-6">
        @csrf
        @if(isset($interaction->id))
            @method('PUT')
        @endif

        <div class="mb-4">
            <label for="medicine_id_1" class="block text-sm font-medium text-gray-700 mb-2">
                Medicine 1 <span class="text-red-500">*</span>
            </label>
            <select name="medicine_id_1" id="medicine_id_1" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                <option value="">Select first medicine</option>
                @foreach($medicines as $medicine)
                    <option value="{{ $medicine->id }}" 
                        {{ (old('medicine_id_1') ?? $interaction->medicine_id_1 ?? '') == $medicine->id ? 'selected' : '' }}>
                        {{ $medicine->name }}
                    </option>
                @endforeach
            </select>
            @error('medicine_id_1')
                <span class="text-red-500 text-sm mt-1">{{ $message }}</span>
            @enderror
        </div>

        <div class="mb-4">
            <label for="medicine_id_2" class="block text-sm font-medium text-gray-700 mb-2">
                Medicine 2 <span class="text-red-500">*</span>
            </label>
            <select name="medicine_id_2" id="medicine_id_2" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                <option value="">Select second medicine</option>
                @foreach($medicines as $medicine)
                    <option value="{{ $medicine->id }}" 
                        {{ (old('medicine_id_2') ?? $interaction->medicine_id_2 ?? '') == $medicine->id ? 'selected' : '' }}>
                        {{ $medicine->name }}
                    </option>
                @endforeach
            </select>
            @error('medicine_id_2')
                <span class="text-red-500 text-sm mt-1">{{ $message }}</span>
            @enderror
        </div>

        <div class="mb-4">
            <label for="interaction_type" class="block text-sm font-medium text-gray-700 mb-2">
                Interaction Type <span class="text-red-500">*</span>
            </label>
            <select name="interaction_type" id="interaction_type" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                <option value="contraindicated" {{ (old('interaction_type') ?? $interaction->interaction_type ?? '') == 'contraindicated' ? 'selected' : '' }}>Contraindicated (Do not use together)</option>
                <option value="serious" {{ (old('interaction_type') ?? $interaction->interaction_type ?? '') == 'serious' ? 'selected' : '' }}>Serious</option>
                <option value="moderate" {{ (old('interaction_type') ?? $interaction->interaction_type ?? '') == 'moderate' ? 'selected' : '' }}>Moderate</option>
                <option value="mild" {{ (old('interaction_type') ?? $interaction->interaction_type ?? '') == 'mild' ? 'selected' : '' }}>Mild</option>
            </select>
            @error('interaction_type')
                <span class="text-red-500 text-sm mt-1">{{ $message }}</span>
            @enderror
        </div>

        <div class="mb-4">
            <label for="interaction_description" class="block text-sm font-medium text-gray-700 mb-2">
                Description <span class="text-red-500">*</span>
            </label>
            <textarea name="interaction_description" id="interaction_description" rows="3"
                      class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                      placeholder="Describe the interaction between these medicines..."
                      required>{{ old('interaction_description') ?? $interaction->interaction_description ?? '' }}</textarea>
            @error('interaction_description')
                <span class="text-red-500 text-sm mt-1">{{ $message }}</span>
            @enderror
        </div>

        <div class="mb-4">
            <label for="recommended_action" class="block text-sm font-medium text-gray-700 mb-2">
                Recommended Action
            </label>
            <textarea name="recommended_action" id="recommended_action" rows="2"
                      class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                      placeholder="What should be done if this interaction is detected?">{{ old('recommended_action') ?? $interaction->recommended_action ?? '' }}</textarea>
            @error('recommended_action')
                <span class="text-red-500 text-sm mt-1">{{ $message }}</span>
            @enderror
        </div>

        <div class="mb-6">
            <label for="source" class="block text-sm font-medium text-gray-700 mb-2">
                Source (FDA, WHO, etc.)
            </label>
            <input type="text" name="source" id="source" 
                   class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                   placeholder="e.g., FDA, WHO"
                   value="{{ old('source') ?? $interaction->source ?? '' }}">
            @error('source')
                <span class="text-red-500 text-sm mt-1">{{ $message }}</span>
            @enderror
        </div>

        <div class="flex gap-2">
            <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white px-6 py-2 rounded">
                {{ isset($interaction->id) ? 'Update' : 'Add' }} Interaction
            </button>
            <a href="{{ route('manager.medicine-interactions.index') }}" class="bg-gray-400 hover:bg-gray-500 text-white px-6 py-2 rounded">
                Cancel
            </a>
        </div>
    </form>
</div>
@endsection
