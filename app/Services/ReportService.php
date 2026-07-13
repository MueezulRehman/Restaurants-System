<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Expense;
use App\Models\MenuItem;
use App\Models\OrderItem;
use App\Models\Cashbook;
use App\Models\Attendance;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

/**
 * Reports Service — Generates business analytics across multiple dimensions
 * Supports: Sales, Income vs Expense, Item Performance, Staff Attendance, Salary
 * Date filters: Today, Week, Month, 6 Months, Year, Past Years, Custom Range
 */
class ReportService
{
    protected $restaurantId;
    protected $startDate;
    protected $endDate;

    public function __construct($restaurantId, $startDate = null, $endDate = null)
    {
        $this->restaurantId = $restaurantId;
        $this->startDate = $startDate ?? now()->startOfDay();
        $this->endDate = $endDate ?? now()->endOfDay();
    }

    /**
     * Sales Report — total sales, order count, avg order value, top-selling items
     */
    public function salesReport(): array
    {
        $orders = Order::where('restaurant_id', $this->restaurantId)
            ->whereBetween('created_at', [$this->startDate, $this->endDate])
            ->where('status', '!=', 'cancelled')
            ->get();

        $totalSales = $orders->sum('total');
        $orderCount = $orders->count();
        $avgOrderValue = $orderCount > 0 ? $totalSales / $orderCount : 0;

        // Top-selling items
        $topItems = OrderItem::whereIn('order_id', $orders->pluck('id'))
            ->groupBy('menu_item_id')
            ->selectRaw('menu_item_id, SUM(quantity) as total_qty, SUM(total_price) as total_revenue')
            ->orderByDesc('total_revenue')
            ->limit(10)
            ->get()
            ->map(function ($item) {
                $menuItem = \App\Models\MenuItem::find($item->menu_item_id);
                return [
                    'name' => $menuItem?->name ?? 'Unknown',
                    'quantity' => $item->total_qty,
                    'revenue' => $item->total_revenue,
                ];
            });

        return [
            'total_sales' => $totalSales,
            'order_count' => $orderCount,
            'avg_order_value' => $avgOrderValue,
            'top_items' => $topItems,
            'period' => ['start' => $this->startDate, 'end' => $this->endDate],
        ];
    }

    /**
     * Income vs Expense Report — cashbook credits vs debits, net profit/loss
     */
    public function incomeExpenseReport(): array
    {
        $income = Cashbook::where('restaurant_id', $this->restaurantId)
            ->where('type', 'in')
            ->whereBetween('created_at', [$this->startDate, $this->endDate])
            ->sum('amount');

        $expense = Cashbook::where('restaurant_id', $this->restaurantId)
            ->where('type', 'out')
            ->whereBetween('created_at', [$this->startDate, $this->endDate])
            ->sum('amount');

        $netProfit = $income - $expense;

        // Expense breakdown by category
        $expenseBreakdown = Expense::where('restaurant_id', $this->restaurantId)
            ->whereBetween('created_at', [$this->startDate, $this->endDate])
            ->groupBy('category')
            ->selectRaw('category, SUM(amount) as total')
            ->get()
            ->keyBy('category');

        return [
            'total_income' => $income,
            'total_expense' => $expense,
            'net_profit' => $netProfit,
            'expense_breakdown' => $expenseBreakdown,
        ];
    }

    /**
     * Item Performance — units sold, revenue, profit margin per item/variant
     */
    public function itemPerformanceReport(): array
    {
        $items = OrderItem::whereIn('order_id',
            Order::where('restaurant_id', $this->restaurantId)
                ->whereBetween('created_at', [$this->startDate, $this->endDate])
                ->pluck('id')
        )
            ->where('item_type', 'menu_item')
            ->groupBy('menu_item_id')
            ->selectRaw('menu_item_id, SUM(quantity) as units_sold, SUM(total_price) as revenue')
            ->get()
            ->map(function ($item) {
                $menuItem = \App\Models\MenuItem::find($item->menu_item_id);
                $costPrice = $menuItem->cost_price ?? 0;
                $profitMargin = $menuItem->price > 0 ? (($menuItem->price - $costPrice) / $menuItem->price) * 100 : 0;
                $totalCost = $costPrice * $item->units_sold;
                $totalProfit = $item->revenue - $totalCost;

                return [
                    'item_name' => $menuItem->name,
                    'units_sold' => $item->units_sold,
                    'revenue' => $item->revenue,
                    'cost_total' => $totalCost,
                    'profit_total' => $totalProfit,
                    'profit_margin_percent' => $profitMargin,
                ];
            });

        return [
            'items' => $items,
            'period' => ['start' => $this->startDate, 'end' => $this->endDate],
        ];
    }

    /**
     * Staff Attendance Summary — present/absent/late count per staff
     */
    public function staffAttendanceReport(): array
    {
        $staff = Attendance::where('restaurant_id', $this->restaurantId)
            ->whereBetween('created_at', [$this->startDate, $this->endDate])
            ->groupBy('user_id')
            ->selectRaw('user_id, status, COUNT(*) as count')
            ->get()
            ->groupBy('user_id')
            ->map(function ($records, $userId) {
                $user = User::find($userId);
                return [
                    'staff_name' => $user->name ?? 'Unknown',
                    'present' => $records->where('status', 'present')->sum('count'),
                    'absent' => $records->where('status', 'absent')->sum('count'),
                    'half_day' => $records->where('status', 'half_day')->sum('count'),
                    'leave' => $records->where('status', 'leave')->sum('count'),
                ];
            });

        return [
            'staff_attendance' => $staff,
            'period' => ['start' => $this->startDate, 'end' => $this->endDate],
        ];
    }

    /**
     * Salary Report — monthly payouts, deductions, paid/unpaid status
     */
    public function salaryReport(): array
    {
        $salaries = \App\Models\Salary::where('restaurant_id', $this->restaurantId)
            ->whereBetween('created_at', [$this->startDate, $this->endDate])
            ->get()
            ->map(function ($salary) {
                $user = User::find($salary->user_id);
                return [
                    'staff_name' => $user->name ?? 'Unknown',
                    'month' => $salary->month->format('Y-m'),
                    'amount' => $salary->amount,
                    'deductions' => $salary->deductions,
                    'net_paid' => $salary->net_paid,
                    'status' => $salary->paid_at ? 'paid' : 'unpaid',
                    'paid_date' => $salary->paid_at?->format('Y-m-d'),
                ];
            });

        $totalSalaries = $salaries->sum('net_paid');
        $totalDeductions = $salaries->sum('deductions');

        return [
            'salaries' => $salaries,
            'total_payroll' => $totalSalaries,
            'total_deductions' => $totalDeductions,
            'paid_count' => $salaries->where('status', 'paid')->count(),
            'unpaid_count' => $salaries->where('status', 'unpaid')->count(),
        ];
    }

    /**
     * Preset date range helpers
     */
    public static function forToday($restaurantId): self
    {
        return new self($restaurantId, now()->startOfDay(), now()->endOfDay());
    }

    public static function forWeek($restaurantId): self
    {
        return new self($restaurantId, now()->startOfWeek(), now()->endOfWeek());
    }

    public static function forMonth($restaurantId): self
    {
        return new self($restaurantId, now()->startOfMonth(), now()->endOfMonth());
    }

    public static function for6Months($restaurantId): self
    {
        return new self($restaurantId, now()->subMonths(6)->startOfDay(), now()->endOfDay());
    }

    public static function forYear($restaurantId): self
    {
        return new self($restaurantId, now()->startOfYear(), now()->endOfYear());
    }

    public static function forYear_($restaurantId, $year): self
    {
        return new self($restaurantId, Carbon::parse("$year-01-01"), Carbon::parse("$year-12-31"));
    }
}
