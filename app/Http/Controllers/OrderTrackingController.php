<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;

class OrderTrackingController extends Controller
{
    /**
     * Show the live tracking page for ONE order.
     *
     * Security note: route model binding here resolves by `tracking_token`
     * (see routes/web.php — Route::get('/track/{order:tracking_token}')),
     * a 36-character UUID. There is no route, endpoint, or admin-less page
     * anywhere in the customer site that lists orders by sequential ID or by
     * phone number, so a customer cannot enumerate or view anyone else's
     * order. The token is only ever shown to the customer who placed that
     * specific order (immediately after checkout, or via the link they were
     * given).
     */
    public function show(Order $order)
    {
        $restaurant = app()->bound('restaurant') ? app('restaurant') : null;

        // If there isn't a bound restaurant but the order belongs to one,
        // bind it into the container so layouts and view composers can
        // render the restaurant's logo/name on the tracking page.
        if (! $restaurant && $order->restaurant_id) {
            $restaurant = $order->restaurant;
            if ($restaurant) {
                app()->instance('restaurant', $restaurant);
            }
        }

        if ($restaurant && $order->restaurant_id !== $restaurant->id) {
            abort(404);
        }

        $order->load(['items.toppings', 'delivery.rider']);

        return view('customer.track', compact('order'));
    }

    /**
     * Optional: let a customer look up an order by entering BOTH the order
     * number and the phone number used to place it. This still cannot leak
     * other people's orders because both pieces of private info must match
     * — guessing an order number alone returns nothing.
     */
    public function lookup(Request $request)
    {
        $validated = $request->validate([
            'order_number' => 'required|string',
            'customer_phone' => 'required|string',
        ]);

        $order = Order::where('order_number', $validated['order_number'])
            ->where('customer_phone', $validated['customer_phone'])
            ->first();

        if (!$order) {
            return back()->withErrors(['order_number' => 'No matching order found. Check your order number and phone.']);
        }

        return redirect()->route('orders.track', $order->tracking_token);
    }
}
