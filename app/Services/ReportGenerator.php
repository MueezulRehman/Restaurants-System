<?php

namespace App\Services;

use App\Models\Report;
use App\Models\Order;
use App\Models\Expense;
use App\Models\MenuItem;
use App\Models\Cashbook;
use Carbon\Carbon;

class ReportGenerator
{
    /**
     * Generate an orders report for a date range.
     */
    public static function generateOrdersReport($restaurantId, $dateFrom, $dateTo): array
    {
        $orders = Order::where('restaurant_id', $restaurantId)
            ->whereBetween('created_at', [$dateFrom, $dateTo])
            ->with('items', 'customer')
            ->get();

        $totalOrders = $orders->count();
        $totalRevenue = $orders->sum('total');
        $avgOrderValue = $totalOrders > 0 ? $totalRevenue / $totalOrders : 0;
        $statusBreakdown = $orders->groupBy('status')->map->count();

        return [
            'total_orders' => $totalOrders,
            'total_revenue' => round($totalRevenue, 2),
            'avg_order_value' => round($avgOrderValue, 2),
            'status_breakdown' => $statusBreakdown,
            'orders' => $orders->map(function ($order) {
                return [
                    'id' => $order->id,
                    'order_number' => $order->order_number,
                    'customer_name' => $order->customer_name,
                    'total' => $order->total,
                    'status' => $order->status,
                    'created_at' => $order->created_at->toDateTimeString(),
                ];
            })->toArray(),
        ];
    }

    /**
     * Generate a sales report for a date range.
     */
    public static function generateSalesReport($restaurantId, $dateFrom, $dateTo): array
    {
        $orders = Order::where('restaurant_id', $restaurantId)
            ->whereBetween('created_at', [$dateFrom, $dateTo])
            ->where('status', 'delivered')
            ->get();

        $itemsSold = [];
        foreach ($orders as $order) {
            foreach ($order->items as $item) {
                $key = $item->item_name;
                if (!isset($itemsSold[$key])) {
                    $itemsSold[$key] = ['quantity' => 0, 'revenue' => 0];
                }
                $itemsSold[$key]['quantity'] += $item->quantity;
                $itemsSold[$key]['revenue'] += $item->total_price;
            }
        }

        // Sort by revenue descending
        uasort($itemsSold, function ($a, $b) {
            return $b['revenue'] <=> $a['revenue'];
        });

        $totalSales = array_sum(array_column($itemsSold, 'revenue'));
        $totalQuantity = array_sum(array_column($itemsSold, 'quantity'));

        return [
            'total_sales' => round($totalSales, 2),
            'total_quantity' => $totalQuantity,
            'items_sold' => array_slice($itemsSold, 0, 20), // Top 20 items
            'top_items' => array_slice(array_keys($itemsSold), 0, 5),
        ];
    }

    /**
     * Generate an inventory/products report.
     */
    public static function generateInventoryReport($restaurantId): array
    {
        $variants = \App\Models\ProductVariant::where('restaurant_id', $restaurantId)
            ->with('menuItem')
            ->get();

        $lowStockItems = $variants->filter(fn ($v) => $v->quantity_available < 10);
        $outOfStock = $variants->filter(fn ($v) => $v->quantity_available == 0);

        return [
            'total_variants' => $variants->count(),
            'total_stock_value' => $variants->sum(fn ($v) => $v->quantity_available * $v->getEffectivePrice()),
            'low_stock_count' => $lowStockItems->count(),
            'out_of_stock_count' => $outOfStock->count(),
            'low_stock_items' => $lowStockItems->map(fn ($v) => [
                'sku' => $v->sku,
                'name' => $v->variant_name,
                'quantity' => $v->quantity_available,
                'price' => $v->getEffectivePrice(),
            ])->values()->toArray(),
        ];
    }

    /**
     * Generate a financial report (expenses, cashbook, revenue).
     */
    public static function generateFinancialReport($restaurantId, $dateFrom, $dateTo): array
    {
        $orders = Order::where('restaurant_id', $restaurantId)
            ->whereBetween('created_at', [$dateFrom, $dateTo])
            ->where('status', 'delivered');

        $revenue = $orders->sum('total');

        $expenses = Expense::where('restaurant_id', $restaurantId)
            ->whereBetween('created_at', [$dateFrom, $dateTo])
            ->get();

        $totalExpenses = $expenses->sum('amount');
        $expenseByCategory = $expenses->groupBy('category')->map->sum('amount');

        $cashbook = Cashbook::where('restaurant_id', $restaurantId)
            ->whereBetween('created_at', [$dateFrom, $dateTo])
            ->get();

        $cashInflow = $cashbook->filter(fn ($c) => $c->type === 'income')->sum('amount');
        $cashOutflow = $cashbook->filter(fn ($c) => $c->type === 'expense')->sum('amount');

        return [
            'revenue' => round($revenue, 2),
            'total_expenses' => round($totalExpenses, 2),
            'net_profit' => round($revenue - $totalExpenses, 2),
            'profit_margin' => $revenue > 0 ? round(((($revenue - $totalExpenses) / $revenue) * 100), 2) : 0,
            'cashbook_inflow' => round($cashInflow, 2),
            'cashbook_outflow' => round($cashOutflow, 2),
            'expense_breakdown' => $expenseByCategory->toArray(),
        ];
    }
}
