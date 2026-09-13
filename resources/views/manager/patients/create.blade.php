@extends('manager.layout.master')
@section('title', 'Add Patient')
@section('page-content')
<div class="max-w-3xl"><x-back-link href="{{ route('manager.patients.index') }}" label="Back to Patients" /><div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm"><h1 class="mb-2 font-display text-lg font-bold text-hut-dark">Add Patient</h1><p class="mb-5 text-sm text-gray-500">Search by CNIC first, then name, phone, and date of birth before creating a new record.</p>@include('manager.patients._form', ['patient' => null])</div></div>
@endsection
