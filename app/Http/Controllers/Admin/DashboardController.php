<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BillingCycle;
use App\Models\Cashbook;
use App\Models\Expense;
use App\Models\Feedback;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Restaurant;
use App\Models\RestaurantSubscription;
use App\Support\Tenancy;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        if (! $user->isSuperAdmin()) {
            // Restaurant staff have their own dashboard/template; send them
            // there instead of duplicating that logic on this route.
            return redirect()->route('manager.dashboard');
        }

        if (Tenancy::isImpersonating()) {
            // Super admin is "inside" one restaurant — show exactly what
            // that restaurant's own manager would see.
            return app(ManagerDashboardController::class)->index();
        }

        $today = now()->toDateString();

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

        // --- Platform-wide KPIs (doc Section 11.2) ---
        $totalActiveBusinesses = Restaurant::where('status', 'active')->count();

        $trialsExpiringThisWeek = Restaurant::where('status', 'trial')
            ->whereNotNull('trial_ends_at')
            ->whereBetween('trial_ends_at', [now(), now()->addDays(7)])
            ->count();

        $overdueSubscriptions = RestaurantSubscription::where('status', 'expired')->count();

        $revenueThisMonth = BillingCycle::where('status', 'paid')
            ->whereNotNull('paid_at')
            ->whereYear('paid_at', now()->year)
            ->whereMonth('paid_at', now()->month)
            ->sum('amount');

        $newFeedbackCount = Feedback::whereNotNull('user_id')
            ->where('status', 'open')
            ->count();

        $platformStats = [
            'total_active_businesses' => $totalActiveBusinesses,
            'trials_expiring_this_week' => $trialsExpiringThisWeek,
            'overdue_subscriptions' => $overdueSubscriptions,
            'revenue_this_month' => $revenueThisMonth,
            'new_feedback' => $newFeedbackCount,
        ];

        return view('admin.dashboard', compact('stats', 'bestSeller', 'recentOrders', 'weekSales', 'platformStats'));
    }
}
