@extends('layouts.ceo')

@section('title', 'CEO Login')

@section('content')
    <div class="mx-auto max-w-md rounded-xl border border-gray-100 bg-white p-6 shadow-sm">
        <h1 class="font-display text-2xl font-bold text-hut-dark">CEO Login</h1>
        <p class="mt-1 text-sm text-gray-500">Access only the businesses assigned to your CEO account.</p>

        <form method="post" action="{{ route('ceo.login.attempt') }}" class="mt-6 space-y-4">
            @csrf
            <label class="block text-sm font-medium text-gray-700">Phone
                <input name="phone" value="{{ old('phone') }}" required class="mt-1 block w-full rounded-lg border border-gray-200 px-3 py-2">
            </label>
            <label class="block text-sm font-medium text-gray-700">Password
                <input type="password" name="password" required class="mt-1 block w-full rounded-lg border border-gray-200 px-3 py-2">
            </label>
            <button class="w-full rounded-lg bg-hut-dark px-4 py-2 font-semibold text-white">Log in</button>
        </form>
    </div>
@endsection
