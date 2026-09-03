# Codeibex — Project Brief Implementation Roadmap
**Author:** Mueez Ul Rehman  
**Platform:** Multi-tenant POS / Inventory (Codeibex)

---

## Phase 1 — Critical (this pack + immediate)

| # | Item | Status |
|---|------|--------|
| 2 | Module access bug (manager sidebar) | **Fixed in this pack** |
| 5 | Subscription expiry + 5-day grace | Middleware included — register on manager routes |
| Routes | admin.reports / platform.settings | Add from previous SaaS pack |

### Module bug root cause
Managers require `users.module_access` grants. Super Admin only set `restaurants.enabled_modules`.  
Empty `module_access` → no sidebar items.  
**Fix:** empty grants = inherit all business-enabled modules.

---

## Phase 2 — Naming & UX (next)

| # | Item | Approach |
|---|------|----------|
| 1 | Table rename | Prefer **app-level aliases** (`Business` model → `restaurants` table) over risky mass SQL rename on live DB. Optional migration rename later. |
| 3 | Module labels per business type | `config/module_labels.php` keyed by business type + module key; layout uses helper `module_label('menu')`. |
| 8 | Search & pagination | Standardize `paginate(20)` + request filters on index controllers. |
| 9 | Friendly errors | Handler + validation `$request->validate()` messages; avoid raw dumps in production. |

---

## Phase 3 — Billing / inventory units

| # | Item | Approach |
|---|------|----------|
| 4 | Unit-based pricing | Add `unit_type` (piece, kg, dozen, custom), `price_per_unit`, `sell_qty` on items/order_lines. POS qty input respects unit. Stock deducts `sell_qty`. |

---

## Phase 4 — Theming, print, alerts

| # | Item | Approach |
|---|------|----------|
| 6 | Theme | Business `theme` JSON: primary/secondary colors + logo. Manager permission `theme.edit`. |
| 7 | Print/reports | Always `where('restaurant_id', effectiveRestaurantId())`. |
| 5 | Expiry alerts | Super admin dashboard count + manager flash (middleware). Optional email job. |

---

## Demo data

Replace placeholder seeds with **Al Majid Aryans Store** (realistic categories/items + manager user).  
Keep Taste Hut as optional second demo or rename in seeder.

---

## Recommended order of work

1. Deploy Phase 1 (module access + subscription middleware + SaaS routes)  
2. Module labels config + layout  
3. Unit fields migration + POS qty  
4. Theme UI  
5. Table/model rename aliases (non-breaking)  
6. Search/pagination pass  
7. Seed Al Majid Aryans Store  

---

## Subscription policy (confirmed)

- Alert starts **7 days** before expiry (flash + later email).  
- **5-day grace** after expiry: full operate.  
- After grace: login allowed only to **Subscription + Logout**; all other manager routes blocked **server-side**.
