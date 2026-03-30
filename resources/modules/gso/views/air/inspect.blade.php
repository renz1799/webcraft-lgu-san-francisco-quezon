@extends('layouts.master')

@php
    $airViewer = auth()->user();
    $airAuthorizer = app(\App\Core\Support\AdminContextAuthorizer::class);
    $canManageAir = $airAuthorizer->allowsAnyPermission($airViewer, [
        'air.create',
        'air.update',
        'air.inspect',
        'air.manage_items',
        'air.manage_files',
        'air.promote_inventory',
        'air.finalize_inspection',
        'air.reopen_inspection',
        'air.archive',
        'air.restore',
    ]);
    $canPromoteInventory = $airAuthorizer->allowsAnyPermission($airViewer, [
        'air.promote_inventory',
        'inventory_items.create',
        'inventory_items.update',
        'inventory_items.import_from_inspection',
    ]);
    $canEditInspection = $canManageAir && (bool) ($air['can_edit_inspection'] ?? false);
    $canViewInspection = (bool) ($air['can_view_inspection'] ?? false);
    $isArchived = (bool) ($air['is_archived'] ?? false);
    $status = (string) ($air['status'] ?? '');
    $continuationNo = max(1, (int) ($air['continuation_no'] ?? 1));
    $canPrintAir = ! $isArchived
        && in_array($status, ['submitted', 'in_progress', 'inspected'], true)
        && $airAuthorizer->allowsAnyPermission($airViewer, ['air.print', 'air.view', 'air.update']);
    $canReopenInspection = $airAuthorizer->allowsPermission($airViewer, 'air.reopen_inspection')
        && (bool) ($air['can_reopen_inspection'] ?? false);
    $canCreateFollowUpAir = $canManageAir && (bool) ($air['can_create_follow_up_air'] ?? false);
    $latestFollowUpAir = is_array($air['latest_follow_up_air'] ?? null)
        ? $air['latest_follow_up_air']
        : null;
@endphp

@section('styles')
    <style>
        #gso-air-inspect-page {
            --gso-air-inspection-sticky-top: 0.75rem;
            --gso-air-inspection-toolbar-height: 0px;
            --gso-air-inspection-tabs-height: 0px;
        }

        .gso-air-inspection-shell {
            width: 100%;
            max-width: 84rem;
            margin: 1rem auto 0;
        }

        .gso-air-inspection-grid {
            display: grid;
            grid-template-columns: repeat(1, minmax(0, 1fr));
            gap: 1rem;
            align-items: start;
        }

        .gso-air-inspection-items {
            display: grid;
            grid-template-columns: repeat(1, minmax(0, 1fr));
            gap: 1rem;
        }

        .gso-air-inspection-toolbar-shell {
            position: sticky;
            top: var(--gso-air-inspection-sticky-top);
            z-index: 40;
            padding: 1rem;
            border: 1px solid rgba(148, 163, 184, 0.18);
            border-radius: 1rem;
            background: rgba(255, 255, 255, 0.92);
            backdrop-filter: blur(18px);
            box-shadow: 0 20px 45px rgba(15, 23, 42, 0.08);
        }

        .dark .gso-air-inspection-toolbar-shell {
            background: rgba(15, 23, 42, 0.92);
            border-color: rgba(148, 163, 184, 0.14);
        }

        .gso-air-inspection-toolbar-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
            align-items: center;
            justify-content: flex-start;
        }

        .gso-air-inspection-tabs {
            display: none;
        }

        .gso-air-inspection-tab-button {
            border: 1px solid rgba(148, 163, 184, 0.24);
            border-radius: 999px;
            padding: 0.65rem 0.9rem;
            font-size: 0.75rem;
            font-weight: 600;
            line-height: 1.1;
            color: #475569;
            background: rgba(248, 250, 252, 0.95);
            transition: all 0.18s ease;
        }

        .gso-air-inspection-tab-button.is-active {
            color: #fff;
            border-color: rgba(59, 130, 246, 0.88);
            background: linear-gradient(135deg, #2563eb, #1d4ed8);
            box-shadow: 0 14px 28px rgba(37, 99, 235, 0.22);
        }

        .dark .gso-air-inspection-tab-button {
            color: #cbd5e1;
            background: rgba(15, 23, 42, 0.92);
            border-color: rgba(148, 163, 184, 0.16);
        }

        .dark .gso-air-inspection-tab-button.is-active {
            color: #fff;
        }

        .gso-air-inspection-section {
            min-width: 0;
        }

        .gso-air-inspection-section-header {
            transition: background 0.18s ease;
        }

        .gso-air-inspection-item-card,
        .gso-air-inspection-unit-card {
            position: relative;
        }

        .gso-air-inspection-row-chip {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 2rem;
            height: 2rem;
            border-radius: 999px;
            padding: 0 0.625rem;
            font-size: 0.75rem;
            font-weight: 700;
            color: #1d4ed8;
            background: rgba(37, 99, 235, 0.12);
            border: 1px solid rgba(37, 99, 235, 0.16);
        }

        .dark .gso-air-inspection-row-chip {
            color: #bfdbfe;
            background: rgba(37, 99, 235, 0.18);
            border-color: rgba(96, 165, 250, 0.18);
        }

        .gso-air-inspection-modal-panel {
            width: 100%;
            max-height: calc(100vh - 48px);
            overflow: auto;
            border-radius: 24px;
            background: #fff;
            box-shadow: 0 28px 64px rgba(15, 23, 42, 0.24);
        }

        .dark .gso-air-inspection-modal-panel {
            background: #0f172a;
        }

        .gso-air-inspection-modal-panel .ti-modal-header {
            position: sticky;
            top: 0;
            z-index: 10;
            background: inherit;
            border-bottom: 1px solid rgba(148, 163, 184, 0.18);
        }

        .dark .gso-air-inspection-modal-panel .ti-modal-header {
            border-bottom-color: rgba(148, 163, 184, 0.12);
        }

        .gso-air-inspection-modal-panel--units {
            width: min(980px, 100%);
        }

        .gso-air-inspection-unit-grid,
        .gso-air-inspection-file-grid,
        .gso-air-inspection-component-grid {
            display: grid;
            grid-template-columns: repeat(1, minmax(0, 1fr));
            gap: 16px;
        }

        .gso-air-inspection-unit-grid--single {
            grid-template-columns: minmax(0, 1fr);
        }

        .gso-air-inspection-unit-summary {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
            gap: 12px;
        }

        .gso-air-inspection-unit-summary-card {
            border: 1px solid rgba(148, 163, 184, 0.18);
            border-radius: 16px;
            background: rgba(248, 250, 252, 0.88);
            padding: 12px 14px;
        }

        .dark .gso-air-inspection-unit-summary-card {
            background: rgba(15, 23, 42, 0.72);
            border-color: rgba(148, 163, 184, 0.14);
        }

        .gso-air-inspection-unit-summary-label {
            display: block;
            font-size: 11px;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }

        .gso-air-inspection-unit-summary-value {
            display: block;
            margin-top: 6px;
            font-size: 20px;
            font-weight: 700;
            color: #0f172a;
        }

        .dark .gso-air-inspection-unit-summary-value {
            color: #f8fafc;
        }

        .gso-air-inspection-unit-workspace-note {
            margin-top: 12px;
            font-size: 12px;
            color: #64748b;
        }

        .gso-air-inspection-empty-state {
            border: 1px dashed rgba(148, 163, 184, 0.45);
            border-radius: 18px;
            padding: 24px;
            text-align: center;
            background: rgba(248, 250, 252, 0.72);
        }

        .dark .gso-air-inspection-empty-state {
            background: rgba(15, 23, 42, 0.5);
        }

        .gso-air-inspection-file-preview {
            display: block;
            width: 100%;
            height: 200px;
            object-fit: cover;
            background: linear-gradient(135deg, rgba(59, 130, 246, 0.08), rgba(15, 23, 42, 0.12));
        }

        .gso-air-inspection-file-fallback {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 100%;
            height: 200px;
            padding: 16px;
            text-align: center;
            color: #64748b;
            background: linear-gradient(135deg, rgba(148, 163, 184, 0.08), rgba(148, 163, 184, 0.18));
        }

        @media (max-width: 1023.98px) {
            .gso-air-inspection-tabs {
                display: flex;
                flex-wrap: wrap;
                gap: 0.5rem;
                position: sticky;
                top: calc(
                    var(--gso-air-inspection-sticky-top) +
                    var(--gso-air-inspection-toolbar-height) +
                    0.5rem
                );
                z-index: 35;
                margin-bottom: 1rem;
                padding: 0.85rem;
                border: 1px solid rgba(148, 163, 184, 0.16);
                border-radius: 1rem;
                background: rgba(255, 255, 255, 0.94);
                backdrop-filter: blur(18px);
                box-shadow: 0 18px 35px rgba(15, 23, 42, 0.08);
            }

            .dark .gso-air-inspection-tabs {
                background: rgba(15, 23, 42, 0.94);
                border-color: rgba(148, 163, 184, 0.14);
            }

            .gso-air-inspection-section.is-tablet-hidden {
                display: none;
            }

            .gso-air-inspection-section-header {
                position: sticky;
                top: calc(
                    var(--gso-air-inspection-sticky-top) +
                    var(--gso-air-inspection-toolbar-height) +
                    var(--gso-air-inspection-tabs-height) +
                    0.75rem
                );
                z-index: 20;
                background: #fff;
                border-bottom: 1px solid rgba(148, 163, 184, 0.18);
            }

            .dark .gso-air-inspection-section-header {
                background: #0f172a;
                border-bottom-color: rgba(148, 163, 184, 0.12);
            }
        }

        @media (min-width: 1024px) {
            .gso-air-inspection-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .gso-air-inspection-section--receiving {
                grid-column: 1;
            }

            .gso-air-inspection-section--summary {
                grid-column: 1 / -1;
                grid-row: 2;
            }

            .gso-air-inspection-section--items {
                grid-column: 2;
                grid-row: 1;
            }

            .gso-air-inspection-unit-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .gso-air-inspection-unit-grid--single {
                grid-template-columns: minmax(0, 1fr);
            }

            .gso-air-inspection-file-grid {
                grid-template-columns: repeat(3, minmax(0, 1fr));
            }
        }
    </style>
@endsection

@section('content')
    <div id="gso-air-inspect-page">
        <div id="gsoAirInspectionToolbar" class="page-header md:flex items-start justify-between gap-4 gso-air-inspection-toolbar-shell">
            <div>
                <h3 class="!text-defaulttextcolor dark:!text-defaulttextcolor/70 dark:text-white text-[1.125rem] font-semibold">
                    AIR Inspection
                </h3>
                <p class="text-xs text-[#8c9097] mt-1">
                    Record delivery and inspection results. Saving updates the current AIR without reloading the workspace.
                </p>
                <div class="text-xs text-[#8c9097] mt-2">
                    PO: <b>{{ $air['po_number'] ?? 'N/A' }}</b>
                    <span>&middot;</span>
                    Status: <b>{{ strtoupper($status !== '' ? $status : 'submitted') }}</b>
                    @if(!empty($air['air_number']))
                        <span>&middot;</span>
                        AIR No.: <b>{{ $air['air_number'] }}</b>
                    @endif
                </div>
                @if($continuationNo > 1)
                    <div class="text-xs text-info mt-1">
                        Follow-up AIR #{{ $continuationNo }} for unresolved items carried over from an earlier partial inspection.
                    </div>
                @endif
            </div>

            <div class="mt-3 md:mt-0 gso-air-inspection-toolbar-actions">
                @if($canEditInspection && in_array($status, ['submitted', 'in_progress'], true))
                    <button type="button" id="gsoAirInspectionSaveBtn" class="ti-btn ti-btn-primary">
                        Save Inspection
                    </button>
                    <button type="button" id="gsoAirInspectionFinalizeBtn" class="ti-btn ti-btn-success">
                        Finalize
                    </button>
                @endif

                @if($status === 'inspected' && $canReopenInspection)
                    <button type="button" id="gsoAirInspectionReopenBtn" class="ti-btn ti-btn-danger">
                        Reopen Inspection
                    </button>
                @endif

                @if($status === 'inspected' && $latestFollowUpAir)
                    <a
                        href="{{ in_array((string) ($latestFollowUpAir['status'] ?? ''), ['submitted', 'in_progress', 'inspected'], true)
                            ? route('gso.air.inspect', ['air' => $latestFollowUpAir['id'] ?? ''])
                            : route('gso.air.edit', ['air' => $latestFollowUpAir['id'] ?? '']) }}"
                        class="ti-btn ti-btn-primary"
                    >
                        View Follow-up AIR
                    </a>
                @elseif($status === 'inspected' && $canCreateFollowUpAir)
                    <button type="button" id="gsoAirInspectionFollowUpBtn" class="ti-btn ti-btn-primary">
                        Create Follow-up AIR
                    </button>
                @endif

                @if($status === 'inspected' && $hasConsumableItems)
                    <button
                        type="button"
                        id="airGenerateRisBtn"
                        class="ti-btn ti-btn-secondary"
                        data-endpoint="{{ $existingRis
                            ? route('gso.ris.edit', ['ris' => $existingRis->id])
                            : route('gso.air.ris.generate', ['air' => $air['id'] ?? '']) }}"
                        data-mode="{{ $existingRis ? 'view' : 'generate' }}"
                    >
                        {{ $existingRis ? 'View RIS' : 'Generate RIS' }}
                    </button>
                @endif

                @if($canPromoteInventory)
                    <button
                        type="button"
                        id="gsoAirInspectionPromoteBtn"
                        class="ti-btn ti-btn-success"
                        @disabled($status !== 'inspected')
                    >
                        Promote to Inventory
                    </button>
                @endif

                @if($canPrintAir && !empty($air['id']))
                    <a
                        href="{{ route('gso.air.print', ['air' => $air['id'], 'preview' => 1]) }}"
                        class="ti-btn ti-btn-secondary"
                        target="_blank"
                        rel="noopener"
                    >
                        Print AIR
                    </a>
                @endif

                <a href="{{ route('gso.air.edit', ['air' => $air['id'] ?? '']) }}" class="ti-btn ti-btn-light">
                    Back
                </a>
            </div>

            <div id="gsoAirInspectionFinalizeHint" class="mt-2 text-[11px] text-warning md:text-right"></div>
        </div>

        @if($isArchived)
            <div class="mb-4 rounded border border-warning bg-warning/10 px-4 py-3 text-sm text-warning">
                This AIR is archived. Restore it from the AIR edit page before continuing inspection work.
            </div>
        @elseif(! $canViewInspection)
            <div class="mb-4 rounded border border-warning bg-warning/10 px-4 py-3 text-sm text-warning">
                This AIR is not yet in the inspection workspace stage. Submit the draft first, then continue inspection here.
            </div>
        @elseif(! $canEditInspection)
            <div class="mb-4 rounded border border-primary bg-primary/10 px-4 py-3 text-sm text-primary">
                This AIR inspection is currently read-only. Submitted and in-progress AIR records can be edited here; inspected AIR records stay visible for continuity.
            </div>
        @endif

        <div class="gso-air-inspection-shell">
            <div id="gsoAirInspectionTabs" class="gso-air-inspection-tabs" role="tablist" aria-label="Inspection sections">
                <button
                    type="button"
                    class="gso-air-inspection-tab-button"
                    data-air-inspection-tab="receiving"
                    aria-pressed="true"
                >
                    Receiving / Invoice Details
                </button>
                <button
                    type="button"
                    class="gso-air-inspection-tab-button"
                    data-air-inspection-tab="summary"
                    aria-pressed="false"
                >
                    Inspection Summary
                </button>
                <button
                    type="button"
                    class="gso-air-inspection-tab-button"
                    data-air-inspection-tab="items"
                    aria-pressed="false"
                >
                    Items Delivered
                </button>
            </div>

            <div class="gso-air-inspection-grid">
                <div class="gso-air-inspection-section gso-air-inspection-section--receiving" data-air-inspection-panel="receiving">
                    <div class="box">
                        <div class="box-header gso-air-inspection-section-header">
                            <h5 class="box-title">Receiving / Invoice Details</h5>
                        </div>

                        <div class="box-body space-y-4">
                            <div id="gsoAirInspectionFormError" class="hidden rounded bg-danger/10 p-3 text-sm text-danger"></div>

                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div>
                                    <label class="ti-form-label">Invoice / DR / SI No. <span class="text-danger">*</span></label>
                                    <input id="gsoAirInspectionInvoiceNumber" type="text" class="ti-form-input w-full" value="{{ $air['invoice_number'] ?? '' }}" @disabled(! $canEditInspection)>
                                </div>

                                <div>
                                    <label class="ti-form-label">Invoice Date <span class="text-danger">*</span></label>
                                    <input id="gsoAirInspectionInvoiceDate" type="date" class="ti-form-input w-full" value="{{ $air['invoice_date'] ?? '' }}" @disabled(! $canEditInspection)>
                                </div>

                                <div>
                                    <label class="ti-form-label">Date Received <span class="text-danger">*</span></label>
                                    <input id="gsoAirInspectionDateReceived" type="date" class="ti-form-input w-full" value="{{ $air['date_received'] ?? '' }}" @disabled(! $canEditInspection)>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div>
                                    <label class="ti-form-label">Received Completeness <span class="text-danger">*</span></label>
                                    <select id="gsoAirInspectionReceivedCompleteness" class="ti-form-select w-full" @disabled(! $canEditInspection)>
                                        <option value="">Select completeness</option>
                                        <option value="complete" @selected(($air['received_completeness'] ?? '') === 'complete')>Complete</option>
                                        <option value="partial" @selected(($air['received_completeness'] ?? '') === 'partial')>Partial</option>
                                    </select>
                                    <div id="gsoAirInspectionCompletenessHint" class="mt-1 text-[11px] text-[#8c9097] dark:text-white/50"></div>
                                </div>

                                <div class="md:col-span-2">
                                    <label class="ti-form-label">Received Notes</label>
                                    <textarea id="gsoAirInspectionReceivedNotes" class="ti-form-input w-full" rows="2" @disabled(! $canEditInspection)>{{ $air['received_notes'] ?? '' }}</textarea>
                                </div>
                            </div>

                            <div class="text-xs text-[#8c9097]">
                                Tip: save invoice, receiving, and item inspection updates together. Encoded unit rows and unit evidence stay under each item's Units workspace.
                            </div>
                        </div>
                    </div>
                </div>

                <div class="gso-air-inspection-section gso-air-inspection-section--summary" data-air-inspection-panel="summary">
                    <div class="box">
                        <div class="box-header gso-air-inspection-section-header flex items-start justify-between gap-3">
                            <div>
                                <h5 class="box-title">Inspection Summary</h5>
                                <div class="text-xs text-[#8c9097] mt-1">
                                    Finalization is only allowed once required receiving fields are complete and unit-tracked items have matching encoded unit rows.
                                </div>
                            </div>
                            <div class="text-xs text-[#8c9097] bg-light px-3 py-2 rounded dark:bg-black/20 dark:text-white/50">
                                Rows: <b id="gsoAirInspectionItemCount">{{ count($items) }}</b>
                            </div>
                        </div>

                        <div class="box-body grid grid-cols-1 md:grid-cols-2 gap-3 text-sm">
                            <div class="flex items-center justify-between gap-3 rounded border border-defaultborder px-3 py-2">
                                <span class="text-[#8c9097]">Workflow Status</span>
                                <span id="gsoAirInspectionStatusText" class="font-medium">{{ $air['status_text'] ?? 'Unknown' }}</span>
                            </div>
                            <div class="flex items-center justify-between gap-3 rounded border border-defaultborder px-3 py-2">
                                <span class="text-[#8c9097]">Inspection Date</span>
                                <span id="gsoAirInspectionDateInspectedText" class="font-medium">{{ $air['date_inspected_text'] ?? '-' }}</span>
                            </div>
                            <div class="flex items-center justify-between gap-3 rounded border border-defaultborder px-3 py-2">
                                <span class="text-[#8c9097]">Inspection Verified</span>
                                <span id="gsoAirInspectionVerifiedText" class="font-medium">{{ $air['inspection_verified_text'] ?? 'Pending' }}</span>
                            </div>
                            <div class="flex items-center justify-between gap-3 rounded border border-defaultborder px-3 py-2">
                                <span class="text-[#8c9097]">Encoded Units</span>
                                <span id="gsoAirInspectionUnitCount" class="font-medium">{{ collect($items)->sum('units_count') }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="gso-air-inspection-section gso-air-inspection-section--items" data-air-inspection-panel="items">
                    <div class="box">
                        <div class="box-header gso-air-inspection-section-header">
                            <h5 class="box-title">Items Delivered</h5>
                        </div>

                        <div class="box-body">
                            <div class="mb-4 rounded bg-light p-3 text-sm text-[#8c9097] dark:bg-black/20 dark:text-white/50">
                                Save the receiving and quantity updates first after changing accepted quantities. Inspection units are managed against the last saved accepted quantity per AIR item.
                            </div>

                            <div id="gsoAirInspectionItems" class="gso-air-inspection-items"></div>
                        </div>
                    </div>
                </div>
            </div>

            <div id="gsoAirUnitModal" class="hs-overlay hidden ti-modal z-[70] pointer-events-none">
                <div class="hs-overlay-open:mt-7 ti-modal-box mt-0 ease-out !max-w-5xl">
                    <div class="ti-modal-content pointer-events-auto gso-air-inspection-modal-panel gso-air-inspection-modal-panel--units">
                        <div class="ti-modal-header">
                            <div>
                                <h6 id="gsoAirUnitModalTitle" class="ti-modal-title text-[1rem] font-semibold">Inspection Units</h6>
                                <p id="gsoAirUnitModalSubtitle" class="text-sm text-[#8c9097] mt-1 mb-0">
                                    Manage encoded unit rows for the selected AIR item.
                                </p>
                            </div>
                            <button
                                type="button"
                                id="gsoAirUnitModalClose"
                                class="ti-modal-close-btn"
                                data-hs-overlay="#gsoAirUnitModal"
                            >
                                <span class="sr-only">Close</span>
                                <svg class="w-3.5 h-3.5" width="8" height="8" viewBox="0 0 8 8" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M1 1L7 7M7 1L1 7" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                            </button>
                        </div>
                        <div class="ti-modal-body px-4">
                            <div id="gsoAirUnitError" class="hidden mb-3 rounded bg-danger/10 p-3 text-sm text-danger"></div>
                            <div id="gsoAirUnitNotice" class="mb-3 rounded bg-light p-3 text-sm text-[#8c9097] dark:bg-black/20 dark:text-white/50"></div>
                            <div id="gsoAirUnitRows" class="gso-air-inspection-unit-grid"></div>
                        </div>
                        <div class="ti-modal-footer !justify-end">
                            <div class="flex flex-wrap gap-2 justify-end">
                                <button
                                    type="button"
                                    id="gsoAirUnitCloseBtn"
                                    class="ti-btn ti-btn-light"
                                    data-hs-overlay="#gsoAirUnitModal"
                                >
                                    Close
                                </button>
                                <button type="button" id="gsoAirUnitSaveBtn" class="ti-btn ti-btn-primary">Save Unit Rows</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div
                id="gsoAirUnitComponentModal"
                class="hs-overlay hidden ti-modal z-[80] pointer-events-none"
                data-hs-overlay-options='{"isClosePrev": false}'
            >
                <div class="hs-overlay-open:mt-7 ti-modal-box mt-0 ease-out !max-w-6xl">
                    <div class="ti-modal-content pointer-events-auto gso-air-inspection-modal-panel">
                        <div class="ti-modal-header">
                            <div>
                                <h6 id="gsoAirUnitComponentModalTitle" class="ti-modal-title text-[1rem] font-semibold">Unit Components</h6>
                                <p id="gsoAirUnitComponentModalSubtitle" class="text-sm text-[#8c9097] mt-1 mb-0">
                                    Record the component schedule for the selected inspection unit.
                                </p>
                            </div>
                            <button
                                type="button"
                                id="gsoAirUnitComponentModalClose"
                                class="ti-modal-close-btn"
                                data-hs-overlay="#gsoAirUnitComponentModal"
                                data-hs-overlay-options='{"isClosePrev": false}'
                            >
                                <span class="sr-only">Close</span>
                                <svg class="w-3.5 h-3.5" width="8" height="8" viewBox="0 0 8 8" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M1 1L7 7M7 1L1 7" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                            </button>
                        </div>
                        <div class="ti-modal-body px-4">
                            <div id="gsoAirUnitComponentError" class="hidden mb-3 rounded bg-danger/10 p-3 text-sm text-danger"></div>
                            <div class="mb-3 rounded bg-light p-3 text-sm text-[#8c9097] dark:bg-black/20 dark:text-white/50">
                                Changes here stay staged with the unit row. Use <strong>Save Unit Rows</strong> in the inspection units modal to persist them.
                            </div>
                            <div id="gsoAirUnitComponentTemplateNote" class="hidden mb-3 rounded bg-primary/10 p-3 text-sm text-primary"></div>
                            <div id="gsoAirUnitComponentEmpty" class="hidden rounded-lg border border-dashed border-defaultborder p-6 text-center text-sm text-[#8c9097]">
                                No component rows recorded yet for this inspection unit.
                            </div>
                            <div id="gsoAirUnitComponentRows" class="gso-air-inspection-component-grid"></div>
                        </div>
                        <div class="ti-modal-footer !justify-between">
                            <div class="flex flex-wrap gap-2">
                                <button type="button" id="gsoAirUnitComponentAddRowBtn" class="ti-btn ti-btn-light">Add Component Row</button>
                            </div>
                            <button
                                type="button"
                                id="gsoAirUnitComponentCloseBtn"
                                class="ti-btn ti-btn-light"
                                data-hs-overlay="#gsoAirUnitComponentModal"
                                data-hs-overlay-options='{"isClosePrev": false}'
                            >
                                Done
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div
                id="gsoAirUnitFileModal"
                class="hs-overlay hidden ti-modal z-[90] pointer-events-none"
                data-hs-overlay-options='{"isClosePrev": false}'
            >
                <div class="hs-overlay-open:mt-7 ti-modal-box mt-0 ease-out !max-w-6xl">
                    <div class="ti-modal-content pointer-events-auto gso-air-inspection-modal-panel">
                        <div class="ti-modal-header">
                            <div>
                                <h6 id="gsoAirUnitFileModalTitle" class="ti-modal-title text-[1rem] font-semibold">Unit Images</h6>
                                <p id="gsoAirUnitFileModalSubtitle" class="text-sm text-[#8c9097] mt-1 mb-0">
                                    Review and upload image evidence for the selected inspection unit.
                                </p>
                            </div>
                            <button
                                type="button"
                                id="gsoAirUnitFileModalClose"
                                class="ti-modal-close-btn"
                                data-hs-overlay="#gsoAirUnitFileModal"
                                data-hs-overlay-options='{"isClosePrev": false}'
                            >
                                <span class="sr-only">Close</span>
                                <svg class="w-3.5 h-3.5" width="8" height="8" viewBox="0 0 8 8" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M1 1L7 7M7 1L1 7" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                            </button>
                        </div>
                        <div class="ti-modal-body px-4">
                            <div id="gsoAirUnitFileError" class="hidden mb-3 rounded bg-danger/10 p-3 text-sm text-danger"></div>
                            @if($canEditInspection)
                                <div class="mb-4 rounded-lg border border-defaultborder p-3">
                                    <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                                        <div>
                                            <div class="font-medium text-defaulttextcolor dark:text-white">Upload Unit Images</div>
                                            <div class="text-xs text-[#8c9097] mt-1">
                                                Upload inspection images such as unit photos, serial close-ups, and box images with optional captions.
                                            </div>
                                        </div>
                                        <div class="grid w-full gap-2 md:grid-cols-[minmax(0,1fr)_180px] xl:grid-cols-[minmax(0,1fr)_180px_minmax(0,1fr)_auto] xl:items-end">
                                            <div>
                                                <label class="ti-form-label !mb-1 text-xs">Images</label>
                                                <input id="gsoAirUnitFileInput" type="file" accept="image/*" multiple class="form-control w-full">
                                            </div>
                                            <div>
                                                <label class="ti-form-label !mb-1 text-xs">Type</label>
                                                <select id="gsoAirUnitFileType" class="ti-form-select w-full">
                                                    <option value="photo">Photo</option>
                                                    <option value="serial_photo">Serial Photo</option>
                                                    <option value="box_photo">Box Photo</option>
                                                    <option value="other">Other</option>
                                                </select>
                                            </div>
                                            <div>
                                                <label class="ti-form-label !mb-1 text-xs">Caption</label>
                                                <input
                                                    id="gsoAirUnitFileCaption"
                                                    type="text"
                                                    class="ti-form-input w-full"
                                                    maxlength="255"
                                                    placeholder="Optional details for this image"
                                                >
                                            </div>
                                            <button type="button" id="gsoAirUnitFileUploadBtn" class="ti-btn ti-btn-primary xl:self-end">
                                                Upload Images
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            @endif
                            <div id="gsoAirUnitFileEmpty" class="hidden rounded-lg border border-dashed border-defaultborder p-6 text-center text-sm text-[#8c9097]">
                                No unit images uploaded yet.
                            </div>
                            <div id="gsoAirUnitFileGrid" class="gso-air-inspection-file-grid"></div>
                        </div>
                        <div class="ti-modal-footer">
                            <button
                                type="button"
                                id="gsoAirUnitFileCloseBtn"
                                class="ti-btn ti-btn-light"
                                data-hs-overlay="#gsoAirUnitFileModal"
                                data-hs-overlay-options='{"isClosePrev": false}'
                            >
                                Close
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    @php
        $inspectionConfig = [
            'air' => $air,
            'items' => $items,
            'saveUrl' => route('gso.air.inspection.save', ['air' => $air['id'] ?? '']),
            'finalizeUrl' => route('gso.air.inspection.finalize', ['air' => $air['id'] ?? '']),
            'followUpCreateUrl' => route('gso.air.follow-up.create', ['air' => $air['id'] ?? '']),
            'reopenUrl' => route('gso.air.inspection.reopen', ['air' => $air['id'] ?? '']),
            'promoteEligibleUrl' => route('gso.air.inventory.eligible', ['air' => $air['id'] ?? '']),
            'promoteUrl' => route('gso.air.inventory.promote', ['air' => $air['id'] ?? '']),
            'unitsIndexUrlTemplate' => route('gso.air.inspection.units.index', ['air' => $air['id'] ?? '', 'airItem' => '__AIR_ITEM__']),
            'unitsSaveUrlTemplate' => route('gso.air.inspection.units.save', ['air' => $air['id'] ?? '', 'airItem' => '__AIR_ITEM__']),
            'unitsDestroyUrlTemplate' => route('gso.air.inspection.units.destroy', ['air' => $air['id'] ?? '', 'airItem' => '__AIR_ITEM__', 'unit' => '__UNIT__']),
            'unitFilesIndexUrlTemplate' => route('gso.air.inspection.unit-files.index', ['air' => $air['id'] ?? '', 'airItem' => '__AIR_ITEM__', 'unit' => '__UNIT__']),
            'unitFilesStoreUrlTemplate' => route('gso.air.inspection.unit-files.store', ['air' => $air['id'] ?? '', 'airItem' => '__AIR_ITEM__', 'unit' => '__UNIT__']),
            'unitFilesDestroyUrlTemplate' => route('gso.air.inspection.unit-files.destroy', ['air' => $air['id'] ?? '', 'airItem' => '__AIR_ITEM__', 'unit' => '__UNIT__', 'file' => '__FILE__']),
            'unitFilesPreviewUrlTemplate' => route('gso.air.inspection.unit-files.preview', ['air' => $air['id'] ?? '', 'airItem' => '__AIR_ITEM__', 'unit' => '__UNIT__', 'file' => '__FILE__']),
            'unitFilesPrimaryUrlTemplate' => route('gso.air.inspection.unit-files.set-primary', ['air' => $air['id'] ?? '', 'airItem' => '__AIR_ITEM__', 'unit' => '__UNIT__', 'file' => '__FILE__']),
            'editUrl' => route('gso.air.edit', ['air' => $air['id'] ?? '']),
            'indexUrl' => route('gso.air.index'),
            'csrf' => csrf_token(),
            'canManage' => $canManageAir,
            'canEditInspection' => $canEditInspection,
            'canPromoteInventory' => $canPromoteInventory,
            'conditionStatuses' => $conditionStatuses,
        ];
    @endphp
    <script>
        window.__gsoAirInspection = {!! \Illuminate\Support\Js::from($inspectionConfig) !!};
    </script>
@endpush
