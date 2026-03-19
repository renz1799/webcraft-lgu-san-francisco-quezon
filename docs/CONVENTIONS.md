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
CurrentContextResolver

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

This ensures Git history remains clean, reviewable, and architecture-focused.

---

# Payload Conventions

## Generic Payload Naming

Generic payload keys should be consistent.

Common examples:

action
module_id
department_id
entity_type
entity_id
message
meta
display

Payload contracts should remain predictable.

For complex payloads, prefer DTO usage.

`module_name` may still appear only as a display or snapshot field when historical readability is needed, but relational Core payloads should prefer `module_id`.

---

## DTO Usage Guidelines

Use DTO when:

payload grows beyond simple structure
payload reused across services
generic service contract needs clarity
module/department context must travel explicitly

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
respect module/department relational scope

Repositories should not:

format UI
build display labels
format timestamps
generate UI flags
resolve runtime context directly from env

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
module-aware when applicable
department-aware when applicable

Prefer:

date filters
module filters
department filters
status filters
actor filters

Avoid heavy broad text search as primary filter.

Use relational filters such as `module_id` and `department_id` instead of string matching on names.

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
module/department display labels

Presenters should not:

query database
execute workflows
resolve runtime context

Example:

AuditLogTablePresenter

---

# Builder Conventions

Builders construct structured payloads.

Examples:

AuditDisplayBuilder
ReportPayloadBuilder
NotificationPayloadBuilder

Builders should focus on structure, not orchestration.

---

# Resolver Conventions

Resolvers locate contextual information.

Examples:

AuditContextResolver
CurrentUserResolver
CurrentContext

Resolvers should not execute business workflows.

Resolvers should be the preferred mechanism for runtime module and default department resolution.

Do not duplicate module lookup logic across services, repositories, or controllers.

---

# Provider Conventions

Providers aggregate data for use cases.

Examples:

ReportDataProvider
DashboardMetricsProvider
ModuleAccessProvider

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

Runtime identity describes which site or application instance is currently running.

It does not replace relational identity in transactional records.

---

## Relational Identity

Shared Core tables should prefer relational identifiers.

Use:

module_id
department_id
user_id

Avoid using names as relational identity:

module_name
department_name

Names may still be used as display or snapshot fields when historical readability is required.

---

## Current Context Rule

Current module and default department must be resolved through `CurrentContext`.

Correct:

$context = app(CurrentContext::class);
$moduleId = $context->moduleId();
$departmentId = $context->defaultDepartmentId();

Avoid repeated ad hoc lookups such as:

Module::find(config('module.id'))
Department::first()

---

## Access Identity

User access to module sites must be controlled through `user_modules`.

A valid user account does not automatically imply access to all module websites.

`user_modules` should determine:

user_id
module_id
department_id
is_active

This prevents cross-module login leakage in a shared database architecture.

---

# Seeder Conventions

Seeders must follow dependency order.

Correct order:

ModuleSeeder
DepartmentSeeder
PermissionsSeeder
UserSeeder
TaskSeeder
NotificationSeeder

Never assume order.

Never use:

Department::first()
Module::first()

Always prefer:

CurrentContext

Example:

$context = app(CurrentContext::class);

System identity seeders should use deterministic IDs when representing platform-owned records.

---

# Model Conventions

All models that use factories must include:

use HasFactory;

All UUID models must include the project-standard UUID trait or UUID behavior used by the model.

Examples:

use HasUuids;
use HasUuid;

Use the project-standard trait already adopted by that model family.

Example:

class Module extends Model
{
use HasFactory, HasUuids;
}

Models should expose relational methods for module and department scope when those columns exist.

Examples:

module()
department()
userModules()

---

# Factory Rules

Factories must respect relational integrity.

If a factory references:

module_id
department_id
user_id

It must create valid related records or be explicitly overridden by the seeder/test using it.

Never leave foreign keys invalid.

---

# Database Conventions

## Index Naming Rules

Composite indexes must be manually named.

Never allow Laravel to auto-name long indexes when the generated name may exceed MySQL limits.

Bad:

$table->index(['module_id', 'department_id', 'user_id', 'created_at']);

Good:

$table->index(
['module_id', 'department_id', 'user_id', 'created_at'],
'notif_module_dept_user_created_idx'
);

Reason:

MySQL 64 character limit.

---

## Migration Rules

Base migrations may be refactored only during Core development.

After production:

Never edit old migrations.

Use forward-only patch migrations.

Development workflow:

edit migration
migrate:fresh
test
repeat

Production workflow:

create new migration only.

---

## Transaction Scope Rule

When creating shared operational records:

Always include `module_id` when module scope exists.

Include `department_id` when department scope is relevant.

Examples:

Tasks
Notifications
Audit Logs
Google Tokens

Example:

Task::create([
'module_id' => current_context()->moduleId(),
'department_id' => current_context()->defaultDepartmentId(),
]);

Use the runtime context only as a default/fallback when more specific business scope is not already known.

---

## System Identity UUID Pattern

System entities may use fixed UUIDs.

This allows deterministic seeding.

Example pattern:

Modules:
10000000-0000-0000-0000-000000000001

Departments:
20000000-0000-0000-0000-000000000001

Roles (optional future):
30000000-0000-0000-0000-000000000001

Benefits:

• predictable records
• stable references
• safer migrations
• easier debugging

Do not generate random UUIDs for platform-owned system identities when stability is required.

---

# Naming Conventions For Database Fields

Tables:

snake_case plural

Models:

Singular PascalCase

Columns:

snake_case

Foreign keys:

*_id

Examples:

module_id
department_id
user_id
primary_department_id
connected_by_user_id

---

# Definition Of Done (Core Modules)

A feature is complete when:

Architecture:

follows layered flow
controller remains thin
repository handles queries
service handles orchestration
module/department scope is explicit where required

Code quality:

clear naming
consistent method patterns
no UI logic in repositories
no duplicated context resolution logic

Integration:

audit logging added if needed
datatable baseline followed where applicable
module isolation preserved where applicable

Frontend:

JS modules properly split
filters follow baseline pattern

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
