@extends('layouts.admin')

@section('title', 'Stock Adjustment History')

@section('content')
    <div class="space-y-6">
        <x-back-link href="{{ route('manager.stock.index') }}" label="Back to Stock" />
        {{-- <a href="{{ route('manager.stock.index') }}" class="text-sm font-medium text-hut-green hover:text-hut-dark">
            Back to stock
        </a> --}}
        <div class="flex items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-display font-bold text-hut-dark">Stock Adjustment History</h1>
                <p class="mt-1 text-sm text-gray-500">Review stock changes recorded for this business.</p>
            </div>

        </div>

        <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead
                        class="border-b border-gray-200 bg-gray-50 text-left text-xs uppercase tracking-wide text-gray-500">
                        <tr>
                            <th class="px-5 py-3">Date</th>
                            <th class="px-5 py-3">Item</th>
                            <th class="px-5 py-3">SKU</th>
                            <th class="px-5 py-3">Stock</th>
                            <th class="px-5 py-3">Change</th>
                            <th class="px-5 py-3">Reason</th>
                            <th class="px-5 py-3">Recorded by</th>
                            <th class="px-5 py-3 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($adjustments as $adjustment)
                            <tr class="hover:bg-gray-50">
                                <td class="whitespace-nowrap px-5 py-3 text-gray-600">
                                    {{ $adjustment->created_at->format('M d, Y H:i') }}
                                </td>
                                <td class="px-5 py-3 font-medium text-hut-dark">
                                    @if($adjustment->medicineBatch)
                                        {{ $adjustment->medicineBatch->medicine->name ?? 'Medicine' }} /
                                        {{ $adjustment->medicineBatch->batch_number ?? 'Batch' }}
                                    @elseif($adjustment->variant)
                                        {{ $adjustment->variant->menuItem->name ?? 'Item' }} /
                                        {{ $adjustment->variant->variant_name }}
                                    @elseif($adjustment->menuItem)
                                        {{ $adjustment->menuItem->name }}
                                    @else
                                        {{ $adjustment->notes ?: 'Stock adjustment' }}
                                    @endif
                                </td>
                                <td class="px-5 py-3 text-gray-600">
                                    @if($adjustment->medicineBatch)
                                        {{ $adjustment->medicineBatch->medicine->sku ?? '—' }}
                                    @elseif($adjustment->variant)
                                        {{ $adjustment->variant->sku ?? '—' }}
                                    @elseif($adjustment->menuItem)
                                        {{ $adjustment->menuItem->sku ?? '—' }}
                                    @else
                                        —
                                    @endif
                                </td>
                                <td class="px-5 py-3 font-semibold text-hut-dark">{{ $adjustment->quantity_after }}</td>
                                <td
                                    class="px-5 py-3 font-semibold {{ $adjustment->change_quantity >= 0 ? 'text-green-700' : 'text-red-700' }}">
                                    {{ $adjustment->change_quantity > 0 ? '+' : '' }}{{ $adjustment->change_quantity }}
                                </td>
                                <td class="px-5 py-3 text-gray-600">{{ $adjustment->getReasonLabel() }}</td>
                                <td class="px-5 py-3 text-gray-600">{{ $actors[$adjustment->user_id] ?? 'Unknown user' }}</td>
                                <td class="px-5 py-3 text-right">
                                    <a href="{{ route('manager.stock.adjustments.edit', $adjustment) }}"
                                        class="group relative inline-flex h-8 w-8 items-center justify-center rounded-lg border border-emerald-100 bg-emerald-50 text-hut-green transition hover:-translate-y-0.5 hover:bg-emerald-100"
                                        aria-label="Edit stock adjustment" title="Edit stock adjustment">
                                        <x-icons.edit class="h-4 w-4" />
                                        <span
                                            class="pointer-events-none absolute bottom-full right-0 mb-2 whitespace-nowrap rounded-md bg-hut-dark px-2 py-1 text-[11px] font-medium text-white opacity-0 shadow-lg transition group-hover:opacity-100">Edit</span>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-5 py-10 text-center text-gray-500">No stock adjustments recorded yet.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <x-pagination :paginator="$adjustments" />
    </div>
@endsection