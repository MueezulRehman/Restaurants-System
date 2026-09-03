# Codeibex — Database-per-Business Tenancy

**Platform:** Codeibex  
**Author:** Mueez Ul Rehman  

TasteHut (or any shop) is just **one business** you add inside Codeibex — not the product name.

---

## Architecture (what we implemented)

| Layer | Role |
|-------|------|
| **Central DB** | restaurants, users, subscriptions, business_types, modules, plans |
| **Tenant DB** | One MySQL database **per business** with the **full** operational schema |
| **Modules** | Access only (what the manager can open) — **not** different tables |

When super admin creates a business:

1. Restaurant row is saved on the central DB  
2. Database `codeibex_tenant_{id}` is created  
3. **All** `database/tenant_migrations` are run (full structure)  
4. Optional seed for demo catalog  
5. `enabled_modules` controls UI/middleware only  

Moving a business later = export that one DB + point `db_connection` to the new host.

---

## Install

From your Laravel project root:

```bash
unzip -o codeibex-tenancy.zip

# optional env
# TENANCY_AUTO_PROVISION=true
# TENANCY_DB_PREFIX=codeibex_tenant_
# TENANCY_SEED_AFTER_MIGRATE=true
# APP_NAME=Codeibex

php artisan config:clear
php artisan migrate
```

MySQL user must have **CREATE DATABASE** privilege.

---

## Commands

```bash
# Create DB + full schema for businesses that do not have one yet
php artisan tenants:provision
php artisan tenants:provision 5          # one id
php artisan tenants:provision --all --seed

# Run migrations on all tenant DBs (e.g. after adding discount columns)
php artisan tenants:migrate
php artisan tenants:migrate 5
php artisan tenants:migrate --seed
```

---

## Files in this package

```
config/tenancy.php
app/Services/TenantProvisioner.php
app/Support/Tenancy.php
app/Console/Commands/MigrateTenantsCommand.php
app/Console/Commands/ProvisionTenantCommand.php
app/Http/Controllers/Admin/RestaurantController.php
app/Models/Order.php                    # order prefix CX-
README_CODEIBEX_TENANCY.md
```

Laravel 11 auto-loads commands under `app/Console/Commands`.

---

## Requirements

1. Keep a complete set of files under `database/tenant_migrations/`  
   (same structure for every business — including discount migrations).  
2. Central connection stays the default for platform screens.  
3. When a request enters a business (storefront / manager / super-admin enter),  
   `Tenancy::configureTenantConnection()` switches to that business DB.

---

## Modules vs tables

- Super admin enables modules on the business → manager sees those menus.  
- Tables always exist in the tenant DB.  
- Enabling a module later does **not** require new migrations.

---

## Order / invoice numbers

Platform prefix is **CX-** (Codeibex), e.g. `CX-20260816-0001`.

---

Built for a clean, movable, multi-business POS platform.
