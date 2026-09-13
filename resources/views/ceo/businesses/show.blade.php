@extends('ceo.layout.master')

@section('title', $assignment->restaurant?->name ?? 'Business')

@section('page-content')
    <div class="mb-6">
        <a href="{{ route('manager.ceo.businesses.index') }}" class="text-sm text-blue-700 hover:underline">Back to businesses</a>
        <h1 class="mt-2 text-2xl font-semibold text-hut-dark">{{ $assignment->restaurant?->name ?? 'Unavailable business' }}</h1>
        <p class="text-sm text-gray-500">{{ ucfirst($assignment->access_level) }} access · {{ str_replace('_', ' ', ucfirst($assignment->access_scope)) }}</p>
    </div>
    @php($report = $summary['reports']->first())
    <div class="grid grid-cols-2 gap-4 lg:grid-cols-4">
        @foreach([
            'Sales' => 'Rs. ' . number_format($report['sales'] ?? 0),
            'Orders' => number_format($report['orders'] ?? 0),
            'Branches' => number_format($report['branches'] ?? 0),
            'Low stock' => number_format($report['low_stock'] ?? 0),
        ] as $label => $value)
            <div class="rounded-xl border border-gray-100 bg-white p-4 shadow-sm">
                <p class="text-xs text-gray-400">{{ $label }}</p>
                <p class="mt-1 text-xl font-semibold text-hut-dark">{{ $value }}</p>
            </div>
        @endforeach
    </div>
@endsection
