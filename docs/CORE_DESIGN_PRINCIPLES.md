# Core Design Principles

## Purpose

This document defines the design philosophy behind the Webcraft Core System.

ARCHITECTURE.md defines structure.
CORE_SERVICE_RULES.md defines service boundaries.
CORE_REFACTOR_GUIDELINES.md defines refactor timing.

This document defines how architectural decisions should be evaluated.

Use these principles when deciding:

where logic belongs  
when to generalize  
when to split responsibilities  
when to move logic into Core  

These principles protect:

long-term maintainability  
platform clarity  
architectural stability  
module scalability  

---

# How To Use This Document

Use this document when:

you are unsure if something belongs in Core  
you are deciding whether to generalize logic  
you are evaluating architectural tradeoffs  
you are deciding whether to split responsibilities  

This document provides decision heuristics, not implementation rules.

---

# Principle 1: Core Provides Capabilities, Modules Provide Behavior

Core implements reusable capabilities.

Modules implement domain behavior.

Core answers:

HOW something works.

Modules answer:

WHEN something happens.

Detailed boundary rules are defined in:

CORE_SERVICE_RULES.md

---

# Principle 2: Generic Before Specific

If multiple modules could use something, design it generically.

Core should contain engines.

Modules should contain scenarios.

Example:

Core:
NotificationDispatcher

Module:
TaskNotificationService

Do not move domain behavior into Core.

---

# Principle 3: Core Must Be Predictable

Good Core code should feel:

predictable  
stable  
consistent  

Core should avoid:

special cases  
domain assumptions  
feature branching  

Predictability is more valuable than cleverness.

---

# Principle 4: Stability Over Convenience

Do not sacrifice architecture for small convenience.

Prefer:

clean boundaries

over:

shortcuts.

Convenience creates debt.
Structure creates longevity.

---

# Principle 5: One Responsibility Per Class

Each class should have:

one purpose  
one architectural layer  
one reason to change  

If a class answers multiple concerns:

it likely needs splitting.

Refactor mechanics are defined in:

CORE_REFACTOR_GUIDELINES.md

---

# Principle 6: Favor Composition Over Expansion

Avoid growing large services.

Prefer:

multiple focused classes

over:

one expanding class.

Good supporting roles include:

Builder  
Resolver  
Dispatcher  
Provider  

Refactor triggers are defined in:

CORE_REFACTOR_GUIDELINES.md

---

# Principle 7: Contracts Before Implementation

Define structure before behavior.

Prefer:

clear interfaces  
DTOs  
explicit payload contracts  

before complex logic.

Detailed DTO usage guidance lives in:

CONVENTIONS.md

Contracts stabilize architecture.

---

# Principle 8: Infrastructure Must Not Know Business

Infrastructure should not know:

tasks  
inspections  
RIS  
DTS  
inventory  

Infrastructure should know:

notification  
audit  
storage  
dispatch  

If infrastructure references domain workflows:

architecture is drifting.

Boundary rules live in:

CORE_SERVICE_RULES.md

---

# Principle 9: Presentation Is Not Data Access

Repositories answer:

How do we get data?

Presentation answers:

How should it appear?

Do not mix these responsibilities.

Structural separation is defined in:

ARCHITECTURE.md

---

# Principle 10: Avoid God Services

Large services create fragility.

When responsibilities grow:

split them.

Refactor timing guidance is defined in:

CORE_REFACTOR_GUIDELINES.md

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

If Core needs module knowledge:

logic belongs in the module.

Detailed rules:

CORE_SERVICE_RULES.md

---

# Principle 13: Optimize For Future Modules

Core is built for systems not yet written.

Design decisions should consider:

future LGU systems  
future workflow modules  
future platform expansion  

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

Prefer:

explicit naming  
explicit contracts  
explicit flows  

Explicit systems scale better.

---

# Principle 15: Consistency Over Perfection

Follow existing patterns unless improvement is clear.

Consistency reduces:

bugs  
cognitive load  
maintenance cost  

Architecture should evolve deliberately.

---

# Principle 16: Refactor Toward Simplicity

Refactor toward:

clearer boundaries  
simpler responsibilities  
better separation  

Avoid refactoring toward:

unnecessary abstraction  
pattern obsession  

Goal:

clarity.

Refactor timing rules:

CORE_REFACTOR_GUIDELINES.md

---

# Principle 17: Core Evolves Carefully

Core changes affect multiple modules.

Prefer:

additive evolution  
safe transitions  
documented changes  

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

# Principle 20: Architecture Is A Long-Term Asset

Architecture is not overhead.

It prevents:

future bugs  
expensive refactors  
feature friction  
engineering confusion  

Every clean boundary today prevents problems later.

---

# Decision Rule: Should This Go In Core?

Add to Core only if:

multiple modules can use it  
no domain wording exists  
no module workflows exist  
no module branching required  
reusable without modification  

Otherwise:

it belongs in the module.

---

# Final Guiding Rule

Core provides:

capabilities.

Modules provide:

behavior.

Keep this separation and the platform will scale cleanly.