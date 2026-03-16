# Print Workspace Standard

This document defines the standard page pattern for printable documents that need both:

- a left-side settings or filters panel
- a right-side live paper preview
- an optional server-generated PDF export

The Core reference implementation is the RPCPPE sample workspace:

- route: `/reports/samples/rpcppe-preview`
- controller: `app/Http/Controllers/Reports/PrintWorkspaceSampleController.php`
- preview view: `resources/views/print-workspace/rpcppe-sample.blade.php`
- PDF view: `resources/views/print-workspace/rpcppe-sample-pdf.blade.php`

## Purpose

Use this pattern when a printable document needs users to adjust values like:

- cutoff date
- office or fund filters
- signatory names and designations
- preview-only toggles before printing or downloading PDF

This keeps the printable document clean while still giving the user a practical working area beside the preview.

## Standard Building Blocks

### 1. Shared workspace styles

Include the shared workspace styles in the `<head>`:

```blade
<x-print.workspace-styles />
```

This provides the standard:

- app-like screen background
- left/right split layout
- sticky settings panel
- centered preview column
- print behavior that hides the sidebar automatically

File:

- `resources/views/components/print/workspace-styles.blade.php`

### 2. Shared workspace shell

Wrap the page in the shared workspace component:

```blade
<x-print.workspace
  sidebar-width="clamp(320px, calc(297mm * 0.30), 390px)"
  preview-width="min(294mm, calc(100vw - clamp(320px, calc(297mm * 0.30), 390px) - 160px))"
>
  <x-slot:sidebar>
    {{-- settings panel --}}
  </x-slot:sidebar>

  {{-- preview pages --}}
</x-print.workspace>
```

Props:

- `sidebar-width`
  - controls the left panel width
  - should stay visually proportional to the paper width
- `preview-width`
  - should normally match the real paper width being previewed

File:

- `resources/views/components/print/workspace.blade.php`

### 3. Shared settings panel shell

Use the shared panel component inside the sidebar:

```blade
<x-print.panel
  kicker="Reports"
  title="RPCPPE Preview"
  copy="Set the report window and signatories here, then review the printable document on the right."
>
  {{-- module-specific controls --}}
</x-print.panel>
```

File:

- `resources/views/components/print/panel.blade.php`

## Preview Rules

The screen workspace is the authoring shell. The paper preview itself should still follow page-level rules:

- keep the document inside a dedicated page container
- keep page numbers inside the paper preview, not in the sidebar
- keep filters and buttons out of the document itself
- keep printable layout decisions in page partials, not in the controller
- prefer mock or DTO-style data shaping in the controller, not Blade condition sprawl

For this Core sample:

- page partial: `resources/views/print-workspace/partials/rpcppe-sample-pages.blade.php`
- item grid partial: `resources/views/print-workspace/partials/rpcppe-sample-items.blade.php`
- page styles: `resources/views/print-workspace/partials/rpcppe-sample-styles.blade.php`

## Pagination Tuning Rule

Keep pagination tuning close to the page partial so it is easy to adjust without reopening service/controller code.

Example:

```php
$rowsPerPage = 11;
$blankRowCutoff = 8;
```

Meaning:

- `rowsPerPage`
  - maximum number of filled records a page should try to render
- `blankRowCutoff`
  - only pad blank rows when the page has fewer than this number of real rows

This lets the team tune report density quickly while keeping the behavior obvious.

## PDF Export Rule

Keep the preview workspace and final PDF export as separate concerns.

- preview page
  - optimized for interactive setup
  - may still depend on browser rendering behavior
- PDF export
  - optimized for stable output
  - should reuse the same shaped report data and page partials when possible

For the Core sample:

- preview route: `/reports/samples/rpcppe-preview`
- PDF route: `/reports/samples/rpcppe-preview/pdf`

The current sample uses a Chrome-based headless print command from the controller so the final exported PDF does not depend on the user manually tuning browser print settings.

## Implementation Checklist

When building a new print workspace:

1. Create the printable page view.
2. Add `<x-print.workspace-styles />` in the `<head>`.
3. Wrap the body content with `<x-print.workspace>`.
4. Put form controls and actions in the `sidebar` slot.
5. Keep the actual paper pages in the default slot.
6. Keep page-density knobs in the page partial itself.
7. Add a dedicated PDF endpoint when the document is expected to be archived, shared, or printed formally.
8. Reuse shared panel/workspace components instead of recreating the shell per module.

## Decision Rule

Use the print workspace standard when the page is both:

- interactive on screen
- intended to print or export as a formal document

If a document has no settings panel and only needs a simple print view, a single-purpose printable Blade is still acceptable.
