# Conventions

## Purpose

This document defines implementation and coding conventions for the Webcraft Core System.

ARCHITECTURE.md defines structure.
CORE_SERVICE_RULES.md defines Core boundaries.
CORE_REFACTOR_GUIDELINES.md defines refactor triggers.

This document defines how code should be written within that structure.

---

# Core Structural Philosophy

The Core System follows these structural priorities:

1. Separation by architectural role
2. Separation by concern/domain inside each role
3. Mirrored contract and implementation namespaces
4. Single responsibility per class
5. Composition over expansion

Preferred structure pattern:

app/
├── Builders/
├── Data/
├── Repositories/
├── Services/
├── Support/

Inside each role:

app/Services/Access/
app/Services/Audit/
app/Services/Login/
app/Builders/User/
app/Builders/Login/

NOT:

app/Services/AuthService.php
app/Services/UserAccessService.php
app/Builders/LoginAttemptBuilder.php

Flat structures are discouraged when the system grows.

---

# Folder Organization Rules

## Role First, Concern Second

Top level folders must represent architectural roles.

Inside each role, folders must represent concerns.

Correct:

app/Services/Access/ModuleAccessService.php
app/Services/Access/UserAccessService.php
app/Services/Audit/AuditLogService.php

app/Builders/User/UserDatatableRowBuilder.php
app/Builders/Login/LoginAttemptLogBuilder.php

Incorrect:

app/Services/ModuleAccessService.php
app/Services/UserAccessService.php
app/Builders/UserDatatableRowBuilder.php

---

## Mirrored Contract Rule

Contracts must mirror their implementation concern.

Correct:

App\Services\Contracts\Access\ModuleAccessServiceInterface
App\Services\Access\ModuleAccessService

App\Builders\Contracts\User\UserDatatableRowBuilderInterface
App\Builders\User\UserDatatableRowBuilder

Incorrect:

App\Services\Contracts\ModuleAccessServiceInterface
App\Services\Access\ModuleAccessService

Contracts should never remain flat when implementations are concern based.

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
CurrentContext

---

## Service Naming

Service names should describe the use case they coordinate.

Good:

TaskService
DtsWorkflowService
ModuleAccessService

Avoid vague names:

Manager
Handler
Processor

If a service only dispatches or transports:

Use Dispatcher.

If a service builds structured data:

Use Builder.

---

## Builder Naming

Builders must describe what structure they produce.

Pattern:

Entity + Purpose + Builder

Examples:

UserDatatableRowBuilder
UserDatatableActionBuilder
LoginAttemptLogBuilder
AuditDisplayBuilder

Builders should never be named vaguely.

Avoid:

DataBuilder
PayloadBuilder
StructureBuilder

---

## Repository Naming

Repositories should be named after entities.

Examples:

UserRepository
AuditLogRepository
AttachmentRepository
ModuleRepository

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
ModuleAccessData

DTOs should represent structured contracts, not arbitrary arrays.

---

# Method Conventions

## Service Methods

Service methods should represent use cases.

Good:

assignUserToTask()
submitInspection()
approveRequest()
grantModuleAccess()

Avoid vague names:

process()
handle()
execute()

Service methods should describe intent.

---

## Repository Methods

Repository methods should describe query purpose.

Good:

findById()
paginateWithFilters()
findActiveUsers()
findByModuleId()
findByDepartmentId()
findAccessibleModulesForUser()

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
switchDepartmentContext()

---

# Commit Conventions

All Core System commits must follow the Core commit strategy.

Commit messages must follow:

<type>(<scope>): <summary>

Examples:

refactor(print): introduce paper profile architecture
feat(tasks): add reassignment workflow
fix(auth): resolve login redirect issue
refactor(core): redesign foundation schema for multi-module LGU architecture

Commit splitting, staging discipline, and workflow rules are defined in:

CORE_COMMIT_STRATEGY.md

---

# Builder Conventions

## Builder Responsibilities

Builders construct structured payloads.

Builders may:

shape arrays
shape DTOs
build datatable rows
build action payloads
build display structures
assemble response fragments

Builders must NOT:

query database
run workflows
call external APIs
perform authorization
contain business rules

Builders are structure only.

---

## Builder Extraction Rule

Extract a Builder when a class starts:

repeating payload arrays
repeating row structures
repeating display formatting
repeating action payloads
exceeding single responsibility

Common extraction targets:

Row builders
Action builders
Payload builders
Option builders
Display builders

---

# Repository Conventions

## Repository Responsibilities

Repositories should:

handle queries
handle persistence
handle filtering
handle pagination
respect module/department relational scope

Repositories should not:

format UI
build display labels
format timestamps
generate UI flags
generate routes
shape datatable rows
resolve runtime context directly from env

Presentation shaping belongs in Builders or Presenters.

---

## Pagination Pattern

Standard pagination response should include:

data
last_page
total

Optional:

recordsTotal
recordsFiltered

---

## Filtering Pattern

Filtering should be:

explicit
predictable
index-friendly
module-aware when applicable
department-aware when applicable

Prefer:

date filters
module filters
department filters
status filters
actor filters

Avoid heavy broad text search as primary filter.

---

# Frontend Datatable Toolbar Convention

Index pages that follow the datatable baseline should use a uniform box-header toolbar layout.

Default pattern:

* use the shared `.datatable-toolbar` class for the box-header row
* use the shared `.datatable-toolbar-actions` class for the control group
* keep the section title and the main controls aligned in one row following the AIR and Accountable Persons reference pages
* keep the visible controls compact: search field, `More Filters`, `Clear`, and one primary action button where applicable
* move secondary filters such as status, department, or date refinements into the `More Filters` panel
* avoid one-off title-height or vertical-alignment CSS when the shared toolbar classes satisfy the layout

This convention exists so future table pages feel uniform across Core and module-owned screens.

---

# Core Service Provider Conventions

## Import Organization

Imports should be grouped by concern.

Example:

/* Audit Logs */
Repositories
Services
Builders

/* Authentication */
Repositories
Services
Builders

This improves traceability.

---

## Registration Organization

Registration must remain grouped by architectural role.

registerRepositories()
registerApplicationBuilders()
registerApplicationServices()
registerInfrastructureServices()

Never mix roles inside one registration method.

---

## Builder Registration

Builders must be registered in:

registerApplicationBuilders()

Not inside services.

---

# Data Conventions

Data objects should follow the same concern pattern.

Correct:

app/Data/Login/LoginAttemptData.php
app/Data/User/UserRowData.php
app/Data/Print/PrintDefinitionData.php

Avoid:

app/Data/LoginAttemptData.php
app/Data/UserRowData.php

---

# Resolver Conventions

Resolvers locate contextual information.

Examples:

AuditContextResolver
CurrentUserResolver
CurrentContext

Resolvers should not execute business workflows.

Resolvers should be the preferred mechanism for runtime module and default department resolution.

---

# Provider Conventions

Providers aggregate data for use cases.

Examples:

ReportDataProvider
DashboardMetricsProvider
ModuleAccessProvider

Use providers when data comes from multiple sources.

---

# Context And Identity Conventions

## Runtime Identity

Runtime identity is defined by configuration.

Pattern:

.env → config → resolver → database

Examples:

APP_MODULE_ID
APP_MODULE_CODE
APP_MODULE_NAME
APP_DEFAULT_DEPARTMENT_ID
APP_DEFAULT_DEPARTMENT_CODE
APP_DEFAULT_DEPARTMENT_NAME

---

## Relational Identity

Shared Core tables should prefer relational identifiers.

Use:

module_id
department_id
user_id

Avoid using names as relational identity.

---

# Definition Of Done (Core Modules)

A feature is complete when:

Architecture:

follows layered flow
controller remains thin
repository handles queries
service handles orchestration
builders handle shaping

Code quality:

clear naming
consistent method patterns
no UI logic in repositories
no duplicated context logic

Integration:

audit logging added if needed
datatable baseline followed where applicable
module isolation preserved where applicable

---

# General Coding Discipline

Prefer:

explicit naming
small focused classes
clear contracts
relational identity over string identity

Avoid:

vague naming
mixed responsibilities
implicit payload structures
manual env lookups in business logic

When unsure about structural placement:

refer to ARCHITECTURE.md.

When unsure about Core boundaries:

refer to CORE_SERVICE_RULES.md.

When unsure about splitting classes:

refer to CORE_REFACTOR_GUIDELINES.md.

When unsure about commit discipline:

refer to CORE_COMMIT_STRATEGY.md.
