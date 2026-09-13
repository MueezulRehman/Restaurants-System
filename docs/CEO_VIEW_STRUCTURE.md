# CEO View Structure

This document maps the live CEO Blade views under `resources/views/ceo/`.
The `ceo.*` route namespace and `/ceo` URL prefix are unchanged.

## Complete current tree

```text
resources/views/ceo/
├── alerts/
│   └── index.blade.php
├── branches/
│   ├── index.blade.php
│   └── show.blade.php
├── businesses/
│   ├── index.blade.php
│   └── show.blade.php
├── dashboard.blade.php
├── layout/
│   ├── footer.blade.php
│   ├── header.blade.php
│   ├── master.blade.php
│   ├── navbar.blade.php
│   └── sidebar.blade.php
├── login.blade.php
├── profile/
│   └── index.blade.php
└── reports/
    └── index.blade.php
```

## Responsibilities and rendering map

| Folder/file | Controller/action | Route and URL | Purpose |
|---|---|---|---|
| `dashboard.blade.php` | `Ceo\DashboardController@index` | `ceo.dashboard`, `GET /ceo/dashboard` | Executive overview. |
| `businesses/index.blade.php` | `Ceo\BusinessController@index` | `ceo.businesses.index`, `GET /ceo/businesses` | Assigned businesses. |
| `businesses/show.blade.php` | `Ceo\BusinessController@show` | `ceo.businesses.show`, `GET /ceo/businesses/{restaurant}` | Business detail. |
| `branches/index.blade.php` | `Ceo\BranchController@index` | `ceo.branches.index`, `GET /ceo/branches` | Assigned branches. |
| `branches/show.blade.php` | `Ceo\BranchController@show` | `ceo.branches.show`, `GET /ceo/branches/{branch}` | Branch detail. |
| `reports/index.blade.php` | `Ceo\ReportController@index` | `ceo.reports.index`, `GET /ceo/reports` | Executive reports. |
| `alerts/index.blade.php` | `Ceo\AlertController@index` | `ceo.alerts.index`, `GET /ceo/alerts` | Executive alerts. |
| `profile/index.blade.php` | `Ceo\ProfileController@index` | `ceo.profile.index`, `GET /ceo/profile` | CEO profile. |
| `login.blade.php` | `Ceo\AuthController@showLogin` | `ceo.login`, `GET /ceo/login` | CEO login form. |

The POST login and logout actions redirect to these pages and do not render
additional CEO views.

## The `layout/` folder

`layout/` is the shared CEO shell and was not changed by page organization work:
`master.blade.php`, `header.blade.php`, `navbar.blade.php`, `sidebar.blade.php`,
and `footer.blade.php`. Page views use:

```blade
@extends('ceo.layout.master')
@section('page-content')
```

## Adding a CEO page

Place a resource page in the matching kebab-case folder, extend
`ceo.layout.master`, define `page-content`, add a controller action returning
`view('ceo.<folder>.<file>', $data)`, and register it under the `/ceo` prefix
with an `ceo.<resource>.*` route name.

## Verification

The tree was generated from `Get-ChildItem resources\views\ceo -Recurse`; the
controller mappings were cross-checked against `view('ceo.*')` calls and the
CEO route group. No stale CEO view references remain.
