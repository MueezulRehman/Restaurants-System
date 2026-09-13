@extends('manager.layout.master')
@section('title', 'Add Doctor')
@section('page-content')
<div class="max-w-3xl"><x-back-link href="{{ route('manager.doctors.index') }}" label="Back to Doctors" /><div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm"><h1 class="mb-5 font-display text-lg font-bold text-hut-dark">Add Doctor</h1>@include('manager.doctors._form', ['doctor' => null])</div></div>
@endsection
