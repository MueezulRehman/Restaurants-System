<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\StockAnalysisService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StockAnalysisController extends Controller
{
    protected StockAnalysisService $stockAnalysisService;

    public function __construct(StockAnalysisService $stockAnalysisService)
    {
        $this->stockAnalysisService = $stockAnalysisService;
    }

    public function adminIndex(Request $request)
    {
        $user = Auth::user();
        $restaurantId = $user->effectiveRestaurantId();

        $startDate = $request->input('start_date', now()->subDays(7)->format('Y-m-d'));
        $endDate = $request->input('end_date', now()->format('Y-m-d'));
        $maxSalesThreshold = (int) $request->input('max_sales_threshold', 0);
        $sortBy = $request->input('sort_by', 'sales_count');
        $filterStatus = $request->input('filter_status', 'all');

        try {
            $startDate = Carbon::createFromFormat('Y-m-d', $startDate);
            $endDate = Carbon::createFromFormat('Y-m-d', $endDate);
        } catch (\Exception $e) {
            $startDate = now()->subDays(7);
            $endDate = now();
        }

        $unsoldItems = $this->stockAnalysisService->getUnsoldAndLowSellingItems(
            $restaurantId,
            $startDate,
            $endDate,
            $maxSalesThreshold
        );

        if ($filterStatus !== 'all') {
            $unsoldItems = $unsoldItems->filter(fn ($item) => $item['status'] === $filterStatus);
        }

        $unsoldItems = $unsoldItems->sortBy(function ($item) use ($sortBy) {
            return match ($sortBy) {
                'name' => $item['name'],
                'stock' => $item['stock_quantity'],
                'price' => $item['price'],
                'profit_margin' => $item['profit_margin'],
                default => $item['sales_count'],
            };
        })->values();

        $topSellingItems = $this->stockAnalysisService->getTopSellingItems($restaurantId, 20, $startDate, $endDate);
        $categoryTrends = $this->stockAnalysisService->getSalesTrendByCategory($restaurantId, $startDate, $endDate);

        $stats = [
            'total_unsold' => $unsoldItems->where('unsold', true)->count(),
            'total_low_sales' => $unsoldItems->where('low_selling', true)->count(),
            'total_analyzed' => $unsoldItems->count(),
            'potential_loss' => $unsoldItems->sum(fn ($item) => $item->stock_quantity * ($item->cost_price ?? 0)),
        ];

        return view('admin.stock-analysis.admin-index', compact(
            'unsoldItems',
            'topSellingItems',
            'categoryTrends',
            'stats',
            'maxSalesThreshold',
            'sortBy',
            'filterStatus'
        ))->with([
            'startDate' => $startDate->format('Y-m-d'),
            'endDate' => $endDate->format('Y-m-d'),
        ]);
    }

    public function managerIndex(Request $request)
    {
        $user = Auth::user();
        $restaurantId = $user->effectiveRestaurantId();

        $startDate = $request->input('start_date', now()->subDays(7)->format('Y-m-d'));
        $endDate = $request->input('end_date', now()->format('Y-m-d'));
        $maxSalesThreshold = (int) $request->input('max_sales_threshold', 0);
        $sortBy = $request->input('sort_by', 'sales_count');
        $viewType = $request->input('view_type', 'unsold');

        try {
            $startDate = Carbon::createFromFormat('Y-m-d', $startDate);
            $endDate = Carbon::createFromFormat('Y-m-d', $endDate);
        } catch (\Exception $e) {
            $startDate = now()->subDays(7);
            $endDate = now();
        }

        $unsoldItems = [];
        $topSellingItems = [];
        $categoryTrends = [];

        if ($viewType === 'unsold' || $viewType === 'comparison') {
            $unsoldItems = $this->stockAnalysisService->getUnsoldAndLowSellingItems(
                $restaurantId,
                $startDate,
                $endDate,
                $maxSalesThreshold
            )->sortBy(function ($item) use ($sortBy) {
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
            $categoryTrends = $this->stockAnalysisService->getSalesTrendByCategory($restaurantId, $startDate, $endDate);
        }

        $stats = [
            'total_unsold' => collect($unsoldItems)->where('unsold', true)->count(),
            'total_low_sales' => collect($unsoldItems)->where('low_selling', true)->count(),
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

    public function adminExport(Request $request)
    {
        $user = Auth::user();
        $restaurantId = $user->effectiveRestaurantId();

        $startDate = $request->input('start_date', now()->subDays(7)->format('Y-m-d'));
        $endDate = $request->input('end_date', now()->format('Y-m-d'));
        $maxSalesThreshold = (int) $request->input('max_sales_threshold', 0);

        try {
            $startDate = Carbon::createFromFormat('Y-m-d', $startDate);
            $endDate = Carbon::createFromFormat('Y-m-d', $endDate);
        } catch (\Exception $e) {
            $startDate = now()->subDays(7);
            $endDate = now();
        }

        $unsoldItems = $this->stockAnalysisService->getUnsoldAndLowSellingItems(
            $restaurantId,
            $startDate,
            $endDate,
            $maxSalesThreshold
        );

        $filename = 'stock-analysis-' . now()->format('Y-m-d-His') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function () use ($unsoldItems) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Item Name', 'Category', 'Stock Qty', 'Price', 'Sales Count', 'Status', 'Revenue']);

            foreach ($unsoldItems as $item) {
                fputcsv($file, [
                    $item->name,
                    $item->category?->name ?? 'N/A',
                    $item->stock_quantity ?? 0,
                    $item->price ?? 0,
                    $item->quantity_sold ?? 0,
                    $item->unsold ? 'Unsold' : 'Low Selling',
                    $item->total_revenue ?? 0,
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
