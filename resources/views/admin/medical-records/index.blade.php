@extends('layouts.admin')
@section('title', 'Medical Records')

@section('content')
<div class="max-w-6xl space-y-6">
    <a href="{{ route('manager.pos.index') }}" class="text-hut-green text-sm mb-4 inline-block hover:underline">← Back to POS</a>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <div class="mb-6">
            <h2 class="text-2xl font-semibold text-hut-dark">Medical Records</h2>
            <p class="text-sm text-gray-500">Track patient and medicine information for medical-store operations.</p>
        </div>

        @if ($errors->any())
            <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg mb-6 text-sm">
                <p class="font-medium mb-2">Please fix the following errors:</p>
                <ul class="list-disc list-inside space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if (session('success'))
            <div class="mb-6 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700">
                {{ session('success') }}
            </div>
        @endif

        <div class="grid gap-4 md:grid-cols-3 mb-6">
            <div class="rounded-xl border border-gray-200 bg-gray-50 p-4">
                <p class="text-xs uppercase tracking-wide text-gray-400">Today’s sales</p>
                <p class="text-2xl font-semibold text-hut-dark">Rs. {{ number_format((float) ($salesSummary->sales_today ?? 0), 0) }}</p>
            </div>
            <div class="rounded-xl border border-gray-200 bg-gray-50 p-4">
                <p class="text-xs uppercase tracking-wide text-gray-400">Orders today</p>
                <p class="text-2xl font-semibold text-hut-dark">{{ $salesSummary->orders_today ?? 0 }}</p>
            </div>
            <div class="rounded-xl border border-gray-200 bg-gray-50 p-4">
                <p class="text-xs uppercase tracking-wide text-gray-400">Sales records</p>
                <a href="{{ route('manager.orders.index') }}" class="text-sm font-medium text-hut-green hover:underline">Open orders list</a>
                <div class="mt-2 text-xs text-gray-500">Reports are also available from the main Reports menu.</div>
            </div>
        </div>

        <form action="{{ route('manager.medical-records.store') }}" method="POST" class="space-y-6 rounded-xl border border-gray-200 bg-gray-50 p-4">
            @csrf

            <div class="grid gap-4 md:grid-cols-2">
                <div>
                    <label class="block text-sm font-medium text-hut-dark mb-1">Patient Name</label>
                    <input type="text" name="patient_name" value="{{ old('patient_name') }}" class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:outline-none focus:border-hut-green" required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-hut-dark mb-1">Medicine Name</label>
                    <input type="text" name="medicine_name" value="{{ old('medicine_name') }}" class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:outline-none focus:border-hut-green" required>
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-hut-dark mb-1">Notes</label>
                <textarea name="notes" rows="4" class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:outline-none focus:border-hut-green">{{ old('notes') }}</textarea>
            </div>

            <div class="flex gap-3">
                <button type="submit" class="rounded-lg bg-hut-dark px-4 py-2 font-semibold text-white hover:bg-black/90">Save Record</button>
                <a href="{{ route('manager.pos.index') }}" class="inline-flex items-center rounded-lg border border-gray-200 px-4 py-2 text-sm font-medium text-hut-dark hover:bg-gray-50">Cancel</a>
            </div>
        </form>

        <div class="mt-6 rounded-xl border border-gray-200 bg-white p-4">
            <div class="mb-3 flex items-center justify-between">
                <h3 class="font-semibold text-hut-dark">Saved records</h3>
                <span class="text-sm text-gray-500">{{ $records->count() }} total</span>
            </div>

            @if($records->isEmpty())
                <p class="text-sm text-gray-500">No medical records yet. Save one above and it will appear here.</p>
            @else
                <div class="space-y-3">
                    @foreach($records as $record)
                        <div class="rounded-lg border border-gray-100 bg-gray-50 p-3">
                            <div class="flex items-center justify-between gap-3">
                                <div>
                                    <p class="font-medium text-hut-dark">{{ $record->patient_name }}</p>
                                    <p class="text-sm text-gray-500">Medicine: {{ $record->medicine_name }}</p>
                                </div>
                                <div class="text-right text-xs text-gray-400">
                                    <p>{{ $record->created_at->format('d M Y') }}</p>
                                    <p>{{ $record->created_at->format('h:i A') }}</p>
                                </div>
                            </div>
                            @if($record->notes)
                                <p class="mt-2 text-sm text-gray-600">{{ $record->notes }}</p>
                            @endif
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        <div class="mt-6 rounded-xl border border-gray-200 bg-white p-4">
            <h3 class="font-semibold text-hut-dark mb-3">Recent sales</h3>
            @if($recentOrders->isEmpty())
                <p class="text-sm text-gray-500">No sales have been recorded yet.</p>
            @else
                <div class="space-y-2">
                    @foreach($recentOrders as $order)
                        <div class="flex items-center justify-between rounded-lg border border-gray-100 px-3 py-2 text-sm">
                            <div>
                                <p class="font-medium text-hut-dark">#{{ $order->order_number ?? $order->id }} · {{ $order->customer_name }}</p>
                                <p class="text-gray-500">{{ $order->created_at->format('d M Y, h:i A') }}</p>
                            </div>
                            <div class="text-right">
                                <p class="font-semibold text-hut-dark">Rs. {{ number_format((float) $order->total, 0) }}</p>
                                <p class="text-gray-500">{{ ucfirst($order->status) }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
