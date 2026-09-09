@extends('layouts.customer')

@section('title', 'Page Not Found')

@section('content')
    <div class="mx-auto flex min-h-[60vh] max-w-2xl items-center justify-center px-4 py-16 text-center">
        <div>
            <p class="text-sm font-bold uppercase tracking-[0.2em] text-hut-green">CodeIbex</p>
            <h1 class="mt-3 text-6xl font-bold text-slate-900">404</h1>
            <h2 class="mt-3 text-2xl font-semibold text-slate-900">We could not find that page</h2>
            <p class="mt-3 text-slate-600">The link may be outdated, or the page may have moved.</p>
            <a href="{{ route('home') }}"
                class="mt-7 inline-flex rounded-lg bg-hut-dark px-5 py-3 font-semibold text-white">Back to CodeIbex</a>
        </div>
    </div>
@endsection