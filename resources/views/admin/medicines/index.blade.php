@extends('layouts.admin')
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
@endsection
