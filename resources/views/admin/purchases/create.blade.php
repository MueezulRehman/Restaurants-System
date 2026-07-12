@extends('layouts.admin')
@section('title', 'Record Purchase')

@section('content')
<div class="max-w-4xl">
    <a href="{{ route('manager.purchases.index') }}" class="text-hut-green text-sm mb-4 inline-block hover:underline">← Back to Purchase Batches</a>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <h2 class="text-lg font-display font-bold text-hut-dark mb-6">Record Purchase / Batch Entry</h2>

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

        <form action="{{ route('manager.purchases.store') }}" method="POST" class="space-y-6">
            @csrf

            <div class="grid gap-4 md:grid-cols-2">
                <div>
                    <label class="block text-sm font-medium text-hut-dark mb-1">Supplier</label>
                    <input type="text" name="supplier_name" value="{{ old('supplier_name') }}" class="w-full rounded-lg border border-gray-200 px-3 py-2 focus:outline-none focus:border-hut-green" placeholder="Supplier name or vendor">
                </div>
                <div>
                    <label class="block text-sm font-medium text-hut-dark mb-1">Invoice / GRN No.</label>
                    <input type="text" name="invoice_no" value="{{ old('invoice_no') }}" class="w-full rounded-lg border border-gray-200 px-3 py-2 focus:outline-none focus:border-hut-green" placeholder="Invoice or document number">
                </div>
                <div>
                    <label class="block text-sm font-medium text-hut-dark mb-1">Purchase Date</label>
                    <input type="date" name="purchase_date" value="{{ old('purchase_date') }}" class="w-full rounded-lg border border-gray-200 px-3 py-2 focus:outline-none focus:border-hut-green">
                </div>
                <div>
                    <label class="block text-sm font-medium text-hut-dark mb-1">Medicine *</label>
                    <select name="medicine_id" required class="w-full rounded-lg border border-gray-200 px-3 py-2 focus:outline-none focus:border-hut-green">
                        <option value="">Select medicine</option>
                        @foreach($medicines as $m)
                            <option value="{{ $m->id }}" {{ old('medicine_id') == $m->id ? 'selected' : '' }}>{{ $m->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-hut-dark mb-1">Batch Number</label>
                    <input type="text" name="batch_number" value="{{ old('batch_number') }}" class="w-full rounded-lg border border-gray-200 px-3 py-2 focus:outline-none focus:border-hut-green" placeholder="Batch ID or lot number">
                </div>
                <div>
                    <label class="block text-sm font-medium text-hut-dark mb-1">Manufacture Date</label>
                    <input type="date" name="mfg_date" value="{{ old('mfg_date') }}" class="w-full rounded-lg border border-gray-200 px-3 py-2 focus:outline-none focus:border-hut-green">
                </div>
                <div>
                    <label class="block text-sm font-medium text-hut-dark mb-1">Expiry Date</label>
                    <input type="date" name="expiry_date" value="{{ old('expiry_date') }}" class="w-full rounded-lg border border-gray-200 px-3 py-2 focus:outline-none focus:border-hut-green">
                </div>
                <div>
                    <label class="block text-sm font-medium text-hut-dark mb-1">Purchase Price</label>
                    <input type="number" name="purchase_price" value="{{ old('purchase_price') }}" step="0.01" min="0" required class="w-full rounded-lg border border-gray-200 px-3 py-2 focus:outline-none focus:border-hut-green" placeholder="Cost price">
                </div>
                <div>
                    <label class="block text-sm font-medium text-hut-dark mb-1">Selling Price</label>
                    <input type="number" name="selling_price" value="{{ old('selling_price') }}" step="0.01" min="0" required class="w-full rounded-lg border border-gray-200 px-3 py-2 focus:outline-none focus:border-hut-green" placeholder="Retail price">
                </div>
                <div>
                    <label class="block text-sm font-medium text-hut-dark mb-1">Quantity</label>
                    <input type="number" name="quantity" value="{{ old('quantity') }}" min="1" required class="w-full rounded-lg border border-gray-200 px-3 py-2 focus:outline-none focus:border-hut-green">
                </div>
                <div>
                    <label class="block text-sm font-medium text-hut-dark mb-1">Rack / Shelf</label>
                    <input type="text" name="rack_number" value="{{ old('rack_number') }}" class="w-full rounded-lg border border-gray-200 px-3 py-2 focus:outline-none focus:border-hut-green" placeholder="Storage location">
                </div>
            </div>

            <div class="flex gap-3 pt-3">
                <button type="submit" class="bg-hut-green text-white px-6 py-2 rounded-lg font-medium hover:bg-hut-green/90">Record Batch</button>
                <a href="{{ route('manager.purchases.index') }}" class="border border-gray-200 text-hut-dark px-6 py-2 rounded-lg font-medium hover:bg-gray-50">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
