# Authorization Standard

This document defines the platform authorization contract after the permission-first refactor.

---

# Core Rule

Permissions are the runtime access contract.

Roles are permission bundles.

New route protection, request authorization, policy elevation checks, sidebar visibility, Blade visibility, and page-action visibility must be built on normalized permissions.

Do not introduce new raw runtime role gates for module-sensitive behavior.

---

# Context Model

Authorization is still context-aware.

Separate these concerns:

- `user_modules` decides whether a user may enter a module context
- permissions decide what the user may do inside that active context

Meaning:

- module access is not the same thing as authorization
- a user may belong to a module but still lack feature permissions
- a user may have a role assigned in a module, but runtime checks should still resolve through permissions

---

# Permission Naming Rule

Use normalized `feature.action` keys.

Examples:

- `users.view`
- `users.manage_access`
- `roles.archive`
- `tasks.view_all`
- `audit_logs.restore_data`
- `inventory_items.manage_files`
- `reports.property_cards.view`

Avoid vague sentence-style names such as:

- `view AIR`
- `modify AIR`
- `view All Tasks`

Avoid relying on broad generic CRUD names when the feature really has workflow actions.

Prefer explicit workflow keys such as:

- `ris.submit`
- `wmr.approve`
- `air.finalize_inspection`
- `inventory_items.import_from_inspection`

---

# Grouping Rule

Permissions should be grouped for admin usability by concern/page.

Current seeded groups include:

- `Core / Users`
- `Core / Roles`
- `Core / Permissions`
- `Core / Identity Governance`
- `Core / Shared Tasks`
- `Core / Audit and Logs`
- `Core / Drive`
- `Core / Theme`
- `Core / Shared Reference Data`
- `GSO / Tasks`
- `GSO / AIR`
- `GSO / RIS`
- `GSO / PAR`
- `GSO / ICS`
- `GSO / PTR`
- `GSO / ITR`
- `GSO / WMR`
- `GSO / Inventory`
- `GSO / Reference Data`
- `GSO / Reports`
- `GSO / Module Access`
- `GSO / Theme`

This grouping is used for:

- permission seeding
- permission management UI
- role assignment UI
- audit display formatting

---

# Runtime Gates

## Routes

Use context-aware permission middleware.

Current normalized route protection uses the `permission:` middleware, which resolves through:

- `app/Core/Http/Middleware/ContextPermissionMiddleware.php`

Do not add new route groups that depend on raw role middleware when a permission exists.

## FormRequest authorize()

Use the context-aware authorizer path.

Preferred entry points:

- `App\Core\Support\AdminContextAuthorizer`
- module request concerns that wrap that authorizer

Do not add new raw `hasRole()`, `hasAnyRole()`, or module-sensitive raw `can()` checks inside FormRequest authorization.

## Policies

Policies remain valid when the decision depends on workflow state, ownership, claimability, or other record-specific rules.

Examples:

- task claim
- task status change
- task comment visibility

Even then, elevated access decisions inside the policy should still be permission-first.

## Sidebar, Blade, and Actions

Sidebar visibility and page action visibility should use:

- `AdminContextAuthorizer`
- or policy-based `@can` only when the decision is record/workflow-specific

Do not use raw global Spatie role checks for module-sensitive UI visibility.

---

# Role Rule

Roles are still useful for:

- permission bundling
- assignment UX
- user/module access reporting
- business-friendly display in admin pages

Roles are not the primary runtime contract.

`Administrator` should pass checks because it is granted the relevant permission bundle through seeders, not because the code contains scattered administrator bypasses.

---

# Seeder Structure

Fresh installs seed normalized permissions only.

Primary seeders:

- `database/seeders/CorePermissionSeeder.php`
- `database/seeders/Modules/GSO/GsoPermissionSeeder.php`
- `database/seeders/CoreRolePermissionSeeder.php`
- `database/seeders/Modules/GSO/GsoRolePermissionSeeder.php`
- `database/seeders/PermissionsSeeder.php`

The dedicated GSO permission catalog lives in:

- `database/seeders/Modules/GSO/GsoPermissionSeeder.php`

This is the module-specific source of truth for GSO permission keys and groupings.

---

# Legacy Upgrade Path

Fresh installs do not seed old permission names anymore.

Existing databases that still carry old permission rows or old permission assignments must normalize through:

- `database/seeders/LegacyPermissionAssignmentMigrationSeeder.php`

That seeder uses:

- `database/seeders/Support/LegacyPermissionCatalog.php`

to map old coarse names onto the normalized permission catalog.

Operational rule:

Run:

`php artisan db:seed --class=PermissionsSeeder`

after pulling permission-taxonomy changes into an existing environment.

---

# Intentional Exceptions

These are currently intentional and acceptable:

1. `TaskPolicy` and Blade `@can(...)` on task detail pages

Reason:

- task actions depend on task-specific workflow state
- claimability and ownership remain policy concerns

2. `ContextRoleMiddleware` and module role services still exist

Reason:

- compatibility
- role assignment flows
- module role reporting

Rule:

- do not use them as the preferred gate for new feature access when a permission exists

3. `AdminContextAuthorizer` convenience methods such as:

- `canManageCurrentContextAccess()`
- `canRegisterUsers()`
- `canViewCurrentContextAuditLogs()`
- `canRestoreCurrentContextAuditData()`

Reason:

- they are permission bundle helpers, not role bypasses

They remain acceptable as long as they resolve through permissions.

---

# Definition of Done For Authorization Work

Authorization work is not complete until all of the following align:

- routes use normalized permission middleware
- FormRequest `authorize()` is permission-first
- policies use permission-first elevated access checks
- sidebar visibility uses context-aware permissions
- Blade/action visibility uses permissions or policy abilities
- seeders include the new normalized permissions
- administrator bundles are synced through seeding
- tests cover the affected gate

If a feature still relies on a raw role check, that must be either:

- removed
- replaced
- or documented as an intentional exception

