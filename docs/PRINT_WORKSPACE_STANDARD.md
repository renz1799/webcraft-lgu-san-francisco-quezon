# Print Workspace Standard

This document defines the standard page pattern for printable documents that need both:

* a left-side settings or filters panel
* a right-side live paper preview
* a server-generated PDF export

The Core reference implementation is the Audit Log print module.

---

# Purpose

Use this pattern when a printable document needs users to adjust values like:

* cutoff date
* module filters
* report parameters
* preview-only toggles before printing or downloading PDF

This keeps the printable document clean while still giving the user a practical working area beside the preview.

---

# Core Principles

Printable modules must follow:

Preview Workspace
→ User configures report
→ User sees live preview
→ User downloads PDF

Not:

Generate PDF directly from buttons without preview.

---

# Standard Building Blocks

## 1. Shared workspace styles

Include the shared workspace styles:

```
<x-print.workspace-styles />
<x-print.workspace-panel-styles />
```

This provides:

* app-like screen background
* left/right split layout
* sticky settings panel
* centered preview column
* standardized filter sidebar design
* print behavior that hides sidebar automatically

Files:

```
resources/views/components/print/workspace-styles.blade.php
resources/views/components/print/workspace-panel-styles.blade.php
```

---

## 2. Shared workspace shell

Wrap the page in:

```
<x-print.workspace>

<x-slot:sidebar>

@include('module.print.partials.controls')

</x-slot:sidebar>

@include('module.print.partials.pages')

</x-print.workspace>
```

File:

```
resources/views/components/print/workspace.blade.php
```

---

## 3. Shared settings panel shell

Use:

```
<x-print.panel>
```

Example:

```
<x-print.panel
kicker="Reports"
title="Audit Log Print Preview"
copy="Set filters and review the document before downloading."
>
```

File:

```
resources/views/components/print/panel.blade.php
```

---

# Sidebar Control Panel Standard

All print workspaces must follow:

Structure:

```
Intro
Filters section
Actions section
```

Example:

```
Report Controls
Filters
Actions
```

---

# Button Hierarchy Standard

All print modules must use:

Primary:

Apply Filters

Secondary:

Download PDF

Tertiary:

Reset Filters

Hierarchy:

```
Primary → filled button
Secondary → outline button
Tertiary → text link
```

---

# Core Button Pattern

Buttons should follow:

```
<button class="ti-btn btn-wave ti-btn-primary-full label-ti-btn">

<i class="ri-filter-3-line label-ti-btn-icon me-2"></i>

Apply Filters

</button>
```

Download example:

```
<a class="ti-btn btn-wave ti-btn-outline-primary label-ti-btn">

<i class="ri-file-pdf-line label-ti-btn-icon me-2"></i>

Download PDF

</a>
```

---

# Paper Standard

All Core printable reports must default to:

```
Paper: A4
Orientation: Portrait
Preview must match PDF output.
```

Do not use browser default sizing.

---

# Header/Footer Standard

Reports must support:

* header image
* footer image
* page numbering
* consistent margins

Images stored in:

```
public/headers/
```

Example:

```
a4_header_template_dark_2480x300.png
a4_footer_template_dark_2480x250.png
```

---

# Paging Standard

Preview must simulate PDF paging.

Rules:

* fixed A4 page height
* page breaks enforced
* footer page numbers bottom right
* header repeated per page

---

# Preview vs PDF Separation Rule

Preview:

* interactive
* may use workspace layout
* may include sidebar

PDF:

Must:

* be standalone
* not extend master layouts
* not include JS
* not include sidebar
* reuse same page partials

Example:

```
<!DOCTYPE html>

@include('module.print.partials.pdf-styles')

@include('module.print.partials.pages')
```

---

# Service Architecture Rule

Printing must follow Core layering:

```
Controller
→ Service
→ Data Builder
→ PDF Generator
→ Blade View
```

Not:

Controller → View → PDF directly.

---

# Report Data Rule

Report data must come from:

Service
Repository
Provider

Not controller logic.

---

# File Structure Standard

Each printable module must follow:

```
module/
 ├─ print/
 │   ├─ index.blade.php
 │   ├─ pdf.blade.php
 │   └─ partials/
 │        ├─ controls.blade.php
 │        ├─ pages.blade.php
 │        ├─ table.blade.php
 │        ├─ meta.blade.php
 │        ├─ styles.blade.php
 │        └─ pdf-styles.blade.php
```

Shared Core:

```
components/
 ├─ print/
 │   ├─ workspace.blade.php
 │   ├─ panel.blade.php
 │   ├─ workspace-styles.blade.php
 │   └─ workspace-panel-styles.blade.php
```

---

# Implementation Checklist

When building a new print workspace:

1 Create printable page view
2 Add workspace styles
3 Wrap with workspace shell
4 Put filters in sidebar
5 Keep pages in preview slot
6 Keep layout logic in partials
7 Add PDF endpoint
8 Reuse shared components
9 Use A4 sizing
10 Follow button hierarchy

---

# Problems Encountered and Solutions (Important)

These issues were discovered during the Audit Log print module implementation and define the baseline for all future print modules.

Future modules should avoid these mistakes.

---

## Problem 1 — Preview did not match PDF size

Issue:

Preview rendered as long bond while PDF used A4.

Cause:

Preview was using browser auto layout instead of fixed page sizing.

Fix:

Always enforce:

```
width:210mm;
height:297mm;
```

And simulate real pages.

Rule:

Preview must visually match PDF dimensions.

---

## Problem 2 — Chrome PDF spacing different from preview

Issue:

Spacing differences between preview and PDF.

Cause:

Browser rendering vs headless Chrome rendering differences.

Fix:

Separate:

```
styles.blade.php
pdf-styles.blade.php
```

Rule:

Never share identical CSS between preview and PDF without testing.

---

## Problem 3 — Missing relationships in report data

Issue:

Undefined relationship errors.

Cause:

Report expected relations not loaded.

Fix:

Ensure repository/service loads required relationships.

Rule:

Report data must be shaped in Service layer, not Blade.

---

## Problem 4 — Missing report fields

Issue:

Undefined array keys like module_name.

Cause:

View expected fields not included in report data.

Fix:

Ensure ReportData builder defines all expected fields.

Rule:

Blade should never assume fields.
Service must define report schema.

---

## Problem 5 — Chrome binary not found

Issue:

Chrome PDF generator failed.

Cause:

Chrome path not configured.

Fix:

Configure:

```
CHROME_BIN
services.chrome.binary
```

Rule:

PDF infrastructure must be configured before using print services.

---

## Problem 6 — Icons not rendering

Issue:

Remix icons not visible.

Cause:

Custom layout did not load icon assets.

Fix:

Ensure print workspace master loads same CSS as main layout.

Rule:

Custom layouts must load UI assets.

---

## Problem 7 — Template styles not applied

Issue:

Form styling inconsistent.

Cause:

Preview layout not extending UI asset stack.

Fix:

Create:

```
print-workspace-master.blade.php
```

Rule:

Preview pages must load same UI assets as main app.

---

# Anti Patterns

Do NOT:

Generate PDF inside controllers.

Do NOT:

Mix preview and PDF styles.

Do NOT:

Duplicate sidebar UI per module.

Do NOT:

Hardcode report layouts.

Do NOT:

Use inconsistent button structures.

---

# Decision Rule

Use Print Workspace when:

Page is interactive
AND
Page is printable/exportable.

Otherwise simple printable Blade is acceptable.

---

# Long Term Goal

The Print Workspace is part of the Core platform direction:

```
Generic
Reusable
Consistent
Module independent
Platform driven
```

---

# Recommended Addition to CORE_DESIGN_PRINCIPLES

Add:

PRINT_WORKSPACE_STANDARD must be followed by all printable modules.

---

# Result

This ensures:

* consistent report UX
* reusable Core components
* predictable PDF output
* faster module creation
* platform-level maintainability

---

# Future Standard (Recommended Next Step)

Next recommended document:

PRINT_SERVICE_STANDARD

This will standardize:

* PrintService pattern
* ReportData pattern
* PdfGenerator usage
* Data shaping rules

This completes the Core printing architecture.
