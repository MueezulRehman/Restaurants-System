# Role Layout Blueprint

## Purpose

This is the short implementation reference for adding pages without
duplicating the application shell.

| Role | Master | Views | Authentication and scope |
| --- | --- | --- | --- |
| Manager | `manager.layout.master` | `manager.*` | Authenticated manager/staff; tenant and branch scope |
| Super Admin | `super-admin.layout.master` | `super-admin.*` | Super Admin middleware; platform scope |
| CEO | `ceo.layout.master` | `ceo.*` | CEO assignment, permission, business and branch scope |
| Customer | `customer.layout.master` | `customer.*` | Customer guard; tenant/storefront scope |

## Shell ownership

- **Manager:** dashboard sidebar, tenant navigation, responsive header,
  tenant-themed content area, notifications, footer.
- **Super Admin:** platform navigation, platform dashboard theme, platform
  notifications, platform management pages, shared dashboard frame.
- **CEO:** executive sidebar, executive search, mobile navigation, scoped
  reports/alerts/businesses/branches, CEO footer.
- **Customer:** platform/business header, menu/account/checkout content,
  tenant branding, storefront notice, customer footer, consent and confirm
  dialogs.

The master owns the shell. A page owns only its content. The required contract
is:

```blade
@extends('manager.layout.master')
@section('title', 'Page title')
@section('page-content')
    <section data-page="unique-page-name">
        {{-- page content --}}
    </section>
@endsection
@push('styles') {{-- page-only styles --}} @endpush
@push('scripts') {{-- page-only scripts --}} @endpush
```

## Shared page primitives

Use the `x-page` components for content inside every role master:

- `x-page.container` and `x-page.header` define the page frame and title/action area.
- `x-page.card` and `x-page.section` group related content.
- `x-page.field` and `x-page.file` keep labels, validation, help text, and upload
  presentation consistent. `x-page.file` also handles the selected-file name.
- `x-page.actions` is the single action row for submit/cancel controls.
- `x-page.table` and `x-page.empty` provide responsive list and empty-state behavior.

The role master is still the outer page contract. A new page should never copy a
sidebar, header, footer, confirmation modal, or upload styling; it should extend
the correct role master and compose these primitives in `page-content`.

Replace the master with `super-admin.layout.master`, `ceo.layout.master`, or
`customer.layout.master` for the other roles.

## Directory rules

```text
resources/views/manager/       tenant operations
└── layout/{sidebar,navbar,header,footer}.blade.php
resources/views/super-admin/   platform ownership
└── layout/{sidebar,navbar,header,footer}.blade.php
resources/views/ceo/           executive experience
└── layout/{sidebar,navbar,header,footer}.blade.php
resources/views/customer/      public storefront/account
└── layout/{sidebar,navbar,header,footer}.blade.php
resources/views/components/    reusable role and primitive components
resources/css/layouts.css      shared shell CSS
resources/js/layouts.js        shared shell JavaScript
```

Do not put platform CRUD in `manager/`, tenant operations in `super-admin/`,
or customer/CEO pages in the authenticated dashboard tree. Keep route names
stable when only the view namespace changes.

Each role's `layout/` directory is the view-local entry point for the four
shell regions. It delegates to the matching role component so the shell
remains reusable while role-specific navigation and branding stay isolated.
Manager and Super Admin have independent authenticated shells. They may reuse
low-level visual primitives and compiled assets, but each shell owns its role
behavior, navigation, authorization assumptions, and layout entry points.

## Implementation checklist

1. Add route middleware and scope checks first.
2. Add the controller action and resolve the correct role namespace.
3. Add the view under the correct role directory.
4. Extend the role master and use `page-content`.
5. Reuse role components; do not copy shell markup.
6. Keep page CSS/JS scoped and use shared assets for reusable behavior.
7. Run `php artisan view:cache`, the focused feature test, and `npm run build`.
