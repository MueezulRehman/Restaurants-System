@extends('layouts.admin')

@section('title', 'Supplier Performance')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-semibold text-hut-dark">Supplier Performance</h2>
            <p class="text-sm text-gray-500">Analysis for last {{ $days }} days</p>
        </div>
        <a href="{{ route('manager.medical-reports.index') }}" class="text-gray-600 hover:text-gray-800">← Back</a>
    </div>

    @if($suppliers->isEmpty())
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-12 text-center">
            <p class="text-gray-500">No supplier data for this period.</p>
        </div>
    @else
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
            <table class="w-full divide-y divide-gray-100">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase text-gray-600">Supplier</th>
                        <th class="px-6 py-3 text-center text-xs font-semibold uppercase text-gray-600">Orders</th>
                        <th class="px-6 py-3 text-right text-xs font-semibold uppercase text-gray-600">Total Value</th>
                        <th class="px-6 py-3 text-center text-xs font-semibold uppercase text-gray-600">Avg Delivery</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase text-gray-600">Payment Terms</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($suppliers as $supplier)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-3 font-medium text-hut-dark">{{ $supplier['name'] }}</td>
                            <td class="px-6 py-3 text-center">
                                <span class="inline-block px-3 py-1 rounded-full bg-blue-100 text-blue-800 font-semibold text-sm">
                                    {{ $supplier['orders'] }}
                                </span>
                            </td>
                            <td class="px-6 py-3 text-right font-semibold text-hut-dark">
                                Rs. {{ number_format($supplier['total_value'], 2) }}
                            </td>
                            <td class="px-6 py-3 text-center text-sm text-gray-600">
                                {{ $supplier['avg_delivery_days'] }} days
                            </td>
                            <td class="px-6 py-3 text-sm">
                                <span class="inline-block px-2 py-1 rounded text-xs font-medium
                                    @if($supplier['payment_terms'] === 'cash') bg-red-100 text-red-800
                                    @elseif($supplier['payment_terms'] === 'credit_7') bg-orange-100 text-orange-800
                                    @else bg-blue-100 text-blue-800 @endif">
                                    {{ ucfirst(str_replace('_', ' ', $supplier['payment_terms'])) }}
                                </span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
@endsection
