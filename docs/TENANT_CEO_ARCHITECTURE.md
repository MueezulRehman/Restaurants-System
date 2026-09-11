# Tenant and CEO architecture

## Default structure

- The platform database stores platform-wide records: users, roles, tenant
  registry, subscriptions, plans, modules, and global settings.
- Each business gets one isolated tenant database for operational records:
  menus, stock, orders, customers, payments, reports, and branches.
- Branches are records inside a business tenant. A branch does not get its own
  database unless legal, compliance, or operational independence requires it.
- The central super-admin dashboard is the platform-owner dashboard. It lists
  businesses, controls tenant provisioning, plans, modules, and platform
  settings, and provides controlled entry into one tenant for support or
  administration.
- CEO is a separate business executive role. A CEO is not a super admin and
  cannot manage platform infrastructure, plans, modules, or every tenant by
  default.
- A CEO may be assigned to one or more businesses. CEO access is an explicit
  business assignment, not a global platform privilege. Multiple CEO accounts
  are supported.

## Access boundaries

- Super admin / platform owner: all tenants, platform settings, provisioning,
  subscriptions, modules, and controlled support access.
- CEO: all permitted branches and executive reports for the businesses
  explicitly assigned to that CEO. A CEO may oversee multiple branches,
  business types, or businesses, but cannot access unassigned businesses.
- Business admin / owner: every branch in their assigned business.
- Branch manager: only the assigned branch.
- Staff / cashier: only the assigned branch and granted modules.

Every operational query must resolve the tenant first, then apply the branch
scope. A branch ID from a request must never be trusted without checking that
it belongs to the active tenant and that the current user can access it.

## Lifecycle

1. Super admin creates a business from the platform restaurant screen.
2. The tenant provisioner creates and migrates that business's database.
3. The business owner/admin and CEO-to-business assignments are stored in the
   platform database.
4. The business admin creates branches inside the tenant.
5. Staff and branch managers are assigned to a branch and receive module
   grants.
6. POS, stock, orders, and reports run inside the active tenant and branch
   context.
7. The platform owner uses the central dashboard to compare and administer
   tenants.
8. The CEO uses a separate executive dashboard limited to the businesses
   assigned to that CEO.

## Implementation rules

- Use `App\Support\Tenancy` and `TenantProvisioner` for tenant switching and
  provisioning.
- Keep `Restaurant` and platform authorization models on the central
  connection.
- Keep operational models tenant-aware and use `BelongsToRestaurant` plus
  branch authorization.
- Never merge raw operational tables across tenants. Aggregate through an
  explicit tenant iteration/reporting service.
- Tenant provisioning must be idempotent, logged, and restored to the central
  connection in a `finally` block.
- Do not use the `super_admin` role for CEO accounts. Keep platform-owner
  authorization and business executive authorization separate.
- CEO-to-business and CEO-to-branch access must be represented by explicit
  assignments and checked before every tenant or branch operation.

## CEO feature file structure

The CEO feature is isolated from the platform-owner and restaurant-manager
areas:

```text
app/
  Http/
    Controllers/
      Ceo/
        AuthController.php
        DashboardController.php
    Middleware/
      EnsureCeo.php
  Models/
    CeoBusinessAssignment.php
    CeoBranchAssignment.php
  Services/
    TenantPortfolioAggregator.php
database/
  migrations/
    *_create_ceo_assignments_tables.php
    *_add_ceo_role_to_users.php
resources/
  views/
    ceo/
      login.blade.php
      dashboard.blade.php
routes/
  web.php  # /ceo/login and /ceo/dashboard
```

CEO assignment records stay in the central database. Operational business
records remain in each tenant database and are read through the portfolio
aggregator.

This is the default architecture for all new modules and integrations.
