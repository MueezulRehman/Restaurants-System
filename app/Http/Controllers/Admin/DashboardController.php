<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Cashbook;
use App\Models\Expense;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $today = now()->toDateString();
        $user = auth()->user();

        if ($user->isSuperAdmin()) {
            $todayOrders = Order::whereDate('created_at', $today)->where('status', '!=', 'cancelled');
            $pendingOrders = Order::whereIn('status', ['pending', 'confirmed', 'preparing']);
            $expenseQuery = Expense::whereDate('date', $today);
            $bestSellerQuery = OrderItem::select('item_name', DB::raw('SUM(quantity) as total_qty'))
                ->whereHas('order', fn ($q) => $q->whereDate('created_at', $today)->where('status', '!=', 'cancelled'));
            $recentOrders = Order::latest()->limit(10)->get();
            $weekSales = Order::where('status', '!=', 'cancelled')
                ->where('created_at', '>=', now()->subDays(7))
                ->selectRaw('DATE(created_at) as day, SUM(total) as total')
                ->groupBy('day')
                ->orderBy('day')
                ->get();
        } else {
            $restaurantId = $user->restaurant_id;
            $todayOrders = Order::where('restaurant_id', $restaurantId)->whereDate('created_at', $today)->where('status', '!=', 'cancelled');
            $pendingOrders = Order::where('restaurant_id', $restaurantId)->whereIn('status', ['pending', 'confirmed', 'preparing']);
            $expenseQuery = Expense::where('restaurant_id', $restaurantId)->whereDate('date', $today);
            $bestSellerQuery = OrderItem::select('item_name', DB::raw('SUM(quantity) as total_qty'))
                ->whereHas('order', fn ($q) => $q->where('restaurant_id', $restaurantId)->whereDate('created_at', $today)->where('status', '!=', 'cancelled'));
            $recentOrders = Order::where('restaurant_id', $restaurantId)->latest()->limit(10)->get();
            $weekSales = Order::where('restaurant_id', $restaurantId)
                ->where('status', '!=', 'cancelled')
                ->where('created_at', '>=', now()->subDays(7))
                ->selectRaw('DATE(created_at) as day, SUM(total) as total')
                ->groupBy('day')
                ->orderBy('day')
                ->get();
        }

        $stats = [
            'orders_today' => $todayOrders->count(),
            'revenue_today' => $todayOrders->sum('total'),
            'pending_orders' => $pendingOrders->count(),
            'expenses_today' => $expenseQuery->sum('amount'),
        ];

        $bestSeller = $bestSellerQuery
            ->groupBy('item_name')
            ->orderByDesc('total_qty')
            ->first();

        return view('admin.dashboard', compact('stats', 'bestSeller', 'recentOrders', 'weekSales'));
    }
}
