<?php

namespace App\Http\Controllers\Admin;

use App\Events\OrderStatusUpdated;
use App\Http\Controllers\Controller;
use App\Models\Cashbook;
use App\Models\Order;
use App\Support\OrderStockService;
use Illuminate\Broadcasting\BroadcastException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Manager orders — stock reserved on CONFIRM (online), not on delivered.
 * POS orders already decremented at sale; OrderStockService skips channel=pos.
 *
 * @author Mueez Ul Rehman
 */
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
        $order->load(['items.toppings', 'delivery']);

        return view('admin.orders.show', compact('order'));
    }

    public function updateStatus(Request $request, Order $order)
    {
        $this->authorizeRestaurant($order);

        $validated = $request->validate([
            'status' => 'required|in:pending,confirmed,preparing,ready,out_for_delivery,delivered,cancelled',
        ]);

        $order->status = $validated['status'];

        // CONFIRM → reserve stock once (menu items + variants)
        if ($validated['status'] === 'confirmed' && ! $order->confirmed_at) {
            $order->confirmed_at = now();
            OrderStockService::decrementOnConfirm($order);
        }

        if ($validated['status'] === 'ready' && ! $order->ready_at) {
            $order->ready_at = now();
        }

        // DELIVERED → cashbook only (no second stock hit)
        if ($validated['status'] === 'delivered' && ! $order->delivered_at) {
            $order->delivered_at = now();

            if ($order->payment_method === 'cash') {
                $exists = Cashbook::where('order_id', $order->id)->exists();
                if (! $exists) {
                    Cashbook::create([
                        'restaurant_id' => $order->restaurant_id,
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
        }

        // CANCEL → restore stock if it was reserved on confirm
        if ($validated['status'] === 'cancelled') {
            OrderStockService::restoreOnCancel($order);
        }

        $order->save();

        try {
            broadcast(new OrderStatusUpdated($order))->toOthers();
        } catch (BroadcastException $e) {
            logger()->warning('Broadcast failed for order status update: ' . $e->getMessage());
        }

        return back()->with('success', "Order #{$order->order_number} updated to {$order->status_label}.");
    }

    protected function authorizeRestaurant(Order $order): void
    {
        $user = Auth::user();
        if ($user->isSuperAdmin()) {
            return;
        }
        if ($order->restaurant_id !== $user->effectiveRestaurantId()) {
            abort(403);
        }
    }
}
