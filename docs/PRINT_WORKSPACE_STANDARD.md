# PRINT WORKSPACE STANDARD (Core System)

This document defines the **UI, layout, and structural rules** for all printable workspaces in the Core System.

This standard ensures that all print workspaces:

* Follow a consistent preview experience
* Support multiple paper profiles
* Keep preview and PDF layouts identical
* Separate report content from paper layout
* Remain scalable as more reports are added
* Remain reusable across modules

This document focuses on **workspace layout and rendering rules**.
Backend orchestration rules belong to PRINT_SERVICE_STANDARD.

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

One report implementation
+
Multiple paper profiles

NOT:

Multiple report implementations per paper size.

Core provides:

How printing works.

Modules define:

What gets printed.

---

# Print Architecture

Print configuration must be separated by ownership.

## Core-owned print infrastructure

Core owns:

* paper definitions
* page dimensions
* preview sizing rules
* default header/footer assets
* global print defaults

These belong in:

```text
config/print.php
```

## Module-owned printable registrations

Each module or Core-admin feature owns its own printable definitions.

Examples:

* GSO AIR
* Core Audit Logs
* future DTS reports
* future HR forms

These belong in:

```text
config/print-modules/
  core.php
  gso.php
  dts.php
  hr.php
```

The aggregated runtime registry is exposed through:

```php
config('printables.gso_air')
config('printables.audit_logs')
```

This prevents `config/print.php` from becoming a central printable registry bottleneck.

---

# Platform Awareness Rule

Print workspaces must support multiple modules without embedding module logic.

Workspaces may know:

* module print configuration
* allowed papers
* default paper

Workspaces must not know:

* module workflows
* module business rules
* module service logic

Module identity should come from:

* route context
* configuration
* CurrentContext

Avoid hardcoded module assumptions.

---

# Workspace Layout Structure

All print workspaces must follow the Core workspace layout:

LEFT:
Report controls

RIGHT:
Preview panel

Structure example:

```
[x-print.panel]
 ├─ Controls (filters + paper selector)
 └─ Preview container
```

Controls modify the preview.
Preview reflects the resolved paper profile.

---

# Paper Profile Concept

All printable reports must use a **paper profile**.

Paper profiles define the physical page behavior.

A paper profile contains:

* paper_code
* label
* width
* height
* orientation
* preview_width
* header_image_web
* footer_image_web
* header_image_pdf
* footer_image_pdf

Module profiles may extend this with:

* pages_view
* styles_view
* pdf_styles_view
* rows_per_page
* first_page_rows (optional)
* later_page_rows (optional)

Paper infrastructure lives in:

```
config/print.php
```

Printable registrations live in:

```text
config/print-modules/*.php
```

Core aggregates those registrations into the runtime printable registry.

---

# Preview Container Rules

Preview must simulate a real printed page.

Preview must:

* Use paper profile width
* Use paper profile height
* Center pages
* Show page shadows
* Allow vertical scroll
* Never auto-resize based on content

Example behavior:

Preview pages should look like real paper stacked vertically.

Preview dimensions must come from:

```
$paperProfile['width']
$paperProfile['height']
```

Never hardcode dimensions like:

```
210mm
297mm
```

Preview must always use the resolved paper profile.

---

# Preview Styling Rules

Preview pages should visually resemble paper.

Recommended:

* white background
* subtle shadow
* spacing between pages
* centered layout

Example visual expectations:

* margin between pages
* box-shadow allowed in preview only
* shadows must NOT appear in PDF

PDF styles must remove preview-only decoration.

---

# Preview vs PDF Rule (Critical)

Preview and PDF must use the **same layout views**.

Preview must include:

```
profile.styles_view
profile.pages_view
```

PDF must include:

```
profile.pdf_styles_view
profile.pages_view
```

The pages view must be identical.

Never maintain separate page structures.

This prevents layout drift.

---

# Folder Structure Standard

All print views must follow this structure:

```
resources/views/{module}/print/
├─ index.blade.php
├─ pdf.blade.php
├─ partials/
│   ├─ controls.blade.php
│   ├─ meta.blade.php
│   ├─ table.blade.php
│   ├─ header.blade.php
│   ├─ footer.blade.php
│   └─ base-styles.blade.php
└─ paper/
    ├─ a4-portrait/
    │   ├─ pages.blade.php
    │   ├─ styles.blade.php
    │   └─ pdf-styles.blade.php
    ├─ letter-portrait/
    └─ future paper profiles
```

Folder naming must match paper codes.

---

# Shared vs Paper Specific Files

Shared partials describe the report.

Paper folders describe layout.

Shared:

* controls.blade.php
* meta.blade.php
* table.blade.php
* header.blade.php
* footer.blade.php
* base-styles.blade.php

Paper specific:

* pages.blade.php
* styles.blade.php
* pdf-styles.blade.php

Rule:

Report content must NEVER move into paper folders.

---

# Pagination Rules

Pagination must live inside:

```
paper/{profile}/pages.blade.php
```

Never paginate in:

* controllers
* services
* report builders

Pagination is a layout concern.

Paper profiles control:

* rows per page
* first page spacing
* later page spacing
* footer space

This allows tuning per paper.

---

# Header/Footer Rules

Header and footer must NOT be hardcoded.

They must come from the resolved paper profile.

Example:

```
$paperProfile['header_image_web']
$paperProfile['footer_image_web']
```

PDF should use:

```
$paperProfile['header_image_pdf']
$paperProfile['footer_image_pdf']
```

Modules may override platform defaults.

If no override exists:

Platform paper defaults must be used.

---

# Controls Rules

Controls must live in:

```
partials/controls.blade.php
```

Controls must include:

* report filters
* paper selector (if multiple supported)
* preview button
* PDF button
* reset button

Paper selector must:

* use printable allowed papers
* default to printable default paper
* persist across preview
* persist to PDF download

---

# Paper Selector Rules

Paper selector values must match:

* config paper codes
* folder names
* request values

Example codes:

* a4-portrait
* a4-landscape
* letter-portrait
* letter-landscape
* long-bond-portrait
* long-bond-landscape

Never use labels as identifiers.

Always use stable codes.

---

# Base Styles Rule

Base styles must be shared.

Located in:

```
partials/base-styles.blade.php
```

Base styles should define:

* page width
* page height
* base typography
* table defaults
* print safe colors

Paper profiles may extend styling.

They must NOT redefine core layout rules.

---

# Paper Profile Extension Rule

Adding a new paper must require only:

1 Create paper folder
2 Add config entry
3 Tune rows per page

Never duplicate reports.

Example process:

```
paper/long-bond-portrait/
├─ pages.blade.php
├─ styles.blade.php
└─ pdf-styles.blade.php
```

Add config.
Done.

---

# Things That Must Never Happen

Never do these:

Do NOT duplicate report blades per paper

Do NOT hardcode A4 sizes

Do NOT put layout logic in controllers

Do NOT paginate in services

Do NOT use different preview and PDF layouts

Do NOT hardcode header/footer paths

Do NOT mix report data with layout decisions

Do NOT create separate reports per paper size

Do NOT hardcode module identity inside views

---

# Workspace UX Rules

Workspace must remain simple.

User flow:

Select filters
Select paper
Preview report
Download PDF

Preview must always reflect selected paper.

PDF must always match preview.

---

# Default Paper Rule

Each printable must define a default paper.

Example:

```
default_paper => a4-portrait
```

If no paper selected:

System must fallback to printable default.

If invalid paper selected:

System must fallback safely.

Never crash.

---

# Final Architecture Summary

Workspace:

One workspace
Many paper profiles

Views:

Shared report content
Paper specific layouts

UX:

Paper selector
Dynamic preview
Identical PDF layout

System:

Config driven
Scalable
Module extensible
ownership aware

---

# Final Rule

Core printing must always follow:

**One Report
Many Paper Profiles**

Never the opposite.

This ensures the Core System remains maintainable as more reports and paper formats are introduced.
