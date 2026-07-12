@extends('layouts.admin')

@section('title', 'Medical Reports')

@section('content')
<div class="space-y-6">
    <div>
        <h2 class="text-2xl font-semibold text-hut-dark">Medical Reports</h2>
        <p class="text-sm text-gray-500">Analytics and insights for medical store operations</p>
    </div>

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

    <div class="grid md:grid-cols-2 gap-4 mt-6">
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6">
            <h3 class="font-semibold text-hut-dark mb-4">Quick Stats</h3>
            <div class="space-y-3">
                <div class="flex justify-between">
                    <span class="text-gray-600">Total Medicines</span>
                    <span class="font-semibold text-hut-dark">—</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">Active Batches</span>
                    <span class="font-semibold text-hut-dark">—</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">Low Stock Items</span>
                    <span class="font-semibold text-red-600">—</span>
                </div>
            </div>
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
