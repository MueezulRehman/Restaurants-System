@extends('layouts.admin')

@section('content')
<div class="container-fluid mt-4">
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h1 class="h3">Stock Analysis Dashboard</h1>
                <a href="{{ route('admin.stock-analysis.export', ['start_date' => $startDate, 'end_date' => $endDate, 'max_sales_threshold' => $maxSalesThreshold]) }}" class="btn btn-success btn-sm">
                    <i class="fas fa-download"></i> Export CSV
                </a>
            </div>

            <!-- Statistics Cards -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card border-danger">
                        <div class="card-body">
                            <h6 class="card-title text-muted">Unsold Items</h6>
                            <h2 class="text-danger">{{ $stats['total_unsold'] }}</h2>
                            <small>Items with zero sales</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-warning">
                        <div class="card-body">
                            <h6 class="card-title text-muted">Low Sales</h6>
                            <h2 class="text-warning">{{ $stats['total_low_sales'] }}</h2>
                            <small>Below threshold</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-info">
                        <div class="card-body">
                            <h6 class="card-title text-muted">Total Analyzed</h6>
                            <h2 class="text-info">{{ $stats['total_analyzed'] }}</h2>
                            <small>This period</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-primary">
                        <div class="card-body">
                            <h6 class="card-title text-muted">Potential Loss</h6>
                            <h2 class="text-primary">Rs {{ number_format($stats['potential_loss'], 0) }}</h2>
                            <small>Stock cost value</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filters Section -->
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Filters</h5>
                </div>
                <div class="card-body">
                    <form method="GET" class="row g-3">
                        <div class="col-md-2">
                            <label for="start_date" class="form-label">Start Date</label>
                            <input type="date" class="form-control" id="start_date" name="start_date" value="{{ $startDate }}">
                        </div>
                        <div class="col-md-2">
                            <label for="end_date" class="form-label">End Date</label>
                            <input type="date" class="form-control" id="end_date" name="end_date" value="{{ $endDate }}">
                        </div>
                        <div class="col-md-2">
                            <label for="max_sales_threshold" class="form-label">Sales Threshold</label>
                            <input type="number" class="form-control" id="max_sales_threshold" name="max_sales_threshold" value="{{ $maxSalesThreshold }}" min="0" placeholder="Max sales count">
                        </div>
                        <div class="col-md-2">
                            <label for="filter_status" class="form-label">Filter by Status</label>
                            <select class="form-control" id="filter_status" name="filter_status">
                                <option value="all" {{ $filterStatus === 'all' ? 'selected' : '' }}>All</option>
                                <option value="unsold" {{ $filterStatus === 'unsold' ? 'selected' : '' }}>Unsold Only</option>
                                <option value="low_sales" {{ $filterStatus === 'low_sales' ? 'selected' : '' }}>Low Sales Only</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label for="sort_by" class="form-label">Sort By</label>
                            <select class="form-control" id="sort_by" name="sort_by">
                                <option value="sales_count" {{ $sortBy === 'sales_count' ? 'selected' : '' }}>Sales Count</option>
                                <option value="name" {{ $sortBy === 'name' ? 'selected' : '' }}>Name</option>
                                <option value="stock" {{ $sortBy === 'stock' ? 'selected' : '' }}>Stock Qty</option>
                                <option value="price" {{ $sortBy === 'price' ? 'selected' : '' }}>Price</option>
                                <option value="profit_margin" {{ $sortBy === 'profit_margin' ? 'selected' : '' }}>Profit Margin</option>
                            </select>
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-filter"></i> Apply Filters
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Unsold & Low Selling Items Table -->
            <div class="card mb-4">
                <div class="card-header bg-danger text-white">
                    <h5 class="mb-0">Unsold & Low-Selling Items ({{ $unsoldItems->count() }})</h5>
                </div>
                <div class="table-responsive">
                    <table class="table table-sm table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Item Name</th>
                                <th>SKU</th>
                                <th>Category</th>
                                <th class="text-center">Stock</th>
                                <th class="text-right">Price</th>
                                <th class="text-right">Cost</th>
                                <th class="text-center">Sales</th>
                                <th>Status</th>
                                <th class="text-right">Profit %</th>
                                <th class="text-right">Stock Value</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($unsoldItems as $item)
                                <tr>
                                    <td>
                                        <strong>{{ $item['name'] }}</strong>
                                        @if(!$item['is_available'])
                                            <br><span class="badge bg-secondary">Unavailable</span>
                                        @endif
                                    </td>
                                    <td><code>{{ $item['sku'] }}</code></td>
                                    <td>{{ $item['category'] ?? 'N/A' }}</td>
                                    <td class="text-center">
                                        <span class="badge bg-info">{{ $item['stock_quantity'] }}</span>
                                    </td>
                                    <td class="text-right">Rs {{ number_format($item['price'], 2) }}</td>
                                    <td class="text-right">Rs {{ number_format($item['cost_price'], 2) }}</td>
                                    <td class="text-center">
                                        <span class="badge @if($item['sales_count'] === 0) bg-danger @else bg-warning text-dark @endif">
                                            {{ $item['sales_count'] }}
                                        </span>
                                    </td>
                                    <td>
                                        @if($item['status'] === 'unsold')
                                            <span class="badge bg-danger">Unsold</span>
                                        @else
                                            <span class="badge bg-warning text-dark">Low Sales</span>
                                        @endif
                                    </td>
                                    <td class="text-right">{{ number_format($item['profit_margin'], 1) }}%</td>
                                    <td class="text-right">Rs {{ number_format($item['stock_quantity'] * $item['cost_price'], 0) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="10" class="text-center py-4 text-muted">
                                        No unsold or low-selling items found for the selected filters.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Top Selling Items -->
            @if($topSellingItems->count() > 0)
                <div class="card mb-4">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0">Top Selling Items</h5>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-sm table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Rank</th>
                                    <th>Item Name</th>
                                    <th>Category</th>
                                    <th class="text-center">Total Sold</th>
                                    <th class="text-center">Orders</th>
                                    <th class="text-right">Revenue</th>
                                    <th class="text-right">Profit</th>
                                    <th class="text-right">Avg/Order</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($topSellingItems as $index => $item)
                                    <tr>
                                        <td><strong>#{{ $index + 1 }}</strong></td>
                                        <td>{{ $item['name'] }}</td>
                                        <td>{{ $item['category'] ?? 'N/A' }}</td>
                                        <td class="text-center"><strong>{{ $item['total_sold'] }}</strong></td>
                                        <td class="text-center">{{ $item['order_count'] }}</td>
                                        <td class="text-right">Rs {{ number_format($item['revenue'], 0) }}</td>
                                        <td class="text-right"><span class="text-success">Rs {{ number_format($item['profit'], 0) }}</span></td>
                                        <td class="text-right">{{ $item['average_per_order'] }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif

            <!-- Category Trends -->
            @if($categoryTrends->count() > 0)
                <div class="card">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0">Category Sales Trends</h5>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-sm table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Category</th>
                                    <th class="text-center">Total Qty Sold</th>
                                    <th class="text-center">Total Orders</th>
                                    <th class="text-right">Total Revenue</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($categoryTrends as $trend)
                                    <tr>
                                        <td><strong>{{ $trend->category_name }}</strong></td>
                                        <td class="text-center">{{ $trend->total_quantity }}</td>
                                        <td class="text-center">{{ $trend->total_orders }}</td>
                                        <td class="text-right">Rs {{ number_format($trend->total_revenue, 0) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

<style>
    .table-hover tbody tr:hover {
        background-color: #f5f5f5;
    }
    .card {
        box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    }
</style>
@endsection
