<?php

namespace App\Http\Controllers;

use App\Models\Delivery;
use App\Models\Deal;
use App\Models\MenuItem;
use App\Models\ProductVariant;
use App\Models\Order;
use App\Models\OrderItemTopping;
use App\Models\Restaurant;
use App\Models\Topping;
use App\Models\Table;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Support\StorefrontPricing;
use App\Models\StockAdjustment;
use App\Models\PlatformNotification;
use App\Models\Notification;
use App\Models\DeliveryZone;
use App\Models\Coupon;
use Illuminate\Support\Facades\DB;
use App\Support\Tenancy;
use App\Events\NewOrderPlaced;
use App\Services\OtpService;

class CheckoutController extends Controller
{
    public function show(Request $request)
    {
        $restaurant = $this->resolveRestaurant($request);

        if (! $restaurant) {
            return view('customer.no-restaurant');
        }

        if (! $restaurant->isStorefrontAvailable()) {
            return view('customer.storefront-unavailable', compact('restaurant'));
        }

        $customer = Auth::guard('customer')->user();
        $tables = Table::where('restaurant_id', $restaurant->id)->orderBy('number')->get();
        $deliveryZones = DeliveryZone::where('restaurant_id', $restaurant->id)->where('is_active', true)->orderBy('name')->get();

        return view('customer.checkout', compact('restaurant', 'customer', 'tables', 'deliveryZones'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'order_type' => 'required|in:dine_in,takeaway,delivery,online,table',
            'table_number' => 'nullable|string|max:50',
            'customer_name' => 'required|string|max:100',
            'customer_phone' => 'required|string|max:20',
            'otp' => 'nullable|string|size:6',
            'otp_channel' => 'nullable|in:sms,whatsapp,both',
            'address' => 'required_if:order_type,delivery|nullable|string|max:500',
            'delivery_zone_id' => 'nullable|integer',
            'coupon_code' => 'nullable|string|max:50|alpha_dash',
            'payment_method' => 'required|in:cash,online',
            'notes' => 'nullable|string|max:500',
            'cart' => 'required|array|min:1',
            'cart.*.type' => 'required|in:menu_item,deal,variant',
            'cart.*.id' => 'required|integer',
            'cart.*.quantity' => 'required|numeric|min:0.001|max:9999',
            'cart.*.size_label' => 'nullable|string',
            'cart.*.topping_ids' => 'nullable|array',
            'cart.*.special_request' => 'nullable|string|max:255',
        ]);

        $restaurant = $this->resolveRestaurant($request);
        if (! $restaurant) {
            return back()->withInput()->withErrors([
                'checkout' => 'This business is not available for orders.',
            ]);
        }

        $isAuthenticatedUser = Auth::check();
        $restaurantIdInRequest = (int) ($request->input('restaurant_id') ?? $request->session()->get('current_restaurant_id', 0));
        if (! $isAuthenticatedUser && ! Auth::guard('customer')->check() && $restaurantIdInRequest <= 0) {
            $otpService = app(OtpService::class);
            $phone = $otpService->normalizePhone($validated['customer_phone']);
            $challenge = $request->session()->get('guest_otp_challenge');
            $challengeMatches = is_array($challenge)
                && (int) ($challenge['restaurant_id'] ?? 0) === (int) $restaurant->id
                && ($challenge['phone'] ?? '') === $phone;

            if ($request->boolean('otp_resend')) {
                $lastSentAt = (int) $request->session()->get('guest_otp_sent_at', 0);
                if ($lastSentAt > now()->subSeconds(60)->timestamp) {
                    return back()->withInput()->with('otp_required', true)->withErrors([
                        'otp' => 'Please wait before requesting another code.',
                    ]);
                }

                $channels = match ($validated['otp_channel'] ?? 'sms') {
                    'both' => ['sms', 'whatsapp'],
                    'whatsapp' => ['whatsapp'],
                    default => ['sms'],
                };

                $otpService->issue($restaurant, $phone, $channels);
                $request->session()->put('guest_otp_challenge', [
                    'restaurant_id' => $restaurant->id,
                    'phone' => $phone,
                ]);
                $request->session()->put('guest_otp_sent_at', now()->timestamp);

                return back()->withInput()->with('otp_required', true)->with(
                    'success',
                    'A new verification code was sent for ' . $restaurant->name . '.'
                );
            }

            if (! $challengeMatches || empty($validated['otp'])) {
                $channels = match ($validated['otp_channel'] ?? 'sms') {
                    'both' => ['sms', 'whatsapp'],
                    'whatsapp' => ['whatsapp'],
                    default => ['sms'],
                };

                $otpService->issue($restaurant, $phone, $channels);
                $request->session()->put('guest_otp_challenge', [
                    'restaurant_id' => $restaurant->id,
                    'phone' => $phone,
                ]);
                $request->session()->put('guest_otp_sent_at', now()->timestamp);

                return back()->withInput()->with('otp_required', true)->with(
                    'success',
                    'We sent a verification code for ' . $restaurant->name . '. Enter it below to place your order.'
                );
            }

            if (! $otpService->verify($restaurant, $phone, $validated['otp'])) {
                return back()->withInput()->with('otp_required', true)->withErrors([
                    'otp' => 'The verification code is invalid or expired. Please request a new code.',
                ]);
            }

            $request->session()->forget('guest_otp_challenge');
        }

        if (
            class_exists(\App\Support\BusinessHours::class)
            && ! \App\Support\BusinessHours::isAcceptingOnlineOrders($restaurant)
        ) {
            $message = trim((string) ($restaurant->closed_message ?? ''))
                ?: \App\Support\BusinessHours::label($restaurant);
            $next = \App\Support\BusinessHours::nextOpenLabel($restaurant);
            if ($next && ! str_contains($message, $next)) {
                $message .= ' · ' . $next;
            }

            return back()->withInput()->withErrors(['checkout' => $message]);
        }

        // Re-price everything server-side from the database — never trust
        // prices sent from the browser, so a tampered request can't pay less
        // than the real menu price.
        $order = DB::transaction(function () use ($validated, $request) {
            $subtotal = 0;
            $lineItems = [];

            $restaurant = $this->resolveRestaurant($request);

            if (! $restaurant) {
                abort(403, 'This restaurant is not available for orders.');
            }

            // Business hours / closed today — manager controlled
            $accepting = true;
            if (class_exists(\App\Support\BusinessHours::class)) {
                $accepting = \App\Support\BusinessHours::isAcceptingOnlineOrders($restaurant);
            } elseif (method_exists($restaurant, 'isAcceptingOnlineOrders')) {
                $accepting = $restaurant->isAcceptingOnlineOrders();
            }
            if (! $accepting) {
                $msg = $restaurant->closed_message ?? '';
                if ($msg === '' && class_exists(\App\Support\BusinessHours::class)) {
                    $msg = \App\Support\BusinessHours::label($restaurant);
                    $next = \App\Support\BusinessHours::nextOpenLabel($restaurant);
                    if ($next) {
                        $msg .= ' · ' . $next;
                    }
                }
                abort(422, $msg !== '' ? $msg : 'This business is not accepting online orders right now.');
            }

            foreach ($validated['cart'] as $line) {
                if ($line['type'] === 'menu_item') {
                    $menuItem = MenuItem::with(['sizes', 'promotions'])
                        ->when($restaurant, fn($query) => $query->where('restaurant_id', $restaurant->id))
                        ->findOrFail($line['id']);
                    if (!$menuItem->is_available) {
                        abort(422, "{$menuItem->name} is currently unavailable.");
                    }

                    // Server-side sale price — never trust browser-sent prices
                    $unitPrice = StorefrontPricing::unitPriceForMenuItem(
                        $menuItem,
                        $line['size_label'] ?? null
                    );

                    if ($menuItem->has_sizes && ($line['size_label'] ?? null) && $unitPrice <= 0 && StorefrontPricing::basePrice($menuItem, $line['size_label'] ?? null) <= 0) {
                        abort(422, "Invalid size for {$menuItem->name}.");
                    }

                    // Stock CHECK only — decrement happens on manager CONFIRM (OrderStockService), not here
                    if ($menuItem->track_stock) {
                        $qty = (float) $line['quantity'];
                        if ((float) $menuItem->stock_quantity < $qty) {
                            abort(422, "Not enough stock for {$menuItem->name} (only {$menuItem->stock_quantity} left).");
                        }
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
                        'product_variant_id' => null,
                        'deal_id' => null,
                        'item_name' => $menuItem->name,
                        'size_label' => $line['size_label'] ?? null,
                        'quantity' => $line['quantity'],
                        'unit_price' => $unitPrice + $toppingTotal,
                        'total_price' => $lineTotal,
                        'special_request' => $line['special_request'] ?? null,
                        'toppings' => $toppings,
                    ];
                } elseif (($line['type'] ?? '') === 'variant') {
                    $variant = ProductVariant::with('menuItem')
                        ->when($restaurant, fn($query) => $query->where('restaurant_id', $restaurant->id))
                        ->findOrFail($line['id']);

                    if (! $variant->is_available) {
                        abort(422, ($variant->variant_name ?? 'Variant') . ' is currently unavailable.');
                    }

                    $qty = (float) $line['quantity'];
                    if ((float) $variant->quantity_available < $qty) {
                        abort(422, 'Not enough stock for ' . ($variant->variant_name ?? 'variant')
                            . ' (only ' . $variant->quantity_available . ' left).');
                    }

                    $unitPrice = (float) $variant->getEffectivePrice();
                    // Optional: apply parent menu item promotion to variant base
                    if (class_exists(\App\Support\StorefrontPricing::class) && $variant->menuItem) {
                        $promo = \App\Support\StorefrontPricing::livePromotion($variant->menuItem);
                        if ($promo) {
                            $unitPrice = $promo->applyTo($unitPrice);
                        }
                    }

                    $lineTotal = $unitPrice * $qty;
                    $subtotal += $lineTotal;

                    $lineItems[] = [
                        'item_type' => 'variant',
                        'menu_item_id' => $variant->menu_item_id,
                        'product_variant_id' => $variant->id,
                        'deal_id' => null,
                        'item_name' => trim(($variant->menuItem->name ?? '') . ' — ' . ($variant->variant_name ?? 'Variant')),
                        'size_label' => $variant->variant_name,
                        'quantity' => $qty,
                        'unit_price' => $unitPrice,
                        'total_price' => $lineTotal,
                        'special_request' => $line['special_request'] ?? null,
                        'toppings' => [],
                    ];
                } else {
                    $deal = Deal::when($restaurant, fn($query) => $query->where('restaurant_id', $restaurant->id))
                        ->findOrFail($line['id']);
                    if (!$deal->is_active || !$deal->isActiveNow()) {
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

            $discountAmount = 0;
            $couponCode = null;
            if (! empty($validated['coupon_code'])) {
                $coupon = Coupon::where('restaurant_id', $restaurant->id)
                    ->whereRaw('UPPER(code) = ?', [strtoupper($validated['coupon_code'])])
                    ->lockForUpdate()
                    ->first();
                if (! $coupon || ! $coupon->isUsableFor((float) $subtotal)) {
                    return back()->withInput()->withErrors([
                        'checkout' => 'This coupon is invalid, expired, exhausted, or does not meet the minimum order amount.',
                    ]);
                }
                $discountAmount = $coupon->discountFor((float) $subtotal);
                $couponCode = $coupon->code;
                $coupon->increment('usage_count');
                $subtotal = round(max(0, $subtotal - $discountAmount), 2);
            }

            $deliveryFee = 0;
            if ($validated['order_type'] === 'delivery') {
                $deliveryZone = DeliveryZone::where('restaurant_id', $restaurant->id)
                    ->where('is_active', true)
                    ->find($validated['delivery_zone_id'] ?? null);
                if (! $deliveryZone) {
                    return back()->withInput()->withErrors([
                        'checkout' => 'Select an available delivery zone.',
                    ]);
                }
                if ($subtotal < (float) $deliveryZone->minimum_order) {
                    return back()->withInput()->withErrors([
                        'checkout' => 'This delivery zone requires a minimum order of Rs. ' . number_format((float) $deliveryZone->minimum_order, 2) . '.',
                    ]);
                }
                $deliveryFee = (float) $deliveryZone->fee;
            }

            $order = Order::create([
                'order_type' => $validated['order_type'],
                'table_number' => $validated['table_number'] ?? null,
                'status' => 'pending',
                'channel' => 'online',
                'table_number' => $validated['table_number'] ?? null,
                'customer_name' => $validated['customer_name'],
                'customer_phone' => $validated['customer_phone'],
                'address' => $validated['address'] ?? null,
                'subtotal' => $subtotal,
                'discount_amount' => $discountAmount,
                'discount_type' => $couponCode ? 'coupon' : 'fixed',
                'coupon_code' => $couponCode,
                'delivery_fee' => $deliveryFee,
                'total' => $subtotal + $deliveryFee,
                'payment_method' => $validated['payment_method'],
                'payment_status' => $validated['payment_method'] === 'online' ? 'pending' : 'paid',
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
        // Notify managers on-screen (Reverb / Echo) — must not break checkout if broadcast fails.
        $orderId = $order instanceof Order ? $order->id : null;
        $restaurantId = $order instanceof Order ? $order->restaurant_id : null;
        $trackingToken = $order instanceof Order ? $order->tracking_token : null;

        if ($order instanceof Order && $restaurantId) {
            try {
                broadcast(new NewOrderPlaced($order))->toOthers();
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning('NewOrderPlaced broadcast failed', [
                    'order_id' => $orderId,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        // Offline managers: persist notification on central DB
        if ($order instanceof Order && $restaurantId) {
            try {
                $central = config('tenancy.central_connection', env('DB_CONNECTION', 'mysql'));
                PlatformNotification::on($central)->create([
                    'restaurant_id' => $restaurantId,
                    'user_id' => null,
                    'type' => 'new_order',
                    'title' => 'New online order ' . $order->order_number,
                    'message' => ($order->customer_name ?? 'Customer') . ' · Rs ' . $order->total . ' · ' . $order->order_type,
                    'data' => [
                        'order_id' => $orderId,
                        'order_number' => $order->order_number,
                        'tracking_token' => $trackingToken,
                        'total' => (string) $order->total,
                    ],
                ]);
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning('PlatformNotification failed', ['error' => $e->getMessage()]);
            }

            try {
                Notification::on($central ?? config('tenancy.central_connection', env('DB_CONNECTION', 'mysql')))->create([
                    'restaurant_id' => $restaurantId,
                    'user_id' => null,
                    'type' => 'order_update',
                    'title' => 'New online order ' . $order->order_number,
                    'message' => ($order->customer_name ?? 'Customer') . ' · Rs ' . $order->total . ' · ' . $order->order_type,
                    'channels' => ['push', 'browser'],
                    'status' => 'sent',
                    'sent_at' => now(),
                ]);
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning('Central order notification failed', [
                    'order_id' => $orderId,
                    'error' => $e->getMessage(),
                ]);
            }

            try {
                Notification::create([
                    'restaurant_id' => $restaurantId,
                    'user_id' => null,
                    'type' => 'order_update',
                    'title' => 'New online order ' . $order->order_number,
                    'message' => ($order->customer_name ?? 'Customer') . ' · Rs ' . $order->total . ' · ' . $order->order_type,
                    'channels' => ['push', 'browser'],
                    'status' => 'sent',
                    'sent_at' => now(),
                ]);
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning('Tenant order notification failed', [
                    'order_id' => $orderId,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        if (empty($trackingToken)) {
            return redirect()->route('orders.lookup.form')
                ->with('success', 'Order placed successfully. Please use the lookup page to track it.')
                ->with('order_placed', true);
        }

        return redirect()->route('orders.track', ['tracking_token' => $trackingToken])
            ->with('success', 'Order placed! Track it below.')
            ->with('order_placed', true);
    }

    protected function resolveRestaurant(Request $request): ?Restaurant
    {
        $restaurantId = $request->input('restaurant_id') ?? $request->session()->get('current_restaurant_id');

        if ($restaurantId) {
            // Always load Restaurant from the central connection
            $central = config('tenancy.central_connection', env('DB_CONNECTION', 'mysql'));
            $restaurant = Restaurant::on($central)->where('id', $restaurantId)
                ->where('status', 'active')
                ->first();
            if ($restaurant) {
                $request->session()->put('current_restaurant_id', $restaurant->id);
                app()->instance('restaurant', $restaurant);
                view()->share('currentRestaurant', $restaurant);

                // CRITICAL: switch to tenant DB when this business has one
                if ($restaurant->hasTenantDatabase()) {
                    Tenancy::configureTenantConnection($restaurant);
                } else {
                    Tenancy::setCurrent($restaurant);
                }

                return $restaurant;
            }
        }

        if (app()->bound('restaurant')) {
            $restaurant = app('restaurant');
            if ($restaurant instanceof Restaurant && $restaurant->hasTenantDatabase()) {
                Tenancy::configureTenantConnection($restaurant);
            }

            return $restaurant;
        }

        return null;
    }
}
