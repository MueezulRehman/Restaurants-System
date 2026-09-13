@extends('manager.layout.master')
@section('title', 'Receive Stock')
@section('page-content')
        <div class="mx-auto max-w-3xl">
                <h1 class="text-2xl font-display font-bold text-hut-dark">Receive stock</h1>
                <p class="mt-1 text-sm text-gray-500">Add purchased mobile, accessory, clothing, or other retail stock.</p>
                <form method="POST" action="{{ route('manager.purchasing.store') }}"
                        class="mt-6 space-y-5 rounded-xl border border-gray-100 bg-white p-6 shadow-sm">@csrf
                        <div class="grid gap-4 sm:grid-cols-2">
                                <div><label class="mb-1 block text-sm font-medium">Supplier</label><select name="supplier_id"
                                                class="w-full rounded-lg border border-gray-200 px-3 py-2">
                                                <option value="">Select supplier</option>@foreach($suppliers as $supplier)
                                                <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>@endforeach
                                        </select></div>
                                <div><label class="mb-1 block text-sm font-medium">Supplier name</label><input
                                                name="supplier_name" class="w-full rounded-lg border border-gray-200 px-3 py-2"
                                                placeholder="Optional new supplier">
                                </div>
                                <div><label class="mb-1 block text-sm font-medium">Invoice number</label><input
                                                name="invoice_no" class="w-full rounded-lg border border-gray-200 px-3 py-2">
                                </div>
                                <div><label class="mb-1 block text-sm font-medium">Purchase date</label><input type="date"
                                                name="purchase_date" required value="{{ now()->toDateString() }}"
                                                class="w-full rounded-lg border border-gray-200 px-3 py-2"></div>
                        </div>
                        <div><label class="mb-1 block text-sm font-medium">Product</label><select name="menu_item_id" required
                                        class="w-full rounded-lg border border-gray-200 px-3 py-2">
                                        <option value="">Select product</option>@foreach($items as $item)
                                                <option value="{{ $item->id }}">{{ $item->name }} ({{ $item->sku ?? 'no SKU' }})
                                        </option>@endforeach
                                </select></div>
                        <div><label class="mb-1 block text-sm font-medium">Variant ID (optional)</label><input type="number"
                                        name="product_variant_id" class="w-full rounded-lg border border-gray-200 px-3 py-2"
                                        placeholder="Use the variant ID for size/color/model stock"></div>
                        <div class="grid gap-4 sm:grid-cols-3">
                                <div><label class="mb-1 block text-sm font-medium">Quantity</label><input type="number"
                                                name="quantity" min="0.001" step="0.001" required
                                                class="w-full rounded-lg border border-gray-200 px-3 py-2"></div>
                                <div><label class="mb-1 block text-sm font-medium">Purchase price</label><input type="number"
                                                name="purchase_price" min="0" step="0.01" required
                                                class="w-full rounded-lg border border-gray-200 px-3 py-2"></div>
                                <div><label class="mb-1 block text-sm font-medium">Selling price</label><input type="number"
                                                name="selling_price" min="0" step="0.01"
                                                class="w-full rounded-lg border border-gray-200 px-3 py-2">
                                </div>
                        </div>
                        <div><label class="mb-1 block text-sm font-medium">Expiry date</label><input type="date"
                                        name="expiry_date" class="w-full rounded-lg border border-gray-200 px-3 py-2">
                                <p class="mt-1 text-xs text-gray-500">Optional. Add this for grocery, food, or other
                                        date-sensitive stock.</p>
                        </div>
                        <div><label class="mb-1 block text-sm font-medium">Notes</label><textarea name="notes" rows="3"
                                        class="w-full rounded-lg border border-gray-200 px-3 py-2"></textarea></div><button
                                class="rounded-lg bg-hut-dark px-5 py-2.5 text-sm font-semibold text-white">Receive
                                stock</button>
                </form>
        </div>
@endsection