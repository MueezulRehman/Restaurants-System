@extends('layouts.admin')

@section('title', 'CEO Access')

@section('content')
    <div class="mb-6 flex items-center justify-between gap-4">
        <div>
            <h2 class="text-2xl font-semibold text-hut-dark">CEO Access</h2>
            <p class="text-sm text-gray-500">Assign one CEO to {{ $restaurant->name }}. The CEO remains separate from the platform owner.</p>
        </div>
        <a href="{{ route('admin.restaurants.index') }}" class="rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm">Back to Businesses</a>
    </div>

    @if(session('success'))
        <div class="mb-6 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700">{{ session('success') }}</div>
    @endif

    @if($errors->any())
        <div class="mb-6 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">{{ $errors->first() }}</div>
    @endif

    <form method="post" action="{{ route('admin.restaurants.ceo-access.assign', $restaurant) }}" class="mb-6 rounded-xl border border-gray-100 bg-white p-6 shadow-sm">
        @csrf
        <h3 class="font-semibold text-hut-dark">Create or assign CEO</h3>
        <p class="mt-1 text-xs text-gray-500">Leave an existing CEO unselected to create a new central CEO account.</p>
        <div class="mt-4 grid gap-4 md:grid-cols-2">
            <label class="text-sm">Existing CEO
                <select name="ceo_user_id" class="mt-1 block w-full rounded-lg border border-gray-200 px-3 py-2">
                    <option value="">Create new CEO</option>
                    @foreach($ceos as $ceo)
                        <option value="{{ $ceo->id }}">{{ $ceo->name }} ({{ $ceo->phone }})</option>
                    @endforeach
                </select>
            </label>
            <label class="text-sm">Access level
                <select name="access_level" class="mt-1 block w-full rounded-lg border border-gray-200 px-3 py-2">
                    <option value="executive">Executive</option>
                    <option value="financial">Financial</option>
                    <option value="operations">Operations</option>
                </select>
            </label>
            <label class="text-sm">New CEO name
                <input name="name" class="mt-1 block w-full rounded-lg border border-gray-200 px-3 py-2">
            </label>
            <label class="text-sm">New CEO phone
                <input name="phone" class="mt-1 block w-full rounded-lg border border-gray-200 px-3 py-2">
            </label>
            <label class="text-sm">New CEO email
                <input type="email" name="email" class="mt-1 block w-full rounded-lg border border-gray-200 px-3 py-2">
            </label>
            <label class="text-sm">New CEO password
                <input type="password" name="password" class="mt-1 block w-full rounded-lg border border-gray-200 px-3 py-2">
            </label>
        </div>
        <button class="mt-5 rounded-lg bg-hut-dark px-4 py-2 text-sm font-semibold text-white">Save CEO Assignment</button>
    </form>

    <div class="rounded-xl border border-gray-100 bg-white p-6 shadow-sm">
        <h3 class="font-semibold text-hut-dark">CEO accounts</h3>
        <div class="mt-4 space-y-2">
            @forelse($ceos as $ceo)
                @if($ceo->ceoBusinessAssignments->isNotEmpty())
                    <div class="flex justify-between rounded-lg border border-gray-100 p-3 text-sm">
                        <span>{{ $ceo->name }} · {{ $ceo->phone }}</span>
                        <span class="text-gray-500">{{ ucfirst($ceo->ceoBusinessAssignments->first()->access_level) }}</span>
                    </div>
                @endif
            @empty
                <p class="text-sm text-gray-500">No CEO accounts have been created.</p>
            @endforelse
        </div>
    </div>
@endsection
