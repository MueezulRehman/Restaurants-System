<?php

namespace App\Services;

use App\Models\MenuItem;
use App\Models\OrderItem;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Builder;

class StockAnalysisService
{
    /**
     * Get unsold and low-selling items within a date range.
     *
     * @param int $restaurantId
     * @param string $startDate (Y-m-d format)
     * @param string $endDate (Y-m-d format)
     * @param int $maxSalesThreshold Maximum sales to be considered "low-selling"
     * @return Collection
     */
    public function getUnsoldAndLowSellingItems(
        int $restaurantId,
        string $startDate = null,
        string $endDate = null,
        int $maxSalesThreshold = 0
    ): Collection {
        $startDate = $startDate ? Carbon::createFromFormat('Y-m-d', $startDate)->startOfDay() : now()->subDays(7)->startOfDay();
        $endDate = $endDate ? Carbon::createFromFormat('Y-m-d', $endDate)->endOfDay() : now()->endOfDay();

        // Get all items for the restaurant
        $items = MenuItem::where('restaurant_id', $restaurantId)
            ->with('category')
            ->get();

        $analysis = $items->map(function ($item) use ($restaurantId, $startDate, $endDate, $maxSalesThreshold) {
            $salesCount = OrderItem::whereHas('order', function ($query) use ($restaurantId, $startDate, $endDate) {
                $query->where('restaurant_id', $restaurantId)
                    ->whereBetween('created_at', [$startDate, $endDate])
                    ->whereIn('status', ['delivered', 'completed', 'ready', 'confirmed']); // Count confirmed orders too
            })
                ->where('menu_item_id', $item->id)
                ->sum('quantity');

            return [
                'id' => $item->id,
                'name' => $item->name,
                'sku' => $item->sku,
                'category' => $item->category?->name,
                'stock_quantity' => $item->stock_quantity,
                'price' => $item->price,
                'cost_price' => $item->cost_price,
                'sales_count' => (int) $salesCount,
                'is_available' => $item->is_available,
                'track_stock' => $item->track_stock,
                'status' => $this->determineStatus($salesCount, $maxSalesThreshold),
                'profit_margin' => $item->cost_price > 0 ? (($item->price - $item->cost_price) / $item->price * 100) : 0,
            ];
        });

        // Filter: unsold items AND items with sales <= threshold
        return $analysis->filter(function ($item) use ($maxSalesThreshold) {
            return $item['sales_count'] === 0 || $item['sales_count'] <= $maxSalesThreshold;
        })->values();
    }

    /**
     * Get top-selling items within a date range.
     *
     * @param int $restaurantId
     * @param int $limit
     * @param string $startDate (Y-m-d format)
     * @param string $endDate (Y-m-d format)
     * @return Collection
     */
    public function getTopSellingItems(
        int $restaurantId,
        int $limit = 20,
        string $startDate = null,
        string $endDate = null
    ): Collection {
        $startDate = $startDate ? Carbon::createFromFormat('Y-m-d', $startDate)->startOfDay() : now()->subDays(7)->startOfDay();
        $endDate = $endDate ? Carbon::createFromFormat('Y-m-d', $endDate)->endOfDay() : now()->endOfDay();

        $topItems = OrderItem::selectRaw('menu_item_id, SUM(quantity) as total_sold, COUNT(DISTINCT order_id) as order_count')
            ->whereHas('order', function ($query) use ($restaurantId, $startDate, $endDate) {
                $query->where('restaurant_id', $restaurantId)
                    ->whereBetween('created_at', [$startDate, $endDate])
                    ->whereIn('status', ['delivered', 'completed', 'ready', 'confirmed']);
            })
            ->where('menu_item_id', '!=', null)
            ->groupBy('menu_item_id')
            ->orderByRaw('SUM(quantity) DESC')
            ->limit($limit)
            ->with('menuItem.category')
            ->get();

        return $topItems->map(function ($item) {
            $menuItem = $item->menuItem;
            return [
                'id' => $menuItem->id,
                'name' => $menuItem->name,
                'sku' => $menuItem->sku,
                'category' => $menuItem->category?->name,
                'stock_quantity' => $menuItem->stock_quantity,
                'price' => $menuItem->price,
                'cost_price' => $menuItem->cost_price,
                'total_sold' => (int) $item->total_sold,
                'order_count' => (int) $item->order_count,
                'average_per_order' => round($item->total_sold / $item->order_count, 2),
                'revenue' => $item->total_sold * $menuItem->price,
                'profit' => $item->total_sold * ($menuItem->price - $menuItem->cost_price),
            ];
        });
    }

    /**
     * Determine item status based on sales.
     */
    private function determineStatus(int $salesCount, int $maxThreshold): string
    {
        if ($salesCount === 0) {
            return 'unsold';
        }
        if ($salesCount <= $maxThreshold) {
            return 'low_sales';
        }
        return 'normal';
    }

    /**
     * Get sales trend for comparison.
     */
    public function getSalesTrendByCategory(
        int $restaurantId,
        string $startDate = null,
        string $endDate = null
    ): Collection {
        $startDate = $startDate ? Carbon::createFromFormat('Y-m-d', $startDate)->startOfDay() : now()->subDays(7)->startOfDay();
        $endDate = $endDate ? Carbon::createFromFormat('Y-m-d', $endDate)->endOfDay() : now()->endOfDay();

        return OrderItem::selectRaw('
            menu_items.category_id,
            categories.name as category_name,
            SUM(order_items.quantity) as total_quantity,
            COUNT(DISTINCT order_items.order_id) as total_orders,
            SUM(order_items.total_price) as total_revenue
        ')
            ->join('menu_items', 'order_items.menu_item_id', '=', 'menu_items.id')
            ->join('categories', 'menu_items.category_id', '=', 'categories.id')
            ->whereHas('order', function ($query) use ($restaurantId, $startDate, $endDate) {
                $query->where('restaurant_id', $restaurantId)
                    ->whereBetween('created_at', [$startDate, $endDate])
                    ->whereIn('status', ['delivered', 'completed', 'ready', 'confirmed']);
            })
            ->groupBy('menu_items.category_id', 'categories.name')
            ->orderByRaw('SUM(order_items.quantity) DESC')
            ->get();
    }
}
