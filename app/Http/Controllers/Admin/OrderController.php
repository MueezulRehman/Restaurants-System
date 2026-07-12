<?php

namespace App\Http\Controllers\Admin;

use App\Events\OrderStatusUpdated;
use App\Http\Controllers\Controller;
use App\Models\Cashbook;
use Illuminate\Broadcasting\BroadcastException;
use App\Models\Order;
use App\Models\StockAdjustment;
use App\Models\ProductVariant;
use App\Models\MenuItem;
use App\Services\StockService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $query = Order::with('items')->latest();

        if (! Auth::user()->isSuperAdmin()) {
            $query->where('restaurant_id', Auth::user()->effectiveRestaurantId());
        }

        if ($request->filled('type')) {
            $query->where('order_type', $request->type);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $orders = $query->paginate(20)->withQueryString();

        return view('admin.orders.index', compact('orders'));
    }

    public function show(Order $order)
    {
        $this->authorizeRestaurant($order);

        $order->load(['items.toppings', 'delivery.rider']);
        return view('admin.orders.show', compact('order'));
    }

    public function updateStatus(Request $request, Order $order)
    {
        $this->authorizeRestaurant($order);

        $validated = $request->validate([
            'status' => 'required|in:pending,confirmed,preparing,ready,out_for_delivery,delivered,cancelled',
        ]);

        $order->status = $validated['status'];

        if ($validated['status'] === 'confirmed' && !$order->confirmed_at) {
            $order->confirmed_at = now();
        }
        if ($validated['status'] === 'ready' && !$order->ready_at) {
            $order->ready_at = now();
        }
        if ($validated['status'] === 'delivered' && !$order->delivered_at) {
            $order->delivered_at = now();

            // Decrement tracked stock for each item and log adjustments
            $order->load(['items.variant', 'items.menuItem']);
            foreach ($order->items as $it) {
                $qty = (int) $it->quantity;

                if ($it->product_variant_id && $it->variant) {
                    $variant = $it->variant;
                    $before = (int) $variant->quantity_available;
                    $after = max(0, $before - $qty);
                    $variant->update(['quantity_available' => $after]);

                    StockAdjustment::create([
                        'restaurant_id' => $order->restaurant_id,
                        'product_variant_id' => $variant->id,
                        'user_id' => Auth::id(),
                        'quantity_before' => $before,
                        'quantity_after' => $after,
                        'change_quantity' => -$qty,
                        'reason' => 'sale',
                        'reference_id' => $order->id,
                        'notes' => "Order {$order->order_number}",
                    ]);

                    if ($after < ($variant->reorder_threshold ?? 10) && $before >= ($variant->reorder_threshold ?? 10)) {
                        \App\Services\NotificationService::sendLowStockAlert($variant->restaurant, $variant);
                    }
                } elseif ($it->menuItem && $it->menuItem->track_stock) {
                    $menuItem = $it->menuItem;
                    $before = (int) $menuItem->stock_quantity;
                    $after = max(0, $before - $qty);
                    $menuItem->update(['stock_quantity' => $after]);

                    StockAdjustment::create([
                        'restaurant_id' => $order->restaurant_id,
                        'product_variant_id' => null,
                        'user_id' => Auth::id(),
                        'quantity_before' => $before,
                        'quantity_after' => $after,
                        'change_quantity' => -$qty,
                        'reason' => 'sale',
                        'reference_id' => $order->id,
                        'notes' => "Order {$order->order_number}",
                    ]);

                    if ($after <= $menuItem->low_stock_threshold && $before > $menuItem->low_stock_threshold) {
                        \App\Services\NotificationService::sendLowStockAlert($menuItem->restaurant, $menuItem);
                    }
                }
            }

            // Auto-log the sale into the cashbook the moment an order completes,
            // so the cashbook stays accurate without manual double-entry.
            if ($order->payment_method === 'cash') {
                Cashbook::create([
                    'type' => 'in',
                    'amount' => $order->total,
                    'description' => "Order {$order->order_number} ({$order->customer_name})",
                    'source' => 'order',
                    'order_id' => $order->id,
                    'date' => now()->toDateString(),
                    'created_by' => Auth::id(),
                ]);
            }
        }

        $order->save();

        // Push the new status live to the customer's tracking page in real time.
        // A failed broadcast must not prevent the status update from persisting.
        try {
            broadcast(new OrderStatusUpdated($order))->toOthers();
        } catch (BroadcastException $e) {
            logger()->warning('Broadcast failed for order status update: ' . $e->getMessage());
        }

        return back()->with('success', "Order #{$order->order_number} updated to {$order->status_label}.");
    }

    /**
     * Abort with 403 if a non-super-admin user tries to view/modify an order
     * that doesn't belong to their own restaurant.
     */
    protected function authorizeRestaurant(Order $order): void
    {
        $user = Auth::user();
        if (! $user->isSuperAdmin() && $order->restaurant_id !== $user->effectiveRestaurantId()) {
            abort(403, 'You do not have access to this order.');
        }
    }
}
