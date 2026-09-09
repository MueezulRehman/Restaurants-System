<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\StockAnalysisService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
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
        $restaurant = $user->effectiveRestaurant();

        if (! $restaurantId) {
            return redirect()->route('admin.restaurants.index')
                ->with('error', 'Enter a business before viewing its stock analysis.');
        }

        $startDate = $request->input('start_date', now()->subDays(7)->format('Y-m-d'));
        $endDate = $request->input('end_date', now()->format('Y-m-d'));
        $maxSalesThreshold = (int) $request->input('max_sales_threshold', 0);
        $sortBy = $request->input('sort_by', 'sales_count');
        $filterStatus = $request->input('filter_status', 'all');
        $search = trim((string) $request->input('search', ''));

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
            $maxSalesThreshold,
            $search ?: null
        );

        if ($filterStatus !== 'all') {
            $unsoldItems = $unsoldItems->filter(fn($item) => $item['status'] === $filterStatus);
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
            'potential_loss' => $unsoldItems->sum(fn($item) => $item->stock_quantity * ($item->cost_price ?? 0)),
        ];
        $unsoldItems = $this->paginateCollection($unsoldItems, $request);

        return view('admin.stock-analysis.admin-index', compact(
            'unsoldItems',
            'topSellingItems',
            'categoryTrends',
            'stats',
            'maxSalesThreshold',
            'sortBy',
            'filterStatus',
            'search',
            'restaurant'
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
        $search = trim((string) $request->input('search', ''));

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
                $maxSalesThreshold,
                $search ?: null
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
        $unsoldItems = $this->paginateCollection(collect($unsoldItems), $request);

        return view('admin.stock-analysis.manager-index', compact(
            'unsoldItems',
            'topSellingItems',
            'categoryTrends',
            'stats',
            'startDate',
            'endDate',
            'maxSalesThreshold',
            'sortBy',
            'viewType',
            'search'
        ));
    }

    public function adminExport(Request $request)
    {
        $user = Auth::user();
        $restaurantId = $user->effectiveRestaurantId();

        abort_unless($restaurantId, 403, 'Enter a business before exporting stock analysis.');

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

    private function paginateCollection($items, Request $request): LengthAwarePaginator
    {
        $page = LengthAwarePaginator::resolveCurrentPage();
        $perPage = 20;
        $items = collect($items);

        return new LengthAwarePaginator(
            $items->forPage($page, $perPage)->values(),
            $items->count(),
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );
    }
}
