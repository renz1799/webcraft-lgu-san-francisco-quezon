# Print Service Standard

This document defines the backend/service architecture standard for all printable modules in the Core System.

This complements PRINT_WORKSPACE_STANDARD which defines the UI structure. This document defines how report data is produced, shaped, and converted into PDFs.

The reference implementation is the Audit Log Print module.

---

# Purpose

Ensure all printable modules follow a consistent backend flow:

Request → Controller → Print Service → Report Data Builder → PDF Generator → View

Goals:

* predictable architecture
* reusable printing pipeline
* clean separation of responsibilities
* generic platform-ready services
* avoid controller-heavy implementations

---

# Core Flow

All print modules must follow:

Controller
→ Print Service
→ Report Data Builder
→ PDF Generator
→ Blade PDF View

Controllers must not generate report data directly.

---

# Standard Class Structure

Example structure:

app/Services/Print/
├─ AuditLogPrintService.php
├─ Contracts/
│    └─ AuditLogPrintServiceInterface.php
├─ Data/
│    └─ AuditLogReportData.php
└─ Generators/
└─ ChromePdfGenerator.php

---

# Responsibilities

## Controller

Responsible only for:

* receiving request
* validating request
* calling service
* returning response

Must NOT:

* build report datasets
* query repositories
* format report structures
* generate PDFs

Example:

```
public function pdf(Request $request)
{
    $report = $this->printService->buildReport($request->validated());

    return $this->printService->downloadPdf($report);
}
```

---

## Print Service

Primary orchestrator.

Responsibilities:

* validate print parameters
* coordinate repositories
* call ReportData builder
* call PDF generator
* define report contracts

Must NOT:

* contain UI layout logic
* contain Blade formatting
* contain HTML

Example responsibilities:

* buildReport()
* downloadPdf()
* streamPdf()

---

## Report Data Builder (Data layer)

This is the most important abstraction.

Purpose:

Convert domain data into report-ready structure.

Responsibilities:

* shape report dataset
* normalize fields
* compute display values
* guarantee required fields exist

Example output:

```
ReportData
 ├─ title
 ├─ filters
 ├─ rows
 ├─ totals
 ├─ metadata
 └─ pagination
```

Must NOT:

* access HTTP
* access Blade
* generate PDFs

---

# Data Layer Rule

Report data builders belong to a Data folder, not Services.

Example:

app/Services/Print/Data/AuditLogReportData.php

Reason:

Services orchestrate.
Data builders transform.

---

# PDF Generator

Handles only PDF conversion.

Responsibilities:

* load Blade view
* pass report data
* execute PDF engine

Must NOT:

* build report data
* query repositories

Example:

ChromePdfGenerator

Possible future generators:

* DomPdfGenerator
* SnappyGenerator
* ExternalPdfService

---

# Report Data Contract Rule

Report data must be predictable.

Every report must define a clear structure.

Example required fields:

* title
* generated_at
* filters
* rows
* totals (optional)
* module_name

Blade must never assume undefined keys.

---

# Common Problems and Solutions

## Problem — Undefined fields in Blade

Cause:

View expected fields not provided.

Fix:

ReportData must guarantee schema.

Rule:

ReportData defines the contract.
Blade consumes it.

---

## Problem — Controllers becoming too large

Cause:

Controllers generating report data.

Fix:

Move logic into PrintService.

Rule:

Controllers orchestrate only.

---

## Problem — Services becoming god classes

Cause:

Service doing both data shaping and PDF generation.

Fix:

Split:

PrintService
ReportData
PdfGenerator

Rule:

One responsibility per class.

---

## Problem — Report tightly coupled to database

Cause:

Blade directly consuming models.

Fix:

Use ReportData DTO.

Rule:

Blade consumes report objects, not models.

---

## Problem — Missing relationships

Cause:

Repositories not eager loading required relations.

Fix:

Load relations in Service or Repository.

Rule:

ReportData must not trigger lazy loading.

---

# Naming Standards

Services:

AuditLogPrintService
DtsPrintService
InventoryPrintService

Data builders:

AuditLogReportData
DtsReportData

Generators:

ChromePdfGenerator

---

# Interface Rule

All PrintServices should have interfaces.

Example:

AuditLogPrintServiceInterface

Reason:

Supports testing and future refactors.

---

# Dependency Injection Rule

Bind in CoreServiceProvider.

Example:

AuditLogPrintServiceInterface → AuditLogPrintService

---

# Extensibility Rule

Future features should not require rewriting services.

Possible additions:

* Excel export
* CSV export
* Email report
* Scheduled reports

Service must support extension.

---

# Anti Patterns

Do NOT:

Generate PDFs in controllers.

Do NOT:

Mix HTML inside services.

Do NOT:

Use Blade as data source.

Do NOT:

Query database inside views.

Do NOT:

Let ReportData depend on HTTP.

---

# Result

Following this standard ensures:

* reusable printing pipeline
* clean architecture
* predictable report generation
* easy module expansion
* platform-level printing foundation

---

# Relationship to Other Standards

This document works with:

PRINT_WORKSPACE_STANDARD → UI
PRINT_SERVICE_STANDARD → Backend
ARCHITECTURE.md → Layer rules
CORE_SERVICE_RULES.md → Service behavior

Together they define the Core printing architecture.
