@extends('manager.layout.master')
@section('title', 'Visits')
@section('page-content')
<div class="space-y-5">
    <h1 class="text-2xl font-semibold text-hut-dark">Consultations</h1>
    <div class="overflow-hidden rounded-xl border border-gray-200 bg-white">
        <table class="min-w-full text-left text-sm"><thead class="bg-gray-50"><tr><th class="px-5 py-3">Patient</th><th>Doctor</th><th>Checked in</th><th>Status</th><th></th></tr></thead><tbody class="divide-y">
        @forelse($visits as $visit)<tr><td class="px-5 py-3">{{ $visit->patient->name }}</td><td>{{ $visit->doctor->name }}</td><td>{{ $visit->checked_in_at?->format('d M Y H:i') }}</td><td>{{ ucfirst(str_replace('_', ' ', $visit->status)) }}</td><td><a class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-hut-green hover:bg-hut-green/10" href="{{ route('manager.visits.show', $visit) }}" aria-label="View consultation" title="View consultation"><x-icons.eye class="h-4 w-4" /></a></td></tr>@empty<tr><td class="px-5 py-8 text-center" colspan="5">No consultations.</td></tr>@endforelse
        </tbody></table>
        <div class="border-t p-4">{{ $visits->links() }}</div>
    </div>
</div>
@endsection
