@extends('manager.layout.master')
@section('title', 'Edit Patient')
@section('page-content')
<div class="max-w-3xl"><x-back-link href="{{ route('manager.patients.index') }}" label="Back to Patients" /><div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm"><h1 class="mb-5 font-display text-lg font-bold text-hut-dark">Edit Patient</h1>@include('manager.patients._form', ['patient' => $patient])</div></div>
@endsection
