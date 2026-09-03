<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BillingCycle;
use App\Models\Feedback;
use App\Models\Restaurant;
use App\Models\RestaurantSubscription;
use App\Support\Tenancy;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

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

        $activeUserIds = collect();
        if (Schema::hasTable('sessions')) {
            $activeSince = now()->subMinutes((int) config('session.lifetime', 120))->timestamp;
            $activeUserIds = DB::table('sessions')
                ->whereNotNull('user_id')
                ->where('last_activity', '>=', $activeSince)
                ->pluck('user_id');
        }

        $businessReports = Restaurant::with(['subscription.plan', 'users' => function ($query) {
            $query->whereIn('role', ['admin', 'manager'])
                ->orderByDesc('last_login_at');
        }])->latest()->get()->map(function (Restaurant $restaurant) use ($activeUserIds) {
            $managers = $restaurant->users;
            $activeManagers = $managers->whereIn('id', $activeUserIds);

            return [
                'restaurant' => $restaurant,
                'manager_count' => $managers->count(),
                'logged_in' => $activeManagers->isNotEmpty(),
                'last_login_at' => $managers->first()?->last_login_at,
                'active_since' => $activeManagers->min('last_login_at'),
                'subscription_status' => $restaurant->subscription?->status ?? 'not configured',
                'plan_name' => $restaurant->subscription?->plan?->name ?? 'No plan',
            ];
        });

        return view('admin.dashboard', compact('platformStats', 'businessReports'));
    }
}
