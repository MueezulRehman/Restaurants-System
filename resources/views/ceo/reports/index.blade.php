@extends('ceo.layout.master')

@section('title', 'CEO Reports')

@section('page-content')
<div class="space-y-6">
    <div>
        <h1 class="text-3xl font-bold text-hut-dark">Reports</h1>
        <p class="text-sm text-gray-500">Portfolio performance for {{ $summary['from'] }} through {{ $summary['to'] }}.</p>
    </div>
    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        @foreach(['businesses' => 'Businesses', 'branches' => 'Branches', 'orders' => 'Orders', 'sales' => 'Sales'] as $key => $label)
            <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
                <p class="text-sm text-gray-500">{{ $label }}</p>
                <p class="mt-2 text-2xl font-bold text-hut-dark">{{ $key === 'sales' ? number_format($summary['totals'][$key], 2) : number_format($summary['totals'][$key]) }}</p>
            </div>
        @endforeach
    </div>
    <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-gray-50"><tr><th class="px-5 py-3 text-left">Business</th><th class="px-5 py-3 text-right">Orders</th><th class="px-5 py-3 text-right">Sales</th><th class="px-5 py-3 text-right">Low stock</th></tr></thead>
            <tbody class="divide-y divide-gray-100">
                @foreach($summary['reports'] as $report)
                    <tr><td class="px-5 py-3">{{ $report['restaurant']->name }}</td><td class="px-5 py-3 text-right">{{ number_format($report['orders']) }}</td><td class="px-5 py-3 text-right">{{ number_format($report['sales'], 2) }}</td><td class="px-5 py-3 text-right">{{ number_format($report['low_stock']) }}</td></tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
