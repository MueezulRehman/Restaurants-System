@extends('layouts.admin')

@section('title', 'Suppliers')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-semibold text-hut-dark">Suppliers</h2>
            <p class="text-sm text-gray-500">Manage medicine suppliers and vendors</p>
        </div>
        <a href="{{ route('manager.suppliers.create') }}" class="px-4 py-2 bg-hut-green text-white rounded-lg hover:bg-hut-green/90 font-medium">
            ➕ Add Supplier
        </a>
    </div>

    @if($suppliers->isEmpty())
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-12 text-center">
            <p class="text-gray-500 mb-4">No suppliers added yet.</p>
            <a href="{{ route('manager.suppliers.create') }}" class="text-hut-green hover:underline font-medium">
                Add your first supplier
            </a>
        </div>
    @else
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
            <table class="w-full divide-y divide-gray-100">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase text-gray-600">Name</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase text-gray-600">Contact</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase text-gray-600">Phone</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase text-gray-600">Payment Terms</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase text-gray-600">Status</th>
                        <th class="px-6 py-3 text-right text-xs font-semibold uppercase text-gray-600">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($suppliers as $supplier)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-3 font-medium text-hut-dark">{{ $supplier->name }}</td>
                            <td class="px-6 py-3 text-sm text-gray-600">
                                {{ $supplier->contact_person }}
                                @if($supplier->email)
                                    <br><a href="mailto:{{ $supplier->email }}" class="text-hut-green hover:underline">{{ $supplier->email }}</a>
                                @endif
                            </td>
                            <td class="px-6 py-3 text-sm text-gray-600">{{ $supplier->phone }}</td>
                            <td class="px-6 py-3 text-sm text-gray-600">
                                <span class="inline-block px-2 py-1 rounded text-xs font-medium
                                    @if($supplier->payment_terms === 'cash') bg-red-100 text-red-800
                                    @elseif($supplier->payment_terms === 'credit_7') bg-orange-100 text-orange-800
                                    @else bg-blue-100 text-blue-800 @endif">
                                    {{ ucfirst(str_replace('_', ' ', $supplier->payment_terms)) }}
                                </span>
                            </td>
                            <td class="px-6 py-3 text-sm">
                                <span class="inline-block px-2 py-1 rounded text-xs font-medium
                                    {{ $supplier->is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-600' }}">
                                    {{ $supplier->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td class="px-6 py-3 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('manager.suppliers.edit', $supplier) }}" class="text-hut-green hover:text-hut-green/80">✏️ Edit</a>
                                    <form action="{{ route('manager.suppliers.destroy', $supplier) }}" method="POST" class="inline" onsubmit="return confirm('Delete this supplier?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:text-red-800">🗑️ Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $suppliers->links() }}
        </div>
    @endif
</div>
@endsection
