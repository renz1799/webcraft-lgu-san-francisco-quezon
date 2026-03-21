# CORE PLATFORM RULES (Core System)

## Purpose

This document defines how the Core System operates as a **multi‑module platform** rather than a single application.

ARCHITECTURE.md defines structure.
CORE_SERVICE_RULES.md defines Core responsibilities.
CORE_DESIGN_PRINCIPLES.md defines philosophy.
CORE_MODULE_STRUCTURE_MAP.md defines the concrete target placement map.

This document defines how Core behaves as a **platform kernel**.

It explains:

how multiple modules coexist
how isolation is enforced
how shared data is managed
how context is resolved
how platform growth remains safe

---

# Platform Definition

Core System is not a module.

Core is the **platform layer**.

Modules are the applications.

Example:

Core → platform
DTS → module
GSO → module
HR → future module
Accounting → future module

Core provides:

identity
access
shared storage
integrations
infrastructure
standards

Modules provide:

workflows
business rules
UI behavior
process logic

Never reverse this relationship.

---

# Platform Boundary Rule (Core vs Modules)

Core is the platform foundation.

Modules are business applications running on the platform.

This boundary must remain explicit in both code placement and dependency direction.

## Core Definition

Core contains platform capabilities that are reusable across multiple modules and are not owned by any single business feature.

Core components must be:

module agnostic
scenario agnostic
reusable across multiple modules
independent of business workflows
stable platform infrastructure

Examples of Core concerns:

audit logging
notification transport
authentication and access control
CurrentContext
print infrastructure
storage integrations
shared repositories
shared builders and providers
platform support services

Simple test:

If a module such as DTS, GSO, or Tasks is removed, would this still exist?

If yes:

it belongs in Core.

## Module Definition

Modules contain business applications built on top of Core.

Modules may depend on Core.

Core must never depend on Modules.

Modules contain:

business workflows
domain services
module specific builders
module specific providers
module repositories
policies
business rules
domain terminology

Examples:

DTS
GSO
Tasks
HR
Procurement
Inventory

Simple test:

Does this exist because this module exists?

If yes:

it belongs in the module.

## Dependency Direction Rule

Dependencies must always follow this direction:

Modules -> Core

Never:

Core -> Modules

Core must never reference:

module services
module workflows
module builders
module providers
module terminology

Core should also avoid direct dependency on module owned models unless access is abstracted through shared contracts or platform owned structures.

Violating this rule breaks platform architecture.

## Ownership Rule

Ownership determines placement.

Core owns platform capabilities.

Modules own business logic.

Never mix these responsibilities.

## Folder Structure Standard

Target platform structure should follow this direction:

```text
app/
  Core/
    Builders/
    Services/
    Providers/
    Repositories/
    Support/

  Modules/
    Tasks/
      Builders/
      Services/
      Providers/
      Repositories/
      Policies/

    DTS/
      Builders/
      Services/
      Providers/

    GSO/
      Builders/
      Services/
```

Core is platform.

Modules are applications running on the platform.

## Reusability Rule

If code becomes reusable across modules, move it from Modules to Core.

Example:

TaskNotificationService -> Module concern
NotificationService -> Core concern

## Architecture Drift Warning Signs

Architecture drift is happening if:

Core references a module
Core contains business workflow logic
module logic appears in Core
module wording appears in Core services
Core services start knowing about DTS, GSO, Tasks, or other business modules

If this happens:

refactor immediately.

## Platform Mental Model

Core is the platform engine.

Modules are applications running on the platform.

Core must remain stable even if modules change.

## Golden Rule

When deciding placement always ask:

Is this platform capability or business feature?

Platform capability -> Core
Business feature -> Module

Short version:

Core = platform infrastructure
Modules = business features

Modules may depend on Core.

Core must never depend on Modules.

---

# Platform Architecture Model

The Core platform follows this structure:

Single codebase
Single database
Multiple modules
Multiple websites
Shared platform services

Conceptually:

Platform Layer
→ Core

Application Layer
→ DTS
→ GSO
→ Future modules

Core must remain stable while modules evolve.

---

# Module Isolation Rule

Modules must behave as independent applications even when sharing the same database.

Isolation must be enforced through:

module_id
user_modules access mapping
context resolution
service boundaries

Isolation must prevent:

users accessing modules without assignment
data leaking across modules
module workflows affecting other modules

Isolation is mandatory.

---

# Shared Database Rule

Core uses a shared database.

Shared tables must be platform‑aware.

Required patterns:

module_id when data is module scoped
department_id when data is office scoped
proper indexing for scope filters

Examples of shared operational tables:

audit_logs
notifications
tasks
integration tokens

Platform identity tables:

users
modules
departments
user_modules

Shared DB does NOT mean shared access.

Access must always be enforced.

---

# Identity vs Access Rule

Identity and access must always be separate.

Users represent identity.

user_modules represent access.

Never assume:

user exists → user has module access.

Correct pattern:

User exists
+
User assigned to module
=======================

Access granted

This prevents cross‑module access leaks.

---

# Context Resolution Rule

Every request must operate inside a module context.

Context must not be guessed.

Context should come from:

configuration
route context
CurrentContext

Context must define:

module_id
default_department_id
module_code

Services should not repeatedly resolve this manually.

Use centralized context resolution.

Example concept:

CurrentContext

This prevents inconsistent behavior.

---

# Default Department Rule

Modules may operate with a default department.

This supports:

single office deployments
simple setups
incremental adoption

Default department must be:

configurable
seeded deterministically
resolvable through context

Do not hardcode departments.

Platform default department is fallback only.

It represents platform ownership or base system context.

It must not be treated as the authoritative department for every module workflow or module access grant.

## Module Department Assignment Rule

Module department must be resolved per module.

Use this priority:

explicit department assignment
module-specific default mapping
platform default department fallback

This means:

`users.primary_department_id` = base or home department
`user_modules.department_id` = actual department context for that module access

`user_modules.department_id` is authoritative for module access scope.

Registration must not blindly copy platform default department into module access.

It should resolve the department for the current module first, then fall back only if no module-specific value exists.

---

# Module Independence Rule

Modules must not depend on each other directly.

Bad:

DTS service calling GSO service.

Good:

Modules interact through Core abstractions if needed.

Example:

Audit logging
Notifications
Storage

Modules may share Core capabilities.

Modules must not share workflows.

---

# Cross Module Communication Rule

If modules must interact:

interaction must happen through Core contracts.

Example patterns:

Events
Shared tables
Dispatch services
Integration services

Never allow direct module coupling.

---

# Platform Identity Rule

Platform owned records should use stable identity.

Examples:

Core module
Default departments
Platform roles

Stable identity improves:

seeding
migrations
integration safety
consistency across environments

Avoid random IDs for platform constants.

---

# Integration Isolation Rule

External integrations must support module separation.

Example:

Different Google Drive per module
Different email per module
Different storage per module

Integration storage should support:

module_id
department_id (optional)

Integrations belong to the platform.

Usage belongs to modules.

---

# Platform Service Rule

Core services may manage:

identity
access
context
integrations
shared storage

Core services must not manage:

module workflows
module processes
module UI logic

Platform services enable modules.

They do not behave like modules.

---

# Module Registration Rule

Modules must be registered in the platform.

Recommended module attributes:

id
code
name
is_active

Modules should be seeded.

Modules should not be dynamically invented at runtime without registration.

This keeps the platform predictable.

---

# Platform Growth Rule

Core must always be designed for modules not yet written.

Every change should consider:

future modules
future departments
future integrations
future workflows

Ask:

Will this still work with 5 modules?

If not:

refactor toward platform readiness.

---

# Platform Stability Rule

Core changes impact every module.

Core must evolve carefully.

Prefer:

additive changes
clear migrations
backward compatible refactors
documentation updates

Avoid:

sudden structure changes
breaking contracts
hidden behavior changes

Platform stability protects module stability.

---

# Module Deployment Model

Modules may run as:

separate websites
separate subdomains
separate routes

Examples:

dts.lgu.local
gso.lgu.local

All using:

same Core
same DB
same platform services

This is the intended deployment model.

---

# Platform Security Rule

Security must be enforced at the platform level.

Must enforce:

module access checks
role permissions
user assignments
department scope

Security must not rely on:

UI hiding features.

Security must exist in:

services
policies
access checks

Never trust UI restrictions alone.

---

# Anti Patterns

Never allow:

modules querying other module tables directly
hardcoded module assumptions
skipping module access checks
shared tables without module scope
module workflows inside Core
direct module service coupling

These break platform integrity.

---

# Final Platform Model

Core:

Platform kernel
Shared capabilities
Shared identity
Shared infrastructure

Modules:

Independent applications
Workflows
Domain behavior
UI

Database:

Shared storage
Scoped access
Module isolation

System goal:

One platform
Many LGU systems
Clean isolation
Predictable growth

---

# Final Rule

Core must always behave like a **platform**.

Not like an application.

If Core starts behaving like a module:

architecture is drifting.

If modules start depending on each other:

platform boundaries are breaking.

Protect the platform first.

Everything else depends on it.
