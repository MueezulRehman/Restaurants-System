@extends('manager.layout.master')

@section('title', 'Medical Reports')

@section('page-content')
<div class="space-y-6">
    <div>
        <h2 class="text-2xl font-semibold text-hut-dark">Medical Reports</h2>
        <p class="text-sm text-gray-500">Analytics and insights for medical store operations</p>
    </div>

    <form method="GET" class="flex flex-wrap items-end gap-3 rounded-2xl border border-gray-200 bg-white p-4">
        <label class="text-sm font-medium">From<input type="date" name="from" value="{{ $from }}" class="mt-1 block rounded-lg border px-3 py-2"></label>
        <label class="text-sm font-medium">To<input type="date" name="to" value="{{ $to }}" class="mt-1 block rounded-lg border px-3 py-2"></label>
        <button class="rounded-lg bg-hut-dark px-4 py-2 text-sm font-semibold text-white">Apply</button>
    </form>

    <div class="grid md:grid-cols-3 gap-4">
        <a href="{{ route('manager.medical-reports.top-medicines') }}" class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-2xl shadow-sm p-6 text-white hover:shadow-md transition">
            <div class="text-3xl mb-2">📊</div>
            <h3 class="font-semibold text-lg mb-1">Top Selling Medicines</h3>
            <p class="text-blue-100 text-sm">Sales performance & trending items</p>
        </a>

        <a href="{{ route('manager.medical-reports.expiry-analysis') }}" class="bg-gradient-to-br from-red-500 to-red-600 rounded-2xl shadow-sm p-6 text-white hover:shadow-md transition">
            <div class="text-3xl mb-2">⏰</div>
            <h3 class="font-semibold text-lg mb-1">Expiry Analysis</h3>
            <p class="text-red-100 text-sm">Expired & expiring soon batches</p>
        </a>

        <a href="{{ route('manager.medical-reports.supplier-performance') }}" class="bg-gradient-to-br from-green-500 to-green-600 rounded-2xl shadow-sm p-6 text-white hover:shadow-md transition">
            <div class="text-3xl mb-2">🏭</div>
            <h3 class="font-semibold text-lg mb-1">Supplier Performance</h3>
            <p class="text-green-100 text-sm">Vendor analysis & statistics</p>
        </a>
    </div>

    <div class="grid gap-4 md:grid-cols-3 mt-6">
        @foreach([
            ['Patients', $stats['patients']],
            ['Active doctors', $stats['doctors']],
            ['Visits in range', $stats['visits']],
            ['Completed visits', $stats['completed_visits']],
            ['Prescriptions', $stats['prescriptions']],
            ['Dispensed', $stats['dispensed']],
            ['Today\'s pending queue', $stats['pending_queue']],
        ] as [$label, $value])
            <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
                <p class="text-sm text-gray-500">{{ $label }}</p>
                <p class="mt-2 text-2xl font-semibold text-hut-dark">{{ number_format($value) }}</p>
            </div>
        @endforeach
    </div>

    <div class="grid md:grid-cols-2 gap-4 mt-6">
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6">
            <h3 class="font-semibold text-hut-dark mb-4">Quick Stats</h3>
            <p class="text-sm text-gray-600">Showing workflow activity from {{ $from }} through {{ $to }}.</p>
            <p class="mt-2 text-sm text-gray-600">Use the detailed reports above for medicine, expiry, supplier, margin, revenue, and audit analysis.</p>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6">
            <h3 class="font-semibold text-hut-dark mb-4">Alerts</h3>
            <div class="space-y-3 text-sm">
                <p class="text-gray-600">✓ No critical alerts at this time</p>
            </div>
        </div>
    </div>
</div>
@endsection
