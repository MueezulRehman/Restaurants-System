# Agent Auth and Dashboard Contract

This document is the implementation contract for agents working on the
CodeIbex multi-business application. Follow it before changing authentication,
dashboard routing, tenant queries, or navigation.

## Authentication boundaries

There are two separate authentication products:

- **Internal users:** `/manager/login`, using the `web` guard.
- **Customers:** `/login` and `/register`, using the `customer` guard.

Never merge the customer guard into the internal login flow. Customers must
not be routed to admin, manager, or CEO dashboards.

## Internal role routing

The shared internal login detects the authenticated user's explicit role:

```text
super_admin -> /admin/dashboard
ceo         -> /manager/ceo-dashboard
admin       -> /manager/dashboard
manager     -> /manager/dashboard
staff       -> staff/manager landing page
cashier     -> staff/manager landing page
kitchen     -> staff/manager landing page
rider       -> staff/manager landing page
```

`super_admin` is the CodeIbex platform owner. It is not a CEO and must never
be granted CEO access through a broad admin check. CEO access requires the
explicit `ceo` role and an active assignment.

## CEO scope and authorization

CEO assignments are stored in the central database. Operational records remain
in the selected business tenant database.

Every CEO request follows this order:

```text
authenticated web user
  -> explicit ceo role
  -> active business assignment
  -> all_branches or active selected-branch assignment
  -> explicit CEO permission
  -> Tenancy::runFor(restaurant, ...)
  -> tenant query / view / export
```

Do not trust business or branch IDs from a URL. Use
`CeoAccessService`, `EnsureCeoBusinessAccess`, and
`EnsureCeoBranchAccess`. Never join operational tables across tenant
connections.

## Module permissions

Manager module access and CEO permissions are different systems:

- Admin/manager/staff access is controlled by enabled business modules and
  user module grants.
- CEO access is controlled by CEO business assignments and explicit flags such
  as `can_view_financials`, `can_view_inventory`, and `can_view_staff`.

Granting a manager module must not grant the CEO permission with the same name,
and CEO access must not expose operational edit routes by default.

## Dashboard and visual system

The CEO dashboard uses the same CodeIbex visual language as the manager and
platform dashboards:

- platform theme colors from `PlatformSetting`
- Poppins/Inter typography through the application assets
- Font Awesome icons
- gradient dark/primary sidebar and header
- responsive sidebar/header structure
- light/dark preference key: `codeibex-dashboard-theme`

The CEO shell is intentionally a separate layout so platform-owner navigation
and restaurant operational navigation cannot leak into the CEO portal. Add CEO
navigation links in `resources/views/layouts/ceo.blade.php`, not the admin
sidebar.

## Multi-user and multi-business rules

- Many internal users may belong to the same business.
- One CEO may be assigned to multiple businesses.
- A CEO assignment can cover all branches or selected branches.
- A manager/staff user remains restricted to their effective business and branch.
- A platform owner can administer businesses but does not inherit CEO portfolio
  views.
- Customer accounts are independent from all internal users.

When adding a new dashboard page, define its role, scope, permission, tenant
connection, and navigation visibility before implementing the controller.

## Existing component and data reuse rule

Before creating a new model, migration, icon, amount field, cuisine/business
type value, notification table, or comment workflow, search the existing
application and reuse the established implementation. Do not create a second
version of a concept that already exists.

Use the existing:

- cuisine/business-type definitions and their existing keys
- amount and money columns, validation, formatting, and currency display
- dashboard theme variables and existing Font Awesome icon conventions
- `Notification` model and `NotificationService`
- existing comments/feedback/activity patterns where the feature is
  conversational
- existing central versus tenant connection conventions

If an older implementation already supports the required behavior, extend it
compatibly instead of replacing it. Preserve old route names, field names,
module keys, notification types, and stored values unless a migration and
backward-compatible mapping are explicitly required.

## Creator alerts and multi-user activity

Any user-created action that requires follow-up must notify the creator or
responsible recipient using the existing notification system. The alert should
include the action, actor, business/branch scope, amount when relevant, and a
link or reference to the record. State-changing notification actions must use
protected `POST`, `PATCH`, or `DELETE` routes; never use `GET` to mark a
notification as read or submit an action.

The system supports multiple internal users on the same business and multiple
users participating in one workflow. Do not store a single hard-coded
recipient when several users need visibility. Until a dedicated notification
recipient pivot is introduced, create one existing `Notification` record per
recipient through `NotificationService::send()` and keep the same event type,
message, amount, and source record reference for every recipient.

Comments and activity records must identify the creator/actor and remain
tenant-scoped. A comment must not silently impersonate another user, overwrite
another user's comment, or expose records from another business or branch.

## New feature checklist

Before implementation, an agent must answer:

1. Which existing model, field, service, route, icon, and notification type
   already represent this?
2. Is the value central/platform data or tenant operational data?
3. Which existing amount/currency and cuisine/business-type definitions should
   be reused?
4. Who is the creator, who must be alerted, and can more than one user receive
   the alert?
5. Which role, business, branch, module, and CEO permission checks apply?
6. How are old routes, stored values, and existing users kept compatible?

Only add a new table or component when the existing one cannot represent the
requirement and the incompatibility is documented before coding.

## POS customer flow and cart preservation

The POS customer flow must remain a continuation of the current sale:

- Use the existing `Customer` model and `manager.customers.store` endpoint.
- When a customer is added from POS, redirect back to
  `manager.pos.index`.
- Preserve the current cart in `pos_last_cart`.
- Preserve the newly created customer in `pos_last_customer_id` so it is
  selected when POS reloads.
- Keep the cart and customer selected until checkout succeeds.
- Clear both values only after a successful sale.
- On checkout errors, restore the existing cart and highlight the failing line
  instead of showing an empty cart.

Agents must not replace this with a second customer form, a separate cart
format, or a redirect to the customer list unless the user explicitly asks
for that behavior. Validate a restored customer against the current tenant
before selecting it.
