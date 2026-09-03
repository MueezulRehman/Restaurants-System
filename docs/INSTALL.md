# Stock-on-confirm + Business hours — Install

## 1. Files

```bash
cp app/Support/OrderStockService.php app/Support/
cp database/migrations/2026_09_03_110000_add_business_hours_to_restaurants.php database/migrations/
cp resources/views/admin/restaurant-profile/hours-section.blade.php resources/views/admin/restaurant-profile/
cp resources/views/customer/partials/open-status-badge.blade.php resources/views/customer/partials/
```

## 2. Restaurant model

- Add to `$fillable`: `opening_hours`, `is_closed_today`, `closed_message`, `accept_orders_when_closed`
- Add casts for array/bool
- Paste methods from `Restaurant_HoursHelpers.php`

## 3. Profile controller update()

Validate and save:

```php
'opening_hours' => 'nullable|array',
'is_closed_today' => 'nullable|boolean',
'closed_message' => 'nullable|string|max:255',
'accept_orders_when_closed' => 'nullable|boolean',
```

```php
$validated['is_closed_today'] = $request->boolean('is_closed_today');
$validated['accept_orders_when_closed'] = $request->boolean('accept_orders_when_closed');
$validated['opening_hours'] = $request->input('opening_hours', $restaurant->opening_hours);
// normalize closed checkboxes per day...
```

Include hours section in profile blade.

## 4. OrderController

Apply `OrderController_STATUS_PATCH.php` (stock on confirm, restore on cancel, cashbook on delivered without stock).

## 5. CheckoutController

At start of `store()` after resolving restaurant:

```php
if (! $restaurant->isAcceptingOnlineOrders()) {
    return back()->withErrors([
        'cart' => $restaurant->closed_message
            ?: ('This business is closed right now. ' . ($restaurant->nextOpenLabel() ?? '')),
    ]);
}
```

- Keep stock **checks** only
- **Remove** stock decrement block if you added it earlier at place-order

## 6. Menu / homepage UI

```blade
@include('customer.partials.open-status-badge', ['restaurant' => $currentRestaurant])
```

Optional: disable “Add to cart” / checkout button when `!$restaurant->isAcceptingOnlineOrders()`.

## 7. Migrate

```bash
php artisan migrate
php artisan view:clear
```
