<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Expense;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ManagerDashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        abort_unless($user instanceof User && $user->isRestaurantManager(), 403);

        $restaurantId = $user->restaurant_id;
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

        $stats = [
            'orders_today' => $todayOrders->count(),
            'revenue_today' => $todayOrders->sum('total'),
            'pending_orders' => $pendingOrders->count(),
            'expenses_today' => $expenseQuery->sum('amount'),
        ];

        $restaurant = $user->restaurant;

        return view('admin.manager-dashboard', compact('stats', 'bestSeller', 'recentOrders', 'weekSales', 'restaurant'));
    }

    public function subscriptionExpired()
    {
        return view('manager.subscription-expired');
    }
}
