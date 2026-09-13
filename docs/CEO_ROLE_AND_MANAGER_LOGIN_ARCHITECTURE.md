# CEO Role and Shared Manager Login Architecture

## Decision

Use one shared staff/manager login entry point, but keep role-specific
authorization and dashboards separate after authentication.

```text
/manager/login
    |
    +-- super_admin -> /admin/dashboard
    +-- ceo         -> /manager/ceo-dashboard
    +-- admin       -> /manager/dashboard
    +-- manager     -> /manager/dashboard
    +-- other staff -> /manager/dashboard or staff-specific landing page
```

The `super_admin` role is the CodeIbex platform owner. It is not a CEO role.
The `ceo` role is a central user account assigned to one or more businesses and
optionally restricted to selected branches.

## Why this is the preferred approach

- Users have one familiar login page.
- The platform owner, CEO, business admin, and branch manager remain separate
  security roles.
- CEOs can oversee multiple businesses without duplicate accounts.
- Existing manager/POS workflows are not exposed to CEOs accidentally.
- CEO reporting can read isolated tenant databases through a dedicated
  aggregation service.
- The sidebar and landing page can change automatically based on the role.

## Role boundaries

| Role | Scope | Default purpose |
| --- | --- | --- |
| `super_admin` | Entire platform and every tenant | Provisioning, subscriptions, modules, support |
| `ceo` | Explicitly assigned businesses and branches | Executive oversight and reporting |
| `admin` | One assigned business and its branches | Business administration and operations |
| `manager` | Assigned business or branch | Daily operations |
| `staff`/`cashier`/other staff | Assigned branch and modules | Assigned operational work |

The `super_admin` must never be treated as a CEO by checking a broad
`isAdmin()` method. CEO access must use an explicit `isCeo()` check.

## Authentication flow

The manager login controller should accept only business-facing roles:

```php
if (! in_array($user->role, ['ceo', 'admin', 'manager', 'staff', 'cashier'], true)) {
    rejectLogin();
}

return match ($user->role) {
    'ceo' => redirect()->route('manager.ceo-dashboard'),
    default => redirect()->intended(route('manager.dashboard')),
};
```

The platform owner continues to use the platform login:

```text
/admin/login -> /admin/dashboard
```

The existing dedicated `/ceo/login` route may remain temporarily for migration
compatibility, but the target user experience is the shared manager login.
Once all CEO users have been migrated, the dedicated login should redirect to
`/manager/login` rather than maintaining a second authentication experience.

## Route and middleware boundaries

CEO routes must use CEO-specific middleware:

```php
Route::middleware([
    AuthenticateManager::class,
    EnsureCeo::class,
])->prefix('manager')->name('manager.')->group(function () {
    Route::get('/ceo-dashboard', [CeoDashboardController::class, 'index'])
        ->name('ceo-dashboard');
});
```

Normal manager routes must continue using:

```php
AuthenticateManager
EnsureRestaurantManager
EnsureSubscriptionActive
```

Do not allow a CEO to enter normal POS, stock-editing, staff-editing, or
business-settings routes unless a separate permission explicitly authorizes
that action.

Every CEO business or branch request must verify:

```text
authenticated user
    -> active ceo role
    -> active business assignment
    -> active branch assignment, if branch scope is restricted
```

Never trust a `restaurant_id` or `branch_id` supplied by the URL alone.

## CEO assignment model

CEO accounts and assignments stay in the central platform database.
Operational data remains in each tenant database.

### Business assignment

```text
ceo_business_assignments
- id
- user_id
- restaurant_id
- access_scope: all_branches | selected_branches
- access_level: executive | financial | operations
- can_view_financials
- can_view_staff
- can_view_inventory
- can_manage_branches
- is_active
- created_at
- updated_at
```

### Branch assignment

```text
ceo_branch_assignments
- id
- user_id
- restaurant_id
- branch_id
- is_active
- created_at
- updated_at
```

Recommended access rule:

```text
if business assignment is all_branches:
    allow every active branch in that business
else:
    allow only active branch assignments
```

When a business-wide CEO assignment is used, newly created branches are
automatically visible to that CEO. When selected-branch scope is used, a new
branch is private until explicitly assigned.

## CEO dashboard hierarchy

The CEO dashboard should follow this hierarchy:

```text
CEO Portfolio
    -> Business overview
        -> Branch comparison
            -> Branch detail
                -> Read-only operational detail
```

### Portfolio screen

Show common metrics across every assigned business:

- Assigned businesses
- Assigned branches
- Sales
- Orders or transactions
- Active/pending work
- Low-stock alerts
- Expenses and profit when permitted
- Business comparison

Provide date filters:

- Today
- This week
- This month
- This year
- Custom date range

### Business screen

Show:

- Business type
- Business totals
- Sales trend
- Order/transaction trend
- Branch comparison
- Top products or services
- Low-stock and operational alerts
- Staff summary

Business-specific widgets should be selected from the business type. For
example, restaurants show orders and menu performance, pharmacies show
expiry and medicine stock, and gyms show membership and attendance metrics.

### Branch screen

Show:

- Branch sales and orders
- Pending and cancelled work
- Inventory and low-stock items
- Staff count and manager
- Expenses and profit when permitted
- Customer feedback
- Branch activity history

The initial branch screen should be read-only.

## CEO sidebar

The CEO sidebar should show only executive modules:

```text
CEO Dashboard
My Businesses
All Branches
Sales Overview
Branch Comparison
Inventory Alerts
Staff Overview
Reports
My Profile
Logout
```

Normal managers should not see CEO modules. CEOs should not see platform-owner
modules such as business provisioning, subscription plans, or global modules.

## Permission defaults

Start CEOs in read-only mode:

| Permission | Default |
| --- | --- |
| View business summary | Yes |
| View branch summary | Yes |
| View sales | Yes |
| View inventory | Yes |
| View staff summary | Yes |
| View financial details | Based on assignment |
| Edit inventory | No |
| Operate POS | No |
| Edit staff | No |
| Delete business data | No |
| Manage branches | No |

Later, permissions can be expanded independently for financial, staff,
inventory, and branch management work.

## Existing module-access model

The CEO architecture does not replace the current manager module-access
system. It keeps the existing model and makes the authority chain explicit.

```text
Platform owner
    -> enables modules for a business
Business admin/owner
    -> grants enabled modules to each manager
Manager
    -> can use only the modules allowed by both levels
```

### Level 1: business module availability

The platform owner controls the modules available to a business through the
business type, subscription, and enabled-module configuration. If a module is
not enabled for the business, no manager can receive or use it.

Examples:

```text
Business enabled modules:
orders, pos, menu, inventory, reports
```

The business admin cannot grant `cashbook` if `cashbook` is not enabled for
that business.

### Level 2: manager grants

The business admin assigns `module_access` to each manager. A manager can use
only the granted modules that are also enabled for the current business.
Module aliases and business-type bundles continue to use the existing
`User::hasModuleAccess()` rules.

```text
Business enabled: orders, pos, menu, inventory, reports
Manager grants:   menu, inventory
Effective access: menu, inventory
```

### Current empty-grant behavior

The current application treats an empty manager grant list as inheritance of
all modules enabled for that business. This preserves existing behavior, but
it must be shown clearly in the staff-management UI:

```text
module_access = []
    -> manager inherits all business-enabled modules

module_access = ['menu', 'inventory']
    -> manager is narrowed to those granted modules
```

If the product requirement changes to “no modules until explicitly granted,”
that is a separate behavior change requiring migration and updated tests. It
should not be changed as part of the CEO work.

### CEO and modules

CEO access is different from manager module grants:

- A CEO is not granted POS, stock-editing, or staff-editing modules merely by
  being assigned to a business.
- CEO access is controlled by central business/branch assignments and CEO
  permissions such as `can_view_financials`, `can_view_staff`, and
  `can_view_inventory`.
- CEO reporting may read the relevant tenant data only after the business and
  branch assignment checks pass.
- If a CEO is separately given an operational manager account, that account
  must still pass the normal business module-access checks; CEO status must
  not bypass them.

This keeps the authority boundaries separate:

```text
Platform owner: which modules exist for the business
Business admin: which enabled modules each manager may use
CEO: which assigned business/branch reports may be viewed
Manager: operational actions inside effective module access
```

## Professional hardening recommendations

### 1. Separate role, scope, and permission

Do not encode every rule in the role name. Treat authorization as three
independent dimensions:

```text
Role       = who the user is
Scope      = which businesses/branches they can access
Permission = what they can do or view there
```

For example, a CEO can have `view_reports` for two businesses, while a branch
manager can have `edit_inventory` for one branch. This avoids creating roles
such as `regional_financial_ceo_with_inventory`.

### 2. Use policies and one access service

Controllers should not each implement their own CEO checks. Create a single
`CeoAccessService` and policies for business and branch authorization:

```text
CeoAccessService
    canViewBusiness(user, restaurant)
    canViewBranch(user, restaurant, branch)
    canViewFinancials(user, restaurant)
    canViewStaff(user, restaurant)
    canViewInventory(user, restaurant, branch)
```

Use the same service in controllers, exports, API endpoints, jobs, and
background reports. This prevents a secure web page from having an insecure
export endpoint.

### 3. Make assignment constraints database-enforced

Add unique constraints and indexes for active assignment lookups:

```text
unique(user_id, restaurant_id)
unique(user_id, branch_id)
index(user_id, is_active)
index(restaurant_id, is_active)
```

When creating a branch assignment, validate that the branch belongs to the
same restaurant recorded on the assignment. Never accept those two IDs
independently without checking their relationship.

### 4. Use explicit permission names

Boolean columns are acceptable for the first release, but a permission table
is more extensible:

```text
ceo_permissions
- id
- user_id
- restaurant_id nullable
- branch_id nullable
- permission
- is_active
```

Recommended permission names:

```text
portfolio.view
business.view
branch.view
reports.view
financials.view
staff.summary.view
inventory.summary.view
branches.manage
```

Keep mutation permissions separate from viewing permissions. For example,
`inventory.summary.view` must not imply `inventory.edit`.

### 5. Add assignment lifecycle fields

Assignments should support controlled changes without deleting history:

```text
starts_at
ends_at
revoked_at
revoked_by
revocation_reason
```

An assignment is usable only when it is active, within its date range, and not
revoked. This supports temporary executives, contractors, and audit reviews.

### 6. Audit all privileged actions

Record:

- CEO login and logout
- Assignment creation, change, and revocation
- Business and branch report access
- Data exports
- Any future CEO write action
- Platform-owner changes to CEO permissions

Audit records should include actor, target business/branch, action, outcome,
IP address, user agent, and timestamp. Do not log passwords, tokens, or raw
customer payment data.

### 7. Keep portfolio aggregation bounded

Synchronous tenant iteration is suitable for a small number of businesses.
For growth:

```text
small portfolio  -> synchronous dashboard query
larger portfolio -> cached snapshots
large/slow report -> queued report generation
```

Use short cache lifetimes for KPI cards, explicit refresh timestamps, and
per-tenant failure status. Never let one unavailable tenant block the entire
portfolio.

### 8. Prefer summary DTOs over raw models in views

The CEO layer should return typed summary objects or arrays containing only
the fields required by the dashboard. Do not pass unrestricted tenant models
to Blade views. This reduces accidental data exposure and makes the UI
independent of tenant database schemas.

### 9. Apply privacy and least-privilege defaults

CEO dashboards should show aggregate staff and customer information by
default, not sensitive personal data. Mask or omit:

- Customer phone and email lists
- Employee salary details
- Payment credentials
- Medical or prescription details
- Full audit payloads

Require a specific permission for sensitive financial or personal reports.

### 10. Add a clear tenant failure state

Every portfolio response should distinguish:

```text
available
unavailable
not_provisioned
restricted
```

Never render a failed tenant as zero sales. Show the last successful sync time
and an actionable warning to the platform owner.

### 11. Use one session and explicit login isolation

The shared manager login may use the existing guard, but role redirects must
be explicit. On login:

1. Regenerate the session.
2. Clear any stale impersonation or tenant context.
3. Store the resolved landing area.
4. Apply the correct middleware on every request.

On logout, invalidate the session and regenerate the CSRF token. A CEO login
must not inherit a previous platform-owner or impersonated tenant context.

### 12. Test authorization as a matrix

Maintain a test matrix for role, scope, permission, tenant state, and endpoint.
At minimum test:

```text
role       business scope       branch scope       permission       result
ceo        assigned             all                reports.view     allow
ceo        assigned             selected           other branch     deny
ceo        unassigned           none               reports.view     deny
manager    assigned             own branch         inventory.edit   allow
manager    assigned             own branch         financials.view  deny
superadmin platform             all                platform.manage  allow
```

Test web pages, JSON endpoints, exports, and queued jobs—not only the
dashboard controller.

### 13. Version the dashboard contract

Different business types will not always have the same operational tables.
The aggregator should expose a stable common contract:

```text
PortfolioSummary
BusinessSummary
BranchSummary
AlertSummary
```

Business-specific metrics should be optional sections, not assumptions in the
common dashboard. This allows restaurants, pharmacies, stores, gyms, and
future business types to coexist safely.

### 14. Keep a platform-owner emergency path

The platform owner may need support access to an unresponsive tenant, but this
must be an explicit audited impersonation action with a visible banner and
clear exit action. CEO access must never be used as an emergency bypass.

## Tenant data access

CEO reporting must use explicit tenant iteration:

1. Read CEO assignments from the central database.
2. Resolve the allowed businesses and branches.
3. Enter one tenant with `Tenancy::runFor()`.
4. Query only that tenant's operational tables.
5. Apply branch restrictions inside the tenant query.
6. Restore the central connection in `finally`.
7. Continue with the next assigned tenant.

Raw order, stock, customer, or payment rows must not be copied into the
central database just to render the dashboard.

## Required security tests

The implementation is not complete until these tests pass:

- A CEO can log in through `/manager/login`.
- A `super_admin` is rejected by the CEO dashboard middleware.
- An `admin` or `manager` is rejected by CEO-only routes.
- A CEO can see an assigned business.
- A CEO cannot see an unassigned business by changing a URL ID.
- An `all_branches` CEO can see newly created branches.
- A `selected_branches` CEO cannot see an unassigned branch.
- An inactive CEO cannot log in.
- Tenant connection state is restored after a failed tenant query.
- One unavailable tenant does not hide metrics from other assigned tenants.
- CEO sidebar modules are not visible to ordinary managers.

## Implementation sequence

1. Move CEO authentication to the shared `/manager/login` flow.
2. Redirect by role while keeping CEO controllers and middleware separate.
3. Add role-aware manager sidebar rendering.
4. Add business-wide versus selected-branch access scope.
5. Add CEO business and branch policies/middleware.
6. Add business and branch drill-down pages.
7. Add focused authorization and tenant-isolation tests.
8. Keep `/ceo/login` as a compatibility redirect, then remove it after
   migration.
