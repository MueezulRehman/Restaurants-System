<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Restaurant;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Super admin per-business reports.
 * @author Mueez Ul Rehman
 */
class BusinessReportController extends Controller
{
    public function index(Request $request)
    {
        abort_unless(Auth::user() instanceof User && Auth::user()->isSuperAdmin(), 403);

        $restaurants = Restaurant::with('businessType')->orderBy('name')->get();

        $from = $request->input('from', now()->subDays(30)->toDateString());
        $to = $request->input('to', now()->toDateString());

        $rows = $restaurants->map(function (Restaurant $r) use ($from, $to) {
            $orders = Order::where('restaurant_id', $r->id)
                ->where('status', '!=', 'cancelled')
                ->whereDate('created_at', '>=', $from)
                ->whereDate('created_at', '<=', $to);

            return [
                'restaurant' => $r,
                'orders_count' => (clone $orders)->count(),
                'revenue' => (clone $orders)->sum('total'),
                'pending' => Order::where('restaurant_id', $r->id)
                    ->whereIn('status', ['pending', 'confirmed', 'preparing'])
                    ->count(),
            ];
        });

        return view('admin.reports.businesses', compact('rows', 'from', 'to'));
    }

    public function show(Restaurant $restaurant, Request $request)
    {
        abort_unless(Auth::user() instanceof User && Auth::user()->isSuperAdmin(), 403);

        $from = $request->input('from', now()->subDays(30)->toDateString());
        $to = $request->input('to', now()->toDateString());

        $orders = Order::where('restaurant_id', $restaurant->id)
            ->whereDate('created_at', '>=', $from)
            ->whereDate('created_at', '<=', $to)
            ->latest()
            ->paginate(30);

        $summary = [
            'orders' => Order::where('restaurant_id', $restaurant->id)
                ->where('status', '!=', 'cancelled')
                ->whereDate('created_at', '>=', $from)
                ->whereDate('created_at', '<=', $to)
                ->count(),
            'revenue' => Order::where('restaurant_id', $restaurant->id)
                ->where('status', '!=', 'cancelled')
                ->whereDate('created_at', '>=', $from)
                ->whereDate('created_at', '<=', $to)
                ->sum('total'),
        ];

        return view('admin.reports.business-show', compact('restaurant', 'orders', 'summary', 'from', 'to'));
    }
}
