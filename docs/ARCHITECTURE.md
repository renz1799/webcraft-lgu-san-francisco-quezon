# Architecture

## Purpose

This project uses a layered architecture to separate HTTP concerns, business rules, data access, and UI behavior. It is intentionally more than plain MVC.

## Layered Flow

Typical request flow:

`Route -> FormRequest -> Controller -> Service -> Repository -> Model/DB`

Response flow:

`Repository -> Service -> Controller -> JSON/Blade -> JS UI`

## Layer Responsibilities

### Routes

- Define URI, route name, and middleware.
- Enforce top-level access control (`auth`, `role_or_permission`, etc).

### FormRequest

- Validate payload shape and data constraints.
- Authorize caller as defense in depth.
- Normalize request input when needed (`prepareForValidation`).

### Controller

- Keep thin.
- Call service contracts only.
- No direct query logic.
- No business rule branching outside orchestration concerns.

### Service

- Own business rules and use cases.
- Coordinate repositories and helper services.
- Own transaction boundaries for write operations.
- Trigger audit logging for domain actions.

### Repository

- Own query and persistence details.
- Return stable payload contracts expected by upper layers.
- Hide model/query internals from controllers/services.

### Model

- Define relations, casts, scopes, and entity-level behavior.
- Avoid workflow orchestration in models.

## Datatable Pattern (Audit Logs Baseline)

Audit Logs is the core reference implementation for remote table pages.

### Backend contract

- Request class validates:
  - `page`
  - `size`
  - filter fields
- Controller:
  - reads validated data
  - clamps `page` and `size`
  - removes `page` and `size` from filter payload
  - calls `service->datatable($filters, $page, $size)`
- Service delegates datatable call to repository.
- Repository returns:
  - `data`
  - `last_page`
  - `total`
  - optional `recordsTotal`
  - optional `recordsFiltered`

### Frontend contract

- Blade mounts table and filter controls.
- Blade exposes runtime config via one window object (example: `window.__audit`).
- JS is split per module:
  - `table.js` for Tabulator setup, row actions, and info text
  - `filters.js` for filter state, advanced panel behavior, and reload wiring
- Table reload hook is exposed globally.
- Filter payload getter is exposed globally.

## Frontend Decomposition Pattern (AIR Inspect Reference)

For large single-page workflows, use an entry module that imports focused submodules.

Reference:

- Entry: `resources/js/air/inspect.js`
- Submodules: `resources/js/air/inspect/*.js`

Benefits:

- Smaller, focused files
- Easier debugging
- Lower merge conflict risk
- Clear ownership of feature slices (save, finalize, units, payload, utils)

## Write Operation Pattern

For create/update/delete actions:

- Validate and authorize via FormRequest.
- Execute domain logic in service method.
- Use `DB::transaction` when multiple writes are involved.
- Persist through repositories.
- Record audit events through `AuditLogServiceInterface`.
- Return user-friendly responses from controllers.

## Authorization Pattern

- Route middleware is first gate.
- Request `authorize()` is second gate.
- Keep role naming consistent; include legacy `admin` where needed by existing data.

## Dependency Injection Pattern

Bindings are centralized in:

- `app/Providers/RepositoryServiceProvider.php`

Use interface-based constructor injection in controllers and services.

## Module Template (New Feature)

When creating a new module, add these in order:

1. Routes and middleware.
2. Request classes.
3. Service contract and service implementation.
4. Repository contract and repository implementation.
5. Controller actions (thin).
6. Blade page(s) and modular JS entries.
7. Vite entry registration for new JS files.
8. Audit logging for domain changes.

## Current Notes

- Some legacy paths remain for backward compatibility.
- New modules should follow the patterns above.
- Existing modules should be incrementally aligned to the Audit Logs baseline.
