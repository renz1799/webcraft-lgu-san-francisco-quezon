# LGU Platform Doctrine

## Purpose

This document defines the operating model of the Webcraft LGU Platform.

Use it as the high-level doctrine for:

- deployment
- database ownership
- module boundaries
- login and routing behavior
- department assignment rules

`ARCHITECTURE.md` defines structural layering.
`CORE_PLATFORM_RULES.md` defines Core vs Module boundaries.
`CORE_MODULE_STRUCTURE_MAP.md` defines the code layout baseline.

---

# Platform Formula

The system should be understood as:

Core Platform + Modules = LGU System Instance

Core provides the reusable platform.

Modules provide business functionality.

An LGU deploys one platform instance with the modules it needs.

---

# Deployment Unit

The deployment unit is the LGU.

Correct examples:

- `lgu1.webcraft.ph`
- `lgu2.webcraft.ph`
- `lgu3.webcraft.ph`

Wrong examples:

- `lgu1dts.webcraft.ph`
- `lgu1gso.webcraft.ph`
- `lgu1tasks.webcraft.ph`

Modules belong inside one LGU deployment.

They are not separate products to deploy independently.

---

# Repository Strategy

The platform uses one monorepo.

Repository model:

- one codebase
- shared Core
- multiple business modules
- shared build and deployment workflow

Code structure baseline:

```text
app/
  Core/
  Modules/
    Tasks/
    DTS/
    GSO/

resources/
  core/
  modules/
    tasks/
```

This keeps platform code and module code in the same repository without duplicating infrastructure.

---

# Database Strategy

The database unit is one database per LGU.

Examples:

- `lgu1_db`
- `lgu2_db`
- `lgu3_db`

Reason:

- client data isolation
- simpler backup and restore
- simpler troubleshooting
- cleaner legal and operational separation

Modules inside the same LGU share that LGU database.

Do not create one database per module.

---

# Module Strategy

Modules are enabled logically inside the LGU deployment.

Examples:

- LGU 1 may enable `DTS`, `GSO`, `Tasks`, and `Procurement`
- LGU 2 may enable only `DTS`
- LGU 3 may enable `GSO` and `Procurement`

This means:

- one deployed platform
- one login
- one database
- multiple module capabilities

---

# URL Model

Platform base:

- `https://lgu1.webcraft.ph`

Canonical login:

- `https://lgu1.webcraft.ph/wc-login`

Platform routes:

- `/wc-login`
- `/logout`
- `/profile`
- `/notifications`
- `/audit-logs`
- `/modules`

Module routes:

- `/dts/*`
- `/gso/*`
- `/tasks/*`
- `/procurement/*`

Modules should normally live under route prefixes inside the LGU platform instance.

---

# Authentication Model

Authentication is single-login per LGU deployment.

User flow:

1. user logs in once through `/wc-login`
2. platform checks active `user_modules` access
3. if the user has one module, the platform may redirect automatically
4. if the user has multiple modules, the platform may show a module selector

Module access is separate from identity.

`users` means the person exists in the LGU platform.

`user_modules` means the person may access a specific module.

---

# CurrentContext Rule

`CurrentContext` is a runtime resolver for the active request context.

It should primarily resolve:

- current module
- current department fallback
- current user

The LGU itself is usually a deployment-level fact, not a per-request tenant lookup in the current architecture.

Use `CurrentContext` for:

- current module identity
- platform default department fallback
- current request scope

Do not overload it with module-specific department assignment rules.

---

# Department Assignment Rule

Platform default department and module department are different concepts.

`CurrentContext::defaultDepartmentId()` means:

- platform fallback department
- base platform ownership context
- default system scope when no module-specific department is resolved

It does not mean:

- authoritative department for every module workflow
- authoritative department for every module access grant

The correct hierarchy for module department assignment is:

1. explicit department assignment
2. database-backed module default mapping
3. configuration fallback mapping
4. platform default department fallback

Authoritative storage:

- `users.primary_department_id` = base or home department
- `user_modules.department_id` = actual department scope inside that module

`user_modules.department_id` is the authoritative department for module access.

---

# Core vs Modules Rule

Core is platform infrastructure.

Modules are business applications.

Dependency direction:

Modules -> Core

Never:

Core -> Modules

Examples of Core concerns:

- authentication
- access control
- audit logs
- notifications
- CurrentContext
- print infrastructure
- storage integrations
- shared platform UI settings

Examples of module concerns:

- task workflows
- DTS routing behavior
- GSO-specific business rules
- module-specific recipients and wording
- module-specific policies

---

# Shared Integration Rule

Modules may share platform capabilities, but they should not depend on each other directly.

Allowed shared infrastructure lives in Core:

- audit logs
- notifications
- file storage
- print services
- shared identity
- shared access mapping

If modules must coordinate, do it through Core contracts or shared platform storage.

---

# Baseline Ready For New Modules

The current codebase baseline is:

- platform code in `app/Core`
- business module code in `app/Modules`
- platform resources in `resources/core`
- module resources in `resources/modules/<module>`

Each new module should follow the same pattern immediately.

Recommended migration order for the next module:

1. move PHP concern into `app/Modules/<Module>`
2. move views and JS into `resources/modules/<module>`
3. add a module service provider
4. update routes and bindings
5. add focused tests
6. update the structure map and architecture docs

---

# Final Doctrine

The platform should always be treated as:

a modular LGU platform where each LGU runs one shared Core instance, one database, and multiple integrated modules inside the same deployment.
