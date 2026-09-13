# Manager View Structure

This document maps the live Manager Blade views under
`resources/views/manager/`. Manager routes use the `/manager` prefix and
`manager.*` route names. The folder structure is feature-oriented; the shared
shell is always under `layout/`.

## Complete current tree

```text
resources/views/manager/
├── account/edit.blade.php
├── appointments/{create,index}.blade.php
├── attendance/{create,edit,index}.blade.php
├── branches/index.blade.php
├── branch-inventory/index.blade.php
├── cashbook/{create,index}.blade.php
├── categories/{create,edit,index}.blade.php
├── collections/index.blade.php
├── commissions/index.blade.php
├── coupons/index.blade.php
├── credit-sales/index.blade.php
├── customers/{index,show,statement}.blade.php
├── customization/index.blade.php
├── dashboard.blade.php
├── deals/{create,edit,index}.blade.php
├── deliveries/index.blade.php
├── delivery-zones/index.blade.php
├── doctors/{_form,create,edit,index}.blade.php
├── expenses/{create,edit,index}.blade.php
├── feedback/{manager-index,manager-show}.blade.php
├── fitting-room/index.blade.php
├── follow-up-reminders/index.blade.php
├── gym/index.blade.php
├── installments/index.blade.php
├── insurance/index.blade.php
├── inventory-purchases/{create,expiry-tracking,index}.blade.php
├── item-sales/{create,edit,index}.blade.php
├── kitchen-display/index.blade.php
├── layout/{footer,header,master,navbar,sidebar}.blade.php
├── login.blade.php
├── loyalty/index.blade.php
├── manager-dashboard.blade.php
├── medical/
│   ├── Advancpos (1).html
│   ├── allergies/{form,index}.blade.php
│   ├── batch-recalls/{form,index,show}.blade.php
│   ├── controlled-medicines/index.blade.php
│   ├── interactions/{form,index}.blade.php
│   ├── prescriptions/{form,index,show}.blade.php
│   ├── reports/{expiry-analysis,index,inventory-audit-trail,margin-analysis,revenue-trends,supplier-performance,top-medicines}.blade.php
│   └── suppliers/{form,index}.blade.php
├── medical-records/index.blade.php
├── medicines/{create,edit,index}.blade.php
├── menu-items/{create,edit,index}.blade.php
├── notifications/index.blade.php
├── orders/{index,show}.blade.php
├── patients/{_form,create,edit,index}.blade.php
├── partials/barcode-scanner.blade.php
├── pos/{_unit_qty_js,counter,receipt,restaurant,sales}.blade.php
├── product-devices/index.blade.php
├── purchases/{create,index}.blade.php
├── recipes/index.blade.php
├── reports/{create,index,pdf,show}.blade.php
├── reservations/{create,index}.blade.php
├── restaurant-profile/{edit,hours,hours-section,storefront-notice}.blade.php
├── restaurant-theme/edit.blade.php
├── retail-operations/index.blade.php
├── retail-tools/{barcode-labels,profit-margins}.blade.php
├── salary/{create,edit,index}.blade.php
├── sales-returns/index.blade.php
├── service-cases/index.blade.php
├── service-packages/index.blade.php
├── staff/{create,edit,index}.blade.php
├── stock/{adjustment-edit,adjustment-history,index}.blade.php
├── stock-analysis/manager-index.blade.php
├── stock-transfers/index.blade.php
├── subscription/{_bank_details,show}.blade.php
├── subscription-expired.blade.php
├── tables/{create,edit,index}.blade.php
├── toppings/{create,edit,index}.blade.php
├── trade-ins/index.blade.php
├── variant-attributes/{create,edit,index}.blade.php
├── variants/{create,edit,index}.blade.php
└── wholesale/{commissions,index}.blade.php
```

The brace notation above expands to every file shown by the live recursive
filesystem inventory; filenames containing spaces and the HTML medical
prototype are intentionally preserved exactly.

The live inventory contains 147 files. The grouped entries above are only a
readability notation: each brace item is one literal filename in that folder,
and no files are omitted.

## Responsibilities and rendering map

Manager pages are rendered by the corresponding `App\Http\Controllers\Admin`
feature controller. The following map covers every feature folder; resource
controllers use the standard `manager.<resource>.*` names and `/manager/<resource>`
URLs, while custom actions retain the names shown by `routes/web.php` and
`routes/admin.php`.

| Folder | Controller/action family | Route/URL family | Contents/purpose |
|---|---|---|---|
| `layout/` | Shared shell includes | No standalone route | Master, header, navbar, sidebar, footer. |
| `account/`, `notifications/`, `subscription/` | `AccountController`, `NotificationController`, `RestaurantSubscriptionController` | `manager.account.*`, `manager.notifications.*`, `manager.subscription.*` | Account, notifications, billing. |
| `appointments/`, `attendance/`, `salary/`, `staff/` | `AppointmentController`, `AttendanceController`, `SalaryController`, `StaffController` | `manager.appointments.*`, `manager.attendance.*`, `manager.salary.*`, `manager.staff.*` | People and scheduling. |
| `doctors/` | `Admin\DoctorController` | `manager.doctors.*`, `/manager/doctors` | Tenant-scoped Doctor directory; `_form.blade.php` is included by create/edit. |
| `branches/`, `branch-inventory/` | `BranchController`, `BranchInventoryController` | `manager.branches.*`, `manager.branch-inventory.*` | Branch administration and stock. |
| `cashbook/`, `expenses/`, `credit-sales/`, `installments/`, `insurance/`, `commissions/` | Matching Admin controllers | `manager.cashbook.*`, `manager.expenses.*`, etc. | Finance and receivables. |
| `categories/`, `menu-items/`, `deals/`, `toppings/`, `variants/`, `variant-attributes/` | `CategoryController`, `MenuItemController`, `DealController`, `ToppingController`, `ProductVariantController`, `VariantAttributeController` | Matching `manager.<resource>.*` resource routes | Menu/catalog management. |
| `customers/` | `CustomerController` | `manager.customers.*`, `/manager/customers` | Customers, statements, receipts. |
| `patients/` | `Admin\PatientController` | `manager.patients.*`, `/manager/patients` | Separate tenant-scoped clinical Patient directory with duplicate detection; `_form.blade.php` is included by create/edit. |
| `orders/`, `deliveries/`, `delivery-zones/`, `reservations/`, `kitchen-display/` | Matching order/delivery/reservation/kitchen controllers | Matching `manager.*` routes | Operations and fulfillment. |
| `pos/`, `sales-returns/`, `item-sales/` | `PosController`, `SalesReturnController`, `ItemSaleController` | `manager.pos.*`, `manager.sales-returns.*`, `manager.item-sales.*` | POS, sales, returns, item sales. |
| `inventory-purchases/`, `stock/`, `stock-transfers/`, `collections/`, `retail-operations/`, `retail-tools/`, `product-devices/`, `recipes/`, `wholesale/` | Matching inventory/retail controllers | Matching `manager.<resource>.*` routes | Inventory and retail operations. |
| `medical/`, `medical-records/`, `medicines/`, `purchases/` | Medical, medicine, purchase, and record controllers | Matching module-gated `manager.*` routes | Medical-store workflows and reports. |
| `reports/`, `stock-analysis/` | `ReportController`, `StockAnalysisController` | `manager.reports.*`, `manager.stock-analysis.*` | Reports, exports, and stock analysis. |
| `feedback/` | `ManagerFeedbackController` | `manager.feedback.*`, `/manager/feedback` | Manager feedback list/detail. |
| `restaurant-profile/`, `restaurant-theme/`, `customization/` | Profile/theme/customization controllers | Matching `manager.*` routes | Tenant branding and settings. |
| `service-cases/`, `service-packages/`, `fitting-room/`, `gym/`, `loyalty/`, `follow-up-reminders/`, `coupons/`, `trade-ins/` | Matching feature controllers | Module-gated `manager.*` routes | Optional business-type features. |
| `partials/`, `subscription/_bank_details.blade.php`, `pos/_unit_qty_js.blade.php` | Included by feature views | No standalone route | Reusable fragments/scripts. |
| `dashboard.blade.php`, `manager-dashboard.blade.php` | `ManagerDashboardController@index` and compatibility rendering | `manager.dashboard`, `/manager/dashboard` | Manager landing/legacy dashboard views. |
| `login.blade.php` | `ManagerAuthController@showLogin` | `manager.login`, `/manager/login` | Internal manager login. |

Each standalone page follows the live convention
`@extends('manager.layout.master')` and `@section('page-content')`; shell
partials and underscore-prefixed fragments are included rather than routed.

## Pre-existing and compatibility folders

`medical/`, `reports/`, `stock-analysis/`, `retail-tools/`, `wholesale/`, and
the optional feature folders were already feature-specific Manager areas; they
were not part of the Super Admin reorganization. `manager-dashboard.blade.php`,
the medical HTML prototype, and the compatibility partials are retained because
existing routes/includes still reference them. No Manager folder was silently
renamed.

## The `layout/` folder

The shared Manager shell is `layout/master.blade.php`, `header.blade.php`,
`navbar.blade.php`, `sidebar.blade.php`, and `footer.blade.php`. It is separate
from page folders and was not moved by page-level extraction. Shared layout
data is supplied by the registered dashboard composer; common CSS/JS is loaded
through the application assets.

## Adding a Manager page

Put the page in the feature folder matching its module, extend
`manager.layout.master`, define `page-content`, and return
`view('manager.<folder>.<file>', $data)` from the feature controller. Register
the route under the `/manager` prefix with a `manager.<resource>.*` name and
apply the relevant module middleware. Put reusable fragments in the feature's
partial folder or `manager/partials/`; do not duplicate shell markup.

## Verification

The tree was generated from `Get-ChildItem resources\views\manager -Recurse`.
Controller `view('manager.*')` calls, manager route groups, module middleware,
and include-based partials were cross-checked against the live codebase. Blade
cache compilation and the full application test suite remain green; no stale
Manager view references were found.
