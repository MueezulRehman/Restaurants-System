@extends('manager.layout.master')
@section('title', $allergy->exists ? 'Edit Patient Allergy' : 'Add Patient Allergy')
@section('page-content')
<div class="mx-auto max-w-2xl space-y-5">
    <div><h1 class="text-2xl font-semibold text-hut-dark">{{ $allergy->exists ? 'Edit Patient Allergy' : 'Add Patient Allergy' }}</h1><p class="text-sm text-gray-500">These records are used for clinical prescription safety checks.</p></div>
    <form method="POST" action="{{ $allergy->exists ? route('manager.patient-allergies.update', $allergy) : route('manager.patient-allergies.store') }}" class="space-y-4 rounded-xl border border-gray-200 bg-white p-6">
        @csrf @if($allergy->exists) @method('PUT') @endif
        <div><label class="block text-sm font-medium">Patient</label><select name="patient_id" required class="mt-1 w-full rounded-lg border p-2"><option value="">Select patient</option>@foreach($patients as $patient)<option value="{{ $patient->id }}" @selected(old('patient_id', $allergy->patient_id) == $patient->id)>{{ $patient->name }} ({{ $patient->patient_number }})</option>@endforeach</select></div>
        <div><label class="block text-sm font-medium">Allergy name</label><input name="allergy_name" required value="{{ old('allergy_name', $allergy->allergy_name) }}" class="mt-1 w-full rounded-lg border p-2"></div>
        <div><label class="block text-sm font-medium">Description</label><textarea name="description" rows="3" class="mt-1 w-full rounded-lg border p-2">{{ old('description', $allergy->description) }}</textarea></div>
        <div><label class="block text-sm font-medium">Severity</label><select name="severity" class="mt-1 rounded-lg border p-2">@foreach(['mild','moderate','severe'] as $severity)<option value="{{ $severity }}" @selected(old('severity', $allergy->severity ?: 'moderate') === $severity)>{{ ucfirst($severity) }}</option>@endforeach</select></div>
        <div><label class="block text-sm font-medium">Trigger medicines</label><select name="trigger_medicines[]" multiple class="mt-1 w-full rounded-lg border p-2">@foreach($medicines as $medicine)<option value="{{ $medicine->id }}" @selected(in_array($medicine->id, old('trigger_medicines', $allergy->trigger_medicines ?: [])))>{{ $medicine->name }}</option>@endforeach</select><p class="text-xs text-gray-500">Use Ctrl/Cmd to select multiple medicines.</p></div>
        <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="is_active" value="1" @checked(old('is_active', $allergy->exists ? $allergy->is_active : true))> Active clinical warning</label>
        <div class="flex gap-3"><button class="rounded-lg bg-hut-green px-5 py-2 font-semibold text-white">Save allergy</button><a href="{{ route('manager.patient-allergies.index') }}" class="rounded-lg border px-5 py-2">Cancel</a></div>
    </form>
</div>
@endsection
