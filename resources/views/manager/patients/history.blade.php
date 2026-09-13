@extends('manager.layout.master')
@section('title', 'Patient Visit History')
@section('page-content')
<div class="space-y-5">
    <div class="flex items-center justify-between">
        <div>
            <x-back-link href="{{ route('manager.patients.index') }}" label="Back to Patients" />
            <h1 class="mt-4 font-display text-xl font-bold text-hut-dark">{{ $patient->name }}</h1>
            <p class="text-sm text-gray-500">Read-only visit history</p>
        </div>
        <a href="{{ route('manager.patients.edit', $patient) }}" class="rounded-lg border border-gray-200 px-4 py-2 text-sm font-semibold">Edit Patient</a>
    </div>
    <div class="rounded-xl border border-gray-200 bg-white shadow-sm">
        <div class="border-b px-5 py-4"><h2 class="font-display text-base font-bold text-hut-dark">Previous Visits</h2></div>
        <div class="divide-y">
            @forelse($visits as $visit)
                <div class="grid gap-2 px-5 py-4 sm:grid-cols-4 sm:items-center">
                    <div><p class="font-semibold text-hut-dark">{{ $visit->checked_in_at?->format('d M Y, H:i') ?: 'Date unavailable' }}</p><p class="text-xs text-gray-500">Checked in</p></div>
                    <div><p class="font-medium">{{ $visit->doctor?->name ?: 'Doctor unavailable' }}</p><p class="text-xs text-gray-500">{{ $visit->doctor?->specialty ?: '—' }}</p></div>
                    <div><span class="rounded-full bg-gray-100 px-2.5 py-1 text-xs font-semibold">{{ ucfirst(str_replace('_', ' ', $visit->status)) }}</span></div>
                    <div class="text-sm text-gray-600">{{ $visit->reason ?: 'No visit reason recorded.' }}</div>
                </div>
            @empty
                <p class="px-5 py-10 text-center text-sm text-gray-500">No previous visits recorded.</p>
            @endforelse
        </div>
        @if($visits->hasPages())<div class="border-t px-5 py-4">{{ $visits->links() }}</div>@endif
    </div>
</div>
@endsection
