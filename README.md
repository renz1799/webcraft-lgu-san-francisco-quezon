# Webcraft Core System

The Webcraft Core System is the shared architectural foundation for Webcraft platforms and LGU information systems.

This repository defines:

- platform architecture standards
- core service contracts
- infrastructure services
- UI patterns
- audit logging standards
- development conventions
- platform governance rules

Application systems such as GSO, DTS, Procurement, HR, and future modules must align with Core standards.

Core is not an application.

Core is the platform foundation.

---

# Core Philosophy

Core provides:

capabilities  
infrastructure  
contracts  
standards  

Modules provide:

business rules  
workflows  
domain behavior  
feature orchestration  

Core defines **how systems work**.

Modules define **what systems do**.

---

# What Core Owns

Core contains reusable platform capabilities such as:

Shared infrastructure services:
- Audit logging
- Notification dispatching
- File storage
- Attachment handling
- Mail dispatching

Architecture standards:
- Layered architecture rules
- Service boundaries
- Repository discipline
- Presentation separation

UI standards:
- Datatable patterns
- Compact datatable toolbars using the shared `.datatable-toolbar` and `.datatable-toolbar-actions` classes, with a visible search field, `More Filters`, `Clear`, and a single primary action button
- Shared reference-data pages owned by Core and surfaced through module-scoped routes when modules need their own navigation entry
- Print workspace patterns
- Frontend entry coordination through a thin global bootstrap plus module-owned JS entry registries
- Module-owned reusable form helpers such as autocomplete plus modal-based resolve flows for document signatories
- Module-owned document workflows such as AIR, RIS, PAR, ICS, PTR, ITR, and WMR staying inside the owning module while reusing shared Core standards
- Module-owned public landings such as `/gso` and authenticated dashboards such as `/gso/dashboard` staying inside the owning module while still rendering through shared platform layouts when needed
- Shared Core capabilities such as Tasks being surfaced through module-scoped routes/controllers when a module needs to preserve its own shell context while still reusing the Core backend

Platform governance:
- Core service rules
- Design principles
- Anti-pattern guidance
- Refactor guidelines

Core must remain:

module agnostic  
domain neutral  
presentation neutral  

---

# What Core Must NOT Contain

Core must not contain:

- business workflows
- module-specific services
- domain wording
- module routes
- feature orchestration
- module UI behavior

Bad example:

TaskNotificationService inside Core.

Good example:

NotificationDispatcher inside Core.
TaskNotificationService inside module.

Core provides engines.
Modules provide scenarios.

---

# How Applications Should Use Core

Applications should:

- follow Core architecture standards
- use Core service contracts
- avoid modifying Core primitives directly
- implement domain behavior in modules
- move reusable infrastructure into Core

If new generic behavior is needed:

Add it to Core first.
Then sync to applications.

See:

docs/CORE_SYNC_OPERATIONS.md

---

# Architecture Documentation

Core engineering standards are defined in:

## Structural Architecture

- docs/ARCHITECTURE.md
- docs/AUTHORIZATION_STANDARD.md

Defines:
- layering model
- Core vs Module boundaries
- service/repository structure
- shared table architecture
- permission-first authorization contract

## Development Conventions

- docs/CONVENTIONS.md

Defines:
- naming rules
- DTO conventions
- repository conventions
- frontend structure
- Definition of Done

## Core Governance

- docs/CORE_SERVICE_RULES.md
- docs/CORE_DESIGN_PRINCIPLES.md
- docs/CORE_ANTI_PATTERNS.md
- docs/CORE_REFACTOR_GUIDELINES.md

Defines:
- Core service boundaries
- design philosophy
- architecture anti-patterns
- safe refactor rules

## Platform Standards

- docs/AUDIT_LOGGING.md
- docs/PRINT_WORKSPACE_STANDARD.md
- docs/CORE_SYNC_OPERATIONS.md

Defines:
- audit policies
- print workspace standards
- Core synchronization workflow

Together these documents form the Core engineering handbook.

---

# When To Add Something To Core

Add logic to Core only if:

- multiple modules can use it
- it contains no domain wording
- it contains no module workflows
- it requires no module branching
- it can be reused without modification

Example:

The shared Accountable Persons reference page belongs in Core because multiple modules can reuse the same master records, datatable behavior, and CRUD rules while each module can still expose it through its own scoped routes and sidebar placement.

If any fail:

It belongs in the module.

---

# Development Philosophy

This Core is designed for:

long-term maintainability  
modular growth  
platform reuse  
engineering discipline  
architecture stability  

Core prioritizes:

clarity over convenience  
structure over shortcuts  
platform health over local optimization  

---

# Intended Usage

This Core supports:

LGU systems  
enterprise internal systems  
document workflow platforms  
modular information systems  

Primary goals:

reusability  
consistency  
maintainability  
scalability  

---

# Guiding Principle

Core provides stability.

Modules provide evolution.

Do not invert this relationship.
