# Platform Database Operations

## Database Layout

The platform uses one central MySQL database and one tenant database per store.

Central database (`DB_DATABASE`, currently `codeibex`) contains platform data:

- users and authentication
- restaurants and domains
- business types and modules
- subscription plans and subscriptions
- platform settings and platform notifications

Tenant databases use the configured prefix and restaurant id, for example:
`codeibex_tenant_1`.

Tenant databases contain store operations:

- categories, menu items, deals, and variants
- orders, deliveries, and customers
- stock, medicines, purchases, and suppliers
- cashbook, expenses, reports, staff, and attendance

The `restaurants.db_connection` value is encrypted and stores the connection
configuration for the corresponding tenant database.

## Initial Setup

Run these commands from the project root after configuring `.env`:

```powershell
php artisan migrate
php artisan db:seed
php artisan tenants:provision --all --seed
```

The default seeder now provisions and seeds every restaurant automatically.
The explicit provisioning command is useful for recovery or for stores added
after the initial seed.

## Store Provisioning

Provision one store by id:

```powershell
php artisan tenants:provision 5 --seed
```

Provision every store that does not already have a tenant connection:

```powershell
php artisan tenants:provision --all --seed
```

Provisioning is idempotent. It creates the database when required, applies
`database/tenant_migrations`, and seeds the tenant catalog.

## Legacy Catalog Migration

Older installations may have catalog records in the central database. Inspect
the records without changing data:

```powershell
php artisan tenants:migrate-legacy-catalog --all
```

Apply the catalog copy only after reviewing the dry-run output:

```powershell
php artisan tenants:migrate-legacy-catalog --all --apply
```

The command copies categories, menu items, menu item sizes, and deals using
restaurant-scoped natural keys. It does not delete central records, so the
central database can be backed up and verified before final cleanup.

## Development Verification

Check migration status:

```powershell
php artisan migrate:status
```

Check the tenant database list in MySQL:

```sql
SELECT SCHEMA_NAME
FROM information_schema.SCHEMATA
WHERE SCHEMA_NAME LIKE 'codeibex_tenant_%'
ORDER BY SCHEMA_NAME;
```

Business data must be accessed inside a tenant context using
`Tenancy::runFor()`, `Tenancy::forRestaurantId()`, or the active tenant
middleware. Central models such as `Restaurant`, `User`, and subscriptions
must remain on the central connection.

Models using `BelongsToRestaurant` reject create and update operations on the
central connection once the restaurant has a configured tenant database. A
write that raises `must be written inside an active tenant context` should be
wrapped in `Tenancy::runFor()` or moved into a `TenantAwareJob`.

## Production Rules

- Do not use the MySQL root account for the application.
- Use separate least-privilege credentials for central and tenant databases.
- Back up the central database and each tenant database independently.
- Run tenant migrations through the tenant migration path, not the central path.
- Do not use `migrate:fresh` against a shared or production database.
- Do not delete a tenant database until its backup and retention requirements
  have been confirmed.
- Replace all seeded demo passwords before production deployment.

## Guest Order OTP

Guest checkout requires a restaurant-scoped phone OTP before creating an
order. The checkout message includes the business name, and the customer can
choose SMS, WhatsApp, or both channels.

Local development can use the log drivers:

```dotenv
SMS_DRIVER=log
WHATSAPP_DRIVER=log
```

For an SMS gateway or local SIM HTTP bridge, configure:

```dotenv
SMS_DRIVER=http
SMS_ENDPOINT=https://your-sms-gateway.example/send
SMS_TOKEN=
SMS_SENDER=CodeIbex
```

For Meta WhatsApp Cloud API, configure the verified phone-number id and an
approved authentication template:

```dotenv
WHATSAPP_DRIVER=meta
WHATSAPP_API_URL=https://graph.facebook.com/v25.0
WHATSAPP_API_TOKEN=
WHATSAPP_FROM=
WHATSAPP_OTP_TEMPLATE=codeibex_otp
WHATSAPP_OTP_LANGUAGE=en_US
```

OTP codes expire after five minutes, allow five attempts, and are stored only
as hashes in each tenant database. Never commit provider tokens to the
repository.

## Legacy Central Operational Data

Older setup runs may have created categories, menu items, orders, and other
operational records in the central database. Do not delete those records yet.
Before cleanup, map each record to its restaurant, copy verified records into
the matching tenant database, validate counts and foreign keys, and take a
backup. Future application writes should use the tenant connection only.