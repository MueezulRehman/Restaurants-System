<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Cashbook;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\SalesReturn;
use App\Models\StockAdjustment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SalesReturnController extends Controller
{
    public function index(Request $request)
    {
        $restaurantId = Auth::user()->effectiveRestaurantId();
        $returns = SalesReturn::with(['order', 'orderItem', 'customer'])
            ->where('restaurant_id', $restaurantId)->latest()->paginate(20)->withQueryString();

        $orders = Order::with(['items', 'customer'])->where('restaurant_id', $restaurantId)
            ->where('status', '!=', 'cancelled')->latest()->limit(100)->get();

        return view('admin.sales-returns.index', compact('returns', 'orders'));
    }

    public function store(Request $request)
    {
        $restaurantId = Auth::user()->effectiveRestaurantId();
        $validated = $request->validate([
            'order_item_id' => 'required|integer',
            'quantity' => 'required|numeric|min:0.001',
            'refund_method' => 'required|in:cash,customer_credit',
            'reason' => 'nullable|string|max:255',
        ]);

        DB::transaction(function () use ($validated, $restaurantId): void {
            $item = OrderItem::with(['order', 'variant', 'menuItem'])->whereKey($validated['order_item_id'])->firstOrFail();
            abort_unless($item->order && $item->order->restaurant_id === $restaurantId, 404);

            $alreadyReturned = (float) SalesReturn::where('order_item_id', $item->id)->sum('quantity');
            $quantity = (float) $validated['quantity'];
            abort_if($alreadyReturned + $quantity > (float) $item->quantity, 422, 'Return quantity exceeds the quantity sold.');

            $amount = round($quantity * (float) $item->unit_price, 2);
            $return = SalesReturn::create([
                'restaurant_id' => $restaurantId,
                'order_id' => $item->order_id,
                'order_item_id' => $item->id,
                'customer_id' => $item->order->customer_id,
                'processed_by' => Auth::id(),
                'quantity' => $quantity,
                'amount' => $amount,
                'refund_method' => $validated['refund_method'],
                'reason' => $validated['reason'] ?? null,
            ]);

            $saleAdjustment = StockAdjustment::where('restaurant_id', $restaurantId)
                ->where('reason', 'sale')->where('reference_id', $item->order_id)
                ->when($item->product_variant_id, fn($q) => $q->where('product_variant_id', $item->product_variant_id))
                ->when(! $item->product_variant_id && $item->menu_item_id, fn($q) => $q->where('menu_item_id', $item->menu_item_id))
                ->first();

            if ($saleAdjustment) {
                $stockBefore = null;
                $stockAfter = null;
                if ($item->variant) {
                    $stockBefore = (float) $item->variant->quantity_available;
                    $stockAfter = $stockBefore + $quantity;
                    $item->variant->update(['quantity_available' => $stockAfter]);
                } elseif ($item->menuItem && $item->menuItem->track_stock) {
                    $stockBefore = (float) $item->menuItem->stock_quantity;
                    $stockAfter = $stockBefore + $quantity;
                    $item->menuItem->update(['stock_quantity' => $stockAfter]);
                }

                if ($stockBefore !== null) {
                    StockAdjustment::create([
                        'restaurant_id' => $restaurantId,
                        'product_variant_id' => $item->product_variant_id,
                        'menu_item_id' => $item->product_variant_id ? null : $item->menu_item_id,
                        'user_id' => Auth::id(),
                        'quantity_before' => $stockBefore,
                        'quantity_after' => $stockAfter,
                        'change_quantity' => $quantity,
                        'reason' => 'return',
                        'reference_id' => $return->id,
                        'notes' => "Customer return for order {$item->order->order_number}",
                    ]);
                }
            }

            if ($validated['refund_method'] === 'cash') {
                Cashbook::create([
                    'restaurant_id' => $restaurantId,
                    'type' => 'out',
                    'amount' => $amount,
                    'description' => "Return for order {$item->order->order_number}",
                    'source' => 'sales_return',
                    'order_id' => $item->order_id,
                    'date' => now()->toDateString(),
                    'created_by' => Auth::id(),
                ]);
            } elseif ($item->order->customer) {
                $item->order->customer->recordBalanceChange($amount, "Credit for return on order {$item->order->order_number}", [
                    'restaurant_id' => $restaurantId,
                    'created_by' => Auth::id(),
                    'source' => 'sales_return',
                    'type' => 'payment',
                ]);
            }
        });

        return back()->with('success', 'Sales return recorded and stock/refund updated.');
    }
}
