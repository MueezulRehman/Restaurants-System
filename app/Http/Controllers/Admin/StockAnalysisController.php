<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\StockAnalysisService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StockAnalysisController extends Controller
{
    protected $stockAnalysisService;

    public function __construct(StockAnalysisService $stockAnalysisService)
    {
        $this->stockAnalysisService = $stockAnalysisService;
    }

    /**
     * Show stock analysis dashboard (Admin view).
     */
    public function adminIndex(Request $request)
    {
        $user = Auth::user();
        $restaurantId = $user->effectiveRestaurantId();

        // Get filter inputs with defaults
        $startDate = $request->input('start_date', now()->subDays(7)->format('Y-m-d'));
        $endDate = $request->input('end_date', now()->format('Y-m-d'));
        $maxSalesThreshold = $request->input('max_sales_threshold', 0);
        $sortBy = $request->input('sort_by', 'sales_count');
        $filterStatus = $request->input('filter_status', 'all'); // all, unsold, low_sales

        // Validate date inputs
        try {
            $startDate = Carbon::createFromFormat('Y-m-d', $startDate)->format('Y-m-d');
            $endDate = Carbon::createFromFormat('Y-m-d', $endDate)->format('Y-m-d');
        } catch (\Exception $e) {
            $startDate = now()->subDays(7)->format('Y-m-d');
            $endDate = now()->format('Y-m-d');
        }

        // Get unsold/low-selling items
        $unsoldItems = $this->stockAnalysisService->getUnsoldAndLowSellingItems(
            $restaurantId,
            $startDate,
            $endDate,
            (int) $maxSalesThreshold
        );

        // Apply status filter
        if ($filterStatus !== 'all') {
            $unsoldItems = $unsoldItems->filter(fn($item) => $item['status'] === $filterStatus);
        }

        // Sort items
        $unsoldItems = $unsoldItems->sortBy(function ($item) use ($sortBy) {
            return match ($sortBy) {
                'name' => $item['name'],
                'stock' => $item['stock_quantity'],
                'price' => $item['price'],
                'profit_margin' => $item['profit_margin'],
                default => $item['sales_count'],
            };
        })->values();

        // Get top-selling items for comparison
        $topSellingItems = $this->stockAnalysisService->getTopSellingItems(
            $restaurantId,
            20,
            $startDate,
            $endDate
        );

        // Get category trends
        $categoryTrends = $this->stockAnalysisService->getSalesTrendByCategory(
            $restaurantId,
            $startDate,
            $endDate
        );

        $stats = [
            'total_unsold' => $unsoldItems->where('status', 'unsold')->count(),
            'total_low_sales' => $unsoldItems->where('status', 'low_sales')->count(),
            'total_analyzed' => $unsoldItems->count(),
            'avg_stock_value' => $unsoldItems->sum(fn($item) => $item['stock_quantity'] * $item['cost_price']) / max(1, $unsoldItems->count()),
            'potential_loss' => $unsoldItems->sum(fn($item) => $item['stock_quantity'] * $item['cost_price']),
        ];

        return view('admin.stock-analysis.admin-index', compact(
            'unsoldItems',
            'topSellingItems',
            'categoryTrends',
            'stats',
            'startDate',
            'endDate',
            'maxSalesThreshold',
            'sortBy',
            'filterStatus'
        ));
    }

    /**
     * Show stock analysis dashboard (Manager view).
     */
    public function managerIndex(Request $request)
    {
        $user = Auth::user();
        $restaurantId = $user->effectiveRestaurantId();

        // Get filter inputs with defaults
        $startDate = $request->input('start_date', now()->subDays(7)->format('Y-m-d'));
        $endDate = $request->input('end_date', now()->format('Y-m-d'));
        $maxSalesThreshold = $request->input('max_sales_threshold', 0);
        $sortBy = $request->input('sort_by', 'sales_count');
        $viewType = $request->input('view_type', 'unsold'); // unsold, top_selling, comparison

        // Validate date inputs
        try {
            $startDate = Carbon::createFromFormat('Y-m-d', $startDate)->format('Y-m-d');
            $endDate = Carbon::createFromFormat('Y-m-d', $endDate)->format('Y-m-d');
        } catch (\Exception $e) {
            $startDate = now()->subDays(7)->format('Y-m-d');
            $endDate = now()->format('Y-m-d');
        }

        $unsoldItems = [];
        $topSellingItems = [];
        $categoryTrends = [];

        if ($viewType === 'unsold' || $viewType === 'comparison') {
            $unsoldItems = $this->stockAnalysisService->getUnsoldAndLowSellingItems(
                $restaurantId,
                $startDate,
                $endDate,
                (int) $maxSalesThreshold
            );

            $unsoldItems = $unsoldItems->sortBy(function ($item) use ($sortBy) {
                return match ($sortBy) {
                    'name' => $item['name'],
                    'stock' => $item['stock_quantity'],
                    'price' => $item['price'],
                    default => $item['sales_count'],
                };
            })->values();
        }

        if ($viewType === 'top_selling' || $viewType === 'comparison') {
            $topSellingItems = $this->stockAnalysisService->getTopSellingItems(
                $restaurantId,
                15,
                $startDate,
                $endDate
            );
        }

        if ($viewType === 'comparison') {
            $categoryTrends = $this->stockAnalysisService->getSalesTrendByCategory(
                $restaurantId,
                $startDate,
                $endDate
            );
        }

        $stats = [
            'total_unsold' => collect($unsoldItems)->where('status', 'unsold')->count(),
            'total_low_sales' => collect($unsoldItems)->where('status', 'low_sales')->count(),
            'total_top_selling' => count($topSellingItems),
        ];

        return view('admin.stock-analysis.manager-index', compact(
            'unsoldItems',
            'topSellingItems',
            'categoryTrends',
            'stats',
            'startDate',
            'endDate',
            'maxSalesThreshold',
            'sortBy',
            'viewType'
        ));
    }

    /**
     * Export stock analysis as CSV (Admin).
     */
    public function adminExport(Request $request)
    {
        $user = Auth::user();
        $restaurantId = $user->effectiveRestaurantId();

        $startDate = $request->input('start_date', now()->subDays(7)->format('Y-m-d'));
        $endDate = $request->input('end_date', now()->format('Y-m-d'));
        $maxSalesThreshold = $request->input('max_sales_threshold', 0);

        $unsoldItems = $this->stockAnalysisService->getUnsoldAndLowSellingItems(
            $restaurantId,
            $startDate,
            $endDate,
            (int) $maxSalesThreshold
        );

        $filename = 'stock-analysis-' . now()->format('Y-m-d-His') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function () use ($unsoldItems) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Item Name', 'SKU', 'Category', 'Stock Qty', 'Price', 'Cost Price', 'Sales Count', 'Status', 'Profit Margin %']);

            foreach ($unsoldItems as $item) {
                fputcsv($file, [
                    $item['name'],
                    $item['sku'],
                    $item['category'],
                    $item['stock_quantity'],
                    $item['price'],
                    $item['cost_price'],
                    $item['sales_count'],
                    $item['status'],
                    round($item['profit_margin'], 2),
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
