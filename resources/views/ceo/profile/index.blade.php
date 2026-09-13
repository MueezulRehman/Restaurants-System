@extends('ceo.layout.master')

@section('title', 'CEO Profile')

@section('page-content')
<div class="max-w-2xl rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
    <h1 class="text-2xl font-bold text-hut-dark">My Profile</h1>
    <dl class="mt-6 divide-y divide-gray-100 text-sm">
        <div class="flex justify-between gap-4 py-3"><dt class="text-gray-500">Name</dt><dd class="font-medium">{{ $user->name }}</dd></div>
        <div class="flex justify-between gap-4 py-3"><dt class="text-gray-500">Email</dt><dd class="font-medium">{{ $user->email }}</dd></div>
        <div class="flex justify-between gap-4 py-3"><dt class="text-gray-500">Role</dt><dd class="font-medium">{{ ucfirst(str_replace('_', ' ', $user->role)) }}</dd></div>
    </dl>
</div>
@endsection
