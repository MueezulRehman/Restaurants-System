@extends('manager.layout.master')
@section('title', 'Nursing Assignments')
@section('page-content')
<div class="space-y-6">
    <div><h1 class="font-display text-lg font-bold text-hut-dark">Nursing Assignments</h1><p class="text-sm text-gray-500">Assign active inpatients to qualified nurses.</p></div>
    @if(session('success'))<div class="rounded-lg bg-green-50 px-4 py-3 text-sm text-green-700">{{ session('success') }}</div>@endif
    <form method="POST" action="{{ route('manager.nursing.store') }}" class="grid gap-4 rounded-xl border border-gray-200 bg-white p-5 md:grid-cols-4">@csrf
        <select name="hospital_admission_id" required class="rounded-lg border-gray-200"><option value="">Select admission</option>@foreach($admissions as $admission)<option value="{{ $admission->id }}">{{ $admission->admission_number }} — {{ $admission->patient?->name }}</option>@endforeach</select>
        <select name="nurse_id" required class="rounded-lg border-gray-200"><option value="">Select nurse</option>@foreach($nurses as $nurse)<option value="{{ $nurse->id }}">{{ $nurse->name }}</option>@endforeach</select>
        <input type="datetime-local" name="assigned_at" value="{{ now()->format('Y-m-d\TH:i') }}" required class="rounded-lg border-gray-200">
        <button class="rounded-lg bg-hut-dark px-4 py-2 text-sm font-semibold text-white">Assign Nurse</button>
        <textarea name="notes" placeholder="Assignment notes (optional)" class="md:col-span-4 rounded-lg border-gray-200"></textarea>
    </form>
    <div class="overflow-x-auto rounded-xl border border-gray-200 bg-white"><table class="w-full text-left text-sm"><thead class="border-b bg-gray-50 text-xs uppercase text-gray-500"><tr><th class="px-5 py-3">Admission</th><th class="px-3 py-3">Patient</th><th class="px-3 py-3">Nurse</th><th class="px-3 py-3">Assigned</th><th class="px-5 py-3">Status</th></tr></thead><tbody class="divide-y">@forelse($assignments as $assignment)<tr><td class="px-5 py-3">{{ $assignment->admission?->admission_number }}</td><td class="px-3 py-3">{{ $assignment->admission?->patient?->name }}</td><td class="px-3 py-3">{{ $assignment->nurse?->name }}</td><td class="px-3 py-3">{{ $assignment->assigned_at?->format('d M Y H:i') }}</td><td class="px-5 py-3 capitalize">{{ $assignment->status }}</td></tr>@empty<tr><td colspan="5" class="px-5 py-10 text-center text-gray-500">No nursing assignments found.</td></tr>@endforelse</tbody></table><div class="px-5 py-4">{{ $assignments->links() }}</div></div>
</div>
@endsection
