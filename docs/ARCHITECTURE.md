# Architecture

## Purpose

The Webcraft Core System uses a layered architecture to separate:

HTTP concerns  
business orchestration  
data access  
presentation behavior  
infrastructure capabilities  

This architecture is intentionally more structured than plain MVC to support platform reuse across multiple systems.

This document defines structural rules.
Coding conventions live in `CONVENTIONS.md`.

---

# Core System Definition

Core is the platform foundation.

Core provides:

infrastructure  
capabilities  
shared contracts  
architecture standards  

Modules provide:

business workflows  
domain behavior  
feature orchestration  
UI behavior  

Core must remain:

module agnostic  
domain neutral  
presentation neutral  

Detailed Core service boundaries are defined in:

docs/CORE_SERVICE_RULES.md

---

# Layered Flow

Typical request flow:

Route  
→ FormRequest  
→ Controller  
→ Service  
→ Repository  
→ Model / Database  

Response flow:

Repository  
→ Service  
→ Controller  
→ Blade / JSON  
→ JS UI  

This flow must remain consistent for new modules.

---

# Layer Responsibilities

## Routes

Responsibilities:

- define URI and route names
- apply middleware
- enforce top-level authorization

Routes must not contain business logic.

---

## FormRequest

Responsibilities:

- validation
- authorization
- payload normalization

FormRequest should ensure controllers receive validated data.

---

## Controller

Controllers must remain thin.

Responsibilities:

- call services
- return responses
- coordinate HTTP concerns

Controllers must not contain:

query logic  
business rules  
domain branching  

Controllers orchestrate requests, not workflows.

---

## Service Layer

Services own:

business use cases  
orchestration  
transaction boundaries  
coordination of repositories  
coordination of infrastructure services  

Services may:

call repositories  
call dispatchers  
call providers  
trigger audit logging  

Services should not:

format UI data  
perform raw queries  
contain presentation logic  

---

## Repository Layer

Repositories own:

query logic  
filtering  
pagination  
persistence  

Repositories should return:

models  
collections  
paginators  
query result objects  

Repositories should not contain:

UI formatting  
display labels  
formatted timestamps  
presentation flags  

Presentation mapping belongs to presenters or transformers.

---

## Model Layer

Models define:

relationships  
casts  
scopes  
entity-level behavior  

Models should not:

orchestrate workflows  
coordinate services  

Models represent entities, not use cases.

---

# Presentation Layer

Presentation shaping should live outside repositories and services.

Examples:

Presenters  
Transformers  
ViewModels  

Presentation layer owns:

display labels  
formatted dates  
status badges  
UI row shaping  

This separation allows:

API reuse  
exports  
mobile clients  
dashboards  

without repository changes.

Naming and coding conventions for presenters are defined in:

docs/CONVENTIONS.md

---

# Core vs Module Responsibilities

## Core Should Contain

Shared capabilities reusable across modules:

audit logging  
notification dispatching  
file storage  
mail dispatching  
attachment handling  

Core provides engines.

---

## Modules Should Contain

Domain behavior:

business workflows  
events  
recipients  
wording  
feature orchestration  

Modules define scenarios.

---

## Structural Boundary Rule

If logic requires module knowledge:

it belongs in the module.

If logic is reusable without modification:

it belongs in Core.

---

# Generic Service Contract Rules

Generic services may accept structured payloads.

Payload structure must be:

explicit  
consistent  
documented  

Detailed payload conventions are defined in:

docs/CONVENTIONS.md

Refactor triggers for generic services are defined in:

docs/CORE_REFACTOR_GUIDELINES.md

---

# Shared Table Rules

Tables used across modules should include:

module_name  
module_code (optional)

This improves:

filtering  
reporting  
scalability  

Shared tables commonly include:

audit_logs  
notifications  
attachments  
comments  

Primary filtering fields should be indexed.

Broad text search should remain secondary.

Search optimization strategies belong in repository implementation, not controllers.

---

# Datatable Pattern (Audit Logs Baseline)

Audit Logs is the reference implementation for remote datatable pages.

Backend contract:

Request validates:
- page
- size
- filters

Controller:

- reads validated payload
- clamps page/size
- passes filters to service

Service:

- coordinates query request

Repository:

returns:

data  
last_page  
total  

Optional:

recordsTotal  
recordsFiltered  

Frontend contract:

Blade:

mounts filters and table container.

JS:

table.js → table setup  
filters.js → filter state  

This pattern should be followed for new table modules.

Detailed frontend conventions live in:

docs/CONVENTIONS.md

---

# Frontend Decomposition Pattern

Large workflows should use modular JS structure.

Pattern:

Entry file:
resources/js/module/feature.js

Submodules:
resources/js/module/feature/*.js

Benefits:

smaller files  
clear ownership  
reduced conflicts  
easier debugging  

---

# Print Workspace Pattern

Interactive print pages should follow workspace architecture:

sidebar:
report inputs and actions

preview:
live paper rendering

optional:
PDF export endpoint

Reference:

docs/PRINT_WORKSPACE_STANDARD.md

Production print modules must still follow layered architecture for data sourcing.

---

# Write Operation Pattern

Create/update/delete flow:

FormRequest:
validation + authorization

Service:
domain logic

Repository:
persistence

Service:
audit logging

Controller:
response

Audit rules defined in:

docs/AUDIT_LOGGING.md

---

# Authorization Pattern

Authorization layers:

Route middleware → first gate

FormRequest authorize() → second gate

Keep role naming consistent across modules.

---

# Dependency Injection Pattern

Bindings centralized in:

app/Providers/RepositoryServiceProvider.php

Use interface-based injection for:

controllers  
services  

Avoid concrete coupling in upper layers.

---

# Module Template (New Feature)

When creating a new module:

1 Routes and middleware  
2 Request classes  
3 Service contract + implementation  
4 Repository contract + implementation  
5 Controller actions  
6 Blade pages  
7 Modular JS entries  
8 Audit logging  

New modules must follow layered flow.

---

# Current Notes

Some legacy modules may not fully follow these rules.

New modules must follow this architecture.

Existing modules should be incrementally aligned.

Refactor guidance is defined in:

docs/CORE_REFACTOR_GUIDELINES.md