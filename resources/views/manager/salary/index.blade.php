@extends('manager.layout.master')
@section('title', 'Salary Management')

@section('page-content')
    <x-page.container>
        <x-page.header
            title="Salary Management"
            description="Review staff payroll disbursements and maintain salary records."
            eyebrow="People & payroll">
            <x-slot:actions>
                <a href="{{ route('manager.salary.create') }}" class="btn-primary">Record salary</a>
            </x-slot:actions>
        </x-page.header>

        <x-page.alerts />

        <div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-2">
        <x-page.card class="bg-white">
            <p class="text-xs text-gray-400">Total Paid</p>
            <p class="text-2xl font-display font-bold text-hut-green">Rs. {{ number_format($summary['total_paid']) }}</p>
        </x-page.card>
        <x-page.card class="bg-white">
            <p class="text-xs text-gray-400">This Month</p>
            <p class="text-2xl font-display font-bold text-hut-green">Rs. {{ number_format($summary['this_month']) }}</p>
        </x-page.card>
        </div>

        <x-page.card title="Salary records" description="Use the actions column to update or remove a payment.">
        <x-page.table>
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
                                class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-hut-green hover:bg-hut-green/10"
                                aria-label="Edit salary record" title="Edit salary record"><x-icons.edit class="h-4 w-4" /></a>
                            <form action="{{ route('manager.salary.destroy', $salary) }}" method="POST" class="inline"
                                data-confirm="Delete this record?">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-red-600 hover:bg-red-50"
                                    aria-label="Delete salary record" title="Delete salary record"><x-icons.trash class="h-4 w-4" /></button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5"><x-page.empty title="No salary records yet" description="Record the first staff payment to see it here." /></td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        </x-page.table>
        </x-page.card>

        <div class="mt-6">{{ $salaries->links() }}</div>
    </x-page.container>

@endsection