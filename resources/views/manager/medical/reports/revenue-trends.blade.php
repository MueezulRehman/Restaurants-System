@extends('manager.layout.master')

@section('title', 'Revenue Trends')

@section('page-content')
    <div class="container mx-auto px-4 py-8">
        <div class="flex items-center justify-between mb-6">
            <div>
                <x-back-link href="{{ route('manager.medical-reports.index') }}" label="Back to Medical Reports"
                    class="mb-0" />
                <h1 class="text-3xl font-bold mt-2">Revenue Trends</h1>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <table class="w-full">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="px-4 py-3 text-left">Date</th>
                        <th class="px-4 py-3 text-right">Orders</th>
                        <th class="px-4 py-3 text-right">Revenue</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($dailyRevenue as $day)
                        <tr class="border-b">
                            <td class="px-4 py-3">{{ $day->date }}</td>
                            <td class="px-4 py-3 text-right">{{ $day->order_count }}</td>
                            <td class="px-4 py-3 text-right">{{ number_format($day->total, 2) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="px-4 py-3 text-center text-gray-500">No revenue data available.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection