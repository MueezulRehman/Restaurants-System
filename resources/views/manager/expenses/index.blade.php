@extends('manager.layout.master')
@section('title', 'Expenses')

@section('page-content')

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
        <a href="{{ route('manager.expenses.create') }}" aria-label="Add expense" title="Add expense"
            class="inline-flex h-10 w-10 items-center justify-center rounded-lg bg-hut-green text-white shadow-sm transition hover:-translate-y-0.5 hover:bg-hut-green/90">
            <x-icons.add class="h-5 w-5" />
        </a>
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
                            <a href="{{ route('manager.expenses.edit', $expense) }}"
                                class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-emerald-100 bg-emerald-50 text-hut-green transition hover:-translate-y-0.5 hover:bg-emerald-100"
                                aria-label="Edit expense" title="Edit">
                                <x-icons.edit class="h-4 w-4" />
                            </a>
                            <form action="{{ route('manager.expenses.destroy', $expense) }}" method="POST" class="inline"
                                data-confirm="Delete this expense?">
                                @csrf
                                @method('DELETE')
                                <button type="submit" aria-label="Delete expense" title="Delete"
                                    class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-red-100 bg-red-50 text-red-600 transition hover:-translate-y-0.5 hover:bg-red-100">
                                    <x-icons.trash class="h-4 w-4" />
                                </button>
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