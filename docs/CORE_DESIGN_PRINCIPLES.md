# Core Design Principles

## Purpose

This document defines the design philosophy behind the Webcraft Core System.

ARCHITECTURE.md defines structure.
CORE_SERVICE_RULES.md defines service boundaries.
CORE_REFACTOR_GUIDELINES.md defines refactor timing.
CONVENTIONS.md defines implementation patterns.

This document defines how architectural decisions should be evaluated.

Use these principles when deciding:

where logic belongs
when to generalize
when to split responsibilities
when to move logic into Core
when to introduce module/department scope

These principles protect:

long‑term maintainability
platform clarity
architectural stability
module scalability
platform isolation

---

# How To Use This Document

Use this document when:

you are unsure if something belongs in Core
you are deciding whether to generalize logic
you are evaluating architectural tradeoffs
you are deciding whether to split responsibilities
you are introducing shared platform identity concepts

This document provides decision heuristics, not implementation rules.

---

# Principle 1: Core Provides Capabilities, Modules Provide Behavior

Core implements reusable capabilities.

Modules implement domain behavior.

Core answers:

HOW something works.

Modules answer:

WHEN something happens.

Shared platform capabilities live in Core when they are reusable across modules but are not platform governance tools.

Example:

Core shared capability:
Tasks engine

Business module behavior:
GSO deciding when an AIR inspection should create or update a task

---

# Principle 2: Generic Before Specific

If multiple modules could use something, design it generically.

Core should contain engines.

Modules should contain scenarios.

Example:

Core:
NotificationDispatcher

Core shared capability:
TaskNotificationService

Module:
InspectionTaskDispatchService deciding when a module workflow should create work

Do not move domain behavior into Core.

---

# Principle 3: Core Must Be Predictable

Good Core code should feel:

predictable
stable
consistent
context‑aware

Core should avoid:

special cases
domain assumptions
feature branching
hidden context resolution

Predictability is more valuable than cleverness.

---

# Principle 4: Stability Over Convenience

Do not sacrifice architecture for small convenience.

Prefer:

clean boundaries

Over:

shortcuts.

Convenience creates debt.
Structure creates longevity.

---

# Principle 5: One Responsibility Per Class

Each class should have:

one purpose
one architectural role
one reason to change

If a class answers multiple concerns:

it likely needs splitting.

---

# Principle 6: Favor Composition Over Expansion

Avoid growing large services.

Prefer:

multiple focused classes

Over:

one expanding class.

Typical supporting roles:

Builder → data shaping
Resolver → contextual lookup
Dispatcher → delivery
Provider → data aggregation
Data → structured contracts

---

# Principle 7: Contracts Before Implementation

Define structure before behavior.

Prefer:

clear interfaces
DTOs
explicit payload contracts
explicit context contracts

before complex logic.

Contracts stabilize architecture.

---

# Principle 8: Infrastructure Must Not Know Business

Infrastructure should not know:

tasks
inspections
RIS
DTS
inventory
module workflows

Infrastructure should know:

notification
audit
storage
dispatch
context resolution
module isolation mechanics

If infrastructure references domain workflows:

architecture is drifting.

---

# Principle 9: Presentation Is Not Data Access

Repositories answer:

How do we get data?

Builders/Presenters answer:

How should it appear?

Never mix these responsibilities.

---

# Principle 10: Avoid God Services

Large services create fragility.

When responsibilities grow:

split them.

Preferred extraction targets:

Builders
Resolvers
Providers
Data objects
Supporting services

---

# Principle 11: Prefer Composition Over Accumulation

Do not continuously add methods to existing services.

Prefer adding supporting classes.

Composition keeps Core flexible.

---

# Principle 12: Core Must Remain Module Agnostic

Core should not know:

module workflows
module wording
module UI
module‑specific branching

If Core needs module knowledge:

logic belongs in the module.

---

# Principle 13: Optimize For Future Modules

Core is built for systems not yet written.

Design decisions should consider:

future LGU systems
future workflow modules
future platform expansion
future integrations

Ask:

Would another module use this?

If yes:

Core candidate.

---

# Principle 14: Prefer Explicit Over Magical

Avoid:

hidden behavior
implicit assumptions
magic payloads
automatic module guessing
implicit department resolution

Prefer:

explicit naming
explicit contracts
explicit flows
explicit relational scope

Explicit systems scale better.

---

# Principle 15: Consistency Over Perfection

Follow existing patterns unless improvement is clear.

Consistency reduces:

bugs
cognitive load
maintenance cost
architecture fragmentation

Architecture should evolve deliberately.

---

# Principle 16: Refactor Toward Simplicity

Refactor toward:

clearer boundaries
simpler responsibilities
better separation
better context isolation

Avoid refactoring toward:

unnecessary abstraction
pattern obsession
overengineering

Goal:

clarity.

---

# Principle 17: Core Evolves Carefully

Core changes affect multiple modules.

Prefer:

additive evolution
safe transitions
documented changes
deterministic identity patterns

Core stability protects platform stability.

---

# Principle 18: Modules Can Be More Flexible Than Core

Core must be strict.

Modules can be pragmatic.

Why:

Core affects everything.
Modules affect themselves.

Keep strict discipline in Core.

---

# Principle 19: Platform Health Over Local Optimization

Sometimes a change helps one module but harms the platform.

Prefer:

platform health.

Do not weaken Core for one module.

---

# Principle 20: Architecture Is A Long‑Term Asset

Architecture is not overhead.

It prevents:

future bugs
expensive refactors
feature friction
engineering confusion
platform instability

Every clean boundary today prevents problems later.

---

# Principle 21: Context Before Convenience

When designing shared functionality:

Always consider module and department context first.

Context defines correctness.

Convenience defines shortcuts.

Correctness must win.

---

# Principle 22: Shared Identity Must Be Separated From Access

Users represent identity.

user_modules represent access.

Never merge these concepts.

Identity answers:

Who is this person?

Access answers:

Where can they operate?

Mixing these concepts causes architecture leakage.

---

# Principle 23: Deterministic Identity Over Random Structure

System‑owned records should prefer stable identity when needed.

Examples:

system modules
default departments
platform roles

Deterministic identity improves:

migrations
seeding
integration safety
debugging

---

# Principle 24: Role Separation Is Architectural Separation

Core structure should separate by architectural role first:

Repositories
Services
Builders
Data
Support
Infrastructure

Inside each role:

organize by concern.

This improves:

traceability
scalability
maintainability

---

# Principle 25: Builders Exist To Protect Boundaries

Builders exist to prevent repositories and services from accumulating shaping logic.

If a class starts:

repeating payload arrays
building datatable rows
building action payloads
formatting display structures

Extract a Builder.

Builders protect architecture purity.

---

# Principle 26: Contracts Should Mirror Structure

Contracts should mirror implementation structure.

Example:

Services/Contracts/Access/X
Services/Access/X

Builders/Contracts/User/X
Builders/User/X

This improves:

readability
traceability
refactor safety

---

# Decision Rule: Should This Go In Core?

Add to Core only if:

multiple modules can use it
no domain wording exists
no module workflows exist
no module branching required
reusable without modification
independent from one department or workflow

Otherwise:

it belongs in the module.

---

# Final Guiding Rule

Core provides:

capabilities.

Modules provide:

behavior.

Keep this separation and the platform will scale cleanly.
