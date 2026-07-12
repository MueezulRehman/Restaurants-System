@extends('layouts.admin')

@section('title', 'Expiry Analysis')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-semibold text-hut-dark">Batch Expiry Analysis</h2>
            <p class="text-sm text-gray-500">Inventory status by expiry date</p>
        </div>
        <a href="{{ route('manager.medical-reports.index') }}" class="text-gray-600 hover:text-gray-800">← Back</a>
    </div>

    @php
        $expiredCount = isset($batches['expired']) ? $batches['expired']->count() : 0;
        $expiringSoonCount = isset($batches['expiring_soon']) ? $batches['expiring_soon']->count() : 0;
        $expiring90Count = isset($batches['expiring_within_90']) ? $batches['expiring_within_90']->count() : 0;
        $goodCount = isset($batches['good']) ? $batches['good']->count() : 0;
    @endphp

    <div class="grid md:grid-cols-4 gap-4">
        <div class="bg-red-50 rounded-2xl p-4 border border-red-200">
            <p class="text-gray-600 text-sm">Expired</p>
            <p class="text-3xl font-bold text-red-600">{{ $expiredCount }}</p>
        </div>
        <div class="bg-orange-50 rounded-2xl p-4 border border-orange-200">
            <p class="text-gray-600 text-sm">Expiring Soon</p>
            <p class="text-3xl font-bold text-orange-600">{{ $expiringSoonCount }}</p>
        </div>
        <div class="bg-yellow-50 rounded-2xl p-4 border border-yellow-200">
            <p class="text-gray-600 text-sm">Within 90 Days</p>
            <p class="text-3xl font-bold text-yellow-600">{{ $expiring90Count }}</p>
        </div>
        <div class="bg-green-50 rounded-2xl p-4 border border-green-200">
            <p class="text-gray-600 text-sm">Good Stock</p>
            <p class="text-3xl font-bold text-green-600">{{ $goodCount }}</p>
        </div>
    </div>

    {{-- Expired Batches --}}
    @if(isset($batches['expired']) && $batches['expired']->isNotEmpty())
        <div class="bg-white rounded-2xl shadow-sm border border-red-200">
            <div class="px-6 py-4 border-b border-red-200 bg-red-50">
                <h3 class="font-semibold text-red-800">🔴 Expired Batches ({{ $batches['expired']->count() }})</h3>
            </div>
            <table class="w-full divide-y divide-gray-100">
                <thead>
                    <tr class="text-xs text-gray-600 uppercase tracking-wide">
                        <th class="px-6 py-2 text-left">Medicine</th>
                        <th class="px-6 py-2 text-left">Batch</th>
                        <th class="px-6 py-2 text-left">Expiry Date</th>
                        <th class="px-6 py-2 text-left">Qty</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($batches['expired'] as $batch)
                        <tr class="border-t border-gray-100 hover:bg-red-50">
                            <td class="px-6 py-3 text-sm font-medium">{{ $batch->medicine->name }}</td>
                            <td class="px-6 py-3 text-sm text-gray-600">{{ $batch->batch_number }}</td>
                            <td class="px-6 py-3 text-sm text-red-600 font-semibold">{{ $batch->expiry_date->format('M d, Y') }}</td>
                            <td class="px-6 py-3 text-sm">{{ $batch->quantity }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    {{-- Expiring Soon --}}
    @if(isset($batches['expiring_soon']) && $batches['expiring_soon']->isNotEmpty())
        <div class="bg-white rounded-2xl shadow-sm border border-orange-200">
            <div class="px-6 py-4 border-b border-orange-200 bg-orange-50">
                <h3 class="font-semibold text-orange-800">🟠 Expiring Soon - Within 30 Days ({{ $batches['expiring_soon']->count() }})</h3>
            </div>
            <table class="w-full divide-y divide-gray-100">
                <thead>
                    <tr class="text-xs text-gray-600 uppercase tracking-wide">
                        <th class="px-6 py-2 text-left">Medicine</th>
                        <th class="px-6 py-2 text-left">Batch</th>
                        <th class="px-6 py-2 text-left">Expiry Date</th>
                        <th class="px-6 py-2 text-left">Days Left</th>
                        <th class="px-6 py-2 text-left">Qty</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($batches['expiring_soon'] as $batch)
                        <tr class="border-t border-gray-100 hover:bg-orange-50">
                            <td class="px-6 py-3 text-sm font-medium">{{ $batch->medicine->name }}</td>
                            <td class="px-6 py-3 text-sm text-gray-600">{{ $batch->batch_number }}</td>
                            <td class="px-6 py-3 text-sm text-orange-600 font-semibold">{{ $batch->expiry_date->format('M d, Y') }}</td>
                            <td class="px-6 py-3 text-sm font-semibold">{{ $batch->expiry_date->diffInDays(now()) }} days</td>
                            <td class="px-6 py-3 text-sm">{{ $batch->quantity }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    {{-- Expiring Within 90 Days --}}
    @if(isset($batches['expiring_within_90']) && $batches['expiring_within_90']->isNotEmpty())
        <div class="bg-white rounded-2xl shadow-sm border border-yellow-200">
            <div class="px-6 py-4 border-b border-yellow-200 bg-yellow-50">
                <h3 class="font-semibold text-yellow-800">🟡 Expiring Within 90 Days ({{ $batches['expiring_within_90']->count() }})</h3>
            </div>
            <table class="w-full divide-y divide-gray-100">
                <thead>
                    <tr class="text-xs text-gray-600 uppercase tracking-wide">
                        <th class="px-6 py-2 text-left">Medicine</th>
                        <th class="px-6 py-2 text-left">Batch</th>
                        <th class="px-6 py-2 text-left">Expiry Date</th>
                        <th class="px-6 py-2 text-left">Days Left</th>
                        <th class="px-6 py-2 text-left">Qty</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($batches['expiring_within_90'] as $batch)
                        <tr class="border-t border-gray-100 hover:bg-yellow-50">
                            <td class="px-6 py-3 text-sm font-medium">{{ $batch->medicine->name }}</td>
                            <td class="px-6 py-3 text-sm text-gray-600">{{ $batch->batch_number }}</td>
                            <td class="px-6 py-3 text-sm">{{ $batch->expiry_date->format('M d, Y') }}</td>
                            <td class="px-6 py-3 text-sm font-semibold">{{ $batch->expiry_date->diffInDays(now()) }} days</td>
                            <td class="px-6 py-3 text-sm">{{ $batch->quantity }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
@endsection
