@extends('manager.layout.master')
@section('title', 'Commissions')
@section('page-content')
    <div class="mb-6">
        <h1 class="text-2xl font-display font-bold text-hut-dark">Staff commissions</h1>
        <p class="mt-1 text-sm text-gray-500">Set commission rules and track earnings from completed appointments.</p>
    </div>
    <section class="mb-6 rounded-xl border border-gray-100 bg-white p-5 shadow-sm">
        <h2 class="font-semibold text-hut-dark">Commission rule</h2>
        <form method="POST" action="{{ route('manager.commissions.rules.store') }}" class="mt-4 grid gap-3 sm:grid-cols-4">
            @csrf
            <select name="staff_id" required class="rounded-lg border border-gray-200 px-3 py-2 text-sm">
                <option value="">Select staff</option>@foreach($staff as $member)
                <option value="{{ $member->id }}">{{ $member->name }}</option>@endforeach
            </select>
            <select name="type" class="rounded-lg border border-gray-200 px-3 py-2 text-sm">
                <option value="percent">Percent of service</option>
                <option value="fixed">Fixed amount</option>
            </select>
            <input name="value" type="number" min="0.01" step="0.01" required placeholder="Value"
                class="rounded-lg border border-gray-200 px-3 py-2 text-sm">
            <button class="rounded-lg bg-hut-dark px-4 py-2 text-sm font-semibold text-white">Save rule</button>
        </form>
        @if($rules->isNotEmpty())
            <div class="mt-4 flex flex-wrap gap-2">@foreach($rules as $rule)<span
                class="rounded-full bg-gray-100 px-3 py-1 text-xs text-gray-700">{{ $rule->staff->name }}:
            {{ $rule->type === 'percent' ? $rule->value . '%' : 'Rs. ' . number_format($rule->value, 2) }}</span>@endforeach
        </div>@endif
    </section>
    <section class="overflow-hidden rounded-xl border border-gray-100 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="min-w-full text-left text-sm">
                <thead class="bg-gray-50 text-xs uppercase text-gray-500">
                    <tr>
                        <th class="px-4 py-3">Staff</th>
                        <th class="px-4 py-3">Appointment</th>
                        <th class="px-4 py-3">Service total</th>
                        <th class="px-4 py-3">Commission</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($earnings as $earning)
                        <tr>
                            <td class="px-4 py-3 font-medium">{{ $earning->staff->name }}</td>
                            <td class="px-4 py-3">{{ $earning->appointment->service_name }}
                                <div class="text-xs text-gray-500">{{ $earning->appointment->customer->name }}</div>
                            </td>
                            <td class="px-4 py-3">Rs. {{ number_format($earning->base_amount, 2) }}</td>
                            <td class="px-4 py-3 font-semibold">Rs. {{ number_format($earning->commission_amount, 2) }}</td>
                            <td class="px-4 py-3">{{ ucfirst($earning->status) }}</td>
                            <td class="px-4 py-3">@if($earning->status === 'pending')
                                <form method="POST" action="{{ route('manager.commissions.pay', $earning) }}">@csrf<button
                                        class="text-xs font-semibold text-hut-dark hover:underline">Mark paid</button></form>
                            @else<span class="text-xs text-gray-500">{{ $earning->paid_at?->format('d M Y') }}</span>@endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-10 text-center text-gray-500">No commission earnings yet. Complete an
                                assigned appointment to generate one.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="border-t border-gray-100 px-5 py-4">{{ $earnings->links() }}</div>
    </section>
@endsection