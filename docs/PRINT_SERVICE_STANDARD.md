# PRINT SERVICE STANDARD (Core System)

This document defines the **backend orchestration, service responsibilities, data flow, and contract rules** for printable reports in the Core System.

This standard ensures that all print services:

* Follow one predictable backend flow
* Keep controllers thin
* Keep report data builders focused on report content
* Resolve paper profiles consistently
* Keep preview and PDF contracts aligned
* Remain reusable as more reports are added
* Remain module‑aware without becoming module‑dependent

This document focuses on **backend orchestration and service contracts**.
Workspace layout and rendering rules belong to PRINT_WORKSPACE_STANDARD.

---

# Core Principle

Print architecture separates:

**Report Content**
vs
**Paper Layout**

and

**Print Infrastructure**
vs
**Printable Ownership**

Meaning:

* Report content is built by the report layer
* Paper layout is resolved through print configuration
* Rendering consumes a resolved report contract and a resolved paper profile

Core printing must implement:

**One report implementation**
+
**Many paper profiles**

Not:

**Many duplicated services per paper size**

---

# Platform Awareness Rule

Print services must remain reusable across modules.

They must support module context but must not contain module workflows.

Print services may know:

* module identity
* module print configuration
* paper capabilities

Print services must not know:

* module workflows
* module business rules
* module UI behavior

Context should be passed or resolved through platform context (ex: CurrentContext).

---

# Standard Backend Flow

All printable reports must follow this flow:

```text
Request
→ Controller
→ Print Service
→ Report Data Builder
→ PDF Generator
→ View
```

This flow is mandatory.

Do not skip layers.

Do not merge unrelated responsibilities.

---

# Layer Responsibilities

## 1. Request

The request layer owns:

* authorization
* validation
* payload normalization
* explicit input contract

Requests may accept:

* report filters
* `paper_profile`

Requests must not:

* resolve print config
* choose view paths
* build report data
* generate PDFs

---

## 2. Controller

Controllers are orchestration only.

Controllers may:

* receive validated filters
* call the print service
* return preview views
* return PDF downloads

Controllers must not:

* query repositories directly for printable report data
* paginate print rows
* resolve paper defaults
* merge module and paper config
* choose paper-specific layout logic
* construct report DTOs manually

Controller code should remain thin and predictable.

---

## 3. Print Service

The print service is the orchestration center for printable reports.

The service owns:

* resolving the active paper profile
* resolving module print configuration
* calling repositories
* delegating report content building
* preparing preview payloads
* preparing PDF rendering payloads
* calling the PDF generator

The service must not:

* contain Blade markup
* contain CSS decisions
* hardcode paper sizes
* duplicate report builder logic per paper size
* contain module workflow logic

---

## 4. Report Data Builder

The report data builder owns:

* transforming domain data into a print-ready report contract
* report title
* report metadata
* printable rows
* report-specific summary fields

The report builder must not:

* resolve paper profiles
* decide page size
* paginate rows for layout
* decide preview width
* choose header/footer assets

Report builders produce **report content**, not layout rules.

---

## 5. PDF Generator

The PDF generator owns:

* rendering a PDF from a view
* saving the output file
* returning the generated path

The PDF generator must not:

* build report data
* choose a paper profile
* contain report logic

For large fixed-layout print batches, the service layer may prepare the document incrementally before handing the final HTML file to the PDF generator.

Example use case:

* sticker sheets
* repeated labels
* other page-stable layouts where each page can be built independently

In those cases:

* page preparation progress may be reported from the print service
* the final PDF generator call may still remain a single render step
* true page-by-page PDF merging is optional and should only be introduced when the platform has a supported merge implementation

---

## 6. View Layer

The view layer owns:

* rendering shared report content
* rendering paper-specific layout
* consuming the resolved report contract
* consuming the resolved paper profile

Views must not:

* query data sources
* resolve configuration
* contain fallback business rules

---

# Required Service Contract

Every print service must expose a predictable contract for:

* preview rendering
* PDF rendering

Recommended shape:

```php
[
    'report' => $report,
    'paperProfile' => $paperProfile,
]
```

Preview and PDF must both use the same resolved paper profile.

This is mandatory to keep rendering aligned.

---

# Print Configuration Rule

Print configuration must be split by ownership.

## Core infrastructure config

Shared paper definitions and global print defaults live in:

```text
config/print.php
```

This file should contain:

* universal paper definitions
* default header/footer assets
* shared print rules

## Module/Core printable registrations

Printable registrations live in:

```text
config/print-modules/*.php
```

The runtime printable registry is aggregated into:

```php
config('printables')
```

Configuration must separate:

## 1. Universal paper definitions

Example:

* code
* label
* width
* height
* orientation
* preview_width
* default header/footer assets

## 2. Printable registrations

Example:

* module or owner code
* allowed papers
* default paper
* pages view
* styles view
* pdf styles view
* rows per page
* optional header/footer overrides

This keeps physical paper definitions reusable while allowing each module or Core-owned printable to define its own layout bindings.

Important:

`config/print-modules/*.php` should describe printable registration and item-table behavior.

It should NOT be treated as the default place for top metadata-row styling.

Use config for things like:

* allowed papers
* default paper
* rows per page
* description wrap estimates
* item table column widths
* engine-specific PDF view bindings

Use `partials/meta.blade.php` for things like:

* metadata label/value widths
* metadata row splitting such as `RIS No.` and `Date`
* rowspan decisions
* legacy form-specific alignment adjustments

This prevents a common failure mode where developers change `item_column_widths` expecting the metadata block to move, but the live output is actually controlled by Blade or overridden by a paper profile.

---

# Print Config Loader Rule

Print services must not duplicate print-config merging logic.

Core should provide a shared loader/resolver such as:

```text
PrintConfigLoaderService
```

Recommended responsibilities:

* load global papers from `print.php`
* load aggregated printable registrations from `printables`
* resolve allowed papers
* resolve default paper
* merge paper defaults with printable profile overrides
* provide compatibility for legacy `print.modules.*` reads during migration

---

# Paper Profile Resolution Rule

Paper profile resolution must happen in the **print service**.

It must not happen in:

* controllers
* requests
* report builders
* Blade views

Resolution flow must be:

1. Read requested `paper_profile`
2. Read printable `allowed_papers`
3. If requested paper is invalid or missing, fallback to printable `default_paper`
4. Load universal paper definition from `print.papers.{code}`
5. Load printable profile from `printables.{printable}.profiles.{code}`
6. Merge them into one resolved paper profile
7. Pass only the resolved paper profile forward

Recommended merge rule:

```php
$resolvedPaperProfile = array_merge($paperDefaults, $printableProfile);
```

Printable profile values override paper defaults.

This supports platform defaults with module-owned overrides.

---

# Module Context Rule

Print services should not hardcode module names.

Module identity should come from:

* configuration
* route binding
* CurrentContext

Avoid:

hardcoded module strings.

Prefer:

config driven module identification.

---

# Fallback Rule

Fallback behavior must be explicit.

If the requested paper is missing or invalid:

* the service must use the module default paper
* the system must not crash

If config is incomplete:

* service should fallback to the module default profile if available
* rendering should remain predictable

Never pass unresolved or invalid paper configuration into Blade.

---

# Preview Payload Rule

Preview actions must receive:

* report contract
* resolved paper profile
* filters if needed by the workspace

Example:

```php
return view('module.print.index', [
    'report' => $payload['report'],
    'paperProfile' => $payload['paperProfile'],
    'filters' => $filters,
]);
```

Controllers must not construct the paper profile manually.

---

# PDF Payload Rule

PDF generation must use the same paper profile resolution path as preview.

The print service should:

1. build the preview payload or equivalent shared payload
2. pass `report` and `paperProfile` to the PDF view
3. generate the PDF from that view

Example:

```php
view: 'module.print.pdf',
data: [
    'report' => $payload['report'],
    'paperProfile' => $payload['paperProfile'],
],
```

Preview and PDF must never resolve paper profiles differently.

---

# Pagination Rule

Pagination for printable pages is a **layout concern**.

Therefore:

* services must not paginate rows for page rendering
* report builders must not paginate rows for page rendering
* paper-specific `pages.blade.php` should control page chunking

Services deliver report rows.
Views decide how many rows fit the page.

This is required to support multiple paper profiles without backend duplication.

---

# Header/Footer Asset Rule

Header and footer assets must not be hardcoded in services or controllers.

Assets must come from the resolved paper profile.

Examples:

* `header_image_web`
* `footer_image_web`
* `header_image_pdf`
* `footer_image_pdf`

Universal paper defaults may define these assets.
Module profiles may override them.

The print service should only pass the resolved profile.
It should not manually rewrite view asset logic unless there is a dedicated resolver abstraction.

---

# Naming Rule

Paper profile codes must be stable and reusable.

Use the same code in:

* config keys
* request values
* control select values
* paper folder names

Examples:

* `a4-portrait`
* `a4-landscape`
* `letter-portrait`
* `letter-landscape`
* `legal-portrait`
* `legal-landscape`

Do not use display labels as identifiers.

---

# Anti-Patterns

Never do these:

* create one print service per paper size
* create one controller action per paper size
* resolve paper profiles in Blade
* hardcode A4 assumptions in services
* duplicate report builders for different paper sizes
* paginate printable pages inside services
* let preview and PDF use different profile resolution logic
* pass raw request `paper_profile` directly to views without resolution
* hardcode module identity inside services

---

# Example Service Pattern

Recommended pattern:

```php
public function buildReport(array $filters): array
{
    $paperProfile = $this->resolvePaperProfile($filters['paper_profile'] ?? null);

    $rows = $this->repository->findForPrint($filters);
    $report = $this->reportBuilder->build($rows, $filters);

    return [
        'report' => $report,
        'paperProfile' => $paperProfile,
    ];
}
```

Recommended PDF pattern:

```php
public function generatePdf(array $filters): string
{
    $payload = $this->buildReport($filters);

    return $this->pdfGenerator->generateFromView(
        view: 'module.print.pdf',
        data: [
            'report' => $payload['report'],
            'paperProfile' => $payload['paperProfile'],
        ],
        outputPath: $path,
    );
}
```

---

# Scalability Rule

Adding a new paper must not require:

* a new service
* a new report builder
* a new controller flow

Adding a new paper should require only:

1. config entry
2. paper layout files
3. optional tuning for rows and spacing

This is the required scalability model for Core printing.

---

# Default Printable Rule

Each printable must define:

* `default_paper`
* `allowed_papers`
* per-paper printable profiles

This keeps printable capabilities explicit.

A printable should not automatically support every paper just because a universal paper definition exists.

---

# Final Architecture Summary

Request:

Validate input and normalize payload.

Controller:

Orchestrate only.

Service:

Resolve paper profile, build payload, coordinate rendering.

Report Builder:

Produce report content only.

View:

Render report content with paper layout.

System:

One report implementation.
Many paper profiles.
Config-driven rendering.

---

# Final Rule

Core print services must always follow:

**One Print Service Flow**
+
**One Report Contract**
+
**One Resolved Paper Profile**

This is the required backend model for scalable multi-paper printing in the Core System.
