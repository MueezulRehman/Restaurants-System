<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Restaurant;
use Illuminate\Http\Request;
use App\Support\Tenancy;

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
    public function show(string $tracking_token)
    {
        $restaurant = app()->bound('restaurant') ? app('restaurant') : null;

        if (! $restaurant) {
            $restaurantId = session('current_restaurant_id');
            $central = config('tenancy.central_connection', env('DB_CONNECTION', 'mysql'));
            $restaurant = $restaurantId ? Restaurant::on($central)->find($restaurantId) : null;
        }

        if ($restaurant && $restaurant->hasTenantDatabase()) {
            Tenancy::configureTenantConnection($restaurant);
        }

        $order = null;

        if ($restaurant) {
            $order = Order::where('tracking_token', $tracking_token)
                ->where('restaurant_id', $restaurant->id)
                ->first();
        } else {
            $restaurants = Restaurant::on(config('tenancy.central_connection', env('DB_CONNECTION', 'mysql')))
                ->where('status', 'active')
                ->get();

            foreach ($restaurants as $candidate) {
                $candidateOrder = Tenancy::runFor($candidate, function () use ($tracking_token, $candidate) {
                    return Order::where('tracking_token', $tracking_token)
                        ->where('restaurant_id', $candidate->id)
                        ->first();
                });

                if ($candidateOrder) {
                    $restaurant = $candidate;
                    $order = $candidateOrder;
                    break;
                }
            }
        }

        abort_unless($order, 404);

        // Keep the customer feedback form scoped to the business that owns
        // this tracked order, including when the visitor is logged in.
        session(['current_restaurant_id' => $order->restaurant_id]);

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
