@extends('manager.layout.master')
@section('title', 'Patient Allergies')
@section('page-content')
<div class="space-y-5">
    <div class="flex items-center justify-between">
        <div><h1 class="text-2xl font-semibold text-hut-dark">Patient Allergies</h1><p class="text-sm text-gray-500">Clinical allergy records used during prescription safety checks.</p></div>
        <a href="{{ route('manager.patient-allergies.create') }}" class="rounded-lg bg-hut-green px-4 py-2 text-sm font-semibold text-white">Add Allergy</a>
    </div>
    @if(session('success'))<div class="rounded-lg bg-green-50 px-4 py-3 text-sm text-green-700">{{ session('success') }}</div>@endif
    <div class="overflow-hidden rounded-xl border border-gray-200 bg-white">
        <table class="min-w-full text-left text-sm"><thead class="bg-gray-50"><tr><th class="px-5 py-3">Patient</th><th>Allergy</th><th>Severity</th><th>Status</th><th></th></tr></thead><tbody class="divide-y">
        @forelse($allergies as $allergy)
            <tr><td class="px-5 py-3">{{ $allergy->patient->name }}</td><td>{{ $allergy->allergy_name }}</td><td>{{ ucfirst($allergy->severity) }}</td><td>{{ $allergy->is_active ? 'Active' : 'Inactive' }}</td><td class="px-5 py-3 text-right"><a class="mr-3 inline-flex h-8 w-8 items-center justify-center rounded-lg text-hut-green hover:bg-hut-green/10" href="{{ route('manager.patient-allergies.edit', $allergy) }}" aria-label="Edit patient allergy" title="Edit patient allergy"><x-icons.edit class="h-4 w-4" /></a><form class="inline" method="POST" action="{{ route('manager.patient-allergies.destroy', $allergy) }}" onsubmit="return confirm('Delete this allergy?')">@csrf @method('DELETE')<button type="submit" class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-red-600 hover:bg-red-50" aria-label="Delete patient allergy" title="Delete patient allergy"><x-icons.trash class="h-4 w-4" /></button></form></td></tr>
        @empty<tr><td colspan="5" class="px-5 py-8 text-center text-sm text-gray-500">No patient allergies recorded.</td></tr>@endforelse
        </tbody></table>
        <div class="border-t p-4">{{ $allergies->links() }}</div>
    </div>
</div>
@endsection
