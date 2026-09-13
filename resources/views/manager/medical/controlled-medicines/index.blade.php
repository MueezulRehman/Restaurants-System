@extends('manager.layout.master')
@section('title', 'Controlled Medicines')
@section('page-content')
    <div class="space-y-6">
        <div>
            <h1 class="text-2xl font-semibold text-hut-dark">Controlled medicine log</h1>
            <p class="text-sm text-gray-500">Record each controlled dispensing event with a witness and prescription
                reference.</p>
        </div>@if(session('success'))
        <div class="rounded-lg bg-green-50 px-4 py-3 text-sm text-green-700">{{ session('success') }}</div>@endif<div
            class="rounded-2xl border border-gray-200 bg-white p-4 shadow-sm">
            <form method="POST" action="{{ route('manager.controlled-medicines.store') }}"
                class="grid gap-3 md:grid-cols-3">@csrf<select name="medicine_id" required
                    class="rounded-lg border-gray-300">
                    <option value="">Controlled medicine</option>@foreach($medicines as $medicine)
                    <option value="{{ $medicine->id }}">{{ $medicine->name }}</option>@endforeach
                </select><select name="customer_id" class="rounded-lg border-gray-300">
                    <option value="">Customer</option>@foreach($customers as $customer)
                    <option value="{{ $customer->id }}">{{ $customer->name }}</option>@endforeach
                </select><select name="prescription_id" class="rounded-lg border-gray-300">
                    <option value="">Prescription reference</option>@foreach($prescriptions as $prescription)
                    <option value="{{ $prescription->id }}">{{ $prescription->prescription_number }}</option>@endforeach
                </select><input name="quantity" type="number" step="0.001" min="0.001" required placeholder="Quantity"
                    class="rounded-lg border-gray-300"><input name="witness_name" required placeholder="Witness name"
                    class="rounded-lg border-gray-300"><input name="reason" required placeholder="Reason / notes"
                    class="rounded-lg border-gray-300"><button
                    class="rounded-lg bg-hut-dark px-4 py-2 text-sm font-semibold text-white md:col-span-3">Record
                    dispensing</button></form>
        </div>
        <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs uppercase text-gray-500">Medicine</th>
                        <th class="px-4 py-3 text-left text-xs uppercase text-gray-500">Customer</th>
                        <th class="px-4 py-3 text-left text-xs uppercase text-gray-500">Quantity</th>
                        <th class="px-4 py-3 text-left text-xs uppercase text-gray-500">Witness</th>
                        <th class="px-4 py-3 text-left text-xs uppercase text-gray-500">Dispensed</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">@forelse($logs as $log)
                    <tr>
                        <td class="px-4 py-3">{{ $log->medicine?->name }}</td>
                        <td class="px-4 py-3">{{ $log->customer?->name ?: 'Walk-in' }}</td>
                        <td class="px-4 py-3">{{ $log->quantity }}</td>
                        <td class="px-4 py-3">{{ $log->witness_name }}</td>
                        <td class="px-4 py-3 text-sm">{{ $log->dispensed_at?->format('M d, Y g:i A') }}</td>
                </tr>@empty<tr>
                        <td colspan="5" class="px-4 py-8 text-center text-sm text-gray-500">No controlled dispensing
                            records.</td>
                    </tr>@endforelse
                </tbody>
            </table>
            <div class="p-4">{{ $logs->links() }}</div>
        </div>
</div>@endsection