<?php

namespace App\Http\Controllers\Admin;

use App\Events\OrderStatusUpdated;
use App\Http\Controllers\Controller;
use App\Models\Cashbook;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $query = Order::with('items')->latest();

        if (! Auth::user()->isSuperAdmin()) {
            $query->where('restaurant_id', Auth::user()->restaurant_id);
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

        // Push the new status live to the customer's tracking page in real time
        broadcast(new OrderStatusUpdated($order))->toOthers();

        return back()->with('success', "Order #{$order->order_number} updated to {$order->status_label}.");
    }

    /**
     * Abort with 403 if a non-super-admin user tries to view/modify an order
     * that doesn't belong to their own restaurant.
     */
    protected function authorizeRestaurant(Order $order): void
    {
        $user = Auth::user();
        if (! $user->isSuperAdmin() && $order->restaurant_id !== $user->restaurant_id) {
            abort(403, 'You do not have access to this order.');
        }
    }
}
