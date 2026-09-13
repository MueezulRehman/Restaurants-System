@extends('manager.layout.master')
@section('title', 'Sales Representative Commissions')
@section('page-content')
    <div class="space-y-6">
        <div>
            <h1 class="text-2xl font-display font-bold text-hut-dark">Sales representative commissions</h1>
            <p class="mt-1 text-sm text-gray-500">Commission totals are calculated from completed wholesale-linked sales.
            </p>
        </div>
        <div class="overflow-hidden rounded-xl border border-gray-100 bg-white shadow-sm">
            <table class="min-w-full text-left text-sm">
                <thead class="bg-gray-50 text-xs uppercase text-gray-500">
                    <tr>
                        <th class="px-4 py-3">Representative</th>
                        <th class="px-4 py-3">Orders</th>
                        <th class="px-4 py-3">Gross sales</th>
                        <th class="px-4 py-3">Rate</th>
                        <th class="px-4 py-3">Commission</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">@forelse($report as $row)
                    <tr>
                        <td class="px-4 py-3 font-medium">{{ $row['representative']?->name ?: 'Unassigned' }}</td>
                        <td class="px-4 py-3">{{ $row['orders'] }}</td>
                        <td class="px-4 py-3">Rs. {{ number_format($row['gross'], 2) }}</td>
                        <td class="px-4 py-3">{{ $row['representative']?->commission_rate ?? 0 }}%</td>
                        <td class="px-4 py-3 font-semibold">Rs. {{ number_format($row['commission'], 2) }}</td>
                </tr>@empty<tr>
                        <td colspan="5" class="px-4 py-8 text-center text-gray-500">No representative sales found.</td>
                    </tr>@endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection