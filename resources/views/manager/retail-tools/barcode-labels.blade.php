@extends('manager.layout.master')
@section('title', 'Barcode Labels')
@section('page-content')
    <div class="mb-6 flex items-end justify-between">
        <div>
            <h1 class="text-2xl font-display font-bold text-hut-dark">Barcode Labels</h1>
            <p class="mt-1 text-sm text-gray-500">Print labels for products and variants.</p>
        </div><button onclick="window.print()"
            class="rounded-lg bg-hut-dark px-4 py-2 text-sm font-semibold text-white print:hidden">Print labels</button>
    </div>
    <form class="mb-6 print:hidden"><input name="q" value="{{ request('q') }}" placeholder="Search product, SKU, or barcode"
            class="w-full max-w-md rounded-lg border border-gray-200 px-3 py-2 text-sm"></form>
    <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-5">@forelse($labels as $label)
        <div class="barcode-label rounded border border-gray-300 bg-white p-3 text-center">
            <p class="truncate text-xs font-semibold">{{ $label['name'] }}</p>
            <div class="my-3 h-10 bg-[repeating-linear-gradient(90deg,#111_0_2px,transparent_2px_5px)]"></div>
            <p class="font-mono text-xs">{{ $label['code'] }}</p>
            <p class="mt-1 text-xs">Rs. {{ number_format((float) $label['price'], 2) }}</p>
    </div>@empty<div class="col-span-full rounded-lg bg-white p-8 text-center text-gray-500">No products found.</div>
        @endforelse
    </div>
    <style>
        @media print {

            .dashboard-sidebar,
            .dashboard-header,
            .print\:hidden {
                display: none !important
            }

            .dashboard-main {
                padding: 0 !important
            }

            .barcode-label {
                break-inside: avoid
            }
        }
    </style>
@endsection