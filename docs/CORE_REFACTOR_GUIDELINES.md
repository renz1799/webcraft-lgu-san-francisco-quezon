# Core Refactor Guidelines

## Purpose

This document defines when and how Core code should be refactored.

ARCHITECTURE.md defines structure.
CORE_SERVICE_RULES.md defines Core boundaries.
CORE_DESIGN_PRINCIPLES.md defines design philosophy.
CORE_ANTI_PATTERNS.md defines warning signals.

This document defines how Core should evolve safely.

Use this document when deciding:

when to split classes  
when to extract responsibilities  
when to introduce DTOs  
when to move logic into Core  
when to move logic out of Core  

Refactoring in Core protects:

architectural clarity  
platform stability  
module compatibility  
long-term maintainability  

---

# Refactor Philosophy

Refactor toward:

smaller responsibilities  
clearer boundaries  
stronger contracts  
reusable capabilities  
less module awareness  

Do not refactor toward:

unnecessary abstraction  
pattern-for-pattern's-sake  
speculative complexity  

Goal:

clarity  
stability  
predictability  

---

# Rule 1: Refactor When Responsibilities Start Mixing

Refactor when a class handles more than one architectural concern.

Examples:

service handling orchestration + presentation  
repository handling querying + UI mapping  

Structural boundaries:

ARCHITECTURE.md

Preferred direction:

split responsibilities.

---

# Rule 2: Split Services Before They Become God Services

Refactor when services show growth signals:

many unrelated methods  
growing constructor dependencies  
module knowledge appearing  

Warning patterns:

CORE_ANTI_PATTERNS.md

Preferred direction:

split into focused roles.

Examples:

Dispatcher → transport  
Builder → structure  
Resolver → context  
Provider → data sourcing  

---

# Rule 3: Extract Presentation Logic When It Starts Growing

Refactor presentation logic out of repositories/services when it answers:

"How should this appear?"

Examples:

display labels  
formatted timestamps  
UI flags  
table row shaping  

Structural rules:

ARCHITECTURE.md

Preferred direction:

extract Presenter or Transformer.

---

# Rule 4: Introduce DTOs When Payload Complexity Grows

Use DTO when:

payload has many fields  
payload reused across services  
contracts become unclear  
vague types appear  

DTO usage conventions:

CONVENTIONS.md

DTOs should clarify contracts, not add ceremony.

---

# Rule 5: Move Logic To Core Only After Reuse Is Real

Move logic into Core when:

multiple modules need it  
behavior is generic  
no module branching required  

Core eligibility rules:

CORE_SERVICE_RULES.md

If reuse is theoretical:

keep logic in module first.

---

# Rule 6: Move Logic Out Of Core When Domain Knowledge Appears

If Core starts needing:

module workflows  
domain events  
module recipients  
feature wording  

This is a refactor signal.

Boundary rules:

CORE_SERVICE_RULES.md

Preferred direction:

move scenario logic back to module.

---

# Rule 7: Extract Builders When Structure Logic Grows

Extract Builder when services spend effort constructing structured data.

Examples:

audit display sections  
report payloads  
notification payloads  

Builders focus on structure, not orchestration.

Naming conventions:

CONVENTIONS.md

---

# Rule 8: Extract Resolvers When Context Lookup Repeats

Extract Resolver when environment/context lookup spreads.

Examples:

current actor resolution  
tenant resolution  
request context  

Resolvers answer:

"What context is needed?"

Naming conventions:

CONVENTIONS.md

---

# Rule 9: Extract Providers When Data Sourcing Expands

Introduce Provider when data comes from:

multiple repositories  
aggregated sources  
computed data  

Repositories remain persistence-focused.

Providers source data for use cases.

Structural layering:

ARCHITECTURE.md

---

# Rule 10: Keep Repositories Focused On Data Access

Refactor repository code when UI shaping appears.

Examples:

display labels  
formatted timestamps  
row shaping  

Structural rules:

ARCHITECTURE.md

Preferred direction:

move shaping to presenters.

---

# Rule 11: Refactor Generic Methods When Contracts Become Fuzzy

Refactor generic methods when:

payload shape becomes inconsistent  
undocumented keys appear  
method serves unrelated use cases  

Payload discipline:

CONVENTIONS.md

Preferred direction:

clarify contracts or split responsibilities.

---

# Rule 12: Refactor Around Stable Contracts

Good refactors improve:

type clarity  
interface clarity  
boundary clarity  
class naming  

Refactoring is not just moving code.

Successful refactors improve understanding.

---

# Rule 13: Prefer Additive Refactors In Core

Because Core affects modules, prefer safe transitions.

Preferred workflow:

introduce new abstraction  
migrate usage gradually  
preserve compatibility  
remove legacy path later  

Avoid disruptive rewrites.

---

# Rule 14: Refactor When Testing Becomes Hard For Structural Reasons

Refactor when tests become difficult due to:

mixed responsibilities  
hidden context  
unclear contracts  

Testing friction often signals architecture drift.

---

# Rule 15: Refactor When Naming No Longer Matches Responsibility

Refactor when class names no longer match behavior.

Examples:

Repository formatting UI  
Service building display payloads  

Naming rules:

CONVENTIONS.md

Preferred direction:

rename or split class.

---

# Rule 16: Refactor For Future Module Growth

Ask:

Will this still be clean with more modules?

Refactor toward:

future module compatibility  
platform scalability  

Design philosophy:

CORE_DESIGN_PRINCIPLES.md

---

# Rule 17: Do Not Abstract Too Early

Refactor only when patterns stabilize.

Avoid abstraction when:

only one use case exists  
behavior still evolving  

Design guidance:

CORE_DESIGN_PRINCIPLES.md

---

# Rule 18: Do Not Delay Boundary Refactors

High priority refactor signals:

Core gaining module logic  
repository gaining UI shaping  
generic contracts becoming unclear  

Boundary definitions:

ARCHITECTURE.md  
CORE_SERVICE_RULES.md

These should be addressed early.

---

# Rule 19: Refactor Shared Tables For Platform Readiness

When tables become cross-module:

add module identification  
add indexed filters  

Structural rules:

ARCHITECTURE.md

---

# Rule 20: Refactor Toward Simpler Call Sites

Good Core refactors simplify usage.

Prefer:

clear module payload  
simple Core capability  

Over:

hidden logic in generic services.

---

# Refactor Priority Guide

### High Priority

Refactor when:

Core contains module logic  
repositories contain UI shaping  
generic contracts unclear  

### Medium Priority

Refactor when:

payload complexity increases  
context lookup repeats  
display shaping grows  

### Low Priority

Refactor when:

code is acceptable but could be cleaner  
pattern still emerging  

Do not delay boundary refactors.

---

# Safe Refactor Workflow

When refactoring Core:

1 Identify responsibility drift  
2 Define new boundary  
3 Introduce new class  
4 Migrate callers gradually  
5 Remove old path  
6 Update documentation  

Refactor should strengthen clarity, not just move code.

---

# Final Rule

Refactor Core toward:

smaller  
clearer  
more explicit  
more stable  

Not toward:

more complex  
more abstract  
more clever  

The best Core refactors reduce confusion and improve reuse.