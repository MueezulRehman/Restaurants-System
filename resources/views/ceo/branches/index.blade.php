@extends('ceo.layout.master')

@section('title', 'All Branches')

@section('page-content')
    <h1 class="mb-6 text-2xl font-semibold text-hut-dark">All Accessible Branches</h1>
    <div class="grid gap-4 md:grid-cols-2">
        @forelse($branches as $entry)
            <a href="{{ route('manager.ceo.branches.show', [$entry['restaurant']->id, $entry['branch']->id]) }}"
                class="rounded-xl border border-gray-100 bg-white p-5 shadow-sm hover:border-hut-yellow">
                <h2 class="font-semibold text-hut-dark">{{ $entry['branch']->name }}</h2>
                <p class="mt-1 text-sm text-gray-500">{{ $entry['restaurant']->name }}</p>
            </a>
        @empty
            <p class="text-sm text-gray-500">No branches are available in your assigned scope.</p>
        @endforelse
    </div>
@endsection
