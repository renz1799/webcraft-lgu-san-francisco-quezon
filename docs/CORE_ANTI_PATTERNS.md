# Core Anti-Patterns

## Purpose

This document defines architectural anti-patterns that must be avoided when developing the Core System.

These patterns usually appear gradually as systems grow and are a primary cause of:

technical debt
architecture drift
fragile services
unclear responsibilities
platform instability
module leakage
context inconsistency

This document acts as an early warning system.

If code starts resembling these patterns, review architecture boundaries.

ARCHITECTURE.md defines structure.
CORE_SERVICE_RULES.md defines boundaries.
CORE_REFACTOR_GUIDELINES.md defines how to refactor safely.
CONVENTIONS.md defines implementation discipline.

This document identifies what to watch for.

---

# Anti-Pattern 1: The God Service

## Description

A service accumulating too many responsibilities.

Typical symptoms:

many unrelated methods
growing constructor dependencies
module-specific logic inside Core
mixed infrastructure and business logic
module access logic mixed with unrelated workflows

## Why This Is Dangerous

God services become:

hard to test
hard to modify
hard to reason about

They also attract unrelated logic.

## Correct Direction

Split responsibilities into focused classes.

Refactor guidance:

CORE_REFACTOR_GUIDELINES.md

---

# Anti-Pattern 2: Business Logic Inside Core

## Description

When Core services begin knowing domain workflows.

Example:

notifyInspectionSubmitted()

inside Core.

## Why This Is Dangerous

Core becomes:

module aware
hard to reuse
hard to scale

## Correct Direction

Core provides capability.
Modules provide scenarios.

Boundary rules:

CORE_SERVICE_RULES.md

---

# Anti-Pattern 3: Presentation Logic Inside Repositories

## Description

Repositories performing UI shaping.

Examples:

row mapping
display labels
formatted dates
UI flags
module badge labels
department display strings

## Why This Is Dangerous

Repositories become:

UI dependent
hard to reuse
tied to one output format

## Correct Direction

Repositories return data.
Presentation handles display.

Structural rules:

ARCHITECTURE.md

---

# Anti-Pattern 4: Generic Methods Becoming Dumping Grounds

## Description

Flexible methods accumulating unrelated use cases.

Symptoms:

inconsistent payloads
undocumented keys
mixed responsibilities
context keys added without contract discipline

## Why This Is Dangerous

Generic methods become:

unpredictable
fragile
hard to maintain

## Correct Direction

Maintain clear contracts.

Payload discipline:

CONVENTIONS.md

Refactor timing:

CORE_REFACTOR_GUIDELINES.md

---

# Anti-Pattern 5: Core Generating Module URLs

## Description

Core referencing module routes.

Example:

route('tasks.show')

inside shared service.

## Why This Is Dangerous

Creates coupling between:

infrastructure
presentation
module-specific route assumptions

## Correct Direction

Modules generate URLs.
Core receives URLs as data.

Boundary rules:

CORE_SERVICE_RULES.md

---

# Anti-Pattern 6: Vague Method Contracts

## Description

Methods using loose types.

Examples:

object $entity
mixed $payload
array $contextWithoutDefinition

## Why This Is Dangerous

Weak contracts cause:

unclear expectations
maintenance friction
runtime surprises

## Correct Direction

Prefer explicit contracts.

DTO guidance:

CONVENTIONS.md

---

# Anti-Pattern 7: Core Assuming HTTP Context

## Description

Core reading request context directly.

Examples:

Request::user()
Request::ip()
auth()->user() inside shared infrastructure logic

## Why This Is Dangerous

Breaks usage in:

queues
console commands
background jobs
seeders

## Correct Direction

Pass context explicitly or resolve via resolver.

Service boundary rules:

CORE_SERVICE_RULES.md

---

# Anti-Pattern 8: Mixed Layer Classes

## Description

Classes operating across architectural layers.

Examples:

service doing presentation
repository doing UI mapping
controller doing business logic
resolver doing orchestration

## Why This Is Dangerous

Creates:

unclear responsibilities
tight coupling
hard debugging

## Correct Direction

Each class should belong to one layer.

Layer definitions:

ARCHITECTURE.md

---

# Anti-Pattern 9: Overly Clever Infrastructure

## Description

Infrastructure trying to be too smart.

Examples:

hidden branching
implicit behavior
magic detection
automatic module guessing without explicit rules

## Why This Is Dangerous

Smart infrastructure becomes:

fragile
hard to debug
hard to predict

## Correct Direction

Prefer explicit contracts and flows.

Design principles:

CORE_DESIGN_PRINCIPLES.md

---

# Anti-Pattern 10: Core Modified For One Module

## Description

Changing Core to simplify one module.

Examples:

module-specific branching
feature-specific conditions
DTS-only assumptions in shared services
GSO-specific storage decisions in shared services

## Why This Is Dangerous

Creates:

Core drift
architecture pollution

## Correct Direction

If only one module needs it:

it belongs in the module.

Core eligibility rules:

CORE_SERVICE_RULES.md

---

# Anti-Pattern 11: Unstructured Shared Tables

## Description

Shared tables lacking relational context.

Examples:

notifications without module_id
tasks without module_id
audit_logs without module_id
shared records relying only on module_name strings

## Why This Is Dangerous

Cross-module filtering becomes difficult.
Reporting becomes harder.
Joins become weaker.
Module isolation becomes fragile.

## Correct Direction

Shared tables should include relational module identification.

Prefer:

module_id
department_id when applicable

Do not rely on string names as the primary relational identity.

Structural rules:

ARCHITECTURE.md

---

# Anti-Pattern 12: Expanding Instead Of Composing

## Description

Continuously adding methods to services instead of splitting.

## Why This Is Dangerous

Large services become:

fragile
slow to modify
hard to reason about

## Correct Direction

Prefer composition.

Refactor triggers:

CORE_REFACTOR_GUIDELINES.md

---

# Anti-Pattern 13: Premature Abstraction

## Description

Creating abstractions before reuse exists.

## Why This Is Dangerous

Creates:

unnecessary complexity
maintenance overhead

## Correct Direction

Abstract when patterns stabilize.

Design guidance:

CORE_DESIGN_PRINCIPLES.md

---

# Anti-Pattern 14: Architecture Drift

## Description

Small violations accumulating over time.

Examples:

small UI logic in repo
small domain logic in Core
small shortcuts
one-off module hacks
repeated direct config lookups instead of a resolver

## Why This Is Dangerous

Small violations accumulate into major refactors.

## Correct Direction

Fix boundary violations early.

Boundary definitions:

ARCHITECTURE.md
CORE_SERVICE_RULES.md

---

# Anti-Pattern 15: Ignoring Warning Signs

## Description

Ignoring early architecture signals.

Examples:

service growing rapidly
mixed responsibilities
unclear contracts
repeated module lookup logic appearing in many classes

## Why This Matters

These are refactor signals.

Ignoring them creates debt.

## Correct Direction

Refactor early when responsibilities blur.

Refactor triggers:

CORE_REFACTOR_GUIDELINES.md

---

# Anti-Pattern 16: Direct env() Usage In Business Code

## Description

Reading environment values directly in services, repositories, models, or domain workflows.

Examples:

env('APP_MODULE_ID')
env('APP_DEFAULT_DEPARTMENT_ID')

## Why This Is Dangerous

Creates:

config caching problems
hard-to-test code
inconsistent platform context resolution
leaky runtime assumptions

## Correct Direction

Use:

config() inside configuration-aware infrastructure
CurrentContext for runtime platform resolution

Never use env() directly outside config files.

Conventions:

CONVENTIONS.md

---

# Anti-Pattern 17: Repeated Ad Hoc Context Resolution

## Description

Multiple classes resolving current module or default department independently.

Examples:

Module::find(config('module.id'))
Department::first()
manual fallback chains repeated across seeders and services

## Why This Is Dangerous

Creates:

drift
inconsistent defaults
unpredictable behavior
duplicate logic

## Correct Direction

Use a dedicated runtime resolver such as:

CurrentContext

This keeps module and default department resolution centralized.

Structural rules:

ARCHITECTURE.md
CONVENTIONS.md

---

# Anti-Pattern 18: Shared Users Without Module Access Boundaries

## Description

Assuming that a user in `users` can automatically access every module website in the shared database.

## Why This Is Dangerous

Creates:

cross-module login leakage
weak isolation
security ambiguity
incorrect assumptions about user scope

## Correct Direction

Shared identity and module access must be separate.

Use:

users for shared identity
user_modules for access mapping

Architecture rules:

ARCHITECTURE.md

---

# Anti-Pattern 19: Treating Default Department As Access Restriction

## Description

Using the configured default department as if it defines all department access for the application.

## Why This Is Dangerous

Confuses:

runtime default context
actual user access scope
record ownership scope

This is especially dangerous in a platform-style CORE site that manages multiple departments.

## Correct Direction

Treat configured department as:

default context only

Actual access must come from relational data such as:

user_modules.department_id
record-level department_id

---

# Anti-Pattern 20: Long Auto-Generated Composite Index Names

## Description

Letting Laravel auto-generate long composite index names for module/department scoped tables.

## Why This Is Dangerous

Creates:

migration failures in MySQL
index name length errors
fragile schema evolution

## Correct Direction

Manually name long composite indexes.

Example:

notif_module_dept_user_created_idx

db conventions:

CONVENTIONS.md

---

# Common Warning Signals

Architecture may be drifting if:

Core references modules directly in workflow logic
repositories format UI
services contain presentation
generic payloads are unclear
shared tables lack relational module context
code uses env() outside config files
multiple classes resolve current module differently
users table is treated as module access control
default department is treated as hard access boundary

If these appear:

review boundaries.

---

# Final Principle

Core must remain:

generic
stable
predictable
context-aware
module-agnostic in behavior

Modules must remain:

flexible
domain aware
feature driven

Never invert this relationship.
