# Tenant Connection Audit + Fixes

**Date:** September 2026  
**Author:** Mueez Ul Rehman

## Audit findings

| Area | Finding | Severity |
|------|---------|----------|
| Manager panel (Order, POS, Menu, Stock…) | `BelongsToRestaurant` global scope calls `Tenancy::configureTenantConnection()` when staff is logged in | OK |
| Super Admin enter | `Tenancy::enter()` switches DB | OK |
| `ResolveRestaurant` (domain/slug storefront) | Switches tenant DB when `hasTenantDatabase()` | OK |
| **Checkout `/checkout`** | Middleware **skips** checkout routes → **no tenant switch** before MenuItem/Order queries | **Critical** |
| **MenuController** | Queried Category/MenuItem without always switching tenant DB | **High** |
| New online order → manager screen | Only `OrderStatusUpdated` on status change; **no event on place order** | **High** |
| Queue jobs | No automatic tenant context (use `TenantAwareJob` from production pack) | Medium |

## Root cause

Public checkout is intentionally excluded from `ResolveRestaurant` path rules. Session holds `current_restaurant_id`, but **default DB connection stayed central**, so with database-per-tenant enabled, online orders can write/read the wrong database.

## Fixes in this pack

1. `CheckoutController::resolveRestaurant()` → always `Tenancy::configureTenantConnection()` when business has tenant DB  
2. `MenuController` → switch tenant before rendering menu  
3. `NewOrderPlaced` event → broadcast on `restaurant.{id}.orders`  
4. Manager listener partial → browser notification + toast + optional reload  

## Professional approach (recommended standard)

1. **One rule:** after resolving a business, immediately switch tenant (or use `Tenancy::runFor`).  
2. **Central models only on central connection** (`Restaurant::on($central)`).  
3. **Public + manager + jobs** all use the same helpers.  
4. **Never rely on session alone** for DB context.  
5. **Broadcast new orders** on a per-business channel; managers subscribe only to their id.

## Install

```bash
cp app/Http/Controllers/CheckoutController.php   your-project/app/Http/Controllers/
cp app/Http/Controllers/MenuController.php       your-project/app/Http/Controllers/
cp app/Events/NewOrderPlaced.php                 your-project/app/Events/
cp resources/views/partials/manager-new-order-listener.blade.php your-project/resources/views/partials/

# In layouts/admin.blade.php (manager section), before </body>:
# @include('partials.manager-new-order-listener')

php artisan view:clear
# Ensure Reverb is running for live notifications
```
