@extends('manager.layout.master')
@section('title', 'Consultation')
@section('page-content')
<div class="mx-auto max-w-3xl space-y-5">
    <div class="flex items-start justify-between gap-4">
        <div>
            <a class="inline-flex items-center gap-2 text-sm text-hut-green hover:underline" href="{{ route('manager.visits.index') }}">
                <span aria-hidden="true">&larr;</span>
                Back to visits
            </a>
            <h1 class="mt-2 text-2xl font-semibold text-hut-dark">{{ $visit->patient->name }} — {{ $visit->doctor->name }}</h1>
        </div>
        <a class="inline-flex shrink-0 items-center gap-2 rounded-lg border border-gray-200 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"
            href="{{ route('manager.visits.print', $visit) }}">
            <x-icons.printer class="h-4 w-4" />
            Print
        </a>
    </div>
    @if($visit->patient->allergies->isNotEmpty())
        <div class="rounded-xl border border-red-200 bg-red-50 p-4 text-red-800" role="alert">
            <div class="flex items-start gap-3">
                <span class="text-lg font-bold" aria-hidden="true">!</span>
                <div>
                    <p class="font-semibold">Allergy warning</p>
                    <p class="mt-1 text-sm">
                        Active allergies recorded:
                        {{ $visit->patient->allergies->map(fn ($allergy) => $allergy->allergy_name)->join(', ') }}.
                        Review before prescribing.
                    </p>
                </div>
            </div>
        </div>
    @endif
    <form method="POST" action="{{ route('manager.visits.update', $visit) }}" class="space-y-4 rounded-xl border border-gray-200 bg-white p-6">@csrf @method('PATCH')
        <div><label class="block text-sm font-medium">Diagnosis</label><textarea name="diagnosis" rows="4" class="mt-1 w-full rounded-lg border p-3">{{ old('diagnosis', $visit->diagnosis) }}</textarea></div>
        <div><label class="block text-sm font-medium">Clinical notes</label><textarea name="notes" rows="5" class="mt-1 w-full rounded-lg border p-3">{{ old('notes', $visit->notes) }}</textarea></div>
        <div><label class="block text-sm font-medium">Status</label><select name="status" class="mt-1 rounded-lg border p-2">@foreach(['checked_in','in_progress','completed','no_show','cancelled'] as $status)<option value="{{ $status }}" @selected($visit->status === $status)>{{ ucfirst(str_replace('_',' ', $status)) }}</option>@endforeach</select></div>
        <button class="rounded-lg bg-hut-green px-5 py-2 font-semibold text-white">Save consultation</button>
    </form>
</div>
@endsection
