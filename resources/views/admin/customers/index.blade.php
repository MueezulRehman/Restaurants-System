@extends('layouts.admin')

@section('title', 'Customers')

@section('content')
<div class="space-y-4">
    <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
        <div>
            <h2 class="text-xl font-semibold text-hut-dark">Customers</h2>
            <p class="text-sm text-gray-500">Customers who have ordered from this restaurant.</p>
        </div>
        <form method="GET" class="flex gap-2">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by name, phone, email" class="w-72 rounded-lg border border-gray-300 px-3 py-2 text-sm" />
            <button type="submit" class="rounded-lg bg-hut-dark px-4 py-2 text-sm font-semibold text-white hover:bg-gray-800">Search</button>
        </form>
    </div>

    <div class="rounded-2xl border border-gray-200 bg-linear-to-r from-hut-dark to-gray-800 p-4 text-white shadow-sm">
        <div class="flex flex-col gap-3 md:flex-row md:items-end md:justify-between">
            <div>
                <h3 class="text-base font-semibold">Register a new customer</h3>
                <p class="text-sm text-gray-200">Create a profile so POS sales, partial payments, and account balances stay organized.</p>
            </div>
            <form method="POST" action="{{ route('manager.customers.store') }}" class="grid gap-2 md:grid-cols-3">
                @csrf
                <input type="text" name="name" required placeholder="Customer name" class="rounded-lg border border-white/20 bg-white/90 px-3 py-2 text-sm text-gray-800" />
                <input type="text" name="phone" required placeholder="Phone" class="rounded-lg border border-white/20 bg-white/90 px-3 py-2 text-sm text-gray-800" />
                <input type="email" name="email" placeholder="Email (optional)" class="rounded-lg border border-white/20 bg-white/90 px-3 py-2 text-sm text-gray-800" />
                <button type="submit" class="md:col-span-3 rounded-lg bg-hut-yellow px-4 py-2 text-sm font-semibold text-hut-dark hover:bg-amber-400">Save customer</button>
            </form>
        </div>
    </div>

    <div class="rounded-2xl border border-gray-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left font-semibold text-gray-700">Name</th>
                        <th class="px-4 py-3 text-left font-semibold text-gray-700">Phone</th>
                        <th class="px-4 py-3 text-left font-semibold text-gray-700">Email</th>
                        <th class="px-4 py-3 text-left font-semibold text-gray-700">Balance</th>
                        <th class="px-4 py-3 text-left font-semibold text-gray-700">Reminder</th>
                        <th class="px-4 py-3 text-left font-semibold text-gray-700">Orders</th>
                        <th class="px-4 py-3 text-right font-semibold text-gray-700">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($customers as $customer)
                        <tr>
                            <td class="px-4 py-3 font-medium text-hut-dark">{{ $customer->name }}</td>
                            <td class="px-4 py-3 text-gray-600">{{ $customer->phone }}</td>
                            <td class="px-4 py-3 text-gray-600">{{ $customer->email ?: '—' }}</td>
                            <td class="px-4 py-3 {{ $customer->balance > 0 ? 'text-red-600 font-semibold' : 'text-gray-600' }}">
                                Rs. {{ number_format($customer->balance, 2) }}
                                @if($customer->balance > 0)
                                    <span class="ml-1 rounded-full bg-red-100 px-2 py-0.5 text-[10px] font-bold uppercase text-red-700">Due</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-gray-600">{{ $customer->last_reminder_at?->diffForHumans() ?? '—' }}</td>
                            <td class="px-4 py-3 text-gray-600">{{ $customer->orders_count }}</td>
                            <td class="px-4 py-3 text-right">
                                <a href="{{ route('manager.customers.show', $customer) }}" class="text-hut-yellow hover:text-amber-600">View</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-8 text-center text-gray-500">No customers found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="{{ $customers->hasPages() ? 'pt-2' : '' }}">
        {{ $customers->links() }}
    </div>
</div>
@endsection
