<?php

namespace App\Services;

use App\Models\Branch;
use App\Models\BranchInventory;
use App\Models\Order;
use App\Models\Restaurant;
use App\Support\Tenancy;
use Illuminate\Support\Collection;
use Throwable;

class TenantPortfolioAggregator
{
    /**
     * Read tenant metrics without ever joining operational tables across DBs.
     *
     * Unavailable tenants are returned with an explicit status so the CEO
     * dashboard never presents a connection failure as zero activity.
     */
    public function summarize(Collection $restaurants, ?string $from = null, ?string $to = null): array
    {
        $fromDate = $from ?: now()->startOfMonth()->toDateString();
        $toDate = $to ?: now()->toDateString();
        $reports = [];

        foreach ($restaurants as $restaurant) {
            $reports[] = $this->summarizeRestaurant($restaurant, $fromDate, $toDate);
        }

        $available = collect($reports)->where('status', 'available');

        return [
            'from' => $fromDate,
            'to' => $toDate,
            'reports' => collect($reports),
            'totals' => [
                'businesses' => $available->count(),
                'orders' => $available->sum('orders'),
                'sales' => $available->sum('sales'),
                'active_orders' => $available->sum('active_orders'),
                'branches' => $available->sum('branches'),
                'low_stock' => $available->sum('low_stock'),
            ],
        ];
    }

    private function summarizeRestaurant(Restaurant $restaurant, string $from, string $to): array
    {
        $base = [
            'restaurant' => $restaurant,
            'status' => 'unavailable',
            'database' => $restaurant->hasTenantDatabase() ? 'tenant' : 'shared',
            'orders' => 0,
            'sales' => 0.0,
            'active_orders' => 0,
            'branches' => 0,
            'low_stock' => 0,
            'branch_reports' => collect(),
            'error' => null,
        ];

        try {
            return Tenancy::runFor($restaurant, function () use ($base, $from, $to): array {
                $orders = Order::query()
                    ->whereBetween('created_at', [$from . ' 00:00:00', $to . ' 23:59:59'])
                    ->where('status', '!=', 'cancelled');
                $branchRows = $orders->clone()
                    ->selectRaw('branch_id, COUNT(*) as orders, COALESCE(SUM(total), 0) as sales')
                    ->groupBy('branch_id')
                    ->get()
                    ->keyBy('branch_id');

                $branches = Branch::query()->where('is_active', true)->get();
                $branchReports = $branches->map(function (Branch $branch) use ($branchRows): array {
                    $row = $branchRows->get($branch->id);

                    return [
                        'branch' => $branch,
                        'orders' => (int) ($row?->orders ?? 0),
                        'sales' => (float) ($row?->sales ?? 0),
                    ];
                });

                return array_merge($base, [
                    'status' => 'available',
                    'orders' => (int) (clone $orders)->count(),
                    'sales' => (float) (clone $orders)->sum('total'),
                    'active_orders' => (int) (clone $orders)
                        ->whereIn('status', ['pending', 'confirmed', 'preparing', 'ready', 'out_for_delivery'])
                        ->count(),
                    'branches' => $branches->count(),
                    'low_stock' => (int) BranchInventory::query()->where('quantity', '<=', 0)->count(),
                    'branch_reports' => $branchReports,
                ]);
            });
        } catch (Throwable $exception) {
            report($exception);
            $base['error'] = 'Tenant data is temporarily unavailable.';

            return $base;
        }
    }
}
