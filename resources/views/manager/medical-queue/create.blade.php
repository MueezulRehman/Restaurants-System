@extends('manager.layout.master')
@section('title', 'Patient Check-In')
@section('page-content')
<div class="max-w-3xl"><x-back-link href="{{ route('manager.medical-queue.index') }}" label="Back to Queue" /><div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm"><h1 class="mb-5 font-display text-lg font-bold text-hut-dark">Patient Check-In</h1>
@if($errors->any())<div class="mb-4 rounded-lg bg-red-50 p-3 text-sm text-red-700">{{ $errors->first() }}</div>@endif
<form method="POST" action="{{ route('manager.medical-queue.store') }}" class="space-y-4">@csrf
@if($appointments->isNotEmpty())<label class="block text-sm font-medium">Scheduled appointment (optional)<select name="appointment_id" class="mt-1 w-full rounded-lg border px-3 py-2"><option value="">Walk-in patient</option>@foreach($appointments as $appointment)<option value="{{ $appointment->id }}">{{ $appointment->starts_at->format('H:i') }} — {{ $appointment->patient?->name ?: 'Patient not linked yet' }} — {{ $appointment->service_name }}</option>@endforeach</select></label>@endif
<label class="block text-sm font-medium">Doctor<select name="doctor_id" required class="mt-1 w-full rounded-lg border px-3 py-2">@foreach($doctors as $doctor)<option value="{{ $doctor->id }}">{{ $doctor->name }} — {{ $doctor->specialty }}</option>@endforeach</select></label>
<label class="block text-sm font-medium">Patient<select name="patient_id" required class="mt-1 w-full rounded-lg border px-3 py-2">@foreach($patients as $patient)<option value="{{ $patient->id }}">{{ $patient->name }} ({{ $patient->cnic ?: $patient->patient_number }})</option>@endforeach</select></label>
<label class="flex items-start gap-2 text-sm"><input type="checkbox" name="notification_consent" value="1" class="mt-1"><span><span class="font-medium">Patient consents to queue notifications</span><span class="block text-xs text-gray-500">The consent applies to this patient's provided phone number and is unchecked by default.</span></span></label>
<label class="block text-sm font-medium">Reason (optional)<textarea name="reason" rows="3" class="mt-1 w-full rounded-lg border px-3 py-2"></textarea></label>
<button class="rounded-lg bg-hut-green px-4 py-2 text-sm font-semibold text-white">Issue Token</button></form></div></div>
@endsection
