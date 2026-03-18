# Conventions

## Purpose

This document defines implementation and coding conventions for the Webcraft Core System.

ARCHITECTURE.md defines structure.
CORE_SERVICE_RULES.md defines Core boundaries.
CORE_REFACTOR_GUIDELINES.md defines refactor triggers.

This document defines how code should be written within that structure.

---

# Naming Conventions

## Class Naming

Use clear suffixes to indicate responsibility:

Service → business orchestration  
Repository → persistence/query logic  
Controller → HTTP orchestration  
Request → validation/authorization  
Model → entity representation  
DTO → structured payload object  
Presenter → UI formatting  
Transformer → output shaping  
Dispatcher → delivery/transport  
Builder → structured payload construction  
Resolver → contextual lookup  
Provider → aggregated data sourcing  

Examples:

AuditLogService  
AuditLogRepository  
AuditRecordData  
AuditLogTablePresenter  
NotificationDispatcher  
AuditContextResolver  

---

## Service Naming

Service names should describe the use case they coordinate.

Good:

TaskService  
DtsWorkflowService  

Avoid vague names:

Manager  
Handler  
Processor  

If a service only dispatches or transports:

Use Dispatcher.

If a service builds structured data:

Use Builder.

---

## Repository Naming

Repositories should be named after entities.

Examples:

UserRepository  
AuditLogRepository  
AttachmentRepository  

Avoid:

UserDataManager  
AuditHandler  

---

## DTO Naming

DTO naming pattern:

Entity + Purpose + Data

Examples:

AuditRecordData  
NotificationData  
ReportRequestData  

DTOs should represent structured contracts, not arbitrary arrays.

---

# Method Conventions

## Service Methods

Service methods should represent use cases.

Good:

assignUserToTask()
submitInspection()
approveRequest()

Avoid vague names:

process()
handle()
execute()

Service methods should describe intent.

---


# Commit Conventions

All Core System commits must follow the Core commit strategy.

Commit messages must follow:

<type>(<scope>): <summary>

Examples:

refactor(print): introduce paper profile architecture  
feat(tasks): add reassignment workflow  
fix(auth): resolve login redirect issue  

Commit splitting, staging discipline, and workflow rules are defined in:

CORE_COMMIT_STRATEGY.md

This ensures Git history remains clean, reviewable, and architecture-focused.

## Repository Methods

Repository methods should describe query purpose.

Good:

findById()
paginateWithFilters()
findActiveUsers()
searchByModule()

Avoid:

getData()
fetchAll()

Method names should reveal query behavior.

---

## Controller Methods

Controller methods should reflect HTTP actions.

Examples:

index()
store()
update()
destroy()
show()

Custom actions should remain descriptive:

restore()
archive()
finalize()

---

# Payload Conventions

## Generic Payload Naming

Generic payload keys should be consistent.

Common examples:

action  
module_name  
entity_type  
entity_id  
message  
meta  
display  

Payload contracts should remain predictable.

For complex payloads, prefer DTO usage.

---

## DTO Usage Guidelines

Use DTO when:

payload grows beyond simple structure  
payload reused across services  
generic service contract needs clarity  

DTO should improve clarity, not add ceremony.

DTO should not contain business logic.

DTO should represent structured data only.

---

# Repository Conventions

## Repository Responsibilities

Repositories should:

handle queries  
handle persistence  
handle filtering  
handle pagination  

Repositories should not:

format UI  
build display labels  
format timestamps  
generate UI flags  

Presentation shaping belongs in presenters.

---

## Pagination Pattern

Standard pagination response should include:

data  
last_page  
total  

Optional:

recordsTotal  
recordsFiltered  

This keeps frontend integration consistent.

---

## Filtering Pattern

Filtering should be:

explicit  
predictable  
index-friendly  

Prefer:

date filters  
module filters  
status filters  
actor filters  

Avoid heavy broad text search as primary filter.

---

# Presenter Conventions

Presenters should answer:

"How should this appear?"

Presenters may format:

display names  
dates  
badges  
row structures  
labels  

Presenters should not:

query database  
execute workflows  

Example:

AuditLogTablePresenter

---

# Builder Conventions

Builders construct structured payloads.

Examples:

AuditDisplayBuilder  
ReportPayloadBuilder  

Builders should focus on structure, not orchestration.

---

# Resolver Conventions

Resolvers locate contextual information.

Examples:

AuditContextResolver  
CurrentUserResolver  

Resolvers should not execute business workflows.

---

# Provider Conventions

Providers aggregate data for use cases.

Examples:

ReportDataProvider  
DashboardMetricsProvider  

Use providers when data comes from multiple sources.

---

# Frontend Conventions

## Module JS Structure

Pattern:

resources/js/module/feature.js

Submodules:

resources/js/module/feature/*.js

Keep files small and focused.

---

## Datatable JS Pattern

Standard file split:

table.js → table setup  
filters.js → filter logic  
actions.js → row actions  

This improves maintainability.

---

# Definition Of Done (Core Modules)

A feature is complete when:

Architecture:

follows layered flow  
controller remains thin  
repository handles queries  
service handles orchestration  

Code quality:

clear naming  
consistent method patterns  
no UI logic in repositories  

Integration:

audit logging added if needed  
datatable baseline followed where applicable  

Frontend:

JS modules properly split  
filters follow baseline pattern  

---

# General Coding Discipline

Prefer:

explicit naming  
small focused classes  
clear contracts  

Avoid:

vague naming  
mixed responsibilities  
implicit payload structures  

When unsure about structural placement:

refer to ARCHITECTURE.md.

When unsure about Core boundaries:

refer to CORE_SERVICE_RULES.md.

When unsure about splitting classes:

refer to CORE_REFACTOR_GUIDELINES.md.