# Sales + Tenant Middleware + Homepage Branding — Install

## 1. Copy files

```bash
# Middleware
cp app/Http/Middleware/EnsurePublicTenantConnection.php app/Http/Middleware/

# Models
cp app/Models/ItemPromotion.php app/Models/

# Controllers (overwrite profile + platform; add ItemSale)
cp app/Http/Controllers/Admin/ItemSaleController.php app/Http/Controllers/Admin/
cp app/Http/Controllers/Admin/RestaurantProfileController.php app/Http/Controllers/Admin/
cp app/Http/Controllers/Admin/PlatformSettingsController.php app/Http/Controllers/Admin/

# Views
cp -r resources/views/admin/item-sales resources/views/admin/
cp resources/views/admin/restaurant-profile/edit.blade.php resources/views/admin/restaurant-profile/
cp resources/views/admin/platform/settings.blade.php resources/views/admin/platform/

# Migrations
cp database/migrations/2026_09_03_100000_create_item_promotions_table.php database/migrations/
cp database/migrations/2026_09_03_100001_add_homepage_branding_platform_settings.php database/migrations/
# Also copy item_promotions migration into database/tenant_migrations/ if you use DB-per-tenant
cp database/migrations/2026_09_03_100000_create_item_promotions_table.php database/tenant_migrations/
```

## 2. Register middleware (bootstrap/app.php or Kernel)

Laravel 11+:
```php
// bootstrap/app.php
->withMiddleware(function (Middleware $middleware) {
    $middleware->alias([
        'public.tenant' => \App\Http\Middleware\EnsurePublicTenantConnection::class,
    ]);
})
```

## 3. Routes to add

**Manager group** (inside existing manager auth middleware):
```php
use App\Http\Controllers\Admin\ItemSaleController;

Route::get('/item-sales', [ItemSaleController::class, 'index'])->name('item-sales.index');
Route::get('/item-sales/create', [ItemSaleController::class, 'create'])->name('item-sales.create');
Route::post('/item-sales', [ItemSaleController::class, 'store'])->name('item-sales.store');
Route::get('/item-sales/{item_sale}/edit', [ItemSaleController::class, 'edit'])->name('item-sales.edit');
Route::put('/item-sales/{item_sale}', [ItemSaleController::class, 'update'])->name('item-sales.update');
Route::delete('/item-sales/{item_sale}', [ItemSaleController::class, 'destroy'])->name('item-sales.destroy');
```

**Public routes** (checkout + track context):
```php
Route::middleware(['public.tenant'])->group(function () {
    Route::get('/checkout', [CheckoutController::class, 'show'])->name('checkout');
    Route::post('/checkout', [CheckoutController::class, 'store'])->name('checkout.store');
});
```

(Or add `public.tenant` to the existing checkout routes.)

**Admin platform** — ensure update route accepts homepage fields (controller already handles them).  
If route name differs, map to `admin.platform.settings` / `admin.platform.settings.update`.

## 4. MenuItem + Restaurant helpers

Copy methods from:
- `app/Models/MenuItem_SaleHelpers.php` into `MenuItem`
- `app/Models/Restaurant_ThemeHelpers.php` into `Restaurant` (replace existing `themeCssVariables` if present)

## 5. Online order → PlatformNotification

In `CheckoutController` after order is created (and after broadcast), add:

```php
try {
    \App\Models\PlatformNotification::create([
        'restaurant_id' => $order->restaurant_id,
        'user_id' => null,
        'type' => 'new_order',
        'title' => 'New online order ' . $order->order_number,
        'message' => $order->customer_name . ' · Rs ' . $order->total . ' · ' . $order->order_type,
        'data' => [
            'order_id' => $order->id,
            'order_number' => $order->order_number,
            'tracking_token' => $order->tracking_token,
            'total' => $order->total,
        ],
    ]);
} catch (\Throwable $e) {
    \Log::warning('PlatformNotification create failed', ['error' => $e->getMessage()]);
}
```

Note: `platform_notifications` is on the **central** DB — create using central connection if default is tenant:

```php
\App\Models\PlatformNotification::on(config('tenancy.central_connection', env('DB_CONNECTION', 'mysql')))->create([...]);
```

## 6. Storefront display (menu cards)

Where you show item price:

```blade
@if($item->has_active_sale)
  <span class="line-through text-gray-400">Rs {{ number_format($item->display_price, 0) }}</span>
  <span class="font-bold text-emerald-600">Rs {{ number_format($item->sale_price, 0) }}</span>
  <span class="text-xs bg-red-100 text-red-700 px-1 rounded">{{ $item->sale_label }}</span>
@else
  Rs {{ number_format($item->display_price, 0) }}
@endif
```

Eager load: `MenuItem::with(['promotions' => fn ($q) => $q->currentlyActive()])`.

## 7. Homepage sale badge

When listing businesses, if `PlatformSetting::getValue('homepage_show_sale_badges') === '1'` and `$restaurant->hasLiveSales()`:

```blade
<span class="rounded-full bg-red-500 text-white text-xs px-2 py-0.5">
  {{ \App\Models\PlatformSetting::getValue('homepage_sale_badge_text', 'Sale live') }}
</span>
```

## 8. Migrate

```bash
php artisan migrate
php artisan tenants:migrate   # if DB-per-tenant
php artisan view:clear
```

## 9. Sidebar link (manager)

Add under Settings or Marketing:

```blade
<a href="{{ route('manager.item-sales.index') }}">Item Sales</a>
```

---

## Behaviour summary

| Actor | Capability |
|-------|------------|
| Manager | Edit business name, phone, address, logo, theme, theme-by-day |
| Manager | Add item sales (% or Rs) with schedule |
| Customer | Sees sale price + badge on menu |
| Super Admin | Homepage title, banner image, sale badge text |
| System | public.tenant middleware switches DB on checkout |
| System | PlatformNotification row for offline managers |
