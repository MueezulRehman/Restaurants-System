@extends('manager.layout.master')
@section('title', 'Insurance')
@section('page-content')
    <div class="space-y-6">
        <div>
            <h1 class="text-2xl font-semibold text-hut-dark">Insurance claims</h1>
            <p class="text-sm text-gray-500">Maintain providers and track patient claim status.</p>
        </div>@if(session('success'))
        <div class="rounded-lg bg-green-50 px-4 py-3 text-sm text-green-700">{{ session('success') }}</div>@endif<div
            class="grid gap-6 lg:grid-cols-2">
            <form method="POST" action="{{ route('manager.insurance.providers.store') }}"
                class="rounded-2xl border border-gray-200 bg-white p-4 shadow-sm">@csrf<h2 class="mb-3 font-semibold">Add
                    provider</h2><input name="name" required placeholder="Provider name"
                    class="mb-3 w-full rounded-lg border-gray-300"><input name="phone" placeholder="Phone"
                    class="mb-3 w-full rounded-lg border-gray-300"><input name="email" type="email" placeholder="Email"
                    class="mb-3 w-full rounded-lg border-gray-300"><button
                    class="rounded-lg bg-hut-dark px-4 py-2 text-sm font-semibold text-white">Save provider</button></form>
            <form method="POST" action="{{ route('manager.insurance.claims.store') }}"
                class="rounded-2xl border border-gray-200 bg-white p-4 shadow-sm">@csrf<h2 class="mb-3 font-semibold">Record
                    claim</h2><select name="provider_id" class="mb-3 w-full rounded-lg border-gray-300">
                    <option value="">Provider</option>@foreach($providers as $provider)
                    <option value="{{ $provider->id }}">{{ $provider->name }}</option>@endforeach
                </select><select name="customer_id" class="mb-3 w-full rounded-lg border-gray-300">
                    <option value="">Patient / customer</option>@foreach($customers as $customer)
                    <option value="{{ $customer->id }}">{{ $customer->name }}</option>@endforeach
                </select>
                <div class="grid grid-cols-2 gap-3"><input name="policy_number" placeholder="Policy number"
                        class="rounded-lg border-gray-300"><input name="claim_number" placeholder="Claim number"
                        class="rounded-lg border-gray-300"><input name="claimed_amount" type="number" step="0.01" min="0"
                        required placeholder="Claim amount" class="rounded-lg border-gray-300"><select name="status"
                        class="rounded-lg border-gray-300">
                        <option value="draft">Draft</option>
                        <option value="submitted">Submitted</option>
                        <option value="approved">Approved</option>
                        <option value="rejected">Rejected</option>
                        <option value="paid">Paid</option>
                    </select></div><textarea name="notes" placeholder="Notes"
                    class="my-3 w-full rounded-lg border-gray-300"></textarea><button
                    class="rounded-lg bg-hut-dark px-4 py-2 text-sm font-semibold text-white">Save claim</button>
            </form>
        </div>
        <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs uppercase text-gray-500">Claim</th>
                        <th class="px-4 py-3 text-left text-xs uppercase text-gray-500">Customer</th>
                        <th class="px-4 py-3 text-left text-xs uppercase text-gray-500">Provider</th>
                        <th class="px-4 py-3 text-left text-xs uppercase text-gray-500">Amount</th>
                        <th class="px-4 py-3 text-left text-xs uppercase text-gray-500">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">@forelse($claims as $claim)
                    <tr>
                        <td class="px-4 py-3">{{ $claim->claim_number ?: 'Unnumbered' }}
                            <div class="text-xs text-gray-500">{{ $claim->policy_number }}</div>
                        </td>
                        <td class="px-4 py-3">{{ $claim->customer?->name ?: 'Not linked' }}</td>
                        <td class="px-4 py-3">{{ $claim->provider?->name ?: 'Not linked' }}</td>
                        <td class="px-4 py-3">{{ number_format($claim->claimed_amount, 2) }}</td>
                        <td class="px-4 py-3">{{ ucfirst($claim->status) }}</td>
                </tr>@empty<tr>
                        <td colspan="5" class="px-4 py-8 text-center text-sm text-gray-500">No claims yet.</td>
                    </tr>@endforelse
                </tbody>
            </table>
            <div class="p-4">{{ $claims->links() }}</div>
        </div>
</div>@endsection