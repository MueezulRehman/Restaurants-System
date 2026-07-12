{{-- @extends('layouts.admin')
@section('title', 'Medicines')

@section('content')
<div class="bg-white rounded-xl p-4">
    <div class="flex justify-between items-center mb-4">
        <h1 class="font-display font-bold">Medicines</h1>
        <a href="{{ route('manager.medicines.create') }}" class="btn">Add Medicine</a>
    </div>

    @if($medicines->isEmpty())
        <div class="rounded-xl border border-dashed border-gray-300 bg-gray-50 p-8 text-center text-sm text-gray-500">
            No medicines found. Add a medicine to begin tracking batches, stock, and prescription data.
        </div>
    @else
        <div class="grid gap-4 sm:grid-cols-2">
            @foreach($medicines as $m)
                <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <h2 class="text-lg font-semibold text-hut-dark">{{ $m->name }}</h2>
                            <p class="text-sm text-gray-500">{{ $m->generic_name ?: 'No generic available' }}</p>
                        </div>
                        <div class="text-right">
                            <span class="inline-flex rounded-full bg-hut-yellow/10 px-3 py-1 text-xs font-semibold text-hut-dark">{{ $m->track_stock ? 'Stocked' : 'Non-stock' }}</span>
                        </div>
                    </div>

                    <div class="mt-4 grid gap-3 sm:grid-cols-2">
                        <div class="rounded-2xl bg-gray-50 p-3">
                            <p class="text-xs uppercase tracking-wide text-gray-500">Dosage</p>
                            <p class="mt-1 text-sm text-hut-dark">{{ $m->dosage_form ?? 'N/A' }}</p>
                        </div>
                        <div class="rounded-2xl bg-gray-50 p-3">
                            <p class="text-xs uppercase tracking-wide text-gray-500">Strength</p>
                            <p class="mt-1 text-sm text-hut-dark">{{ $m->strength ?? 'N/A' }}</p>
                        </div>
                        <div class="rounded-2xl bg-gray-50 p-3">
                            <p class="text-xs uppercase tracking-wide text-gray-500">SKU</p>
                            <p class="mt-1 text-sm text-hut-dark">{{ $m->sku ?? '—' }}</p>
                        </div>
                        <div class="rounded-2xl bg-gray-50 p-3">
                            <p class="text-xs uppercase tracking-wide text-gray-500">Barcode</p>
                            <p class="mt-1 text-sm text-hut-dark">{{ $m->barcode ?? '—' }}</p>
                        </div>
                    </div>

                    <div class="mt-4 grid gap-3 sm:grid-cols-3">
                        <div class="rounded-2xl bg-gray-50 p-3">
                            <p class="text-xs uppercase tracking-wide text-gray-500">Stock</p>
                            <p class="mt-1 text-sm font-semibold text-hut-dark">{{ $m->batches->sum('quantity') }}</p>
                        </div>
                        <div class="rounded-2xl bg-gray-50 p-3">
                            <p class="text-xs uppercase tracking-wide text-gray-500">Batches</p>
                            <p class="mt-1 text-sm font-semibold text-hut-dark">{{ $m->batches->count() }}</p>
                        </div>
                        <div class="rounded-2xl bg-gray-50 p-3">
                            <p class="text-xs uppercase tracking-wide text-gray-500">Min stock</p>
                            <p class="mt-1 text-sm font-semibold text-hut-dark">{{ $m->min_stock }}</p>
                        </div>
                    </div>

                    <div class="mt-4 flex flex-wrap gap-2 text-sm">
                        @if($m->requires_prescription)
                            <span class="rounded-full bg-red-50 px-3 py-1 font-medium text-red-700">Prescription required</span>
                        @endif
                        @if($m->tax !== null)
                            <span class="rounded-full bg-gray-100 px-3 py-1 text-gray-700">Tax {{ number_format($m->tax, 2) }}%</span>
                        @endif
                    </div>

                    @if($m->description)
                        <p class="mt-4 text-sm leading-6 text-gray-600">{{ \Illuminate\Support\Str::limit($m->description, 150) }}</p>
                    @endif

                    <div class="mt-5 flex flex-wrap items-center gap-3 border-t border-gray-100 pt-4">
                        <a href="{{ route('manager.medicines.edit', $m) }}" class="text-hut-yellow font-semibold hover:underline">Edit</a>
                        <form action="{{ route('manager.medicines.destroy', $m) }}" method="POST" class="inline-block">
                            @csrf
                            @method('DELETE')
                            <button class="text-hut-red hover:underline" onclick="return confirm('Delete this medicine?')">Delete</button>
                        </form>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="mt-6">{{ $medicines->links() }}</div>
    @endif
</div>
@endsection --}}
@extends('layouts.admin')
@section('title', 'Medicines')

@section('content')
<div class="bg-white rounded-xl border border-gray-200 shadow-sm">

    {{-- Header --}}
    <div class="flex items-center justify-between gap-4 border-b border-gray-100 px-5 py-4">
        <div>
            <h1 class="text-base font-display font-bold text-hut-dark">Medicines</h1>
            <p class="text-xs text-gray-500">{{ $medicines->total() ?? $medicines->count() }} medicines in inventory</p>
        </div>
        <a href="{{ route('manager.medicines.create') }}"
           class="btn inline-flex items-center gap-1.5 rounded-lg bg-hut-yellow px-3.5 py-2 text-xs font-semibold text-hut-dark hover:bg-hut-yellow/90">
            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
            </svg>
            Add Medicine
        </a>
    </div>

    @if($medicines->isEmpty())
        <div class="m-5 rounded-xl border border-dashed border-gray-300 bg-gray-50 p-8 text-center text-sm text-gray-500">
            No medicines found. Add a medicine to begin tracking batches, stock, and prescription data.
        </div>
    @else
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="border-b border-gray-100 bg-gray-50/70 text-[11px] uppercase tracking-wide text-gray-500">
                        <th class="px-5 py-2.5 font-medium">Medicine</th>
                        <th class="px-3 py-2.5 font-medium">SKU</th>
                        <th class="px-3 py-2.5 font-medium">Dosage</th>
                        <th class="px-3 py-2.5 font-medium text-right">Stock</th>
                        <th class="px-3 py-2.5 font-medium text-right">Min</th>
                        <th class="px-3 py-2.5 font-medium">Tax</th>
                        <th class="px-3 py-2.5 font-medium">Flags</th>
                        <th class="px-5 py-2.5 font-medium text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($medicines as $m)
                        @php
                            $stock = $m->batches->sum('quantity');
                            $isLow = $m->track_stock && $stock <= $m->min_stock;
                        @endphp
                        <tr class="hover:bg-gray-50/60 transition-colors">
                            <td class="px-5 py-2.5">
                                <p class="font-semibold text-hut-dark leading-tight">{{ $m->name }}</p>
                                <p class="text-[11px] text-gray-500 leading-tight">{{ $m->generic_name ?: 'No generic available' }}</p>
                            </td>
                            <td class="px-3 py-2.5 text-gray-600">{{ $m->sku ?? '—' }}</td>
                            <td class="px-3 py-2.5 text-gray-600">
                                {{ $m->dosage_form ?? 'N/A' }}
                                @if($m->strength)
                                    <span class="text-gray-400">· {{ $m->strength }}</span>
                                @endif
                            </td>
                            <td class="px-3 py-2.5 text-right font-medium {{ $isLow ? 'text-hut-red' : 'text-hut-dark' }}">
                                {{ $stock }}
                            </td>
                            <td class="px-3 py-2.5 text-right text-gray-500">{{ $m->min_stock }}</td>
                            <td class="px-3 py-2.5 text-gray-600">
                                {{ $m->tax !== null ? number_format($m->tax, 1).'%' : '—' }}
                            </td>
                            <td class="px-3 py-2.5">
                                <div class="flex flex-wrap gap-1">
                                    @if($m->requires_prescription)
                                        <span class="rounded-full bg-red-50 px-2 py-0.5 text-[10px] font-medium text-red-700">Rx</span>
                                    @endif
                                    <span class="rounded-full {{ $m->track_stock ? 'bg-hut-yellow/10 text-hut-dark' : 'bg-gray-100 text-gray-500' }} px-2 py-0.5 text-[10px] font-medium">
                                        {{ $m->track_stock ? 'Stocked' : 'Non-stock' }}
                                    </span>
                                    @if($isLow)
                                        <span class="rounded-full bg-red-50 px-2 py-0.5 text-[10px] font-medium text-red-700">Low</span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-5 py-2.5 text-right">
                                <div class="flex items-center justify-end gap-3">
                                    <a href="{{ route('manager.medicines.edit', $m) }}"
                                       class="text-hut-yellow font-semibold hover:underline">Edit</a>
                                    <form action="{{ route('manager.medicines.destroy', $m) }}" method="POST" class="inline-block">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                class="text-hut-red hover:underline"
                                                onclick="return confirm('Delete this medicine?')">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="border-t border-gray-100 px-5 py-3">{{ $medicines->links() }}</div>
    @endif
</div>
@endsection
