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

---

# Anti-Pattern 1: The God Service

## Description

A service accumulating too many responsibilities.

Typical symptoms:

many unrelated methods
growing constructor dependencies
module-specific logic inside Core
mixed infrastructure and business logic
payload shaping inside services
route generation inside services

## Correct Direction

Split responsibilities into focused classes.

Typical splits:

Builder → structure
Resolver → context
Provider → data
Dispatcher → transport
AccessService → access rules

---

# Anti-Pattern 2: Business Logic Inside Core

## Description

When Core services begin knowing domain workflows.

Example:

notifyInspectionSubmitted()

inside Core.

## Correct Direction

Core provides capability.
Modules provide scenarios.

---

# Anti-Pattern 3: Presentation Logic Inside Repositories

## Description

Repositories performing UI shaping.

Examples:

row mapping
formatted dates
UI flags
display labels
action URL generation
datatable structures

## Correct Direction

Repositories return data.
Builders shape structures.

---

# Anti-Pattern 4: Generic Methods Becoming Dumping Grounds

## Description

Flexible methods accumulating unrelated use cases.

Symptoms:

inconsistent payloads
undocumented keys
mixed responsibilities
context keys added without contract discipline

## Correct Direction

Maintain clear contracts.

If method grows:

split into:

Builder
DTO
Service

---

# Anti-Pattern 5: Core Generating Module URLs

## Description

Core referencing module routes.

Example:

route('tasks.show')

inside shared service or repository.

## Correct Direction

Modules or UI Builders generate URLs.
Core receives URLs as data only.

---

# Anti-Pattern 6: Vague Method Contracts

## Description

Methods using loose types.

Examples:

object $entity
mixed $payload
array $contextWithoutDefinition

## Correct Direction

Prefer explicit contracts:

DTOs
Interfaces
Explicit parameters

---

# Anti-Pattern 7: Core Assuming HTTP Context

## Description

Core reading request context directly.

Examples:

auth()->user()
Request::user()
Request::ip()

## Correct Direction

Pass context explicitly or resolve via resolver.

---

# Anti-Pattern 8: Mixed Layer Classes

## Description

Classes operating across architectural layers.

Examples:

service doing presentation
repository doing UI mapping
controller doing business logic
resolver doing orchestration
builder running workflows

## Correct Direction

Each class should belong to one layer.

---

# Anti-Pattern 9: Overly Clever Infrastructure

## Description

Infrastructure trying to be too smart.

Examples:

hidden branching
implicit behavior
magic detection
automatic module guessing

## Correct Direction

Prefer explicit contracts and flows.

---

# Anti-Pattern 10: Core Modified For One Module

## Description

Changing Core to simplify one module.

Examples:

module-specific branching
feature-specific conditions
DTS-only assumptions
GSO-only storage logic

## Correct Direction

If only one module needs it:

it belongs in the module.

---

# Anti-Pattern 11: Unstructured Shared Tables

## Description

Shared tables lacking relational context.

Examples:

notifications without module_id
tasks without module_id
audit_logs without module_id
shared records relying on module_name

## Correct Direction

Shared tables should include:

module_id
department_id when applicable

---

# Anti-Pattern 12: Expanding Instead Of Composing

## Description

Continuously adding methods to services instead of splitting.

## Correct Direction

Prefer composition.

Extract:

Builders
Resolvers
Providers
Data objects

---

# Anti-Pattern 13: Premature Abstraction

## Description

Creating abstractions before reuse exists.

## Correct Direction

Abstract when patterns stabilize.

---

# Anti-Pattern 14: Architecture Drift

## Description

Small violations accumulating over time.

Examples:

small UI logic in repo
small domain logic in Core
small shortcuts
one-off module hacks
repeated direct config lookups

## Correct Direction

Fix boundary violations early.

---

# Anti-Pattern 15: Ignoring Warning Signs

## Description

Ignoring early architecture signals.

Examples:

service growing rapidly
mixed responsibilities
unclear contracts
repeated context lookup logic

## Correct Direction

Refactor early when responsibilities blur.

---

# Anti-Pattern 16: Direct env() Usage In Business Code

## Description

Reading environment values directly in services, repositories, or domain workflows.

## Correct Direction

Use:

config()
CurrentContext

Never use env() outside config.

---

# Anti-Pattern 17: Repeated Ad Hoc Context Resolution

## Description

Multiple classes resolving module or department independently.

## Correct Direction

Use centralized resolver:

CurrentContext

---

# Anti-Pattern 18: Shared Users Without Module Access Boundaries

## Description

Assuming users automatically access every module.

## Correct Direction

Separate:

users → identity
user_modules → access

---

# Anti-Pattern 19: Treating Default Department As Access Restriction

## Description

Using default department as full access boundary.

## Correct Direction

Default department is context only.

Access must come from relational data.

---

# Anti-Pattern 20: Long Auto-Generated Composite Index Names

## Description

Letting Laravel auto-generate long composite index names.

## Correct Direction

Manually name indexes.

---

# Anti-Pattern 21: Builders Inside Service Folders

## Description

Placing builders inside Services folders instead of a dedicated Builders role.

Example bad structure:

Services/User/UserDatatableBuilder

## Why This Is Dangerous

Blurs architectural roles.
Makes responsibilities unclear.
Encourages service growth.

## Correct Direction

Builders belong in:

app/Builders

Organized by concern.

---

# Anti-Pattern 22: Flat Contracts With Concern-Based Implementations

## Description

Contracts stored flat while implementations are concern-based.

Example bad:

Services/Contracts/ModuleAccessServiceInterface
Services/Access/ModuleAccessService

## Correct Direction

Mirror structure:

Services/Contracts/Access/X
Services/Access/X

Builders/Contracts/User/X
Builders/User/X

---

# Anti-Pattern 23: Services Doing Structure Work

## Description

Services constructing rows, payload arrays, or action structures.

Examples:

building datatable rows
building action URLs
building display arrays
repeating payload structures

## Correct Direction

Extract Builders.

Services orchestrate.
Builders construct.

---

# Common Warning Signals

Architecture may be drifting if:

Core references modules directly
repositories format UI
services contain presentation
services shape payloads
builders appear inside services
contracts not mirrored
shared tables lack relational module context
code uses env() outside config
multiple classes resolve context differently

If these appear:

review boundaries.

---

# Final Principle

Core must remain:

generic
stable
predictable
context-aware
module-agnostic

Modules must remain:

flexible
domain aware
feature driven

Never invert this relationship.
