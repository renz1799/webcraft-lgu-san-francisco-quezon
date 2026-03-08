# GSO San Francisco Quezon

General Services Office Information System built with Laravel, Blade, and Vite.

## Architecture At A Glance

This project is not plain MVC. It uses layered application architecture:

- `FormRequest` for validation + authorization
- `Controller` for HTTP orchestration only
- `Service` for business rules and transaction boundaries
- `Repository` for persistence/query logic
- `Model` for Eloquent relationships and entity behavior

Read the full documentation:

- [Architecture](docs/ARCHITECTURE.md)
- [Conventions](docs/CONVENTIONS.md)
- [Core Sync Operations](docs/CORE_SYNC_OPERATIONS.md)

## Core Stack

- PHP / Laravel
- MySQL
- Blade templates
- Vite (modular JS entries)
- Tabulator (server-driven data tables)
- SweetAlert2 (user feedback)
- Spatie Roles and Permissions

## Local Setup

1. Install dependencies:
```bash
composer install
npm install
```

2. Configure environment:
```bash
cp .env.example .env
php artisan key:generate
```

3. Configure database in `.env`, then run:
```bash
php artisan migrate
```

4. Run app and assets:
```bash
php artisan serve
npm run dev
```

## Build

```bash
npm run build
```

## Frontend Entry Pattern (Template-safe)

Use this flow for custom page JavaScript so template updates are easy to merge:

1. Keep template `vite.config.js` mostly unchanged; only keep `...customViteInputs` in the `input` array.
2. Register custom entries in `vite.custom.inputs.js`.
3. Keep `resources/js/custom-entry.js` as the single custom entry, and lazy-load page modules with `import()` based on DOM markers.
4. In page blades, do not add per-page `@vite('resources/js/...')` calls for module files.
5. Add an `onReady` guard in lazy-loaded modules so they still initialize when imported after `DOMContentLoaded`.
6. After changes, run `npm run build` (or restart `npm run dev`) so the manifest is refreshed.

## Datatable Baseline Keywords

Use these command keywords to request table refactors with consistent scope:

- `DTBL-FS` (Datatable Baseline, Full Stack)
  - Apply the Users baseline from backend to UI.
  - Backend flow: `Request -> Controller -> Service -> Repository` using `datatable(...)`.
  - Soft-delete + restore flow is required (no force-delete endpoints/actions in DTBL-FS modules).
  - Response contract: `data`, `last_page`, `total`.
  - Frontend flow: Blade search + advanced filters + table info, with JS split into `table.js`, `filters.js`, `actions.js`.
  - Advanced filters must include `archived` scope (`active|archived|all`), and row actions must switch between archive/restore based on row state.
  - After archive/restore/delete actions, refresh tables with the module reload helper (force remote fetch); avoid bare `table.setData()` calls.

- `DTBL-UI` (Datatable Baseline, UI Only)
  - Apply only Blade and JS baseline structure.
  - Skip backend contract/method refactor unless explicitly requested.

Canonical baseline reference:

- `resources/views/access/users/index.blade.php`
- `resources/js/access-users/table.js`
- `resources/js/access-users/filters.js`
- `resources/js/access-users/actions.js`

## Project Rules

- New modules should follow `Request -> Controller -> Service -> Repository`.
- Keep role checks consistent, including legacy `admin` where needed.
- For table pages, follow the Users datatable baseline (`DTBL-FS` / `DTBL-UI`) for backend payload + Blade/JS structure.
- DTBL-FS modules must use soft delete + restore only; do not add force-delete routes/actions.
- For large frontend flows, split files like `resources/js/air/inspect.js` and load them through `resources/js/custom-entry.js` lazy imports.

See [docs/CONVENTIONS.md](docs/CONVENTIONS.md) for the full checklist.
