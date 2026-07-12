<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Cashbook;
use App\Models\Expense;
use App\Models\Feedback;
use App\Models\MenuItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use App\Support\Tenancy;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ManagerDashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $canAccess = $user instanceof User
            && ($user->isRestaurantManager() || ($user->isSuperAdmin() && Tenancy::isImpersonating()));
        abort_unless($canAccess, 403);

        $restaurantId = $user->effectiveRestaurantId();
        $today = now()->toDateString();

        $todayOrders = Order::where('restaurant_id', $restaurantId)
            ->whereDate('created_at', $today)
            ->where('status', '!=', 'cancelled');

        $pendingOrders = Order::where('restaurant_id', $restaurantId)
            ->whereIn('status', ['pending', 'confirmed', 'preparing']);

        $expenseQuery = Expense::where('restaurant_id', $restaurantId)
            ->whereDate('date', $today);

        $bestSeller = OrderItem::select('item_name', DB::raw('SUM(quantity) as total_qty'))
            ->whereHas('order', fn ($query) => $query->where('restaurant_id', $restaurantId)
                ->whereDate('created_at', $today)
                ->where('status', '!=', 'cancelled'))
            ->groupBy('item_name')
            ->orderByDesc('total_qty')
            ->first();

        $recentOrders = Order::where('restaurant_id', $restaurantId)
            ->latest()
            ->limit(10)
            ->get();

        $weekSales = Order::where('restaurant_id', $restaurantId)
            ->where('status', '!=', 'cancelled')
            ->where('created_at', '>=', now()->subDays(7))
            ->selectRaw('DATE(created_at) as day, SUM(total) as total')
            ->groupBy('day')
            ->orderBy('day')
            ->get();

        // Low Stock Items — menu items (or products, for shop-type
        // businesses) that opted into stock tracking and have fallen at or
        // below their own low_stock_threshold.
        $lowStockCount = MenuItem::where('restaurant_id', $restaurantId)
            ->where('track_stock', true)
            ->whereColumn('stock_quantity', '<=', 'low_stock_threshold')
            ->count();

        // Monthly Net Profit — cashbook credits minus debits, current
        // calendar month.
        $monthCashbook = Cashbook::where('restaurant_id', $restaurantId)
            ->whereYear('date', now()->year)
            ->whereMonth('date', now()->month);
        $monthlyNetProfit = (clone $monthCashbook)->where('type', 'in')->sum('amount')
            - (clone $monthCashbook)->where('type', 'out')->sum('amount');

        // New Customer Feedback — open feedback submitted by a customer
        // (as opposed to a staff suggestion) for this restaurant.
        $newFeedbackCount = Feedback::where('restaurant_id', $restaurantId)
            ->whereNotNull('customer_id')
            ->where('status', 'open')
            ->count();

        $periods = [
            'day' => ['label' => 'Daily', 'start' => now()->startOfDay(), 'end' => now()->endOfDay()],
            'week' => ['label' => 'Weekly', 'start' => now()->startOfWeek(), 'end' => now()->endOfWeek()],
            'month' => ['label' => 'Monthly', 'start' => now()->startOfMonth(), 'end' => now()->endOfMonth()],
            '6months' => ['label' => '6 Months', 'start' => now()->subMonths(5)->startOfMonth(), 'end' => now()->endOfMonth()],
            'year' => ['label' => 'Yearly', 'start' => now()->startOfYear(), 'end' => now()->endOfYear()],
        ];

        $periodSummaries = [];
        foreach ($periods as $key => $period) {
            $ordersQuery = Order::where('restaurant_id', $restaurantId)
                ->where('status', '!=', 'cancelled')
                ->whereBetween('created_at', [$period['start'], $period['end']]);
            $expensesQuery = Expense::where('restaurant_id', $restaurantId)
                ->whereBetween('date', [$period['start']->toDateString(), $period['end']->toDateString()]);

            $income = (float) $ordersQuery->sum('total');
            $expense = (float) $expensesQuery->sum('amount');

            $periodSummaries[] = [
                'key' => $key,
                'label' => $period['label'],
                'income' => $income,
                'expense' => $expense,
                'profit' => $income - $expense,
            ];
        }

        $stats = [
            'orders_today' => $todayOrders->count(),
            'revenue_today' => $todayOrders->sum('total'),
            'pending_orders' => $pendingOrders->count(),
            'expenses_today' => $expenseQuery->sum('amount'),
            'low_stock_items' => $lowStockCount,
            'monthly_net_profit' => $monthlyNetProfit,
            'new_customer_feedback' => $newFeedbackCount,
            'period_summaries' => $periodSummaries,
        ];

        $restaurant = $user->effectiveRestaurant();

        return view('admin.manager-dashboard', compact('stats', 'bestSeller', 'recentOrders', 'weekSales', 'restaurant'));
    }

    public function subscriptionExpired()
    {
        return view('manager.subscription-expired');
    }
}
