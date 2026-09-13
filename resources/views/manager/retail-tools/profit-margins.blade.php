@extends('manager.layout.master')
@section('title', 'Profit Margins')
@section('page-content')
    <div class="mb-6">
        <h1 class="text-2xl font-display font-bold text-hut-dark">Profit Margins</h1>
        <p class="mt-1 text-sm text-gray-500">Review cost, selling price, stock value, and gross margin.</p>
    </div>
    <div class="overflow-hidden rounded-xl border border-gray-100 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-gray-50 text-xs uppercase text-gray-500">
                    <tr>
                        <th class="px-4 py-3">Product</th>
                        <th class="px-4 py-3">SKU</th>
                        <th class="px-4 py-3">Cost</th>
                        <th class="px-4 py-3">Price</th>
                        <th class="px-4 py-3">Profit</th>
                        <th class="px-4 py-3">Margin</th>
                        <th class="px-4 py-3">Stock</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">@forelse($rows as $row)
                    @php $profit = $row['price'] - $row['cost'];
                    $margin = $row['price'] > 0 ? ($profit / $row['price']) * 100 : 0; @endphp
                    <tr>
                        <td class="px-4 py-3 font-medium">{{ $row['name'] }}</td>
                        <td class="px-4 py-3 text-gray-500">{{ $row['sku'] ?: '—' }}</td>
                        <td class="px-4 py-3">Rs. {{ number_format($row['cost'], 2) }}</td>
                        <td class="px-4 py-3">Rs. {{ number_format($row['price'], 2) }}</td>
                        <td class="px-4 py-3 {{ $profit >= 0 ? 'text-green-700' : 'text-red-600' }}">Rs.
                            {{ number_format($profit, 2) }}</td>
                        <td class="px-4 py-3">{{ number_format($margin, 1) }}%</td>
                        <td class="px-4 py-3">{{ rtrim(rtrim(number_format($row['stock'], 3), '0'), '.') }}</td>
                </tr>@empty<tr>
                        <td colspan="7" class="px-4 py-10 text-center text-gray-500">No products found.</td>
                    </tr>@endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection