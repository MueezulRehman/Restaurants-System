@extends('layouts.admin')

@section('content')
<div class="container-fluid mt-4">
    <div class="row mb-4">
        <div class="col-md-12">
            <h1 class="h3 mb-3">Stock Analysis & Sales Report</h1>

            <!-- Statistics Cards -->
            <div class="row mb-4">
                <div class="col-md-4">
                    <div class="card border-danger">
                        <div class="card-body">
                            <h6 class="card-title text-muted">Unsold Items</h6>
                            <h2 class="text-danger">{{ $stats['total_unsold'] }}</h2>
                            <small>Zero sales in period</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card border-warning">
                        <div class="card-body">
                            <h6 class="card-title text-muted">Low Sales</h6>
                            <h2 class="text-warning">{{ $stats['total_low_sales'] }}</h2>
                            <small>Below threshold</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card border-success">
                        <div class="card-body">
                            <h6 class="card-title text-muted">Top Sellers</h6>
                            <h2 class="text-success">{{ $stats['total_top_selling'] }}</h2>
                            <small>Most popular items</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filters Section -->
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Filters & View Options</h5>
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
                            <input type="number" class="form-control" id="max_sales_threshold" name="max_sales_threshold" value="{{ $maxSalesThreshold }}" min="0" placeholder="Max sales">
                        </div>
                        <div class="col-md-2">
                            <label for="view_type" class="form-label">View Type</label>
                            <select class="form-control" id="view_type" name="view_type">
                                <option value="unsold" {{ $viewType === 'unsold' ? 'selected' : '' }}>Unsold Items</option>
                                <option value="top_selling" {{ $viewType === 'top_selling' ? 'selected' : '' }}>Top Selling</option>
                                <option value="comparison" {{ $viewType === 'comparison' ? 'selected' : '' }}>Comparison</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label for="sort_by" class="form-label">Sort By</label>
                            <select class="form-control" id="sort_by" name="sort_by">
                                <option value="sales_count" {{ $sortBy === 'sales_count' ? 'selected' : '' }}>Sales Count</option>
                                <option value="name" {{ $sortBy === 'name' ? 'selected' : '' }}>Name</option>
                                <option value="stock" {{ $sortBy === 'stock' ? 'selected' : '' }}>Stock Qty</option>
                                <option value="price" {{ $sortBy === 'price' ? 'selected' : '' }}>Price</option>
                            </select>
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-filter"></i> Apply
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Unsold Items Section -->
            @if($viewType === 'unsold' || $viewType === 'comparison')
                <div class="card mb-4">
                    <div class="card-header bg-danger text-white">
                        <h5 class="mb-0">Unsold & Low-Selling Items ({{ count($unsoldItems) }})</h5>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-sm table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Item Name</th>
                                    <th>Category</th>
                                    <th class="text-center">Stock Qty</th>
                                    <th class="text-right">Price</th>
                                    <th class="text-center">Sales</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($unsoldItems as $item)
                                    <tr>
                                        <td><strong>{{ $item['name'] }}</strong></td>
                                        <td>{{ $item['category'] ?? 'N/A' }}</td>
                                        <td class="text-center">
                                            <span class="badge bg-info">{{ $item['stock_quantity'] }}</span>
                                        </td>
                                        <td class="text-right">Rs {{ number_format($item['price'], 2) }}</td>
                                        <td class="text-center">
                                            <span class="badge @if($item['sales_count'] === 0) bg-danger @else bg-warning text-dark @endif">
                                                {{ $item['sales_count'] }}
                                            </span>
                                        </td>
                                        <td>
                                            @if($item['status'] === 'unsold')
                                                <span class="badge bg-danger">❌ Unsold</span>
                                            @else
                                                <span class="badge bg-warning text-dark">⚠️ Low Sales</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-4 text-muted">
                                            No unsold or low-selling items found for the selected filters.
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
                <div class="card mb-4">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0">🏆 Top Selling Items</h5>
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
                                    <th class="text-right">Avg/Order</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($topSellingItems as $index => $item)
                                    <tr>
                                        <td>
                                            @if($index === 0)
                                                <span class="badge bg-warning text-dark">🥇 #1</span>
                                            @elseif($index === 1)
                                                <span class="badge bg-secondary">🥈 #2</span>
                                            @elseif($index === 2)
                                                <span class="badge bg-warning">🥉 #3</span>
                                            @else
                                                <strong>#{{ $index + 1 }}</strong>
                                            @endif
                                        </td>
                                        <td><strong>{{ $item['name'] }}</strong></td>
                                        <td>{{ $item['category'] ?? 'N/A' }}</td>
                                        <td class="text-center">
                                            <span class="badge bg-success">{{ $item['total_sold'] }}</span>
                                        </td>
                                        <td class="text-center">{{ $item['order_count'] }}</td>
                                        <td class="text-right"><span class="text-success">Rs {{ number_format($item['revenue'], 0) }}</span></td>
                                        <td class="text-right">{{ $item['average_per_order'] }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center py-4 text-muted">
                                            No sales data available for this period.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif

            <!-- Category Trends -->
            @if($viewType === 'comparison' && $categoryTrends->count() > 0)
                <div class="card">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0">📊 Category Sales Overview</h5>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-sm table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Category</th>
                                    <th class="text-center">Qty Sold</th>
                                    <th class="text-center">Orders</th>
                                    <th class="text-right">Revenue</th>
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
    .badge {
        padding: 0.375rem 0.75rem;
    }
</style>
@endsection
