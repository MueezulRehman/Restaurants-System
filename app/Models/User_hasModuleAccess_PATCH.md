# Patch for app/Models/User.php — hasModuleAccess()

Replace the existing hasModuleAccess() method with the version in User.php
in this pack (copy the method body).

## Behaviour after fix

1. Super admin (entered) / restaurant **admin** / **manager** → all modules enabled
   on the business.
2. Legacy `module_access` values are no longer used to hide enabled business
   modules, so direct Manager login and Super Admin impersonation stay consistent.
3. Business-level enablement remains the single source of truth and disabled
   modules remain blocked.
