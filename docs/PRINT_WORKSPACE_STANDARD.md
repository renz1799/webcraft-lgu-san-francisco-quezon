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
* last_page_grid_rows (optional)
* description_chars_per_line (optional)

Portrait print workspaces created or refactored in the platform should register this default paper set:

* a4-portrait
* letter-portrait
* legal-portrait

This should be treated as the standard baseline for new printable modules and print refactors.

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

Greedy chunking should be avoided for multi-page reports when it creates an underfilled tail page.

Default pagination should be sequential fill-first:

* fill page 1 to its configured capacity
* fill later pages to their configured capacity
* leave spare table space only on the last page

If a form needs the last page to keep a taller table before a signature or acceptance block:

Use `last_page_grid_rows` in the printable paper profile.

This value represents the total visible table rows to reserve on the last page before the trailing section.

Blank rows are then added automatically based on however many real rows landed on that last page.

When data rows can wrap to multiple visual lines, pagination should use estimated line units instead of assuming every row consumes exactly one slot.

Use `description_chars_per_line` to tune the wrap estimate for the main text column of that paper profile.

If a row has more than one text column that can wrap, estimate against the tallest printable cell for that row instead of only counting one column.

This helps keep:

* one-line rows dense
* wrapped rows from overflowing the page
* filler rows mostly limited to the last page

Continuation labels should live in page chrome such as:

* header notes
* footer notes
* page metadata areas

They should not consume table rows unless the printed form explicitly requires them inside the table body.

For multi-page reports, continuation chrome is required:

* pages after page 1 should show `Continuation from Page x`
* pages before the last page should show `Continued on Page x`

These notes should remain visible after refactors and when creating new print layouts.

Balanced redistribution across earlier pages should be treated as an exception, not the default.

Use it only when a specific printed form explicitly benefits from that layout.

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

Header and footer images should render full bleed across the page width.

Do not wrap header/footer images in side padding containers.

If page numbers or metadata need padding:

Place that padded content in a separate block above or below the image.

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

Controls may also expose printable layout settings when the report supports user-tunable pagination.

Typical layout settings:

* rows_per_page
* grid_rows
* last_page_grid_rows
* description_chars_per_line

Layout settings must:

* default from the resolved paper profile
* stay optional so the report still works with config defaults
* persist across preview
* persist to PDF download
* include concise hover or focus help text when a numeric control may not be self-explanatory

Preview stats should prefer compact text rows instead of readonly input boxes when the sidebar already contains multiple tuning fields.

When JavaScript is available, `Update Preview` should refresh the print workspace in place instead of doing a full page reload.

In-place preview refresh must:

* update the rendered preview pages
* update dependent sidebar stats and control defaults returned by the server
* keep the current scroll context stable so the user does not lose the preview position
* keep the URL query string in sync with the active preview state
* replace paper-specific head styles when a different paper profile changes the preview CSS

Paper selector must:

* use printable allowed papers
* default to printable default paper
* persist across preview
* persist to PDF download
* include `a4-portrait`, `letter-portrait`, and `legal-portrait` for standard portrait print workspaces

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
