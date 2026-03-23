@extends('layouts.master')

@php
    $canManageAir = auth()->user()?->hasAnyRole(['Administrator', 'admin'])
        || auth()->user()?->can('modify AIR');
    $canPromoteInventory = auth()->user()?->hasAnyRole(['Administrator', 'admin'])
        || auth()->user()?->can('modify AIR')
        || auth()->user()?->can('modify Inventory Items');
    $canEditInspection = $canManageAir && (bool) ($air['can_edit_inspection'] ?? false);
    $canViewInspection = (bool) ($air['can_view_inspection'] ?? false);
    $isArchived = (bool) ($air['is_archived'] ?? false);
    $status = (string) ($air['status'] ?? '');
    $continuationNo = max(1, (int) ($air['continuation_no'] ?? 1));
    $canPrintAir = ! $isArchived && in_array($status, ['submitted', 'in_progress', 'inspected'], true);
@endphp

@section('styles')
    <style>
        .gso-air-inspection-shell {
            width: 100%;
            max-width: 84rem;
            margin: 0 auto;
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

        .gso-air-inspection-modal {
            position: fixed;
            inset: 0;
            z-index: 60;
            display: none;
            align-items: center;
            justify-content: center;
            padding: 24px;
            background: rgba(15, 23, 42, 0.58);
            backdrop-filter: blur(4px);
        }

        .gso-air-inspection-modal.is-open {
            display: flex;
        }

        .gso-air-inspection-modal-panel {
            width: min(1120px, 100%);
            max-height: calc(100vh - 48px);
            overflow: auto;
            border-radius: 24px;
            background: #fff;
            box-shadow: 0 28px 64px rgba(15, 23, 42, 0.24);
        }

        .dark .gso-air-inspection-modal-panel {
            background: #0f172a;
        }

        .gso-air-inspection-unit-grid,
        .gso-air-inspection-file-grid,
        .gso-air-inspection-component-grid {
            display: grid;
            grid-template-columns: repeat(1, minmax(0, 1fr));
            gap: 16px;
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

        @media (min-width: 1024px) {
            .gso-air-inspection-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .gso-air-inspection-unit-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .gso-air-inspection-file-grid {
                grid-template-columns: repeat(3, minmax(0, 1fr));
            }
        }
    </style>
@endsection

@section('content')
    <div id="gso-air-inspect-page">
        <div class="page-header md:flex items-start justify-between gap-4">
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

            <div class="mt-3 md:mt-0 flex flex-wrap items-center gap-2">
                @if($canEditInspection && in_array($status, ['submitted', 'in_progress'], true))
                    <button type="button" id="gsoAirInspectionSaveBtn" class="ti-btn ti-btn-primary">
                        Save Inspection
                    </button>
                    <button type="button" id="gsoAirInspectionFinalizeBtn" class="ti-btn ti-btn-success">
                        Finalize
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
            <div class="gso-air-inspection-grid">
                <div class="space-y-4">
                    <div class="box">
                        <div class="box-header">
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

                    <div class="box">
                        <div class="box-header flex items-start justify-between gap-3">
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

                <div>
                    <div class="box">
                        <div class="box-header">
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

            <div id="gsoAirUnitModal" class="gso-air-inspection-modal">
                <div class="gso-air-inspection-modal-panel">
                    <div class="ti-modal-header">
                        <div>
                            <h6 id="gsoAirUnitModalTitle" class="modal-title text-[1rem] font-semibold">Inspection Units</h6>
                            <p id="gsoAirUnitModalSubtitle" class="text-sm text-[#8c9097] mt-1 mb-0">
                                Manage encoded unit rows for the selected AIR item.
                            </p>
                        </div>
                        <button type="button" id="gsoAirUnitModalClose" class="!text-[1rem] !font-semibold !text-defaulttextcolor">
                            <span class="sr-only">Close</span>
                            <i class="ri-close-line"></i>
                        </button>
                    </div>
                    <div class="ti-modal-body px-4">
                        <div id="gsoAirUnitError" class="hidden mb-3 rounded bg-danger/10 p-3 text-sm text-danger"></div>
                        <div id="gsoAirUnitNotice" class="mb-3 rounded bg-light p-3 text-sm text-[#8c9097] dark:bg-black/20 dark:text-white/50"></div>
                        <div id="gsoAirUnitRows" class="gso-air-inspection-unit-grid"></div>
                    </div>
                    <div class="ti-modal-footer !justify-between">
                        <button type="button" id="gsoAirUnitAddRowBtn" class="ti-btn ti-btn-light">Add Unit Row</button>
                        <div class="flex flex-wrap gap-2 justify-end">
                            <button type="button" id="gsoAirUnitCloseBtn" class="ti-btn ti-btn-light">Close</button>
                            <button type="button" id="gsoAirUnitSaveBtn" class="ti-btn ti-btn-primary">Save Unit Rows</button>
                        </div>
                    </div>
                </div>
            </div>

            <div id="gsoAirUnitComponentModal" class="gso-air-inspection-modal">
                <div class="gso-air-inspection-modal-panel">
                    <div class="ti-modal-header">
                        <div>
                            <h6 id="gsoAirUnitComponentModalTitle" class="modal-title text-[1rem] font-semibold">Unit Components</h6>
                            <p id="gsoAirUnitComponentModalSubtitle" class="text-sm text-[#8c9097] mt-1 mb-0">
                                Record the component schedule for the selected inspection unit.
                            </p>
                        </div>
                        <button type="button" id="gsoAirUnitComponentModalClose" class="!text-[1rem] !font-semibold !text-defaulttextcolor">
                            <span class="sr-only">Close</span>
                            <i class="ri-close-line"></i>
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
                            <button type="button" id="gsoAirUnitComponentUseDefaultsBtn" class="ti-btn ti-btn-light hidden">Use Item Template</button>
                            <button type="button" id="gsoAirUnitComponentAddRowBtn" class="ti-btn ti-btn-light">Add Component Row</button>
                        </div>
                        <button type="button" id="gsoAirUnitComponentCloseBtn" class="ti-btn ti-btn-light">Done</button>
                    </div>
                </div>
            </div>

            <div id="gsoAirUnitFileModal" class="gso-air-inspection-modal">
                <div class="gso-air-inspection-modal-panel">
                    <div class="ti-modal-header">
                        <div>
                            <h6 id="gsoAirUnitFileModalTitle" class="modal-title text-[1rem] font-semibold">Unit Files</h6>
                            <p id="gsoAirUnitFileModalSubtitle" class="text-sm text-[#8c9097] mt-1 mb-0">
                                Review and upload evidence for the selected inspection unit.
                            </p>
                        </div>
                        <button type="button" id="gsoAirUnitFileModalClose" class="!text-[1rem] !font-semibold !text-defaulttextcolor">
                            <span class="sr-only">Close</span>
                            <i class="ri-close-line"></i>
                        </button>
                    </div>
                    <div class="ti-modal-body px-4">
                        <div id="gsoAirUnitFileError" class="hidden mb-3 rounded bg-danger/10 p-3 text-sm text-danger"></div>
                        @if($canEditInspection)
                            <div class="mb-4 rounded-lg border border-defaultborder p-3">
                                <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                                    <div>
                                        <div class="font-medium text-defaulttextcolor dark:text-white">Upload Unit Evidence</div>
                                        <div class="text-xs text-[#8c9097] mt-1">
                                            Images and PDFs are stored in the configured AIR unit files Google Drive folder.
                                        </div>
                                    </div>
                                    <div class="flex flex-col gap-2 sm:flex-row sm:items-center">
                                        <input id="gsoAirUnitFileInput" type="file" accept="image/*,.pdf" multiple class="form-control w-full sm:w-[320px]">
                                        <button type="button" id="gsoAirUnitFileUploadBtn" class="ti-btn ti-btn-primary">
                                            Upload Files
                                        </button>
                                    </div>
                                </div>
                            </div>
                        @endif
                        <div id="gsoAirUnitFileEmpty" class="hidden rounded-lg border border-dashed border-defaultborder p-6 text-center text-sm text-[#8c9097]">
                            No unit files uploaded yet.
                        </div>
                        <div id="gsoAirUnitFileGrid" class="gso-air-inspection-file-grid"></div>
                    </div>
                    <div class="ti-modal-footer">
                        <button type="button" id="gsoAirUnitFileCloseBtn" class="ti-btn ti-btn-light">Close</button>
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
