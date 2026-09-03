# Codeibex — Master Integration Log

**Date:** 2026-09-03  
**Author:** Mueez Ul Rehman  

This log reconciles the original project + every pack against decisions from the discussion.  
**Install packs in the order below.** Later packs override earlier files when listed.

---

## Agreed design (source of truth)

| Topic | Decision |
|-------|----------|
| Tenancy | Keep custom hybrid: central DB + optional DB-per-business; harden (do not migrate to stancl) |
| Modules | Business type → modules → `enabled_modules`; plan `max_modules` enforced UI + server |
| UI wording | User-facing “Business”; internal model/routes may stay `Restaurant` |
| Homepage | Super Admin `show_on_homepage` + sort; search; slug + custom domain menu kept |
| Public tenant switch | `ResolveRestaurant` + `EnsurePublicTenantConnection` on checkout |
| Item sales | Manager % or fixed Rs; start/end; upcoming banner; server-side `StorefrontPricing` |
| Online stock | **CHECK** at place-order; **DECREMENT only on manager CONFIRM**; restore on cancel |
| POS stock | Decrement at sale (unchanged) |
| Hours | Weekly schedule + closed today + early close + extend; nightly reset command |
| New order notify | `NewOrderPlaced` broadcast + `PlatformNotification` on central DB |

---

## Pack inventory (what each provides)

| Pack file | Purpose | Key artifacts |
|-----------|---------|---------------|
| `codeibex-tenancy-hardened.zip` | Safe Tenancy enter/exit, provisioner | Tenancy.php, TenantProvisioner |
| `codeibex-tenancy-production.zip` | Production tenancy + TenantAwareJob + tenants:run + coding standard | Final Tenancy layer |
| `codeibex-homepage-multi-business.zip` | Multi-business homepage listing | HomeController, show_on_homepage migration |
| `codeibex-modules-business-fix.zip` | Single module JS, business labels, modules:audit | create/edit/index restaurants, ModulesAuditCommand |
| `codeibex-order-tenant-fixes.zip` | Checkout/Menu tenant switch + NewOrderPlaced | Checkout (older), MenuController, event |
| `codeibex-sales-settings-pack.zip` | Item promotions, profile theme-by-day, platform homepage branding | ItemSaleController, ItemPromotion, public.tenant middleware |
| `codeibex-storefront-stock-pack.zip` | Sale price UI + StorefrontPricing + (old) checkout | menu partials, StorefrontPricing |
| `codeibex-hours-stock-policy.zip` | Stock-on-confirm policy + OrderStockService | OrderStockService, OrderController patch |
| `codeibex-hours-audit-pack.zip` | Full hours UI + BusinessHours + daily reset | BusinessHoursController, BusinessHours.php |
| **`codeibex-final-reconcile.zip`** | **Fixed Checkout matching policy** + this log | CheckoutController (canonical) |

---

## Issues found and fixed

### Critical: Checkout double policy

| Version | Behaviour | Status |
|---------|-----------|--------|
| storefront-stock-pack Checkout | Sale price + stock **decrement** on place-order | **Wrong vs final decision** |
| hours-stock-policy | Decrement on **confirm** only | Correct |
| **final-reconcile Checkout** | Sale price + stock **check** + hours gate; **no** decrement | **Matches discussion** |

### Overlapping files (use latest)

| File | Use from |
|------|----------|
| `app/Support/Tenancy.php` | tenancy-production |
| `app/Support/BusinessHours.php` | hours-audit-pack |
| `app/Support/OrderStockService.php` | hours-stock-policy |
| `app/Support/StorefrontPricing.php` | storefront-stock-pack |
| `CheckoutController.php` | **final-reconcile** (this pack) |
| `MenuController.php` | homepage pack + merge tenant switch from order-tenant-fixes |
| Restaurant create/edit blades | modules-business-fix (business wording + single JS) |
| Profile edit | sales-settings (theme) + hours UI from hours-audit |
| Platform settings | sales-settings-pack |

### Stock adjustment schema

Two historical migrations disagree (variant vs menu_item). Runtime model expects both.  
**Recommendation:** one consolidating migration making both FKs nullable; all writers use OrderStockService / StockService only.

### Automated hours “sync”

Not an external API — `business:reset-daily-hours` daily at 00:05 clears same-day flags. Weekly schedule stays.

---

## Recommended install order

```text
1. codeibex (5).zip                     # base project
2. codeibex-tenancy-production.zip      # tenancy core
3. codeibex-homepage-multi-business.zip
4. codeibex-modules-business-fix.zip
5. codeibex-sales-settings-pack.zip
6. codeibex-storefront-stock-pack.zip   # pricing + menu partials (skip its Checkout if conflict)
7. codeibex-hours-stock-policy.zip      # OrderStockService + OrderController patch
8. codeibex-hours-audit-pack.zip        # BusinessHours + manager hours UI
9. codeibex-final-reconcile.zip         # CANONICAL CheckoutController + this log
10. codeibex-order-tenant-fixes.zip     # NewOrderPlaced + Menu tenant switch if not already in
```

Then:

```bash
php artisan migrate
php artisan tenants:migrate   # if DB-per-tenant
# Register routes: hours, item-sales, homepage, modules:audit
# Schedule: business:reset-daily-hours
php artisan view:clear
php artisan modules:audit --seed
```

---

## End-to-end checklist (must pass)

- [ ] Super Admin creates business → type modules auto-selected → plan max modules enforced  
- [ ] Business appears on homepage only if `show_on_homepage`  
- [ ] Custom domain / slug opens that business menu only  
- [ ] Manager sets weekly hours + closed today / early close → checkout blocked when closed  
- [ ] Manager adds item sale with start/end → menu shows sale; checkout uses server price  
- [ ] Online order pending → stock **unchanged**  
- [ ] Manager confirms → stock decrements once (`reference_id = order id`)  
- [ ] Manager cancels confirmed → stock restored  
- [ ] POS sale → stock out immediately  
- [ ] New order → Echo channel + PlatformNotification row  
- [ ] Midnight job clears closed-today / early-close / extend  

---

## Still optional (explicitly deferred)

| Item | Note |
|------|------|
| Variant stock on public online cart | POS already handles variants |
| Full StockAudit schema merge migration | Recommended before production scale |
| Unified single deploy repo | Packs are incremental by design |

---

## Log of discussion → implementation

1. Document architecture → Full Technical + MultiTenant docs  
2. Explore tenancy packages → harden custom  
3. Harden + production tenancy  
4. Multi-business homepage  
5. Modules + business wording  
6. Order tenant path + notifications  
7. Item sales + profile theme + platform branding  
8. Storefront sale price (then stock-on-place — **superseded**)  
9. Stock-on-confirm + basic hours  
10. Full hours + early close/extend + stock audit notes  
11. **This reconcile** — Checkout aligned with confirm-only stock + hours gate  

---

When in doubt: **this log’s “Agreed design” table wins** over any older pack comment.
