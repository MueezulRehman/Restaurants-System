# Codeibex – Multi-Business Public Homepage

**Author:** Mueez Ul Rehman  
**Date:** September 2026

---

## Goal

When the platform is live on the main domain, visitors should see a clean list of businesses that Super Admin has chosen to feature. From there they pick a restaurant, open its menu, and place an order that goes straight to that business’s manager dashboard.

Existing custom-domain and slug behaviour is fully preserved.

---

## User Journey

1. Visitor opens **main domain** → `/`
2. Sees searchable cards of businesses marked `show_on_homepage = true`
3. Clicks a card → goes to `/{slug}` menu page
4. Browses menu → places order
5. Order is created in that business’s tenant database
6. Manager receives on-screen notification and processes the order

### Custom domain path (unchanged)

- Super Admin sets `custom_domain` (or `domain`) on the restaurant
- Visitor opens that domain → only that restaurant’s menu is shown
- No homepage listing on a custom domain

### No custom domain

- Works on main domain via slug: `yoursite.com/pizza-house`

---

## Technical Changes

| Item | Change |
|------|--------|
| Migration | `show_on_homepage` (bool), `homepage_sort_order` (int) on `restaurants` |
| Model | Added fields to `$fillable` + `$casts` |
| HomeController | New controller for `/` listing + search |
| MenuController | Cleaned – only handles `/{slug}` menu |
| Routes | `/` → HomeController@index |
| Admin forms | Checkbox “Show on Homepage” + sort order |
| Admin index | Shows homepage badge |

---

## Super Admin How-to

1. Go to **Admin → Restaurants → Edit**
2. Check **Show on Homepage**
3. Optionally set **Homepage Sort Order** (lower = appears first)
4. Save

Only businesses that are:
- `status = active`
- `show_on_homepage = true`
- Storefront available (valid subscription / trial)

…will appear on the public homepage.

---

## Order Flow (unchanged, confirmed)

- Checkout already stores `restaurant_id` / uses current tenant context
- Order lands in the correct tenant DB
- Existing `OrderStatusUpdated` event + Reverb / browser notification can notify the manager screen

---

## Install

```bash
# Copy files from this pack into the project, then:
php artisan migrate
php artisan view:clear
php artisan route:clear
```

---

## Files in this pack

```
database/migrations/2026_09_01_000001_add_show_on_homepage_to_restaurants_table.php
app/Http/Controllers/HomeController.php
app/Http/Controllers/MenuController.php
resources/views/customer/home.blade.php
docs/HOMEPAGE_MULTI_BUSINESS.md
+ patches for Restaurant model, RestaurantController, admin blades, routes
```
