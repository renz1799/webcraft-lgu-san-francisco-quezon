# Architecture

## Purpose

The Webcraft Core System uses a layered architecture to separate:

HTTP concerns
business orchestration
data access
presentation behavior
infrastructure capabilities
runtime platform context
access boundaries

This architecture is intentionally more structured than plain MVC to support platform reuse across multiple systems.

This document defines structural rules.
Coding conventions live in `CONVENTIONS.md`.

---

# Core System Definition

Core is the platform foundation.

Core provides:

infrastructure
capabilities
shared contracts
architecture standards
runtime context resolution
module isolation foundations
organization structure foundations

Modules provide:

business workflows
domain behavior
feature orchestration
UI behavior
module-specific wording
module-specific policies

Core must remain:

module agnostic
domain neutral
presentation neutral

Detailed Core service boundaries are defined in:

docs/CORE_SERVICE_RULES.md

Detailed platform boundary rules between Core and Modules are defined in:

docs/CORE_PLATFORM_RULES.md

Concrete target placement and migration mapping are defined in:

docs/CORE_MODULE_STRUCTURE_MAP.md

The LGU deployment and operating model is defined in:

docs/LGU_PLATFORM_DOCTRINE.md

---

# Core vs Modules Boundary

The system must be treated as a platform with applications running on top of it.

Core is the platform foundation.

Modules are business applications built on the platform.

## Placement Rule

If logic exists because the platform needs it regardless of business module:

it belongs in Core.

If logic exists because a specific module exists:

it belongs in that module.

Examples of Core concerns:

audit logging
notification transport
authentication and access control
CurrentContext
print infrastructure
print config loading and paper-profile resolution
storage integrations
shared repositories
shared builders and providers
platform support services

Examples of module concerns:

business workflows
domain services
module specific builders
module specific providers
module repositories
policies
business rules
domain terminology

## Dependency Direction

Dependencies must always flow:

Modules -> Core

Never:

Core -> Modules

Core must not contain:

module workflows
module wording
module specific builders or providers
module specific service branching

## Ownership Rule

Core owns platform capabilities.

Modules own business behavior.

Shared platform capabilities are Core-owned features used by all modules without becoming Core admin tools.

Examples:

Tasks
Notifications
future shared workflow engines

Print infrastructure is also Core-owned, while printable registrations remain owned by the module or Core feature that produces the document.

Do not mix these responsibilities.

## Structural Direction

Target structure should follow this direction:

```text
app/
  Core/
    Builders/
    Data/
    Http/
    Models/
    Policies/
    Providers/
    Repositories/
    Services/
    Support/
  Modules/
    DTS/
    GSO/
```

Concern folders such as Services, Builders, Providers, Repositories, and Policies should live under the owning side of that boundary.

Tasks is no longer modeled as a business module. It is a Core-owned shared workflow capability, so its code belongs under Core while its records still carry `module_id` to indicate the owning business workflow context.

## Platform Layers

The platform now has three architectural layers:

Core Admin
platform governance and oversight

Business Modules
department workflows and domain records

Shared Platform Capabilities
cross-cutting features such as Tasks that are Core-owned but used across modules

## Platform Mental Model

Core asks:
Does this person exist?

Module asks:
Can this person work here?

Tasks asks:
What work must this person do?

Printing asks:
How should this document be rendered?

Printable registration asks:
What document does this owner expose for printing?

---

# Platform Context Model

The Core System is designed for:

one database per LGU deployment
one deployed platform per LGU
multiple enabled modules inside that deployment
multiple departments
shared user identities
module-specific access boundaries

Examples:

CORE
DTS
GSO
future LGU modules

This means the platform must separate:

runtime identity
relational identity
access identity

## Runtime Identity

Runtime identity is defined by configuration.

Pattern:

.env → config → CurrentContext → database

Examples:

APP_MODULE_ID
APP_MODULE_CODE
APP_MODULE_NAME
APP_DEFAULT_DEPARTMENT_ID
APP_DEFAULT_DEPARTMENT_CODE
APP_DEFAULT_DEPARTMENT_NAME

Runtime identity answers:

Which application instance is currently running?

It does not replace relational identity inside transactional records.

Important distinction:

platform default department is not the same thing as module department assignment.

`CurrentContext::defaultDepartmentId()` should represent the platform fallback or base department context.

Module-specific department assignment should be resolved separately when writing records such as `user_modules.department_id`.

## Relational Identity

Relational identity is stored in database records.

Shared operational tables should prefer:

module_id
department_id

Use relational foreign keys instead of string-based identity whenever Core needs filtering, joins, reporting, or isolation.

## Access Identity

Access identity is defined through:

user_modules

This determines which user may access which module and under which department scope.

A valid shared user account does not automatically imply access to every module website.

---

# Layered Flow

Typical request flow:

Route
→ FormRequest
→ Controller
→ Service
→ Repository
→ Model / Database

Response flow:

Repository
→ Service
→ Controller
→ Blade / JSON
→ JS UI

This flow must remain consistent for new modules.

Context resolution should happen before business orchestration becomes fragmented.

Runtime module/default department resolution belongs in a resolver such as `CurrentContext`, not scattered across services and controllers.

---

# Layer Responsibilities

## Routes

Responsibilities:

* define URI and route names
* apply middleware
* enforce top-level authorization

Routes must not contain business logic.

Routes may be module-aware through middleware or route groups, but routing definitions must still remain thin.

---

## FormRequest

Responsibilities:

* validation
* authorization
* payload normalization

FormRequest should ensure controllers receive validated data.

If a request accepts module or department filters, those should be validated explicitly.

---

## Controller

Controllers must remain thin.

Responsibilities:

* call services
* return responses
* coordinate HTTP concerns

Controllers must not contain:

query logic
business rules
domain branching
module resolution branching duplicated across actions

Controllers orchestrate requests, not workflows.

If current module or default department context is needed, it should be consumed through a resolver, not rebuilt manually.

---

## Service Layer

Services own:

business use cases
orchestration
transaction boundaries
coordination of repositories
coordination of infrastructure services
coordination of module-aware or department-aware workflows

Services may:

call repositories
call dispatchers
call providers
trigger audit logging
apply module/department scope rules

Services should not:

format UI data
perform raw queries
contain presentation logic
read env directly

Services should prefer runtime resolvers for current platform context.

---

## Repository Layer

Repositories own:

query logic
filtering
pagination
persistence
module/department scoped retrieval

Repositories should return:

models
collections
paginators
query result objects

Repositories should not contain:

UI formatting
display labels
formatted timestamps
presentation flags
runtime identity resolution from env/config directly

Presentation mapping belongs to presenters or transformers.

Runtime context may be passed in, but repository classes should not become app identity resolvers.

---

## Model Layer

Models define:

relationships
casts
scopes
entity-level behavior

Models should not:

orchestrate workflows
coordinate services
resolve runtime env context directly

Models represent entities, not use cases.

Examples of platform entities now treated as Core foundations:

modules
departments
user_modules
google_tokens
audit_logs
notifications
tasks may be shared operational records, but Task business behavior belongs in Modules

---

# Presentation Layer

Presentation shaping should live outside repositories and services.

Examples:

Presenters
Transformers
ViewModels

Presentation layer owns:

display labels
formatted dates
status badges
UI row shaping
module/department display labels

This separation allows:

API reuse
exports
mobile clients
dashboards
multiple module frontends

without repository changes.

Naming and coding conventions for presenters are defined in:

docs/CONVENTIONS.md

---

# Core vs Module Responsibilities

## Core Should Contain

Shared capabilities reusable across modules:

audit logging
notification dispatching
file storage
mail dispatching
attachment handling
runtime context resolution
module isolation foundations
department structure
module access mapping
shared integration storage

Core provides engines.

---

## Modules Should Contain

Domain behavior:

business workflows
events
recipients
wording
feature orchestration
module-specific UI behavior
module-specific policies

Modules define scenarios.

---

## Structural Boundary Rule

If logic requires module knowledge:

it belongs in the module.

If logic is reusable without modification:

it belongs in Core.

If logic is about platform context, access boundaries, shared identity, module resolution, department structure, or shared infrastructure:

it belongs in Core.

Ask two questions:

Would this still exist if the module were removed?

If yes:

Core.

Does this exist because this module exists?

If yes:

Module.

---

# Generic Service Contract Rules

Generic services may accept structured payloads.

Payload structure must be:

explicit
consistent
documented
module-aware where applicable
department-aware where applicable

Detailed payload conventions are defined in:

docs/CONVENTIONS.md

Refactor triggers for generic services are defined in:

docs/CORE_REFACTOR_GUIDELINES.md

---

# Shared Table Rules

Tables used across modules should prefer relational scope fields:

module_id
department_id (when applicable)

Optional snapshot/display fields may still exist when useful for historical readability, but they must not replace relational identity.

This improves:

filtering
reporting
scalability
joins
module isolation
department traceability

Shared tables commonly include:

audit_logs
notifications
attachments
comments
tasks
google_tokens

Primary relational filtering fields should be indexed.

Broad text search should remain secondary.

Search optimization strategies belong in repository implementation, not controllers.

---

# Access Boundary Rules

## Users

Users are shared platform identities.

A user existing in `users` means:

this person exists in the shared platform

It does not mean:

this person can access every module website

## Module Access

Access must be controlled through `user_modules`.

`user_modules` should represent:

user_id
module_id
department_id
is_active

A module website must verify both:

valid shared user credentials
active module access

This prevents DTS users from automatically accessing GSO, and vice versa, while still using the same LGU database.

---

# Context Resolution Pattern

Current platform context must be resolved through a dedicated resolver.

Reference pattern:

CurrentContext

This resolver should provide:

module()
moduleId()
defaultDepartment()
defaultDepartmentId()

Use this resolver for:

current application identity
default system-generated scope
seeder defaults
module-aware infrastructure operations

Do not treat `CurrentContext` as the source of LGU tenancy. In the current platform model, the LGU is the deployment boundary and the runtime context is primarily module and department aware.

Do not reinterpret `defaultDepartmentId()` as a universal module department.

If a workflow needs the department for a specific module assignment, use a dedicated resolver for module department mapping and keep `defaultDepartmentId()` as platform fallback.

Do not repeat ad hoc lookups such as:

Module::find(config('module.id'))
Department::first()

That repetition leads to drift and unstable behavior.

---

# Datatable Pattern (Audit Logs Baseline)

Audit Logs is the reference implementation for remote datatable pages.

Backend contract:

Request validates:

* page
* size
* filters
* module filters when applicable
* department filters when applicable

Controller:

* reads validated payload
* clamps page/size
* passes filters to service

Service:

* coordinates query request

Repository:

returns:

data
last_page
total

Optional:

recordsTotal
recordsFiltered

Frontend contract:

Blade:

mounts filters and table container.

JS:

table.js → table setup
filters.js → filter state

This pattern should be followed for new table modules.

Current shared reference-data example:

`accountable-persons`

Current module-owned document example:

`gso.pars.index`

Pattern notes:

* Core owns the model, repository, service, requests, controller, Blade, and datatable JS
* modules may expose the page through scoped routes such as `gso.accountable-persons.*`
* Blade should resolve endpoints through `AdminRouteResolver` so the same Core view can work in both platform and module context
* module navigation decides where the page appears; in GSO it lives under `Reference Data`
* default to a compact toolbar with a visible search field, `More Filters`, `Clear`, and a single primary action button
* secondary filters such as status and department should live inside the `More Filters` panel instead of occupying the main toolbar
* box headers should use the shared `.datatable-toolbar` and `.datatable-toolbar-actions` classes so titles and controls align consistently across table pages
* the AIR index and shared Accountable Persons page are the visual reference for this toolbar layout
* avoid page-specific title-height or vertical-alignment overrides unless a document-specific layout genuinely requires them

Detailed frontend conventions live in:

docs/CONVENTIONS.md

---

# Frontend Decomposition Pattern

Large workflows should use modular JS structure.

Pattern:

Entry file:
resources/js/module/feature.js

Submodules:
resources/js/module/feature/*.js

Benefits:

smaller files
clear ownership
reduced conflicts
easier debugging
module-specific separation without violating shared Core rules

## Frontend Entry Coordination

Authenticated pages load a single shared Vite entry from the main layout:

`resources/core/js/custom-entry.js`

That file should remain a thin coordinator only.

Current structure:

```text
resources/
  core/
    js/
      custom-entry.js
      entry/
        page-loader.js
        core-pages.js
        module-pages.js
  modules/
    gso/
      js/
        entry.js
```

Responsibilities:

`custom-entry.js`
boot once and delegate to registries

`resources/core/js/entry/core-pages.js`
register Core-owned page loaders

`resources/core/js/entry/module-pages.js`
register module entry bootstraps

`resources/modules/<module>/js/entry.js`
register page detectors and lazy imports for that module only

`resources/core/js/entry/page-loader.js`
shared DOM-ready and page-loader helpers

### Page Detection Rule

Page-specific JS should still be activated by stable DOM markers such as:

`tasks-table`
`gso-air-edit-page`
`risForm`
`par-table`
`parShowPage`
`ptr-table`
`ptrForm`
`itr-table`
`itrForm`

The registry should:

1 map a DOM marker to a lazy import group
2 keep error reporting local to that page loader
3 avoid embedding business logic in the global coordinator

### Module Growth Rule

When a new module is added:

1 create `resources/modules/<module>/js/entry.js`
2 keep module-specific page loaders inside that file
3 register that module bootstrap in `resources/core/js/entry/module-pages.js`
4 do not expand `custom-entry.js` with page-by-page module logic

This keeps the global bootstrap stable while allowing module-owned frontend growth.

## Reusable Form Helper Rule

Reusable document-form interactions should live in module-owned helper files instead of being copied into each page script.

Example pattern inside GSO:

`resources/modules/gso/js/accountable-officers/autocomplete.js`
search and selection UI

`resources/modules/gso/js/accountable-officers/details-modal.js`
modal-backed create or resolve flow for incomplete signatory records

Page files such as AIR, RIS, PAR, ICS, PTR, ITR, and WMR editors should only own:

role labels
field mapping
page-specific autofill behavior

Helper files should own:

DOM plumbing
shared modal markup
shared validation prompts
shared request callbacks

When document editors need to create or enrich a reusable accountable person record, prefer the module's resolve endpoint so users with document-level permissions can complete the flow without requiring access to the shared Accountable Persons reference page.

---

# Print Workspace Pattern

Interactive print pages should follow workspace architecture:

sidebar:
report inputs and actions

preview:
live paper rendering

optional:
PDF export endpoint

Reference:

docs/PRINT_WORKSPACE_STANDARD.md

Production print modules must still follow layered architecture for data sourcing.

If print data is module-specific or department-specific, that scope should be resolved in the service/repository layer, not embedded in the Blade layer.

---

# Write Operation Pattern

Create/update/delete flow:

FormRequest:
validation + authorization

Service:
domain logic

Repository:
persistence

Service:
audit logging

Controller:
response

Audit rules defined in:

docs/AUDIT_LOGGING.md

Shared write operations should persist `module_id` and `department_id` when the target table supports those fields.

---

# Authorization Pattern

Authorization layers:

Route middleware → first gate

FormRequest authorize() → second gate

Keep role naming consistent across modules.

Module access remains separate from role authorization.

Meaning:

`user_modules` decides whether user can enter module
roles/permissions decide what user can do inside module

---

# Dependency Injection Pattern

Most application bindings are centralized in:

app/Core/Providers/CoreServiceProvider.php

Platform-wide shared container services such as `CurrentContext` may be registered in:

app/Core/Providers/AppServiceProvider.php

Use interface-based injection for:

controllers
services

Avoid concrete coupling in upper layers.

---

# Seeder Architecture Pattern

Seeders must respect structural dependencies.

Recommended order:

ModuleSeeder
DepartmentSeeder
PermissionsSeeder
UserSeeder
TaskSeeder
NotificationSeeder

Context-dependent seeders should resolve runtime identity through `CurrentContext` instead of arbitrary database ordering.

System-owned platform records may use deterministic UUIDs when identity stability matters.

---

# Module Template (New Feature)

When creating a new module:

1 Routes and middleware
2 Request classes
3 Service contract + implementation
4 Repository contract + implementation
5 Controller actions
6 Blade pages
7 Modular JS entries
8 Audit logging
9 Module access rules
10 Module/department relational scope where applicable

New modules must follow layered flow.

---

# Current Notes

Some legacy modules may not fully follow these rules.

New modules must follow this architecture.

Existing modules should be incrementally aligned.

Refactor guidance is defined in:

docs/CORE_REFACTOR_GUIDELINES.md
