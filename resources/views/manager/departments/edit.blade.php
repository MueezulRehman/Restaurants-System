@extends('manager.layout.master')
@section('title', 'Edit Department')
@section('page-content')
<div class="mx-auto max-w-2xl rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
    <h1 class="font-display text-lg font-bold text-hut-dark">Edit Department</h1>
    @include('manager.departments.form', ['department' => $department])
</div>
@endsection
