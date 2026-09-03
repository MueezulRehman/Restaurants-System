# Patch for app/Models/User.php — hasModuleAccess()

Replace the existing hasModuleAccess() method with the version in User.php
in this pack (copy the method body).

## Behaviour after fix

1. Super admin (entered) / restaurant **admin** → all modules enabled on the business.
2. **Manager** with explicit `module_access` list → intersection of grants ∩ business modules.
3. **Manager** with empty/null `module_access` → inherits all business-enabled modules
   (this was the main bug: Super Admin enabled modules on the business, but manager
   had empty grants so the sidebar stayed empty).
4. Alias map expanded for pharmacy / general_store / restaurant label keys.

