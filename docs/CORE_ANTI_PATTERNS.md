# Core Anti-Patterns

## Purpose

This document defines architectural anti-patterns that must be avoided when developing the Core System.

These patterns usually appear gradually as systems grow and are a primary cause of:

technical debt  
architecture drift  
fragile services  
unclear responsibilities  
platform instability  

This document acts as an early warning system.

If code starts resembling these patterns, review architecture boundaries.

ARCHITECTURE.md defines structure.
CORE_SERVICE_RULES.md defines boundaries.
CORE_REFACTOR_GUIDELINES.md defines how to refactor safely.

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

inside Core services.

## Why This Is Dangerous

Breaks usage in:

queues  
console commands  
background jobs  

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

Shared tables lacking module context.

Examples:

notifications without module_name.

## Why This Is Dangerous

Cross-module filtering becomes difficult.

Reporting becomes harder.

## Correct Direction

Shared tables should include module identification.

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

## Why This Matters

These are refactor signals.

Ignoring them creates debt.

## Correct Direction

Refactor early when responsibilities blur.

Refactor triggers:

CORE_REFACTOR_GUIDELINES.md

---

# Common Warning Signals

Architecture may be drifting if:

Core references modules  
repositories format UI  
services contain presentation  
generic payloads unclear  
shared tables lack module context  

If these appear:

review boundaries.

---

# Final Principle

Core must remain:

generic  
stable  
predictable  

Modules must remain:

flexible  
domain aware  
feature driven  

Never invert this relationship.