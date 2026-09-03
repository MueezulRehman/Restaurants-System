@extends('layouts.admin')
@section('title', 'Business Reports')

@section('content')
<div class="mb-6 flex flex-col gap-3 md:flex-row md:items-end md:justify-between">
    <div>
        <h2 class="text-2xl font-semibold text-hut-dark">Business reports</h2>
        <p class="text-sm text-gray-500">Sales overview per business on Codeibex</p>
    </div>
    <form method="GET" class="flex gap-2 items-end">
        <div>
            <label class="text-xs text-gray-500">From</label>
            <input type="date" name="from" value="{{ $from }}" class="rounded-lg border border-gray-300 px-3 py-2 text-sm">
        </div>
        <div>
            <label class="text-xs text-gray-500">To</label>
            <input type="date" name="to" value="{{ $to }}" class="rounded-lg border border-gray-300 px-3 py-2 text-sm">
        </div>
        <button class="rounded-lg bg-hut-dark text-white px-4 py-2 text-sm font-semibold">Filter</button>
    </form>
</div>

<div class="overflow-x-auto rounded-2xl border border-gray-200 bg-white shadow-sm">
    <table class="min-w-full text-sm">
        <thead class="bg-gray-50 text-left text-xs uppercase text-gray-500">
            <tr>
                <th class="px-4 py-3">Business</th>
                <th class="px-4 py-3">Type</th>
                <th class="px-4 py-3">Orders</th>
                <th class="px-4 py-3">Revenue</th>
                <th class="px-4 py-3">Pending</th>
                <th class="px-4 py-3"></th>
            </tr>
        </thead>
        <tbody>
            @foreach($rows as $row)
                <tr class="border-t border-gray-100">
                    <td class="px-4 py-3 font-medium text-hut-dark">{{ $row['restaurant']->name }}</td>
                    <td class="px-4 py-3 text-gray-600">{{ $row['restaurant']->businessType?->name ?? '—' }}</td>
                    <td class="px-4 py-3">{{ $row['orders_count'] }}</td>
                    <td class="px-4 py-3 font-semibold text-hut-green">Rs. {{ number_format($row['revenue'], 2) }}</td>
                    <td class="px-4 py-3">{{ $row['pending'] }}</td>
                    <td class="px-4 py-3 text-right space-x-2">
                        <a href="{{ route('admin.reports.businesses.show', $row['restaurant']) }}" class="text-hut-dark font-medium hover:underline">View</a>
                        <form action="{{ route('admin.restaurants.payment-reminder', $row['restaurant']) }}" method="POST" class="inline">
                            @csrf
                            <button type="submit" class="text-amber-700 font-medium hover:underline" onclick="return confirm('Send payment reminder to this business staff?')">Remind payment</button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
