@extends('layouts.admin')
@section('title', 'Expenses')

@section('content')

<div class="grid grid-cols-2 gap-4 mb-6">
    <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
        <p class="text-xs text-gray-400">Total Expenses</p>
        <p class="text-2xl font-display font-bold text-red-600">Rs. {{ number_format($summary['total']) }}</p>
    </div>
    <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
        <p class="text-xs text-gray-400">Today's Expenses</p>
        <p class="text-2xl font-display font-bold text-orange-600">Rs. {{ number_format($summary['today']) }}</p>
    </div>
</div>

<div class="flex justify-between items-center mb-6">
    <h2 class="text-lg font-display font-bold text-hut-dark">Expenses</h2>
    <a href="{{ route('manager.expenses.create') }}" class="bg-hut-green text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-hut-green/90">+ Add Expense</a>
</div>

<div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-gray-50 text-gray-500 text-xs uppercase border-b">
            <tr>
                <th class="px-4 py-3 text-left">Date</th>
                <th class="px-4 py-3 text-left">Category</th>
                <th class="px-4 py-3 text-left">Description</th>
                <th class="px-4 py-3 text-right">Amount</th>
                <th class="px-4 py-3 text-right">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @forelse($expenses as $expense)
            <tr class="hover:bg-gray-50 transition-colors">
                <td class="px-4 py-3 text-gray-600">{{ $expense->date->format('d M, Y') }}</td>
                <td class="px-4 py-3 font-medium text-hut-dark">{{ $expense->category }}</td>
                <td class="px-4 py-3 text-gray-600">{{ Str::limit($expense->description, 40) }}</td>
                <td class="px-4 py-3 text-right font-medium text-red-600">Rs. {{ number_format($expense->amount) }}</td>
                <td class="px-4 py-3 text-right space-x-2 flex justify-end">
                    <a href="{{ route('manager.expenses.edit', $expense) }}" class="text-hut-green hover:underline text-xs font-medium">Edit</a>
                    <form action="{{ route('manager.expenses.destroy', $expense) }}" method="POST" class="inline" onsubmit="return confirm('Delete this expense?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-red-600 hover:underline text-xs font-medium">Delete</button>
                    </form>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="5" class="px-4 py-8 text-center text-gray-500">No expenses recorded.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-6">
    {{ $expenses->links() }}
</div>

@endsection
