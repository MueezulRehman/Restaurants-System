# Codeibex Tenancy – Production Pack

**Author:** Mueez Ul Rehman  
**Date:** September 2026  
**Status:** Official hardened tenancy + team coding standard

This is the complete, professional tenancy solution for Codeibex.

## What is included

| Path | Purpose |
|------|---------|
| `app/Support/Tenancy.php` | Core helper (runFor, enter/exit, current, etc.) |
| `app/Services/TenantProvisioner.php` | Safe DB create / migrate / seed / drop |
| `app/Jobs/TenantAwareJob.php` | Base class for all tenant queue jobs |
| `app/Console/Commands/RunForTenantsCommand.php` | `tenants:run` |
| `app/Console/Commands/ProvisionTenantCommand.php` | `tenants:provision` |
| `app/Console/Commands/MigrateTenantsCommand.php` | `tenants:migrate` |
| `app/Http/Middleware/ResolveRestaurant.php` | Hardened storefront resolver |
| `app/Http/Middleware/EnsureSubscriptionActive.php` | Grace period + warnings |
| `config/tenancy.php` | Updated config |
| `docs/CODEIBEX_TENANCY_CODING_STANDARD.md` | **Mandatory team standard** |
| `docs/TENANCY_HARDENING.md` | Technical hardening notes |
| `examples/UsageExamples.php` | Copy-paste ready patterns |

## Installation

```bash
unzip -o codeibex-tenancy-production.zip

cp app/Support/Tenancy.php                    ../your-project/app/Support/
cp app/Services/TenantProvisioner.php         ../your-project/app/Services/
cp app/Jobs/TenantAwareJob.php                ../your-project/app/Jobs/
cp app/Console/Commands/*                     ../your-project/app/Console/Commands/
cp app/Http/Middleware/ResolveRestaurant.php  ../your-project/app/Http/Middleware/
cp app/Http/Middleware/EnsureSubscriptionActive.php ../your-project/app/Http/Middleware/
cp config/tenancy.php                         ../your-project/config/

cd ../your-project
php artisan config:clear
```

## Core Rule (for every developer)

Never touch tenant data without using one of these:

- `Tenancy::runFor($restaurant, fn () => ...)`
- `Tenancy::forRestaurantId($id, fn () => ...)`
- Extending `TenantAwareJob`
- Or running inside existing middleware context

## Commands

```bash
php artisan tenants:provision
php artisan tenants:migrate
php artisan tenants:run "cache:clear" --only-with-db
```

## Mandatory reading

1. `docs/CODEIBEX_TENANCY_CODING_STANDARD.md`
2. `examples/UsageExamples.php`

Keep one pattern. Keep it consistent. Keep it safe.
