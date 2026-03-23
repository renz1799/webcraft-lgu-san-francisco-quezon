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
it operates correctly regardless of department context

If any of these fail:

it belongs in the module.

Core should contain:

capabilities
infrastructure
transport
storage
dispatching
runtime context resolution
module isolation support
shared identity services
shared integration storage

Modules should contain:

events
workflows
recipients
business rules
module policies

---

# Core Service Responsibility Rule

Core services should focus on:

dispatching
storing
recording
transporting
normalizing
resolving platform context
enforcing shared structure
coordinating shared access rules

Core services should not:

decide business events
decide recipients
define domain workflows
contain module wording
contain department workflow assumptions
shape UI rows
format display payloads
generate action URLs

Example:

Core:
NotificationDispatcher

Core shared capability:
TaskNotificationService deciding task-related message payloads and recipients.

Business module:
InspectionTaskDispatchService deciding when a GSO workflow should create or escalate work.

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
department-specific messaging

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
module route assumptions

Bad:

route('tasks.show',$id)

Good:

UI-facing Builder or shared capability layer builds URL.
Core receives URL as data when needed.

Core should treat URLs as payload only.

## Shared Capability Rule

Some services belong in Core even though they are user-facing, because they provide cross-cutting platform capabilities rather than governance.

Examples:

Tasks
Notifications

These services may remain under Core if they are:

used by multiple modules
not a business domain of their own
module-aware through relational ownership such as `module_id`

When this happens, business modules should decide when to invoke the capability, while Core-owned services provide the engine.

---

# Service vs Builder Rule

Services coordinate workflows.

Builders shape structures.

Services may:

coordinate repositories
call builders
call dispatchers
call resolvers
call providers
coordinate access logic

Services must not become structure-shaping classes.

If a service starts repeating:

payload arrays
row arrays
action arrays
display formatting

Extract a Builder.

---

# Builder Support Rule

Builders are first-class support classes for services.

Builders may be used for:

payload construction
response shaping
row shaping
action shaping
option shaping

Builders must not:

query persistence
run workflows
perform authorization
call external services
resolve application state on their own

Builders support services.
They do not replace services.

---

# Generic Service Payload Rule

Generic services may accept structured payloads.

Payload must be:

explicit
predictable
documented
context-aware when required

Avoid undocumented keys.

If payload complexity grows:

prefer structured DTOs or Builders.

Generic services must not become dumping grounds.

---

# Typed Contract Rule

Avoid vague method signatures such as:

object $entity
mixed $payload
array $contextWithoutDefinition

Prefer:

explicit models
interfaces
DTOs
explicit context parameters

Clear contracts protect Core stability.

---

# HTTP Context Independence Rule

Core services should not assume:

HTTP request
session
controller context
web-only execution

Because Core must also work in:

queues
console commands
scheduled jobs
background processing
seeders

Context should be:

passed explicitly
resolved via resolver classes

Recommended pattern:

CurrentContext

Core should remain environment-agnostic.

---

# Context Resolution Rule

Core services should not independently resolve module or department context repeatedly.

Bad:

Module::find(config('module.id'))
Department::first()
manual env reads

Good:

Use a centralized resolver:

CurrentContext

Example:

$context = app(CurrentContext::class);
$moduleId = $context->moduleId();
$departmentId = $context->defaultDepartmentId();

This prevents context drift and duplicated lookup logic.

---

# God Service Prevention Rule

Core services must remain focused.

Warning signs:

too many methods
unrelated responsibilities
module knowledge
growing dependencies
context resolution mixed with workflows
payload shaping mixed with orchestration

When a service grows beyond one responsibility:

split it.

Typical split patterns:

Dispatcher → transport
Builder → structure
Resolver → context
Provider → data sourcing
AccessService → module access logic
Data → structured contracts

---

# Module Neutrality Rule

Core must remain neutral.

Core must not know:

tasks
inspections
RIS
DTS workflows
inventory workflows
module UI decisions

Core may know:

notification
audit
storage
dispatch
context resolution
module access mapping
department structure

If Core needs domain knowledge:

logic belongs in module.

---

# Shared Identity Rule

Core may manage shared identity concepts such as:

users
modules
departments
user_modules
integration tokens

Core may define structure.

Modules define behavior using that structure.

Example:

Core:
ModuleAccessService

Module:
DtsUserAssignmentService

---

# Relational Scope Rule

Core services that persist shared operational data should support relational scope when applicable.

Examples:

module_id
department_id

Examples of affected services:

Audit logging
Notifications
Tasks
Integration storage

Core should support scope.

Modules decide how scope is used.

---

# Framework Coupling Rule

Framework coupling is acceptable in infrastructure layers when intentional.

Examples:

Laravel Eloquent
Laravel queues
Laravel mail
Laravel events

But framework details should not leak into:

domain conventions
generic service contracts
module boundaries

Infrastructure may depend on framework.

Core contracts should remain framework-stable.

---

# Integration Service Rule

Core may contain services managing external integrations when they are reusable.

Examples:

Google token storage
Drive integration support
Mail providers
SMS providers

Core should manage:

credential storage
connection lifecycle
provider abstraction

Modules should manage:

how integrations are used in workflows.

---

# Concern Mirroring Rule

Service contracts should mirror service implementations by concern.

Correct:

App\Services\Contracts\Access\ModuleAccessServiceInterface
App\Services\Access\ModuleAccessService

App\Services\Contracts\Audit\AuditLogServiceInterface
App\Services\Audit\AuditLogService

Avoid flat contract namespaces when implementations are concern based.

Mirrored structure improves:

traceability
maintainability
refactor safety

---

# Final Rule

Core provides:

capabilities.

Modules provide:

behavior.

Never invert this relationship.
