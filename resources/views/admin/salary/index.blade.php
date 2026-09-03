@extends('layouts.admin')
@section('title', 'Salary Management')

@section('content')

    <div class="grid grid-cols-2 gap-4 mb-6">
        <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
            <p class="text-xs text-gray-400">Total Paid</p>
            <p class="text-2xl font-display font-bold text-hut-green">Rs. {{ number_format($summary['total_paid']) }}</p>
        </div>
        <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
            <p class="text-xs text-gray-400">This Month</p>
            <p class="text-2xl font-display font-bold text-hut-green">Rs. {{ number_format($summary['this_month']) }}</p>
        </div>
    </div>

    <div class="flex justify-between items-center mb-6">
        <h2 class="text-lg font-display font-bold text-hut-dark">Salary Records</h2>
        <a href="{{ route('manager.salary.create') }}"
            class="bg-hut-green text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-hut-green/90">+ Record
            Salary</a>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 text-gray-500 text-xs uppercase border-b">
                <tr>
                    <th class="px-4 py-3 text-left">Month</th>
                    <th class="px-4 py-3 text-left">Staff Member</th>
                    <th class="px-4 py-3 text-right">Amount</th>
                    <th class="px-4 py-3 text-left">Notes</th>
                    <th class="px-4 py-3 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($salaries as $salary)
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-4 py-3 font-medium text-hut-dark">{{ $salary->month }}</td>
                        <td class="px-4 py-3 text-gray-600">{{ $salary->user->name }}</td>
                        <td class="px-4 py-3 text-right font-medium text-hut-green">Rs. {{ number_format($salary->amount) }}
                        </td>
                        <td class="px-4 py-3 text-gray-600 text-xs">{{ Str::limit($salary->notes, 30) ?? '-' }}</td>
                        <td class="px-4 py-3 text-right space-x-2 flex justify-end">
                            <a href="{{ route('manager.salary.edit', $salary) }}"
                                class="text-hut-green hover:underline text-xs font-medium">Edit</a>
                            <form action="{{ route('manager.salary.destroy', $salary) }}" method="POST" class="inline"
                                data-confirm="Delete this record?">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 hover:underline text-xs font-medium">Delete</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-4 py-8 text-center text-gray-500">No salary records found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-6">
        {{ $salaries->links() }}
    </div>

@endsection