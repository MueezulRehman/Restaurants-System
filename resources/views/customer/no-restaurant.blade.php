@extends('customer.layout.master')

@section('title', config('app.name', 'CodeIbex'))

@section('page-content')
<div class="min-h-full bg-hut-dark flex items-center justify-center px-4 py-16">
    <div class="text-center">
        <div class="w-20 h-20 bg-hut-yellow rounded-full flex items-center justify-center font-display font-bold text-hut-dark text-3xl mx-auto mb-4">{{ strtoupper(substr(config('app.name', 'CodeIbex'), 0, 2)) }}</div>
        <h1 class="text-2xl font-display font-bold text-white mb-2">{{ config('app.name', 'CodeIbex') }}</h1>
        <p class="text-gray-300 mb-6">No business is currently available here. Please log in as a super admin to register a new business.</p>
        <a href="/admin/login" class="btn-accent">Go to Admin Panel →</a>
    </div>
</div>
@endsection
