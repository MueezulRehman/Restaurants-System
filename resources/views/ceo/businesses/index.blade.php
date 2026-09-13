@extends('ceo.layout.master')

@section('title', 'My Businesses')

@section('page-content')
    <h1 class="mb-6 text-2xl font-semibold text-hut-dark">My Businesses</h1>
    <div class="grid gap-4 md:grid-cols-2">
        @forelse($businesses as $assignment)
            <a href="{{ route('manager.ceo.businesses.show', $assignment->restaurant_id) }}"
                class="rounded-xl border border-gray-100 bg-white p-5 shadow-sm hover:border-hut-yellow">
                <h2 class="font-semibold text-hut-dark">{{ $assignment->restaurant?->name ?? 'Unavailable business' }}</h2>
                <p class="mt-1 text-sm text-gray-500">{{ ucfirst($assignment->access_level) }} access</p>
                <p class="mt-3 text-xs text-gray-500">{{ str_replace('_', ' ', ucfirst($assignment->access_scope)) }}</p>
            </a>
        @empty
            <p class="text-sm text-gray-500">No businesses have been assigned.</p>
        @endforelse
    </div>
@endsection
