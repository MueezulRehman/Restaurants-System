<?php

/**
 * Replace the body of updateStatus() in Admin\OrderController with this logic.
 * Or merge the confirmed / cancelled / delivered branches carefully.
 *
 * KEY CHANGE: stock moves on CONFIRMED, not delivered. Cashbook still on delivered.
 */

/*
    public function updateStatus(Request $request, Order $order)
    {
        $this->authorizeRestaurant($order);

        $validated = $request->validate([
            'status' => 'required|in:pending,confirmed,preparing,ready,out_for_delivery,delivered,cancelled',
        ]);

        $previous = $order->status;
        $order->status = $validated['status'];

        if ($validated['status'] === 'confirmed' && ! $order->confirmed_at) {
            $order->confirmed_at = now();
            // BEST APPROACH: reserve stock when manager accepts the order
            \App\Support\OrderStockService::decrementOnConfirm($order);
        }

        if ($validated['status'] === 'ready' && ! $order->ready_at) {
            $order->ready_at = now();
        }

        if ($validated['status'] === 'delivered' && ! $order->delivered_at) {
            $order->delivered_at = now();

            // Cashbook only — stock already handled on confirm (online) or POS sale
            if ($order->payment_method === 'cash') {
                $exists = \App\Models\Cashbook::where('order_id', $order->id)->exists();
                if (! $exists) {
                    \App\Models\Cashbook::create([
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

        if ($validated['status'] === 'cancelled') {
            // Restore if this order had already reserved stock
            \App\Support\OrderStockService::restoreOnCancel($order);
        }

        $order->save();

        try {
            broadcast(new \App\Events\OrderStatusUpdated($order))->toOthers();
        } catch (\Illuminate\Broadcasting\BroadcastException $e) {
            logger()->warning('Broadcast failed for order status update: ' . $e->getMessage());
        }

        return back()->with('success', "Order #{$order->order_number} updated to {$order->status_label}.");
    }
*/
