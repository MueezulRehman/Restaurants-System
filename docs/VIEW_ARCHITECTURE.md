# View architecture and page workflow

This application has four separate presentation experiences. The former
generic “admin” label is product-facing **Manager** for business operations.

## One-page role and layout blueprint

| Role | User boundary | Master entry point | Page tree | Shell structure |
| --- | --- | --- | --- | --- |
| **Manager** | Authenticated tenant owner, manager, or staff | `manager.layout.master` | `resources/views/manager` | Manager shell: sidebar, navbar, header, tenant content, footer |
| **Super Admin** | Platform owner; never tenant-scoped unless impersonating | `super-admin.layout.master` | `resources/views/super-admin` | Super Admin shell: platform sidebar, navbar, header, content, footer |
| **CEO** | Assigned executive with business/branch scope | `ceo.layout.master` | `resources/views/ceo` | CEO shell: executive sidebar, searchable navbar/header, scoped content, footer |
| **Customer** | Public/customer guard and tenant storefront | `customer.layout.master` | `resources/views/customer` | Customer shell: storefront header, tenant content, customer footer |

### Master composition

Every master owns the page frame. Pages must provide only page content:

```text
role master
├── document head, title, CSRF, Vite assets, global styles
├── role sidebar (dashboard roles only)
├── role navbar/header
├── main[data-master-section="content"]
│   └── @yield('page-content') ← supplied by the role master
├── role footer
└── shared scripts + @stack('scripts')
```

The public master additionally owns tenant branding, storefront notices,
cookie consent, and customer confirmation behavior. The CEO master owns its
mobile sidebar/backdrop and executive search behavior. Manager and Super Admin have independent authenticated shell implementations
directly in their role-local masters. Only low-level visual primitives and
compiled assets are shared.

### File responsibilities

```text
resources/views/{manager,super-admin,ceo,customer}/
├── layout/master.blade.php       Complete role shell and page contract
├── layout/sidebar.blade.php      Role navigation shell
├── layout/navbar.blade.php       Role navigation region
├── layout/header.blade.php       Role header region
└── layout/footer.blade.php       Role footer region

resources/views/components/
├── layouts/                       Reusable sidebar/navbar/header/footer primitives
├── manager/                       Manager shell wrappers
├── super-admin/                   Super Admin shell wrappers
├── ceo/                           CEO shell wrappers
└── customer/                      Customer shell wrappers
```

`resources/css/layouts.css` contains reusable shell styling. Global reusable
behavior belongs in `resources/js/layouts.js`; page-only behavior belongs in
the page's `@push('scripts')` block.

The four role directories are the only page/layout namespaces. Components are
framework-level reusable primitives, not additional role layouts.

## Role ownership and namespaces

Tenant operational controllers resolve `manager.*` views: POS, orders,
inventory, purchasing, branches, staff, cashbook, expenses, salary, restaurant
profile/theme, manager feedback, manager notifications, and tenant reports.

Platform-owner controllers resolve `super-admin.*` views: business types,
modules, subscription plans, restaurants and access assignment, platform
settings, platform feedback, aggregate business reports, and Super Admin stock
analysis. Existing route names such as `admin.restaurants.*` are intentionally
unchanged because route names are API contracts, not view namespaces.

Super Admin domains follow a predictable resource shape:

```text
resources/views/super-admin/
├── restaurants/          index, create, edit, access pages, partials
├── business-types/       index, create, edit
├── modules/              index, create, edit
├── subscription-plans/   index, create, edit
├── feedback/             index, show
├── reports/              index, show
├── platform-settings/    edit
└── stock-analysis/       index
```

The `restaurants` folder is the platform's business domain because the
underlying model and controller are `Restaurant`; changing the display label
to “Business” does not require a second duplicate view tree. Each domain owns
its CRUD/detail views and local partials, while the Super Admin master owns
the shell, assets, navigation, alerts, and script stacks.

CEO controllers resolve `ceo.*` views and must retain CEO assignment,
permission, business, and branch middleware. Customer controllers resolve
`customer.*` views and must retain the customer guard plus tenant-aware
restaurant resolution.

## Standard page template

```blade
@extends('manager.layout.master') {{-- or super-admin.layout.master, ceo.layout.master, customer.layout.master --}}

@section('title', 'Inventory')

@section('page-content')
    <section data-page="inventory">
        {{-- page-specific markup only --}}
    </section>
@endsection

@push('styles')
    {{-- page-only CSS only --}}
@endpush

@push('scripts')
    {{-- page-only JavaScript only --}}
@endpush
```

Use the matching role master and role directory. Never copy a sidebar,
navbar, header, footer, global asset import, authentication check, or tenant
theme block into a page.

## New-page workflow

1. Define the role boundary, route name, guard, permission, tenant/branch
   scope, and controller action.
2. Select the matching master and place the view in the matching role tree.
3. Put reusable shell changes in the role wrapper or shared layout primitive.
4. Put reusable CSS/JavaScript in `layouts.css`/`layouts.js`; keep page hooks
   data-attribute based and scoped to the page.
5. Use `@section('page-content')`, `@push('styles')`, and `@push('scripts')`.
6. Compile views, run the focused feature test, and build assets.
