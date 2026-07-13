@extends('layouts.admin')

@section('title', 'Subscription Details')

@section('content')
<div class="max-w-6xl rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
    <div class="mb-6 flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
        <div>
            <h2 class="text-2xl font-semibold text-hut-dark">Subscription Details</h2>
            <p class="text-sm text-gray-500">Review billing status, latest invoices, and payment options for your restaurant.</p>
        </div>
        <a href="{{ route('manager.dashboard') }}" class="rounded-lg border border-gray-300 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">Back to dashboard</a>
    </div>

    <div class="grid gap-6 md:grid-cols-2 mb-8">
        <div class="rounded-2xl border border-gray-200 bg-gray-50 p-5">
            <h3 class="text-lg font-semibold text-hut-dark mb-3">Current Plan</h3>
            <p class="text-sm text-gray-600">{{ $subscription->plan?->name ?? 'No plan assigned' }}</p>
            <p class="mt-2 text-sm text-gray-500">Billing cycle: <strong>{{ ucfirst($subscription->billing_cycle) }}</strong></p>
            <p class="mt-1 text-sm text-gray-500">Subscription status: <strong>{{ ucfirst($subscription->status) }}</strong></p>
            @if($subscription->current_period_start)
                <p class="mt-1 text-sm text-gray-500">Current period: <strong>{{ $subscription->current_period_start->format('M d, Y') }}</strong> to <strong>{{ $subscription->current_period_end?->format('M d, Y') ?? 'N/A' }}</strong></p>
            @endif
            @if($subscription->trial_ends_at)
                <p class="mt-1 text-sm text-gray-500">Trial ends: <strong>{{ $subscription->trial_ends_at->format('M d, Y') }}</strong></p>
            @endif
        </div>

        <div class="rounded-2xl border border-gray-200 bg-gray-50 p-5">
            <h3 class="text-lg font-semibold text-hut-dark mb-3">Next Invoice</h3>
            <p class="text-sm text-gray-600">Amount due: <strong>{{ number_format($subscription->billing_cycle === 'yearly' ? $subscription->plan?->price_yearly : $subscription->plan?->price_monthly, 2) }}</strong></p>
            <p class="mt-2 text-sm text-gray-500">Payment method: <strong>{{ ucfirst($subscription->payment_method ?: 'manual') }}</strong></p>
            <p class="mt-2 text-sm text-gray-500">Auto-renew: <strong>{{ $subscription->auto_renew ? 'Enabled' : 'Disabled' }}</strong></p>
        </div>
    </div>

    <div class="grid gap-6 lg:grid-cols-2">
        <div class="rounded-2xl border border-gray-200 bg-white p-5">
            <h3 class="text-lg font-semibold text-hut-dark mb-4">Make a Payment</h3>
            <form action="{{ route('manager.subscription.pay') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="mb-2 block text-sm font-medium text-gray-700">Payment Method</label>
                    <select name="payment_method" required class="w-full rounded-lg border border-gray-300 px-3 py-2">
                        <option value="manual">Manual / Bank Transfer</option>
                        <option value="stripe">Stripe</option>
                        <option value="jazzcash">JazzCash</option>
                        <option value="easypaisa">EasyPaisa</option>
                    </select>
                </div>

                <div>
                    <label class="mb-2 block text-sm font-medium text-gray-700">Reference / Token</label>
                    <input type="text" name="payment_reference" value="{{ old('payment_reference') }}" placeholder="Bank transfer reference or gateway token" class="w-full rounded-lg border border-gray-300 px-3 py-2" />
                    <p class="text-xs text-gray-500 mt-2">Optional payment reference for manual transfers or gateway tokens.</p>
                </div>

                <button type="submit" class="rounded-lg bg-hut-dark px-4 py-2 text-sm font-semibold text-white hover:bg-gray-800">Pay Now</button>
            </form>

            @if(session('success'))
                <div class="mt-4 rounded-lg border border-green-200 bg-green-50 p-4 text-sm text-green-700">{{ session('success') }}</div>
            @endif
            @if(session('error'))
                <div class="mt-4 rounded-lg border border-red-200 bg-red-50 p-4 text-sm text-red-700">{{ session('error') }}</div>
            @endif
        </div>

        <div class="rounded-2xl border border-gray-200 bg-white p-5">
            <h3 class="text-lg font-semibold text-hut-dark mb-4">Billing History</h3>

            @if($billingCycles->isEmpty())
                <p class="text-sm text-gray-500">No billing cycles have been created yet.</p>
            @else
                <div class="space-y-3">
                    @foreach($billingCycles as $cycle)
                        <div class="rounded-2xl border border-gray-200 p-4">
                            <p class="text-sm text-gray-700">Invoice: <strong>{{ $cycle->invoice_number ?? 'Pending' }}</strong></p>
                            <p class="text-sm text-gray-500">Period: {{ $cycle->period_start->format('M d, Y') }} — {{ $cycle->period_end->format('M d, Y') }}</p>
                            <p class="text-sm text-gray-500">Amount: {{ number_format($cycle->amount, 2) }}</p>
                            <p class="text-sm text-gray-500">Status: <strong>{{ ucfirst($cycle->status) }}</strong></p>
                            @if($cycle->paid_at)
                                <p class="text-sm text-gray-500">Paid: {{ $cycle->paid_at->format('M d, Y') }}</p>
                            @endif
                        </div>
                    @endforeach
                </div>
                <div class="mt-4">{{ $billingCycles->links() }}</div>
            @endif
        </div>
    </div>
</div>
@endsection
