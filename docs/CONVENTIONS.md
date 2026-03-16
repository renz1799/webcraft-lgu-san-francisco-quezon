# Conventions

## Naming Conventions

### Classes

- Controllers: `*Controller`
- Requests: `*Request`
- Services: `*Service`
- Service contracts: `*ServiceInterface`
- Repositories: `Eloquent*Repository`
- Repository contracts: `*RepositoryInterface`

### Methods

Use consistent verb naming by use case:

- Datatable listing: `datatable(array $filters, int $page, int $size): array`
- Read by id: `findById(...)`
- Create draft: `createDraft(...)`
- Update: `updateX(...)` where `X` is module name
- Restore: `restoreX(...)`

### Routes

- Use named routes consistently.
- Prefer resource-like naming (`module.index`, `module.data`, `module.update`, etc).

## Controller Rules

- Inject interfaces, not concrete classes.
- Keep actions thin and focused.
- Do not place query builders in controllers.
- Do not duplicate business rules already in services.

## Request Rules

- Every mutating endpoint should use a dedicated FormRequest.
- `rules()` should define complete input contract.
- `authorize()` should reflect role/permission gates.
- Use `prepareForValidation()` for trim and normalization when needed.

## Service Rules

- Service is the main owner of use-case logic.
- Use transactions for multi-step writes.
- Call repositories for persistence and retrieval.
- Record audit logs for meaningful state changes.

## Repository Rules

- Keep query logic in repositories.
- Return stable output contracts expected by service/controller.
- For datatables, return:
  - `data`
  - `last_page`
  - `total`
  - optional `recordsTotal`
  - optional `recordsFiltered`
- DTBL-FS repositories must support archived scope (`active|archived|all`) through soft deletes and provide restore flow.
- Do not add force-delete operations in DTBL-FS modules.

## Frontend Rules (Blade + JS)

### Baseline Reference

Use Users Access as the canonical baseline for remote listing pages:

- `resources/views/access/users/index.blade.php`
- `resources/js/access-users/table.js`
- `resources/js/access-users/filters.js`
- `resources/js/access-users/actions.js`

Other modules should align to this pattern unless explicitly scoped otherwise.

### Command Keywords

Use these keywords in requests to define scope clearly:

- `DTBL-FS` (Datatable Baseline, Full Stack)
  - Apply full backend-to-frontend baseline.
  - Includes: Request, Controller `data()`, Service `datatable()`, Repository `datatable()`, Blade structure, and JS split (`table.js`, `filters.js`, `actions.js`).
  - Must keep response contract: `data`, `last_page`, `total`.
  - Must include soft-delete + restore flow (no force-delete route/action).
  - Advanced filters must include `archived` scope (`active|archived|all`).
  - Row actions must switch between archive and restore based on row state.

- `DTBL-UI` (Datatable Baseline, UI Only)
  - Apply Blade + JS baseline only.
  - Backend method/contract refactor is excluded unless explicitly requested.

### Blade

- Keep table page structure consistent:
  - primary search
  - optional status selector
  - advanced filters panel
  - clear/reset actions
  - table container
  - info text container
- Expose runtime config in a single window object (example: `window.__accessUsers`).

### JS module splitting

- For table pages, split into at least:
  - `table.js`
  - `filters.js`
  - `actions.js`
- For complex flows, use `resources/js/custom-entry.js` and lazy-load feature modules by page marker.
- Reference pattern:
  - `resources/js/air/inspect.js` imports `resources/js/air/inspect/*.js`.
- Each file should own one concern (payload, save, finalize, units, utils, etc).

### Vite Entry Strategy (Template-safe)

- Keep template `vite.config.js` stable and import `customViteInputs` from `vite.custom.inputs.js`.
- Register custom JS entry files in `vite.custom.inputs.js` (default should include `resources/js/custom-entry.js`).
- Use `resources/js/custom-entry.js` to lazy-load page modules with `import()` only when required DOM markers exist.
- In page Blade files, avoid per-page `@vite('resources/js/module/file.js')` calls for lazy-loaded modules.
- Use an `onReady(...)` guard inside lazy-loaded modules so they can initialize even when imported after DOM ready.
- After JS entry changes, run `npm run build` (or restart `npm run dev`) to refresh the manifest.

## Tabulator Conventions

- Use remote pagination mode.
- Send `page` and `size` to backend.
- Backend responds with `data`, `last_page`, `total`.
- Keep footer info text updated based on current page and total.
- For post-mutation refresh (archive/restore/delete), do not use bare `table.setData()`; use module reload helper that forces remote fetch via `setPage(1)` and `replaceData()` (or `setData(ajaxUrl, params)` fallback).

## UI Feedback

- Use SweetAlert2 for:
  - destructive action confirmation
  - success and error outcomes
  - actionable validation messages

## Authorization Conventions

- Prefer `role_or_permission` middleware at route level.
- Add request-level `authorize()` checks for defense in depth.
- Keep support for legacy role names when required (`Administrator` and `admin`).

## Audit Logging Conventions

- Use dot notation action names (example: `ris.updated`, `user.restored`).
- Keep audit calls in service layer for domain operations.
- Prefer business milestones, lifecycle events, and cross-module outcomes over draft-edit churn.
- Write searchable `message` values that include the main business identifier.
- Use structured display payloads for user-facing audit detail modals.
- See `docs/AUDIT_LOGGING.md` for the full audit policy.

## Git Commit Conventions

- Use multiple `-m` flags for readability in history.
- First `-m`: short subject line.
- Additional `-m`: grouped details (`what`, `why`, `notes`).

Example:

```bash
git commit \
  -m "refactor(access-users): align listing flow with datatable baseline" \
  -m "Apply Request->Controller->Service->Repository datatable contract" \
  -m "Split frontend into table.js, filters.js, and actions.js"
```

## Definition Of Done Checklist

Before marking a feature complete:

1. Request validation and authorization added.
2. Controller remains thin.
3. Business logic placed in service.
4. Queries placed in repository.
5. UI follows Users datatable baseline (`DTBL-FS` / `DTBL-UI`) for list pages.
6. DTBL-FS list pages include soft-delete + restore with archived filter; no force-delete route/action.
7. `vite.custom.inputs.js` and `resources/js/custom-entry.js` updated for new page JS modules.
8. Role and permission gates applied.
9. Audit logging added for write operations.
10. Basic lint, syntax, and build checks passed.
