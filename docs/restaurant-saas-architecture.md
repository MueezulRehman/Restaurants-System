# Restaurant SaaS Architecture

## 1. Core roles
- Super Admin: manages the platform, creates restaurants, assigns plans, and controls branding.
- Restaurant Admin: manages its own restaurant data, menu, staff, orders, reports.
- Staff / Manager: operational roles inside a restaurant.

## 2. Frontend structure
- Public site: menu, checkout, order tracking
- Super Admin panel: restaurant registration, plans, custom domains, owner credentials
- Restaurant dashboard: orders, inventory, staff, menu, expenses, reports

## 3. Backend structure
- Auth layer for super admin and restaurant users
- Restaurant management controller for onboarding
- Restaurant-specific resource controllers for menu, staff, orders, expenses
- Domain and branding settings module
- Subscription and billing module later

## 4. Database structure
### restaurants
- id
- name
- slug
- email
- phone
- address
- custom_domain
- plan
- status
- logo_path
- theme
- trial_ends_at

### restaurant_domains
- id
- restaurant_id
- domain
- is_primary
- status
- verified_at

### users
- id
- restaurant_id
- name
- email
- phone
- role
- password
- is_active
- joined_at

### menu, orders, expenses, staff, attendance, salary
- Each should be linked to a restaurant_id in the future for full tenant isolation.

## 5. Domain strategy
- Preferred: custom domain per restaurant
- DNS must point to the app server
- The app stores the domain and later resolves it to the correct tenant context

## 6. Recommended roadmap
1. Super admin onboarding
2. Restaurant owner account creation
3. Brand settings and custom domain storage
4. Restaurant-scoped data isolation
5. Subscription billing and invoicing
