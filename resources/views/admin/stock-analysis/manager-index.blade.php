@extends('layouts.admin')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-gray-50 to-gray-100 py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header Section -->
        <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-8">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">📈 Stock & Sales Analysis</h1>
                <p class="text-gray-600 mt-1">Monitor inventory performance and identify optimization opportunities</p>
            </div>
        </div>

        <!-- Statistics Summary Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="bg-white rounded-xl shadow-md hover:shadow-lg transition duration-300 border-l-4 border-red-500">
                <div class="p-6">
                    <div class="flex items-center justify-between mb-3">
                        <h3 class="text-sm font-semibold text-gray-600">Unsold Items</h3>
                        <span class="text-2xl">🚫</span>
                    </div>
                    <p class="text-3xl font-bold text-red-600">{{ $stats['total_unsold'] }}</p>
                    <p class="text-xs text-gray-500 mt-2">Zero sales this period</p>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-md hover:shadow-lg transition duration-300 border-l-4 border-yellow-500">
                <div class="p-6">
                    <div class="flex items-center justify-between mb-3">
                        <h3 class="text-sm font-semibold text-gray-600">Low Sales Items</h3>
                        <span class="text-2xl">⚠️</span>
                    </div>
                    <p class="text-3xl font-bold text-yellow-600">{{ $stats['total_low_sales'] }}</p>
                    <p class="text-xs text-gray-500 mt-2">Below threshold</p>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-md hover:shadow-lg transition duration-300 border-l-4 border-green-500">
                <div class="p-6">
                    <div class="flex items-center justify-between mb-3">
                        <h3 class="text-sm font-semibold text-gray-600">Top Sellers</h3>
                        <span class="text-2xl">🏆</span>
                    </div>
                    <p class="text-3xl font-bold text-green-600">{{ $stats['total_top_selling'] }}</p>
                    <p class="text-xs text-gray-500 mt-2">Best performers</p>
                </div>
            </div>
        </div>

        <!-- Filters & View Options -->
        <div class="bg-white rounded-xl shadow-md p-6 mb-8">
            <h2 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                <i class="fas fa-sliders-h mr-2 text-blue-600"></i> Filters & View Options
            </h2>
            <form method="GET" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-6 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Start Date</label>
                    <input type="date" name="start_date" value="{{ $startDate }}" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">End Date</label>
                    <input type="date" name="end_date" value="{{ $endDate }}" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Sales Threshold</label>
                    <input type="number" name="max_sales_threshold" value="{{ $maxSalesThreshold }}" min="0" 
                           placeholder="Max sales" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">View Type</label>
                    <select name="view_type" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="unsold" {{ $viewType === 'unsold' ? 'selected' : '' }}>Unsold Items</option>
                        <option value="top_selling" {{ $viewType === 'top_selling' ? 'selected' : '' }}>Top Sellers</option>
                        <option value="comparison" {{ $viewType === 'comparison' ? 'selected' : '' }}>Full Comparison</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Sort By</label>
                    <select name="sort_by" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="sales_count" {{ $sortBy === 'sales_count' ? 'selected' : '' }}>Sales Count</option>
                        <option value="name" {{ $sortBy === 'name' ? 'selected' : '' }}>Item Name</option>
                        <option value="stock" {{ $sortBy === 'stock' ? 'selected' : '' }}>Stock Qty</option>
                        <option value="price" {{ $sortBy === 'price' ? 'selected' : '' }}>Price</option>
                    </select>
                </div>
                <div class="flex items-end">
                    <button type="submit" class="w-full px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition duration-200">
                        <i class="fas fa-filter mr-2"></i> Apply
                    </button>
                </div>
            </form>
        </div>

        <!-- Unsold Items Section -->
        @if($viewType === 'unsold' || $viewType === 'comparison')
            <div class="bg-white rounded-xl shadow-md overflow-hidden mb-8">
                <div class="bg-gradient-to-r from-red-500 to-red-600 px-6 py-4">
                    <h2 class="text-lg font-semibold text-white flex items-center">
                        <i class="fas fa-exclamation-triangle mr-2"></i> Unsold & Low-Selling Items
                        <span class="ml-2 bg-white text-red-600 text-xs font-bold px-3 py-1 rounded-full">{{ count($unsoldItems) }}</span>
                    </h2>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="bg-gray-50 border-b border-gray-200">
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Item Name</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Category</th>
                                <th class="px-6 py-3 text-center text-xs font-semibold text-gray-700 uppercase">Stock</th>
                                <th class="px-6 py-3 text-right text-xs font-semibold text-gray-700 uppercase">Price</th>
                                <th class="px-6 py-3 text-center text-xs font-semibold text-gray-700 uppercase">Sales</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @forelse($unsoldItems as $item)
                                <tr class="hover:bg-gray-50 transition duration-150">
                                    <td class="px-6 py-4 font-semibold text-gray-900">{{ $item['name'] }}</td>
                                    <td class="px-6 py-4 text-gray-700">{{ $item['category'] ?? '—' }}</td>
                                    <td class="px-6 py-4 text-center">
                                        <span class="inline-block px-3 py-1 text-sm font-semibold text-white bg-blue-500 rounded-full">{{ $item['stock_quantity'] }}</span>
                                    </td>
                                    <td class="px-6 py-4 text-right font-medium text-gray-900">Rs {{ number_format($item['price'], 2) }}</td>
                                    <td class="px-6 py-4 text-center">
                                        <span class="inline-block px-3 py-1 text-sm font-bold rounded-full @if($item['sales_count'] === 0) bg-red-100 text-red-700 @else bg-yellow-100 text-yellow-700 @endif">
                                            {{ $item['sales_count'] }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4">
                                        @if($item['status'] === 'unsold')
                                            <span class="inline-flex items-center px-3 py-1 text-xs font-semibold text-red-700 bg-red-100 rounded-full">
                                                <i class="fas fa-times-circle mr-1"></i> Unsold
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-3 py-1 text-xs font-semibold text-yellow-700 bg-yellow-100 rounded-full">
                                                <i class="fas fa-exclamation-circle mr-1"></i> Low Sales
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-12 text-center">
                                        <i class="fas fa-check-circle text-4xl text-green-500 mb-3"></i>
                                        <p class="text-lg font-medium text-gray-900">Perfect! All items selling</p>
                                        <p class="text-sm text-gray-500 mt-1">No unsold items found for this period</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        <!-- Top Selling Items Section -->
        @if($viewType === 'top_selling' || $viewType === 'comparison')
            <div class="bg-white rounded-xl shadow-md overflow-hidden mb-8">
                <div class="bg-gradient-to-r from-green-500 to-green-600 px-6 py-4">
                    <h2 class="text-lg font-semibold text-white flex items-center">
                        <i class="fas fa-crown mr-2"></i> Top Performing Items
                        <span class="ml-2 bg-white text-green-600 text-xs font-bold px-3 py-1 rounded-full">{{ count($topSellingItems) }}</span>
                    </h2>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="bg-gray-50 border-b border-gray-200">
                                <th class="px-6 py-3 text-center text-xs font-semibold text-gray-700 uppercase">Rank</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Item Name</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Category</th>
                                <th class="px-6 py-3 text-center text-xs font-semibold text-gray-700 uppercase">Sold</th>
                                <th class="px-6 py-3 text-center text-xs font-semibold text-gray-700 uppercase">Orders</th>
                                <th class="px-6 py-3 text-right text-xs font-semibold text-gray-700 uppercase">Revenue</th>
                                <th class="px-6 py-3 text-right text-xs font-semibold text-gray-700 uppercase">Avg/Order</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @forelse($topSellingItems as $index => $item)
                                <tr class="hover:bg-gray-50 transition duration-150">
                                    <td class="px-6 py-4 text-center font-bold">
                                        @if($index === 0)
                                            <span class="text-2xl">🥇</span>
                                        @elseif($index === 1)
                                            <span class="text-2xl">🥈</span>
                                        @elseif($index === 2)
                                            <span class="text-2xl">🥉</span>
                                        @else
                                            <span class="text-gray-700">#{{ $index + 1 }}</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 font-semibold text-gray-900">{{ $item['name'] }}</td>
                                    <td class="px-6 py-4 text-gray-700">{{ $item['category'] ?? '—' }}</td>
                                    <td class="px-6 py-4 text-center">
                                        <span class="inline-block px-3 py-1 text-sm font-bold text-white bg-green-500 rounded-full">{{ $item['total_sold'] }}</span>
                                    </td>
                                    <td class="px-6 py-4 text-center text-gray-700">{{ $item['order_count'] }}</td>
                                    <td class="px-6 py-4 text-right font-semibold text-gray-900">Rs {{ number_format($item['revenue'], 0) }}</td>
                                    <td class="px-6 py-4 text-right text-gray-700">{{ $item['average_per_order'] }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-6 py-12 text-center text-gray-500">
                                        <p class="text-lg font-medium">No sales data available</p>
                                        <p class="text-sm mt-1">Try adjusting your date range</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        <!-- Category Trends (Comparison View) -->
        @if($viewType === 'comparison' && $categoryTrends->count() > 0)
            <div class="bg-white rounded-xl shadow-md overflow-hidden">
                <div class="bg-gradient-to-r from-indigo-500 to-indigo-600 px-6 py-4">
                    <h2 class="text-lg font-semibold text-white flex items-center">
                        <i class="fas fa-chart-bar mr-2"></i> Category Performance
                    </h2>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="bg-gray-50 border-b border-gray-200">
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Category</th>
                                <th class="px-6 py-3 text-center text-xs font-semibold text-gray-700 uppercase">Qty Sold</th>
                                <th class="px-6 py-3 text-center text-xs font-semibold text-gray-700 uppercase">Orders</th>
                                <th class="px-6 py-3 text-right text-xs font-semibold text-gray-700 uppercase">Revenue</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @foreach($categoryTrends as $trend)
                                <tr class="hover:bg-gray-50 transition duration-150">
                                    <td class="px-6 py-4 font-semibold text-gray-900">{{ $trend->category_name }}</td>
                                    <td class="px-6 py-4 text-center">
                                        <span class="inline-block px-3 py-1 text-sm font-semibold text-white bg-blue-500 rounded-full">{{ $trend->total_quantity }}</span>
                                    </td>
                                    <td class="px-6 py-4 text-center text-gray-700">{{ $trend->total_orders }}</td>
                                    <td class="px-6 py-4 text-right font-semibold text-gray-900">Rs {{ number_format($trend->total_revenue, 0) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    </div>
</div>

<style>
    body {
        font-family: 'Poppins', 'Inter', sans-serif;
    }
</style>
@endsection
