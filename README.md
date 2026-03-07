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

## Project Rules

- New modules should follow `Request -> Controller -> Service -> Repository`.
- Keep role checks consistent, including legacy `admin` where needed.
- For table pages, follow the Audit Logs pattern for backend payload + Blade/JS structure.
- For large frontend flows, split files like `resources/js/air/inspect.js` and keep an entry file that imports focused submodules.

See [docs/CONVENTIONS.md](docs/CONVENTIONS.md) for the full checklist.
