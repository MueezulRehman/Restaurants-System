@extends('layouts.admin')

@section('title', $supplier->id ? 'Edit Supplier' : 'Add Supplier')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">
    <div>
        <h2 class="text-2xl font-semibold text-hut-dark">{{ $supplier->id ? 'Edit Supplier' : 'Add New Supplier' }}</h2>
        <p class="text-sm text-gray-500">Manage supplier information</p>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6">
        <form method="POST" action="{{ $supplier->id ? route('manager.suppliers.update', $supplier) : route('manager.suppliers.store') }}" class="space-y-4">
            @csrf
            @if($supplier->id) @method('PUT') @endif

            <div class="grid md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Supplier Name *</label>
                    <input type="text" name="name" value="{{ old('name', $supplier->name) }}" required class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-hut-green focus:border-transparent @error('name') border-red-500 @enderror">
                    @error('name') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Contact Person</label>
                    <input type="text" name="contact_person" value="{{ old('contact_person', $supplier->contact_person) }}" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-hut-green focus:border-transparent">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                    <input type="email" name="email" value="{{ old('email', $supplier->email) }}" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-hut-green focus:border-transparent @error('email') border-red-500 @enderror">
                    @error('email') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Phone *</label>
                    <input type="tel" name="phone" value="{{ old('phone', $supplier->phone) }}" required class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-hut-green focus:border-transparent @error('phone') border-red-500 @enderror">
                    @error('phone') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">City</label>
                    <input type="text" name="city" value="{{ old('city', $supplier->city) }}" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-hut-green focus:border-transparent">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">State</label>
                    <input type="text" name="state" value="{{ old('state', $supplier->state) }}" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-hut-green focus:border-transparent">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">GSTIN / Tax ID</label>
                    <input type="text" name="gstin" value="{{ old('gstin', $supplier->gstin) }}" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-hut-green focus:border-transparent">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Avg. Delivery Days</label>
                    <input type="number" name="average_delivery_days" value="{{ old('average_delivery_days', $supplier->average_delivery_days) }}" step="0.1" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-hut-green focus:border-transparent">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Payment Terms *</label>
                    <select name="payment_terms" required class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-hut-green focus:border-transparent">
                        <option value="cash" {{ old('payment_terms', $supplier->payment_terms) === 'cash' ? 'selected' : '' }}>Cash</option>
                        <option value="credit_7" {{ old('payment_terms', $supplier->payment_terms) === 'credit_7' ? 'selected' : '' }}>Credit 7 Days</option>
                        <option value="credit_14" {{ old('payment_terms', $supplier->payment_terms) === 'credit_14' ? 'selected' : '' }}>Credit 14 Days</option>
                        <option value="credit_30" {{ old('payment_terms', $supplier->payment_terms) === 'credit_30' ? 'selected' : '' }}>Credit 30 Days</option>
                        <option value="custom" {{ old('payment_terms', $supplier->payment_terms) === 'custom' ? 'selected' : '' }}>Custom</option>
                    </select>
                    @error('payment_terms') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="flex items-center gap-2 mt-6">
                        <input type="checkbox" name="is_active" value="1" {{ old('is_active', $supplier->is_active) ? 'checked' : '' }} class="rounded">
                        <span class="text-sm font-medium text-gray-700">Active</span>
                    </label>
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Address</label>
                <textarea name="address" rows="3" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-hut-green focus:border-transparent">{{ old('address', $supplier->address) }}</textarea>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                <textarea name="notes" rows="3" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-hut-green focus:border-transparent" placeholder="Additional information about this supplier...">{{ old('notes', $supplier->notes) }}</textarea>
            </div>

            <div class="flex gap-3 pt-4">
                <button type="submit" class="px-6 py-2 bg-hut-green text-white rounded-lg hover:bg-hut-green/90 font-medium">
                    {{ $supplier->id ? 'Update Supplier' : 'Create Supplier' }}
                </button>
                <a href="{{ route('manager.suppliers.index') }}" class="px-6 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 font-medium">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
