# Codeibex Tenancy Coding Standard

**Platform:** Codeibex  
**Author:** Mueez Ul Rehman  
**Status:** Official team standard (September 2026)  
**Applies to:** All developers working on Codeibex

---

## 1. Core Rule (Memorize This)

> **Never touch tenant data without going through the Tenancy helpers.**

One pattern. Everywhere. No exceptions.

---

## 2. The Only Allowed Patterns

### A. Web Request (Storefront / Public)

Handled automatically by `ResolveRestaurant` middleware.  
Do **not** call `Tenancy::configureTenantConnection()` manually in controllers.

### B. Super Admin Impersonation

```php
// Enter
Tenancy::enter($restaurant);

// ... work ...

// Always exit
Tenancy::exit();
```

### C. Any code that needs a specific business database

```php
use App\Support\Tenancy;

Tenancy::runFor($restaurant, function () {
    // Everything here runs on the tenant DB
    Order::create([...]);
    MenuItem::all();
});
// Connection is automatically restored after the callback
```

### D. Queue Jobs (Mandatory)

Every job that touches tenant data **must** extend `TenantAwareJob`:

```php
namespace App\Jobs;

use App\Jobs\TenantAwareJob;
use App\Models\Order;

class SendOrderInvoice extends TenantAwareJob
{
    public function __construct(
        public int $restaurantId,
        public int $orderId
    ) {}

    public function handleTenant(): void
    {
        $order = Order::findOrFail($this->orderId);
        // Safe – default connection is already the correct tenant DB
    }
}

// Dispatch
SendOrderInvoice::dispatch($restaurant->id, $order->id);
```

### E. Artisan / Maintenance

```bash
php artisan tenants:migrate
php artisan tenants:run "cache:clear" --only-with-db
php artisan tenants:run "db:seed --class=SomeSeeder" --id=12
```

---

## 3. Forbidden Practices (Will be rejected in code review)

| Forbidden | Why | Correct Way |
|-----------|-----|-------------|
| `DB::connection('tenant')->...` scattered in code | Hard to maintain, easy to forget restore | `Tenancy::runFor()` |
| Manually changing `config(['database.default' => ...])` | Connection leaks into other requests/jobs | Use helpers |
| Jobs that assume current connection is correct | Queue workers have no request context | Extend `TenantAwareJob` |
| Reading `Restaurant` model while inside tenant connection without care | Can cause wrong connection issues | Prefer `Tenancy::current()` or load from central |
| Skipping tenancy “just this once” | Creates data leaks and hard-to-find bugs | Always use the pattern |

---

## 4. Controller Guidelines

### Manager / Admin Controllers

Most manager controllers already run inside the correct context because of middleware.  
When you need to force a specific restaurant (rare):

```php
public function someAction(Restaurant $restaurant)
{
    return Tenancy::runFor($restaurant, function () use ($restaurant) {
        // tenant-scoped work
        return view('...', compact('restaurant'));
    });
}
```

### Super Admin Controllers

When Super Admin works on a specific business:

```php
public function enter(Restaurant $restaurant)
{
    Tenancy::enter($restaurant);
    return redirect()->route('manager.dashboard');
}

public function exit()
{
    Tenancy::exit();
    return redirect()->route('admin.restaurants.index');
}
```

---

## 5. Model & Query Rules

- Models that live in the **tenant** database (Order, MenuItem, Stock, Medicine, etc.) must only be queried while inside a tenant context.
- The `Restaurant`, `User`, `RestaurantSubscription`, `SubscriptionPlan`, `Module`, `BusinessType` models live on the **central** database. Prefer loading them on the central connection when possible.

Helper:

```php
$restaurant = Tenancy::current();          // current restaurant
$id         = Tenancy::currentId();        // current restaurant id
$isTenant   = Tenancy::isTenantContext();  // true when default connection is tenant
```

---

## 6. Subscription & Module Gates

These middlewares are already registered and must stay active:

- `EnsureSubscriptionActive` → blocks expired businesses (with 5-day grace)
- `EnsureModuleEnabled` → gates features by enabled modules + staff permissions

Do not bypass them.

---

## 7. New Feature Checklist

Before merging any new feature that touches business data:

- [ ] Does it use `Tenancy::runFor()` or run inside existing middleware context?
- [ ] If it is a Job → does it extend `TenantAwareJob`?
- [ ] Does it avoid raw `DB::connection('tenant')`?
- [ ] Does it restore context properly (helpers do this for you)?
- [ ] Are central models (`Restaurant`, `User`, subscriptions) treated carefully?

---

## 8. Quick Reference

```php
// Preferred
Tenancy::runFor($restaurant, fn () => ...);
Tenancy::forRestaurantId($id, fn () => ...);
Tenancy::enter($restaurant);
Tenancy::exit();
Tenancy::current();
Tenancy::currentId();
Tenancy::isTenantContext();

// Jobs
class MyJob extends TenantAwareJob { public function handleTenant(): void {} }

// CLI
php artisan tenants:migrate
php artisan tenants:run "..." 
php artisan tenants:provision
```

---

## 9. Decision Authority

This standard is mandatory for all Codeibex code.

If a developer believes an exception is required, it must be discussed and documented first.

**Keep it simple. Keep it consistent. Keep it safe.**
