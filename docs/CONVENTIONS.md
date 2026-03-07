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

## Frontend Rules (Blade + JS)

### Baseline Reference

Use Audit Logs as the baseline for remote listing pages:

- `resources/views/logs/audits/index.blade.php`
- `resources/js/audit-logs/table.js`
- `resources/js/audit-logs/filters.js`

RIS and other modules should align to this pattern.

### Blade

- Keep table page structure consistent:
  - primary search
  - optional status selector
  - advanced filters panel
  - clear/reset actions
  - table container
  - info text container
- Expose runtime config in a single window object (example: `window.__audit`).

### JS module splitting

- For table pages, split into at least:
  - `table.js`
  - `filters.js`
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
- Capture meaningful before and after payloads when possible.
- Keep audit calls in service layer for domain operations.

## Git Commit Conventions

- Use multiple `-m` flags for readability in history.
- First `-m`: short subject line.
- Additional `-m`: grouped details (`what`, `why`, `notes`).

Example:

```bash
git commit \
  -m "refactor(audit-logs): align listing flow with core pattern" \
  -m "Move filtering/query logic into repository datatable contract" \
  -m "Split frontend into table.js and filters.js for maintainability"
```

## Definition Of Done Checklist

Before marking a feature complete:

1. Request validation and authorization added.
2. Controller remains thin.
3. Business logic placed in service.
4. Queries placed in repository.
5. UI follows Audit Logs baseline (if list page).
6. `vite.custom.inputs.js` and `resources/js/custom-entry.js` updated for new page JS modules.
7. Role and permission gates applied.
8. Audit logging added for write operations.
9. Basic lint, syntax, and build checks passed.
