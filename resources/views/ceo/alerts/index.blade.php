@extends('ceo.layout.master')

@section('title', 'CEO Alerts')

@section('page-content')
<div class="space-y-6">
    <div><h1 class="text-3xl font-bold text-hut-dark">Alerts</h1><p class="text-sm text-gray-500">Operational issues across your assigned businesses.</p></div>
    <div class="rounded-xl border border-gray-200 bg-white shadow-sm">
        @forelse($summary['reports']->where('low_stock', '>', 0) as $report)
            <div class="flex items-center justify-between border-b border-gray-100 px-5 py-4 last:border-0">
                <span class="font-medium text-gray-800">{{ $report['restaurant']->name }}</span>
                <span class="rounded-full bg-red-50 px-3 py-1 text-sm font-semibold text-red-700">{{ number_format($report['low_stock']) }} low-stock items</span>
            </div>
        @empty
            <p class="p-5 text-sm text-gray-500">No low-stock alerts for the assigned portfolio.</p>
        @endforelse
    </div>
</div>
@endsection
