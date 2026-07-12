@extends('layouts.admin')

@section('title', 'Top Selling Medicines')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-semibold text-hut-dark">Top Selling Medicines</h2>
            <p class="text-sm text-gray-500">Sales performance for last {{ $days }} days</p>
        </div>
        <a href="{{ route('manager.medical-reports.index') }}" class="text-gray-600 hover:text-gray-800">← Back</a>
    </div>

    <div class="grid md:grid-cols-4 gap-4">
        <div class="bg-blue-50 rounded-2xl p-4 border border-blue-200">
            <p class="text-gray-600 text-sm">Time Period</p>
            <p class="text-2xl font-bold text-blue-600">{{ $days }} Days</p>
        </div>
    </div>

    @if($topMedicines->isEmpty())
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-12 text-center">
            <p class="text-gray-500">No sales data for this period.</p>
        </div>
    @else
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
            <table class="w-full divide-y divide-gray-100">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase text-gray-600">Rank</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase text-gray-600">Medicine</th>
                        <th class="px-6 py-3 text-right text-xs font-semibold uppercase text-gray-600">Units Sold</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($topMedicines as $index => $medicine)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-3">
                                <span class="inline-block w-8 h-8 bg-hut-yellow text-hut-dark rounded-full flex items-center justify-center font-bold text-sm">
                                    {{ $index + 1 }}
                                </span>
                            </td>
                            <td class="px-6 py-3 font-medium text-hut-dark">{{ $medicine->medicine_name }}</td>
                            <td class="px-6 py-3 text-right">
                                <span class="inline-block px-3 py-1 rounded-full bg-green-100 text-green-800 font-semibold">
                                    {{ $medicine->total_sold }}
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
