# Codeibex — Full project status (before today + today)

**Generated:** 2026-09-03  

## A. Original project (`codeibex (5).zip`)

Multi-tenant SaaS POS: central + optional tenant DB, modules by business type, POS with variants/batches, deals, orders, Reverb, subscriptions.

## B. Packs (chronological)

| # | Pack | What it delivered |
|---|------|-------------------|
| 1 | tenancy-hardened / production | Safe Tenancy, provision, TenantAwareJob, tenants:run |
| 2 | homepage-multi-business | show_on_homepage, HomeController listing |
| 3 | modules-business-fix | Business labels, single module JS, modules:audit |
| 4 | order-tenant-fixes | Public tenant switch, NewOrderPlaced |
| 5 | sales-settings | Item promotions, theme-by-day, platform homepage branding, public.tenant MW |
| 6 | storefront-stock | StorefrontPricing, sale UI (checkout later reconciled) |
| 7 | hours-stock-policy | OrderStockService, stock on confirm |
| 8 | hours-audit | BusinessHours, early close/extend, daily reset |
| 9 | final-reconcile | Canonical checkout: check stock, no decrement, hours gate |
| 10 | **variant-public (this)** | **Variant on public cart + stock check; OrderController confirm wired** |

## C. Agreed runtime behaviour

```
Public menu → variants with qty / sold out
Checkout:
  - hours gate (BusinessHours)
  - types: menu_item | variant | deal
  - server sale price
  - stock CHECK (menu track_stock + variant quantity_available)
  - NO decrement
  - channel = online
Confirm (manager):
  - OrderStockService::decrementOnConfirm (menu + variant)
Cancel after confirm:
  - restoreOnCancel
POS:
  - decrement at sale (unchanged)
```

## D. Install this pack last

```bash
cp app/Http/Controllers/CheckoutController.php app/Http/Controllers/
cp app/Http/Controllers/Admin/OrderController.php app/Http/Controllers/Admin/
cp app/Support/OrderStockService.php app/Support/
cp app/Support/BusinessHours.php app/Support/
cp resources/views/customer/menu_partials/item-variants.blade.php resources/views/customer/menu_partials/

# In menu.blade.php for each item, after price partial:
@include('customer.menu_partials.item-variants', ['item' => $item])

# MenuController eager load:
'availableMenuItems.variants', 'availableMenuItems.promotions'

php artisan migrate   # prior packs' migrations
php artisan modules:audit --seed
```

## E. End-to-end checklist

- [ ] Homepage lists only show_on_homepage businesses  
- [ ] Modules by type; plan max modules  
- [ ] Item sales live + upcoming banner  
- [ ] Hours / closed today / early close block checkout  
- [ ] Menu shows variants with stock  
- [ ] Checkout accepts type=variant, checks quantity_available  
- [ ] Confirm decrements variant + menu stock once  
- [ ] Cancel restores  
- [ ] POS still works for variants/batches  
