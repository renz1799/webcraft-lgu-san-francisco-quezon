# Core Service Rules

## Purpose

This document defines rules for designing and maintaining Core shared services.

ARCHITECTURE.md defines structural layering.
CONVENTIONS.md defines coding discipline.
CORE_DESIGN_PRINCIPLES.md defines philosophy.

This document defines what Core services may and may not do.

---

# What Qualifies As A Core Service

A service belongs in Core only if:

multiple modules can use it  
it contains no domain workflows  
it contains no module wording  
it requires no module branching  
it can be reused without modification  

If any of these fail:

it belongs in the module.

Core should contain:

capabilities  
infrastructure  
transport  
storage  
dispatching  

Modules should contain:

events  
workflows  
recipients  
business rules  

---

# Core Service Responsibility Rule

Core services should focus on:

dispatching  
storing  
recording  
transporting  
normalizing  

Core services should not:

decide business events  
decide recipients  
define domain workflows  
contain module wording  

Example:

Core:
NotificationDispatcher

Module:
TaskNotificationService deciding event, recipients, and message.

Core provides transport.
Modules provide scenarios.

---

# Transport vs Scenario Separation

Core services must remain scenario-agnostic.

Bad:

NotificationService:
notifyTaskAssigned()

Good:

Core:
NotificationDispatcher::dispatch()

Module:
TaskNotificationService:
builds message
selects recipients
calls dispatcher.

Scenario logic must remain in modules.

---

# Domain Wording Rule

Core services must not contain:

module titles  
domain messages  
feature wording  
UI text  

Bad:

"Inspection Submitted"

Good:

Module builds message.
Core stores or dispatches.

Wording belongs to domain logic.

---

# Module URL Rule

Core services must not generate:

route URLs  
module navigation links  
UI navigation hints  

Bad:

route('tasks.show',$id)

Good:

Module builds URL.
Core receives URL as data.

Core should treat URLs as payload only.

---

# Generic Service Payload Rule

Generic services may accept structured payloads.

Payload must be:

explicit  
predictable  
documented  

Avoid undocumented keys.

If payload complexity grows:

prefer structured DTOs (see CONVENTIONS.md).

Generic services must not become dumping grounds.

---

# Typed Contract Rule

Avoid vague method signatures such as:

object $entity  
mixed $payload  

Prefer:

explicit models  
interfaces  
DTOs  

Clear contracts protect Core stability.

---

# HTTP Context Independence Rule

Core services should not assume:

HTTP request  
session  
controller context  

Because Core must also work in:

queues  
console commands  
scheduled jobs  
background processing  

Context should be:

passed explicitly  
resolved via resolver classes  

Future patterns may include:

AuditContextResolver

Core should remain environment-agnostic.

---

# God Service Prevention Rule

Core services must remain focused.

Warning signs:

too many methods  
unrelated responsibilities  
module knowledge  
growing dependencies  

When a service grows beyond one responsibility:

split it.

Typical split patterns:

Dispatcher → transport  
Builder → structure  
Resolver → context  
Provider → data sourcing  

Refactor timing guidance is defined in:

CORE_REFACTOR_GUIDELINES.md

---

# Module Neutrality Rule

Core must remain neutral.

Core must not know:

tasks  
inspections  
RIS  
DTS  
inventory  

Core may know:

notification  
audit  
storage  
dispatch  

If Core needs domain knowledge:

logic belongs in module.

---

# Framework Coupling Rule

Framework coupling is acceptable in infrastructure layers when intentional.

Examples:

Laravel Eloquent  
Laravel queues  
Laravel mail  

But framework details should not leak into:

domain conventions  
generic service contracts  

Infrastructure may depend on framework.

Core contracts should remain framework-stable.

---

# Final Rule

Core provides:

capabilities.

Modules provide:

behavior.

Never invert this relationship.