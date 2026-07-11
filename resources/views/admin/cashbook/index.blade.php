@extends('layouts.admin')
@section('title', 'Cashbook')

@section('content')

<div class="grid grid-cols-3 gap-4 mb-6">
    <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
        <p class="text-xs text-gray-400">Total Income</p>
        <p class="text-2xl font-display font-bold text-hut-green">Rs. {{ number_format($summary['total_income']) }}</p>
    </div>
    <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
        <p class="text-xs text-gray-400">Total Expense</p>
        <p class="text-2xl font-display font-bold text-red-600">Rs. {{ number_format($summary['total_expense']) }}</p>
    </div>
    <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
        <p class="text-xs text-gray-400">Balance</p>
        <p class="text-2xl font-display font-bold {{ $summary['balance'] >= 0 ? 'text-hut-green' : 'text-red-600' }}">Rs. {{ number_format($summary['balance']) }}</p>
    </div>
</div>

<div class="flex justify-between items-center mb-6">
    <h2 class="text-lg font-display font-bold text-hut-dark">Cashbook Entries</h2>
    <a href="{{ route('manager.cashbook.create') }}" class="bg-hut-green text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-hut-green/90">+ Add Entry</a>
</div>

<div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-gray-50 text-gray-500 text-xs uppercase border-b">
            <tr>
                <th class="px-4 py-3 text-left">Date</th>
                <th class="px-4 py-3 text-left">Type</th>
                <th class="px-4 py-3 text-left">Description</th>
                <th class="px-4 py-3 text-left">Reference</th>
                <th class="px-4 py-3 text-right">Amount</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @forelse($entries as $entry)
            <tr class="hover:bg-gray-50 transition-colors">
                <td class="px-4 py-3 text-gray-600">{{ $entry->created_at->format('d M, Y') }}</td>
                <td class="px-4 py-3">
                    <span class="inline-flex items-center gap-1">
                        @if($entry->type === 'income')
                            <span class="w-2 h-2 bg-hut-green rounded-full"></span>
                            <span class="font-medium text-hut-green">Income</span>
                        @else
                            <span class="w-2 h-2 bg-red-600 rounded-full"></span>
                            <span class="font-medium text-red-600">Expense</span>
                        @endif
                    </span>
                </td>
                <td class="px-4 py-3 text-gray-600">{{ $entry->description }}</td>
                <td class="px-4 py-3 text-gray-500 text-xs">{{ $entry->reference ?? '-' }}</td>
                <td class="px-4 py-3 text-right font-medium {{ $entry->type === 'income' ? 'text-hut-green' : 'text-red-600' }}">
                    {{ $entry->type === 'income' ? '+' : '-' }}Rs. {{ number_format($entry->amount) }}
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="5" class="px-4 py-8 text-center text-gray-500">No entries yet.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-6">
    {{ $entries->links() }}
</div>

@endsection
