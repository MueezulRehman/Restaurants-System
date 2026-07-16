<?php

namespace App\Services;

use App\Models\MenuItem;
use App\Models\OrderItem;
use App\Models\Category;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;

class StockAnalysisService
{
    /**
     * Get unsold and low-selling items for a given restaurant.
     */
    public function getUnsoldAndLowSellingItems(int $restaurantId, Carbon $startDate, Carbon $endDate, int $maxSalesThreshold = 5): Collection
    {
        $items = MenuItem::where('menu_items.restaurant_id', $restaurantId)
            ->leftJoinSub(
                OrderItem::whereBetween('created_at', [$startDate->startOfDay(), $endDate->endOfDay()])
                    ->groupBy('menu_item_id')
                    ->select(
                        'menu_item_id',
                        DB::raw('COUNT(id) as total_sold'),
                        DB::raw('SUM(quantity) as quantity_sold')
                    ),
                'sales',
                'menu_items.id',
                '=',
                'sales.menu_item_id'
            )
            ->select(
                'menu_items.id',
                'menu_items.name',
                'menu_items.price',
                'menu_items.cost_price',
                'menu_items.stock_quantity',
                'menu_items.category_id',
                'menu_items.restaurant_id',
                DB::raw('COALESCE(sales.total_sold, 0) as total_sold'),
                DB::raw('COALESCE(sales.quantity_sold, 0) as quantity_sold'),
                DB::raw('COALESCE(sales.quantity_sold * menu_items.price, 0) as total_revenue')
            )
            ->where(DB::raw('COALESCE(sales.quantity_sold, 0)'), '<=', $maxSalesThreshold)
            ->orderBy('quantity_sold', 'desc')
            ->get()
            ->map(function ($item) {
                $item->unsold = $item->quantity_sold == 0;
                $item->low_selling = $item->quantity_sold > 0 && $item->quantity_sold <= 5;
                return $item;
            });

        return $items;
    }

    /**
     * Get top selling items for a given restaurant.
     */
    public function getTopSellingItems(int $restaurantId, int $limit = 10, Carbon $startDate = null, Carbon $endDate = null): Collection
    {
        $startDate = $startDate ?? Carbon::now()->subMonths(1);
        $endDate = $endDate ?? Carbon::now();

        return MenuItem::where('menu_items.restaurant_id', $restaurantId)
            ->with(['category'])
            ->leftJoinSub(
                OrderItem::whereBetween('created_at', [$startDate->startOfDay(), $endDate->endOfDay()])
                    ->groupBy('menu_item_id')
                    ->select(
                        'menu_item_id',
                        DB::raw('COUNT(id) as times_ordered'),
                        DB::raw('SUM(quantity) as quantity_sold')
                    ),
                'sales',
                'menu_items.id',
                '=',
                'sales.menu_item_id'
            )
            ->select(
                'menu_items.id',
                'menu_items.name',
                'menu_items.price',
                'menu_items.cost_price',
                'menu_items.stock_quantity',
                'menu_items.category_id',
                'menu_items.restaurant_id',
                DB::raw('COALESCE(sales.times_ordered, 0) as times_ordered'),
                DB::raw('COALESCE(sales.quantity_sold, 0) as quantity_sold'),
                DB::raw('COALESCE(sales.quantity_sold * menu_items.price, 0) as total_revenue')
            )
            ->havingRaw('COALESCE(sales.quantity_sold, 0) > 0')
            ->orderBy('quantity_sold', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get sales trend by category.
     */
    public function getSalesTrendByCategory(int $restaurantId, Carbon $startDate, Carbon $endDate): Collection
    {
        $categorySales = MenuItem::where('menu_items.restaurant_id', $restaurantId)
            ->leftJoin('order_items', 'menu_items.id', '=', 'order_items.menu_item_id')
            ->whereBetween('order_items.created_at', [$startDate->startOfDay(), $endDate->endOfDay()])
            ->groupBy('menu_items.category_id')
            ->select(
                'menu_items.category_id',
                DB::raw('SUM(order_items.quantity) as total_quantity'),
                DB::raw('SUM(order_items.quantity * menu_items.price) as total_revenue'),
                DB::raw('COUNT(DISTINCT menu_items.id) as item_count')
            )
            ->get()
            ->keyBy('category_id');

        return Category::where('restaurant_id', $restaurantId)
            ->get()
            ->map(function ($category) use ($categorySales) {
                $sales = $categorySales->get($category->id);
                $category->total_quantity = $sales?->total_quantity ?? 0;
                $category->total_revenue = $sales?->total_revenue ?? 0;
                $category->item_count = $sales?->item_count ?? 0;
                return $category;
            })
            ->filter(fn ($cat) => $cat->total_revenue > 0)
            ->sortByDesc('total_revenue')
            ->values();
    }
}
