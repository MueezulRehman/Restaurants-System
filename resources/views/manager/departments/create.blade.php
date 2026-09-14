@extends('manager.layout.master')
@section('title', 'Add Department')
@section('page-content')
<div class="mx-auto max-w-2xl rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
    <h1 class="font-display text-lg font-bold text-hut-dark">Add Hospital Department</h1>
    @include('manager.departments.form', ['department' => null])
</div>
@endsection
