# Core Glossary

## Purpose

This document defines architectural terminology used in the Webcraft Core System.

The goal is to maintain consistent vocabulary across the platform so class names remain predictable and responsibilities remain clear.

This glossary standardizes naming meanings, not coding rules.

Coding rules live in:

CONVENTIONS.md

Architecture structure lives in:

ARCHITECTURE.md

---

# Core Architectural Terms

## Core

Core refers to the platform foundation that provides reusable capabilities across modules.

Core contains:

infrastructure
shared services
dispatchers
storage mechanisms
platform contracts
runtime context resolution
module isolation foundations
department structure
access mapping

Core must remain:

module agnostic
domain neutral
presentation neutral

Core provides capabilities.

Modules provide behavior.

---

## Module

A module represents a system application or business domain running on the Core platform.

Examples:

CORE
DTS
GSO
Procurement
HR
Inventory

Modules contain:

workflows
business rules
feature orchestration
UI behavior
module policies

Modules define scenarios.

Core defines capabilities.

---

## Department

A department represents an organizational unit inside the LGU structure.

Examples:

IT Office
Accounting Office
Budget Office
GSO
Mayor's Office

Departments represent organizational structure, not deployment boundaries.

A module may serve multiple departments.

Department scope helps identify:

record ownership
workflow responsibility
organizational context

---

## Runtime Identity

Runtime identity defines which application instance is currently running.

Defined through:

.env → config → resolver

Examples:

APP_MODULE_ID
APP_MODULE_CODE
APP_MODULE_NAME
APP_DEFAULT_DEPARTMENT_ID
APP_DEFAULT_DEPARTMENT_CODE
APP_DEFAULT_DEPARTMENT_NAME

Runtime identity answers:

"Which system is currently running?"

It does not define relational ownership of records.

---

## Relational Identity

Relational identity defines how records are connected in the database.

Examples:

module_id
department_id
user_id

Relational identity supports:

joins
filtering
reporting
module isolation
department traceability

Relational identity should use foreign keys, not string names.

---

## Access Identity

Access identity defines which users may access which modules.

Defined through:

user_modules

Structure:

user_id
module_id
department_id
is_active

This separates:

shared identity → users
access mapping → user_modules

---

## CurrentContext

A resolver that determines the running platform identity.

Provides:

module()
moduleId()
defaultDepartment()
defaultDepartmentId()

CurrentContext answers:

"What module is running?"
"What is the default department context?"

This prevents duplicated lookup logic across the system.

---

## Module Isolation

Module isolation is the architectural rule that prevents data leakage between modules sharing one database.

Achieved through:

module_id filtering
user_modules access checks
module-aware queries

Example:

DTS users should not automatically access GSO.

---

## Default Department

Default department is the runtime organizational context configured for the application.

Defined in:

.env

Used for:

default record scope
system-generated records
seeders
fallback context

Default department does NOT define access boundaries.

Access comes from relational mappings.

---

# Layer Definitions

## Controller

Handles HTTP orchestration.

Responsibilities:

receive request
call services
return responses

Controllers must not contain:

business logic
query logic
presentation shaping

Controllers coordinate requests, not workflows.

---

## Service

Coordinates business use cases.

Responsibilities:

orchestration
transactions
workflow coordination
calling repositories
calling Core services

A Service answers:

"What needs to happen?"

Not:

"How data is stored"

Not:

"How UI appears"

Examples:

TaskService
DtsWorkflowService
ModuleAccessService

---

## Repository

Handles persistence and querying.

Responsibilities:

queries
filtering
pagination
saving data
module scoped retrieval
department scoped retrieval

Repository answers:

"How do we get or store data?"

Repository should not answer:

"How should this appear?"

---

## Model

Represents a domain entity.

Responsibilities:

relationships
scopes
casts
entity-level behavior

Models should not coordinate workflows.

---

# Supporting Class Types

## DTO (Data Transfer Object)

Represents structured data contracts between layers.

Used when:

payload becomes complex
contracts need clarity
generic services need structure
module/department context must travel together

DTO contains:

data only.

DTO does not contain:

business logic
queries
orchestration

Examples:

AuditRecordData
NotificationData
ModuleAccessData

---

## Dispatcher

Handles transport or delivery.

Examples:

NotificationDispatcher
MailDispatcher

Dispatcher answers:

"How do we deliver this?"

Dispatcher does not decide:

when to send
who receives
what message says

Modules decide scenarios.

---

## Builder

Constructs structured payloads.

Examples:

AuditDisplayBuilder
ReportPayloadBuilder
NotificationPayloadBuilder

Builder answers:

"How should this data be structured?"

Builder does not orchestrate workflows.

---

## Resolver

Finds contextual information.

Examples:

AuditContextResolver
CurrentActorResolver
CurrentContext

Resolver answers:

"What context do we need?"

Resolver should not execute workflows.

---

## Provider

Aggregates data from multiple sources.

Examples:

ReportDataProvider
DashboardMetricsProvider
ModuleAccessProvider

Provider answers:

"Where does this data come from?"

Provider may call multiple repositories.

Provider does not orchestrate workflows.

---

## Presenter

Shapes data for UI consumption.

Examples:

AuditLogTablePresenter
NotificationPresenter

Presenter answers:

"How should this appear?"

Presenter may format:

dates
labels
badges
row structures
module display names
department display names

Presenter should not query database directly.

---

## Transformer

Converts data between formats.

Examples:

AuditLogTransformer
ApiUserTransformer

Transformer answers:

"How should this data be converted?"

Often used for:

API responses
exports
format conversion

---

## Contract

An interface defining behavior.

Examples:

AuditLogRepositoryInterface
NotificationDispatcherInterface

Contracts define:

what must exist

Implementations define:

how it works.

---

## Infrastructure Service

A service providing technical capability rather than business workflow.

Examples:

AuditLogService
NotificationDispatcher
FileStorageService

Infrastructure services should remain domain neutral.

---

# Naming Anti-Terms (Avoid These)

These names are discouraged because they lack clarity:

Manager
Helper
Processor
Handler
Utility

Why:

They hide responsibility.

Prefer explicit names instead.

Bad:

UserManager

Better:

UserService
UserRepository
UserProvider

Names should reveal purpose.

---

# Decision Guide

If naming a class, ask:

Is it orchestrating workflow?
→ Service

Is it querying data?
→ Repository

Is it shaping UI?
→ Presenter

Is it delivering something?
→ Dispatcher

Is it building structured data?
→ Builder

Is it finding context?
→ Resolver

Is it aggregating data?
→ Provider

Is it structured payload?
→ DTO

If unclear:

re-evaluate responsibility.

---

# Final Rule

Clear naming protects architecture.

If a class name does not clearly indicate responsibility:

it likely needs renaming or splitting.
