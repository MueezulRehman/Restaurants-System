<?php

namespace App\Http\Controllers;

use App\Models\Delivery;
use App\Models\Deal;
use App\Models\MenuItem;
use App\Models\Order;
use App\Models\OrderItemTopping;
use App\Models\Restaurant;
use App\Models\Topping;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CheckoutController extends Controller
{
    public function show(Request $request)
    {
        $restaurant = $this->resolveRestaurant($request);

        return view('customer.checkout', compact('restaurant'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'order_type' => 'required|in:dine_in,takeaway,delivery,online',
            'customer_name' => 'required|string|max:100',
            'customer_phone' => 'required|string|max:20',
            'address' => 'required_if:order_type,delivery|nullable|string|max:500',
            'payment_method' => 'required|in:cash,online',
            'notes' => 'nullable|string|max:500',
            'cart' => 'required|array|min:1',
            'cart.*.type' => 'required|in:menu_item,deal',
            'cart.*.id' => 'required|integer',
            'cart.*.quantity' => 'required|integer|min:1|max:50',
            'cart.*.size_label' => 'nullable|string',
            'cart.*.topping_ids' => 'nullable|array',
            'cart.*.special_request' => 'nullable|string|max:255',
        ]);

        // Re-price everything server-side from the database — never trust
        // prices sent from the browser, so a tampered request can't pay less
        // than the real menu price.
        $order = DB::transaction(function () use ($validated, $request) {
            $subtotal = 0;
            $lineItems = [];

            $restaurant = $this->resolveRestaurant($request);

            foreach ($validated['cart'] as $line) {
                if ($line['type'] === 'menu_item') {
                    $menuItem = MenuItem::with('sizes')
                        ->when($restaurant, fn ($query) => $query->where('restaurant_id', $restaurant->id))
                        ->findOrFail($line['id']);
                    if (!$menuItem->is_available) {
                        abort(422, "{$menuItem->name} is currently unavailable.");
                    }
                    $unitPrice = $menuItem->has_sizes
                        ? optional($menuItem->sizes->firstWhere('size_label', $line['size_label'] ?? null))->price
                        : $menuItem->price;

                    if ($unitPrice === null) {
                        abort(422, "Invalid size for {$menuItem->name}.");
                    }

                    $toppingTotal = 0;
                    $toppings = [];
                    if (!empty($line['topping_ids'])) {
                        foreach (Topping::whereIn('id', $line['topping_ids'])->get() as $t) {
                            $toppingTotal += $t->price;
                            $toppings[] = $t;
                        }
                    }

                    $lineTotal = ($unitPrice + $toppingTotal) * $line['quantity'];
                    $subtotal += $lineTotal;

                    $lineItems[] = [
                        'item_type' => 'menu_item',
                        'menu_item_id' => $menuItem->id,
                        'deal_id' => null,
                        'item_name' => $menuItem->name,
                        'size_label' => $line['size_label'] ?? null,
                        'quantity' => $line['quantity'],
                        'unit_price' => $unitPrice + $toppingTotal,
                        'total_price' => $lineTotal,
                        'special_request' => $line['special_request'] ?? null,
                        'toppings' => $toppings,
                    ];
                } else {
                    $deal = Deal::when($restaurant, fn ($query) => $query->where('restaurant_id', $restaurant->id))
                        ->findOrFail($line['id']);
                    if (!$deal->is_active) {
                        abort(422, "{$deal->name} is currently unavailable.");
                    }
                    $lineTotal = $deal->price * $line['quantity'];
                    $subtotal += $lineTotal;

                    $lineItems[] = [
                        'item_type' => 'deal',
                        'menu_item_id' => null,
                        'deal_id' => $deal->id,
                        'item_name' => $deal->name,
                        'size_label' => null,
                        'quantity' => $line['quantity'],
                        'unit_price' => $deal->price,
                        'total_price' => $lineTotal,
                        'special_request' => $line['special_request'] ?? null,
                        'toppings' => [],
                    ];
                }
            }

            $deliveryFee = $validated['order_type'] === 'delivery' ? 100 : 0;

            $order = Order::create([
                'order_type' => $validated['order_type'],
                'status' => 'pending',
                'customer_name' => $validated['customer_name'],
                'customer_phone' => $validated['customer_phone'],
                'address' => $validated['address'] ?? null,
                'subtotal' => $subtotal,
                'delivery_fee' => $deliveryFee,
                'total' => $subtotal + $deliveryFee,
                'payment_method' => $validated['payment_method'],
                'notes' => $validated['notes'] ?? null,
                'estimated_minutes' => $validated['order_type'] === 'delivery' ? 45 : 25,
                'restaurant_id' => $restaurant?->id,
            ]);

            foreach ($lineItems as $line) {
                $toppingsToAttach = $line['toppings'];
                unset($line['toppings']);
                $orderItem = $order->items()->create($line);

                foreach ($toppingsToAttach as $t) {
                    OrderItemTopping::create([
                        'order_item_id' => $orderItem->id,
                        'topping_id' => $t->id,
                        'topping_name' => $t->name,
                        'price' => $t->price,
                    ]);
                }
            }

            if ($validated['order_type'] === 'delivery') {
                Delivery::create([
                    'order_id' => $order->id,
                    'status' => 'pending',
                ]);
            }

            return $order;
        });

        // Redirect straight to THIS order's private tracking page using its
        // unique token — never to a generic "my orders" list, since there's
        // no login and we must not expose other customers' orders.
        return redirect()->route('orders.track', $order->tracking_token)
            ->with('success', 'Order placed! Track it below.');
    }

    protected function resolveRestaurant(Request $request): ?Restaurant
    {
        $restaurantId = $request->input('restaurant_id') ?? $request->session()->get('current_restaurant_id');

        if ($restaurantId) {
            $restaurant = Restaurant::where('id', $restaurantId)
                ->where('status', 'active')
                ->first();
            if ($restaurant) {
                $request->session()->put('current_restaurant_id', $restaurant->id);
                app()->instance('restaurant', $restaurant);
                view()->share('currentRestaurant', $restaurant);

                return $restaurant;
            }
        }

        if (app()->bound('restaurant')) {
            return app('restaurant');
        }

        return null;
    }
}
