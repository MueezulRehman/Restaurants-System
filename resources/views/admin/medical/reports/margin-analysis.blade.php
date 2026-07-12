@extends('layouts.admin')

@section('title', 'Margin Analysis')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex items-center justify-between mb-6">
        <div>
            <a href="{{ route('manager.medical-reports.index') }}" class="text-gray-600 hover:text-gray-800">← Back</a>
            <h1 class="text-3xl font-bold mt-2">Margin Analysis</h1>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow p-6">
        <table class="w-full">
            <thead class="bg-gray-100">
                <tr>
                    <th class="px-4 py-3 text-left">Medicine</th>
                    <th class="px-4 py-3 text-right">Revenue</th>
                    <th class="px-4 py-3 text-right">Cost</th>
                    <th class="px-4 py-3 text-right">Margin</th>
                    <th class="px-4 py-3 text-right">Margin %</th>
                </tr>
            </thead>
            <tbody>
                @forelse($medicines as $medicine)
                    <tr class="border-b">
                        <td class="px-4 py-3">{{ $medicine['name'] }}</td>
                        <td class="px-4 py-3 text-right">{{ number_format($medicine['total_revenue'], 2) }}</td>
                        <td class="px-4 py-3 text-right">{{ number_format($medicine['total_cost'], 2) }}</td>
                        <td class="px-4 py-3 text-right">{{ number_format($medicine['margin'], 2) }}</td>
                        <td class="px-4 py-3 text-right">{{ number_format($medicine['margin_percent'], 2) }}%</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-4 py-3 text-center text-gray-500">No margin data available.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
