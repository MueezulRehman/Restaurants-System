@extends('layouts.admin')
@section('title', 'Loyalty')
@section('content')
    <div class="space-y-6">
        <div>
            <h1 class="text-2xl font-display font-bold text-hut-dark">Loyalty</h1>
            <p class="mt-1 text-sm text-gray-500">Track customer points and reward balances.</p>
        </div>
        <section class="rounded-xl border border-gray-100 bg-white p-5 shadow-sm">
            <h2 class="font-semibold">Add points</h2>
            <form method="POST" action="{{ route('manager.loyalty.store') }}" class="mt-4 grid gap-3 md:grid-cols-3">
                @csrf<select name="customer_id" required class="rounded-lg border px-3 py-2 text-sm">
                    <option value="">Customer</option>@foreach($customers as $customer)
                    <option value="{{ $customer->id }}">{{ $customer->name }}</option>@endforeach
                </select><input name="points" required type="number" min="0" step="1" placeholder="Points"
                    class="rounded-lg border px-3 py-2 text-sm"><button
                    class="rounded-lg bg-hut-dark px-4 py-2 text-sm font-semibold text-white">Add points</button></form>
        </section>
        <section class="overflow-hidden rounded-xl border border-gray-100 bg-white shadow-sm">
            <table class="min-w-full text-left text-sm">
                <thead class="bg-gray-50 text-xs uppercase text-gray-500">
                    <tr>
                        <th class="px-4 py-3">Customer</th>
                        <th class="px-4 py-3">Points</th>
                        <th class="px-4 py-3">Redeem</th>
                    </tr>
                </thead>
                <tbody class="divide-y">@forelse($accounts as $account)
                    <tr>
                        <td class="px-4 py-3">{{ $account->customer?->name ?? 'Customer' }}</td>
                        <td class="px-4 py-3 font-semibold">{{ $account->points }}</td>
                        <td class="px-4 py-3">
                            <form method="POST" action="{{ route('manager.loyalty.redeem', $account) }}" class="flex gap-2">
                                @csrf<input name="points" required type="number" min="1" max="{{ $account->points }}"
                                    placeholder="Points" class="w-24 rounded border px-2 py-1 text-xs"><button
                                    class="text-xs font-semibold text-hut-blue">Redeem</button></form>
                        </td>
                </tr>@empty<tr>
                        <td colspan="3" class="px-4 py-10 text-center text-gray-500">No loyalty accounts yet.</td>
                    </tr>@endforelse
                </tbody>
            </table>
        </section>
    </div>
@endsection