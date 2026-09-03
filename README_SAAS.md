# Codeibex SaaS upgrades

**Author:** Mueez Ul Rehman

## Included

1. **Branding** — Super admin sidebar/title uses **Codeibex** (not Taste Hut Management).
2. **Dashboard** — Clarified platform-wide metrics; best-seller labeled as platform aggregate.
3. **Business reports** — Super admin list + per-business report + date filter.
4. **Platform bank account** — Editable by super admin; shown on manager Subscription page (not hardcoded).
5. **Payment reminders** — Super admin can remind a business’s managers/owners.
6. **Browser notify helper** — Partial for desktop notifications when a new order arrives (wire to your order event/polling).

## Install

```bash
unzip -o codeibex-saas-pack.zip
php artisan migrate
php artisan view:clear
```

Add routes from `ROUTES_TO_ADD.php` into the admin route group in `routes/web.php` (names are under `admin.` prefix if your group uses `->name('admin.')`).

If your group is:

```php
Route::prefix('admin')->name('admin.')->group(...)
```

then the routes in the file become `admin.platform.settings`, etc.

## Manager subscription

Ensure `admin/subscription/show.blade.php` includes:

```blade
@include('admin.subscription._bank_details')
```

(already applied in this pack’s copy of the file).

## Web push (outside browser)

True push when the browser is closed needs Firebase/OneSignal + a service worker.  
This pack includes **browser Notification API** when the manager tab is open.  
For full background push, add OneSignal next — recommended professional path.

## Why “best seller” on super admin dashboard?

It aggregates **all businesses’** top item for today. It is platform activity, not one shop’s POS. Label updated to **Top item across platform today**.
