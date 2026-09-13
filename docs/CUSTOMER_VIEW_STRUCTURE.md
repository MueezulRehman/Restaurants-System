# Customer View Structure

This document maps the live Customer Blade views under
`resources/views/customer/`. Public storefront routes use the root URL
namespace, while authenticated customer routes use `customer.*`.

## Complete current tree

```text
resources/views/customer/
├── account/
│   └── dashboard.blade.php
├── auth/
│   ├── login.blade.php
│   └── register.blade.php
├── checkout.blade.php
├── faq.blade.php
├── feedback/
│   ├── create.blade.php
│   ├── index.blade.php
│   └── show.blade.php
├── home.blade.php
├── layout/
│   ├── footer.blade.php
│   ├── header.blade.php
│   ├── master.blade.php
│   ├── navbar.blade.php
│   └── sidebar.blade.php
├── lookup.blade.php
├── menu.blade.php
├── menu_partials/
│   ├── categories.blade.php
│   ├── hero-carousel.blade.php
│   ├── item-price.blade.php
│   ├── item-variants.blade.php
│   └── upcoming-sales-banner.blade.php
├── menu_templates/
│   ├── city-pharmacy-menu.blade.php
│   ├── default.blade.php
│   └── modern.blade.php
├── menu-empty.blade.php
├── menu-fallback.blade.php
├── no-restaurant.blade.php
├── partials/
│   ├── business-header.blade.php
│   ├── business-order-status.blade.php
│   ├── open-status-badge.blade.php
│   ├── platform-header.blade.php
│   └── storefront-notice.blade.php
├── privacy.blade.php
├── storefront-unavailable.blade.php
├── terms.blade.php
└── track.blade.php
```

## Responsibilities and rendering map

| Folder/file | Controller/action | Route and URL | Purpose |
|---|---|---|---|
| `home.blade.php` | `HomeController@index`, `MenuController@index` | `home`, `GET /` | Public storefront landing page. |
| `menu.blade.php` | `MenuController@showBySlug` | `menu.restaurant`, `GET /{slug}` | Restaurant menu. |
| `checkout.blade.php` | `CheckoutController@show` | `checkout`, `GET /checkout` | Cart checkout. |
| `lookup.blade.php` | Route closure | `orders.lookup.form`, `GET /track` | Tracking lookup form. |
| `track.blade.php` | `OrderTrackingController@show/lookup` | `orders.track`, `orders.lookup` | Order tracking result. |
| `feedback/index.blade.php` | `Customer\FeedbackController@index` | `customer.feedback.index`, `GET /feedback` | Customer feedback list. |
| `feedback/create.blade.php` | `Customer\FeedbackController@create` | `customer.feedback.create`, `GET /feedback/create` | Feedback form. |
| `feedback/show.blade.php` | `Customer\FeedbackController@show` | `customer.feedback.show`, `GET /feedback/{feedback}` | Feedback detail. |
| `account/dashboard.blade.php` | `Customer\DashboardController@index` | `account.dashboard`, `GET /account` | Customer account/orders. |
| `auth/login.blade.php` | `Customer\AuthController@showLogin` | `customer.login`, `GET /login` | Customer login. |
| `auth/register.blade.php` | `Customer\AuthController@showRegister` | `customer.register`, `GET /register` | Customer registration. |
| `faq.blade.php`, `privacy.blade.php`, `terms.blade.php` | Route views | `faq`, `privacy`, `terms` | Public informational pages. |
| `menu-empty.blade.php`, `menu-fallback.blade.php`, `no-restaurant.blade.php`, `storefront-unavailable.blade.php` | `MenuController`, `CheckoutController`, storefront flows | Related public storefront routes | Empty, unavailable, and fallback states. |

`menu_partials/`, `menu_templates/`, and `partials/` contain include-based
fragments selected by the menu/storefront pages; they are not standalone
routes. `layout/` partials are included by the customer master/header shell.

## Pre-existing support folders

`menu_partials/`, `menu_templates/`, and `partials/` are established rendering
support folders, not new page categories. They remain separate because they
are reusable fragments/templates rather than controller-rendered pages.

## The `layout/` folder

The shared Customer shell contains `master.blade.php`, `header.blade.php`,
`navbar.blade.php`, `sidebar.blade.php`, and `footer.blade.php`. It was not
renamed or replaced by page extraction. Customer pages use
`@extends('customer.layout.master')`; the header owns the customer content
wrapper and the single `@yield('page-content')`.

## Adding a Customer page

Put a controller-rendered page at the role root or in a responsibility folder,
extend `customer.layout.master`, define `page-content`, and return
`view('customer.<folder>.<file>', $data)`. Public routes use descriptive root
names such as `checkout` or `orders.track`; authenticated customer routes use
the `customer.*` naming convention. Use `partials/` for reusable fragments,
not additional controller pages.

## Verification

The tree was generated from `Get-ChildItem resources\views\customer -Recurse`;
controller view calls and public/customer route definitions were cross-checked.
No stale customer view references were found.
