# Codeibex Tenancy Hardening Pack

**Author:** Mueez Ul Rehman  
**Date:** September 2026  
**Status:** Production-ready hardening of the existing custom tenancy layer

---

## Why this pack exists

Codeibex already uses the correct architecture:

- Central DB → platform data (restaurants, users, subscriptions, modules…)
- Tenant DB → `codeibex_tenant_{id}` with full operational schema
- Modules control UI/middleware only

This pack **does not replace** that design. It hardens the existing implementation so it is safer, cleaner, and ready for production queues, long-running workers, and multi-tenant maintenance.

---

## What was improved

### 1. `App\Support\Tenancy` (completely rewritten)

| Feature | Before | After |
|---------|--------|-------|
| Connection restore | Incomplete | Always restores previous default |
| Current tenant tracking | Session only | Request-level + container bindings |
| Safe execution | None | `Tenancy::runFor($restaurant, fn)` |
| Queue support | None | `Tenancy::forRestaurantId($id, fn)` |
| Impersonation | Basic | Clean enter / exit with logging |
| Double-switch safety | Risky | Tracks state, safe to call multiple times |

### 2. `App\Services\TenantProvisioner`

- Always restores the central connection in a `finally` block
- Added `dropDatabase()` for controlled cleanup
- Added `databaseExists()` health check
- Better logging and error context
- Safer SQLite + MySQL creation

### 3. New `tenants:run` command

```bash
php artisan tenants:run "cache:clear"
php artisan tenants:run "migrate --force" --id=5
php artisan tenants:run "db:seed --class=SomeSeeder" --only-with-db
```

### 4. `App\Jobs\TenantAwareJob` base class

Any job that needs a tenant database should extend this class and implement `handleTenant()` instead of `handle()`.

```php
class SendOrderInvoice extends TenantAwareJob
{
    public function __construct(
        public int $restaurantId,
        public int $orderId
    ) {}

    public function handleTenant(): void
    {
        $order = Order::findOrFail($this->orderId);
        // ...
    }
}
```

### 5. Hardened `ResolveRestaurant` middleware

- Clears previous tenant context before switching
- Cleaner host → subdomain → path resolution order
- Consistent container bindings

### 6. Config additions

- `central_connection`
- `filesystem_isolation` flag (ready for storage disks)

---

## Installation

```bash
# From project root
cp -r path/to/hardened/app/Support/Tenancy.php          app/Support/
cp -r path/to/hardened/app/Services/TenantProvisioner.php app/Services/
cp -r path/to/hardened/app/Console/Commands/RunForTenantsCommand.php app/Console/Commands/
cp -r path/to/hardened/app/Jobs/TenantAwareJob.php       app/Jobs/
cp -r path/to/hardened/app/Http/Middleware/ResolveRestaurant.php app/Http/Middleware/
cp path/to/hardened/config/tenancy.php                   config/

php artisan config:clear
php artisan view:clear
```

No migration required. Fully backward compatible with existing `db_connection` data.

---

## Recommended usage patterns

### A. Super Admin enters a business
```php
Tenancy::enter($restaurant);
// ... work inside that business
Tenancy::exit();
```

### B. Run code for one business safely
```php
Tenancy::runFor($restaurant, function () {
    Order::create([...]);
});
// connection is automatically restored
```

### C. Queue jobs
```php
SendOrderInvoice::dispatch($restaurant->id, $order->id);
```

### D. Maintenance across all tenants
```bash
php artisan tenants:migrate
php artisan tenants:run "cache:clear" --only-with-db
```

---

## What you should still do later (optional)

1. Add a dedicated filesystem disk per tenant when `TENANCY_FILESYSTEM_ISOLATION=true`
2. Add a scheduled health-check command that verifies every tenant DB still exists
3. Consider permission-controlled MySQL users for enterprise clients (each tenant gets its own DB user)

---

## Summary

You now have a clean, production-hardened custom tenancy layer that:

- Matches your original architecture perfectly
- Is safe for queues and long-running processes
- Gives you `runFor` / `forRestaurantId` helpers
- Keeps full ownership (no third-party package lock-in)

Stay with this layer until you have a strong business reason to adopt `stancl/tenancy`.
