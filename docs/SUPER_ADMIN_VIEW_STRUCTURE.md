# Super Admin View Structure

This document is the source of truth for the current Super Admin Blade view
organization under `resources/views/super-admin/`. The folders organize views
by platform responsibility; they do not change the public URL or route-name
contract. The Super Admin shell remains shared through `layout/master.blade.php`.

## Complete current tree

```text
resources/views/super-admin/
├── account/
│   └── edit.blade.php
├── business-types/
│   ├── create.blade.php
│   ├── edit.blade.php
│   └── index.blade.php
├── businesses/
│   ├── _homepage_fields.blade.php
│   ├── _module-plan-script.blade.php
│   ├── ceo-access.blade.php
│   ├── create.blade.php
│   ├── edit.blade.php
│   ├── index.blade.php
│   └── manager-access.blade.php
├── feedback/
│   ├── index.blade.php
│   └── show.blade.php
├── layout/
│   ├── footer.blade.php
│   ├── header.blade.php
│   ├── master.blade.php
│   ├── navbar.blade.php
│   └── sidebar.blade.php
├── modules/
│   ├── create.blade.php
│   ├── edit.blade.php
│   └── index.blade.php
├── notifications/
│   └── index.blade.php
├── platform-settings/
│   └── edit.blade.php
├── reports/
│   ├── index.blade.php
│   └── show.blade.php
├── stock-analysis/
│   └── index.blade.php
├── subscription-plans/
│   ├── create.blade.php
│   ├── edit.blade.php
│   └── index.blade.php
└── dashboard.blade.php
```

There are 30 Blade files in this tree. The underscore-prefixed business files
are partials and are included by the business create/edit pages; they are not
standalone routes.

## Folder responsibilities and rendering map

### `businesses/`

This is the platform's business/restaurant administration area. The route
contract intentionally remains `admin.restaurants.*` and the URLs remain under
`/admin/restaurants`; “businesses” is the view terminology.

| View | Controller/action | Route and URL | Purpose |
|---|---|---|---|
| `index.blade.php` | `Admin\RestaurantController@index` | `admin.restaurants.index`, `GET /admin/restaurants` | List businesses and platform actions. |
| `create.blade.php` | `Admin\RestaurantController@create` | `admin.restaurants.create`, `GET /admin/restaurants/create` | Register a business. |
| `edit.blade.php` | `Admin\RestaurantController@edit` | `admin.restaurants.edit`, `GET /admin/restaurants/{restaurant}/edit` | Edit business settings, modules, plan, and storefront settings. |
| `manager-access.blade.php` | `Admin\RestaurantController@managerAccess` | `admin.restaurants.manager-access`, `GET /admin/restaurants/{restaurant}/manager-access` | Review manager access for a business. |
| `ceo-access.blade.php` | `Admin\RestaurantController@ceoAccess` | `admin.restaurants.ceo-access`, `GET /admin/restaurants/{restaurant}/ceo-access` | Review CEO assignments for a business. |
| `_homepage_fields.blade.php` | Included by `create.blade.php` and `edit.blade.php` | No route | Shared homepage/storefront form fields. |
| `_module-plan-script.blade.php` | Included by `create.blade.php` and `edit.blade.php` | No route | Shared module/plan form behavior. |

The same controller also handles the POST/PATCH actions for these pages,
including entering a business for impersonation and updating manager/CEO
access. Those actions redirect using the unchanged `admin.restaurants.*`
route names.

### `business-types/`

This folder exists and is active; it does not need to be created or mapped to
another folder. It stores reusable business-type definitions and their enabled
module defaults.

| View | Controller/action | Route and URL |
|---|---|---|
| `index.blade.php` | `Admin\BusinessTypeController@index` | `admin.business-types.index`, `GET /admin/business-types` |
| `create.blade.php` | `Admin\BusinessTypeController@create` | `admin.business-types.create`, `GET /admin/business-types/create` |
| `edit.blade.php` | `Admin\BusinessTypeController@edit` | `admin.business-types.edit`, `GET /admin/business-types/{business_type}/edit` |

### `modules/`

This is the platform catalog of modules that can be enabled for businesses.

| View | Controller/action | Route and URL |
|---|---|---|
| `index.blade.php` | `Admin\ModuleController@index` | `admin.modules.index`, `GET /admin/modules` |
| `create.blade.php` | `Admin\ModuleController@create` | `admin.modules.create`, `GET /admin/modules/create` |
| `edit.blade.php` | `Admin\ModuleController@edit` | `admin.modules.edit`, `GET /admin/modules/{module}/edit` |

### `subscription-plans/`

This folder manages plans offered to businesses.

| View | Controller/action | Route and URL |
|---|---|---|
| `index.blade.php` | `Admin\SubscriptionPlanController@index` | `admin.subscription-plans.index`, `GET /admin/subscription-plans` |
| `create.blade.php` | `Admin\SubscriptionPlanController@create` | `admin.subscription-plans.create`, `GET /admin/subscription-plans/create` |
| `edit.blade.php` | `Admin\SubscriptionPlanController@edit` | `admin.subscription-plans.edit`, `GET /admin/subscription-plans/{subscription_plan}/edit` |

### `feedback/`

This is the platform feedback centre. Both the dedicated
`SuperAdmin\FeedbackCentreController` and the existing
`Admin\FeedbackController` render these views for their corresponding
feedback actions.

| View | Controller/action | Route and URL |
|---|---|---|
| `index.blade.php` | `Admin\FeedbackController@index`; `SuperAdmin\FeedbackCentreController@index` | `admin.feedback.index`, `GET /admin/feedback` |
| `show.blade.php` | `Admin\FeedbackController@show`; `SuperAdmin\FeedbackCentreController@show` | `admin.feedback.show`, `GET /admin/feedback/{feedback}` |

Reply, status, and delete actions stay on the same feedback routes and do not
require separate view files.

### `notifications/`

This folder contains the platform notification feed.

| View | Controller/action | Route and URL |
|---|---|---|
| `index.blade.php` | `Admin\NotificationController@index` | `admin.notifications.index`, `GET /admin/notifications` |

The feed and mark-as-read endpoints use the same controller but return data or
redirects rather than another Blade page.

### `account/`

This folder exists and is active; it does not need to be created or renamed.
It contains the Super Admin's own account settings, separate from business
account pages.

| View | Controller/action | Route and URL |
|---|---|---|
| `edit.blade.php` | `Admin\AccountController@edit` | `admin.account.edit`, `GET /admin/account` |

The PATCH account, password, and validation actions are handled by
`Admin\AccountController` and return to this view.

### `platform-settings/`

This folder contains platform-wide settings such as branding, theme defaults,
and payment/bank settings.

| View | Controller/action | Route and URL |
|---|---|---|
| `edit.blade.php` | `Admin\PlatformSettingsController@edit` | `admin.platform.settings`, `GET /admin/platform-settings` |

`Admin\PlatformSettingsController@update` handles the PUT save action.

### `reports/`

This folder existed before the Super Admin business-view reorganization. It was
not created, moved, or renamed as part of that work. It contains platform
business-report pages:

| View | Controller/action | Route and URL |
|---|---|---|
| `index.blade.php` | `Admin\BusinessReportController@index` | `admin.reports.index`, `GET /admin/reports` |
| `show.blade.php` | `Admin\BusinessReportController@show` | `admin.reports.show`, `GET /admin/reports/{restaurant}` |

### `stock-analysis/`

This folder also existed before the reorganization and was not created or
moved during it. It contains the platform stock-analysis report:

| View | Controller/action | Route and URL |
|---|---|---|
| `index.blade.php` | `Admin\StockAnalysisController@adminIndex` | `admin.stock-analysis.index`, `GET /admin/stock-analysis` |

The export endpoint is handled by `admin.stock-analysis.export` and does not
render another Blade view.

### `dashboard.blade.php`

This is the platform dashboard, rendered by
`Admin\DashboardController@index` at `admin.dashboard` (`GET /admin/dashboard`).
It remains at the Super Admin root because it is the landing page for the
platform panel rather than a child resource.

## Moved-file map

These are the physical files moved during the reorganization. Route names and
URLs were deliberately not renamed.

| Original path | Current path |
|---|---|
| `resources/views/super-admin/restaurants/_homepage_fields.blade.php` | `resources/views/super-admin/businesses/_homepage_fields.blade.php` |
| `resources/views/super-admin/restaurants/_module-plan-script.blade.php` | `resources/views/super-admin/businesses/_module-plan-script.blade.php` |
| `resources/views/super-admin/restaurants/index.blade.php` | `resources/views/super-admin/businesses/index.blade.php` |
| `resources/views/super-admin/restaurants/create.blade.php` | `resources/views/super-admin/businesses/create.blade.php` |
| `resources/views/super-admin/restaurants/edit.blade.php` | `resources/views/super-admin/businesses/edit.blade.php` |
| `resources/views/super-admin/restaurants/manager-access.blade.php` | `resources/views/super-admin/businesses/manager-access.blade.php` |
| `resources/views/super-admin/restaurants/ceo-access.blade.php` | `resources/views/super-admin/businesses/ceo-access.blade.php` |

No physical `feedback-centre/` folder was moved. The old
`super-admin.feedback-centre.*` references were stale view names and were
corrected to the existing `super-admin.feedback.*` views.

## The `layout/` folder

`layout/` is the shared Super Admin shell and was intentionally untouched by
the reorganization. Its files are:

- `footer.blade.php` — shared footer.
- `header.blade.php` — shared header, title/search area, and impersonation state.
- `master.blade.php` — shell entry point and `@yield('page-content')`.
- `navbar.blade.php` — platform/manager navigation.
- `sidebar.blade.php` — shell sidebar wrapper and branding.

Page views extend it with:

```blade
@extends('super-admin.layout.master')

@section('page-content')
    {{-- page-specific content --}}
@endsection
```

## Adding a new Super Admin page

1. Place the view in the folder matching its platform responsibility. Use a
   new kebab-case resource folder for a new resource; keep the dashboard at
   the root.
2. Extend `super-admin.layout.master` and define `page-content`.
3. Add the controller action and use the matching dotted view name, for example:

   ```php
   return view('super-admin.businesses.index', $data);
   ```

4. Add the route under the existing `admin` prefix and preserve the established
   `admin.<resource>.*` naming convention.
5. Use includes only for markup fragments that do not need component props or
   slots; keep shared shell changes in `layout/`, not in individual pages.
6. Verify the route, Blade cache compilation, relevant feature tests, and the
   authenticated browser page before considering the page complete.

## Verification

This map was cross-checked against the recursive filesystem inventory,
controller `view('super-admin.*')` calls, and the Super Admin route definitions.
The old `super-admin.restaurants.*` and `super-admin.feedback-centre.*` view
references are absent from application controllers, views, and tests.
