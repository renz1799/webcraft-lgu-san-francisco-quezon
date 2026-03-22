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
@endphp

@section('styles')
    <style>
        .gso-air-inspection-layout {
            display: grid;
            grid-template-columns: repeat(1, minmax(0, 1fr));
            gap: 16px;
        }

        .gso-air-inspection-grid {
            display: grid;
            grid-template-columns: repeat(1, minmax(0, 1fr));
            gap: 16px;
        }

        .gso-air-inspection-card {
            border: 1px solid rgba(148, 163, 184, 0.18);
            border-radius: 18px;
            background: rgba(255, 255, 255, 0.92);
            box-shadow: 0 12px 28px rgba(15, 23, 42, 0.06);
        }

        .dark .gso-air-inspection-card {
            background: rgba(15, 23, 42, 0.88);
            border-color: rgba(148, 163, 184, 0.16);
        }

        .gso-air-inspection-items {
            display: grid;
            grid-template-columns: repeat(1, minmax(0, 1fr));
            gap: 16px;
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
            .gso-air-inspection-layout {
                grid-template-columns: minmax(0, 1.7fr) minmax(320px, 1fr);
            }

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
    <div class="block justify-between page-header md:flex">
        <div>
            <h3 class="!text-defaulttextcolor dark:!text-defaulttextcolor/70 dark:text-white text-[1.125rem] font-semibold">
                {{ $air['label'] ?? 'AIR Inspection' }}
            </h3>
                    <p class="text-[#8c9097] dark:text-white/50 text-[0.813rem] mb-0">
                Receiving details, delivered quantities, accepted quantities, inspection units, unit components, and unit evidence now live in the GSO platform.
            </p>
        </div>
        <ol class="flex items-center whitespace-nowrap min-w-0">
            <li class="text-[0.813rem] ps-[0.5rem]">
                <a class="flex items-center text-primary hover:text-primary dark:text-primary truncate" href="{{ route('gso.dashboard') }}">
                    GSO
                    <i class="ti ti-chevrons-right flex-shrink-0 text-[#8c9097] dark:text-white/50 px-[0.5rem] overflow-visible rtl:rotate-180"></i>
                </a>
            </li>
            <li class="text-[0.813rem] ps-[0.5rem]">
                <a class="flex items-center text-primary hover:text-primary dark:text-primary truncate" href="{{ route('gso.air.index') }}">
                    AIR
                    <i class="ti ti-chevrons-right flex-shrink-0 text-[#8c9097] dark:text-white/50 px-[0.5rem] overflow-visible rtl:rotate-180"></i>
                </a>
            </li>
            <li class="text-[0.813rem] text-defaulttextcolor font-semibold dark:text-white/50" aria-current="page">
                Inspection
            </li>
        </ol>
    </div>

    @if($isArchived)
        <div class="box border border-warning/20">
            <div class="box-body text-warning">
                This AIR is archived. Restore it from the edit page before continuing inspection work.
            </div>
        </div>
    @elseif(! $canViewInspection)
        <div class="box border border-warning/20">
            <div class="box-body text-warning">
                This AIR is not yet in the inspection workspace stage. Submit the draft first, then continue inspection here.
            </div>
        </div>
    @elseif(! $canEditInspection)
        <div class="box border border-primary/20">
            <div class="box-body text-primary">
                This AIR inspection is currently read-only. Submitted and in-progress AIR records can be edited here; inspected AIR records stay visible for continuity.
            </div>
        </div>
    @endif

    <div class="gso-air-inspection-layout">
        <div class="space-y-4">
            <div class="box gso-air-inspection-card">
                <div class="box-header">
                    <div class="box-title">Receiving / Invoice Details</div>
                </div>
                <div class="box-body">
                    <div id="gsoAirInspectionFormError" class="hidden mb-3 rounded bg-danger/10 p-3 text-sm text-danger"></div>

                    <div class="gso-air-inspection-grid">
                        <div>
                            <label class="text-sm text-[#8c9097]">Invoice / DR / SI No.</label>
                            <input id="gsoAirInspectionInvoiceNumber" type="text" class="ti-form-input w-full" value="{{ $air['invoice_number'] ?? '' }}" @disabled(! $canEditInspection)>
                        </div>
                        <div>
                            <label class="text-sm text-[#8c9097]">Invoice Date</label>
                            <input id="gsoAirInspectionInvoiceDate" type="date" class="ti-form-input w-full" value="{{ $air['invoice_date'] ?? '' }}" @disabled(! $canEditInspection)>
                        </div>
                        <div>
                            <label class="text-sm text-[#8c9097]">Date Received</label>
                            <input id="gsoAirInspectionDateReceived" type="date" class="ti-form-input w-full" value="{{ $air['date_received'] ?? '' }}" @disabled(! $canEditInspection)>
                        </div>
                        <div>
                            <label class="text-sm text-[#8c9097]">Received Completeness</label>
                            <select id="gsoAirInspectionReceivedCompleteness" class="ti-form-select w-full" @disabled(! $canEditInspection)>
                                <option value="">Select completeness</option>
                                <option value="complete" @selected(($air['received_completeness'] ?? '') === 'complete')>Complete</option>
                                <option value="partial" @selected(($air['received_completeness'] ?? '') === 'partial')>Partial</option>
                            </select>
                        </div>
                        <div class="lg:col-span-2">
                            <label class="text-sm text-[#8c9097]">Received Notes</label>
                            <textarea id="gsoAirInspectionReceivedNotes" class="ti-form-input w-full" rows="4" @disabled(! $canEditInspection)>{{ $air['received_notes'] ?? '' }}</textarea>
                        </div>
                    </div>
                </div>
                <div class="ti-modal-footer !justify-between">
                    <a href="{{ route('gso.air.edit', ['air' => $air['id'] ?? '']) }}" class="ti-btn ti-btn-light">Back to AIR</a>
                    <div class="flex flex-wrap gap-2 justify-end">
                        @if(!empty($air['id']))
                            <a
                                href="{{ route('gso.air.print', ['air' => $air['id'], 'preview' => 1]) }}"
                                target="_blank"
                                rel="noopener"
                                class="ti-btn ti-btn-light"
                            >
                                Print Preview
                            </a>
                        @endif
                        @if($canEditInspection)
                            <button type="button" id="gsoAirInspectionSaveBtn" class="ti-btn btn-wave ti-btn-primary-full">
                                Save Inspection
                            </button>
                            <button type="button" id="gsoAirInspectionFinalizeBtn" class="ti-btn btn-wave ti-btn-success-full">
                                Finalize Inspection
                            </button>
                        @endif
                    </div>
                </div>
            </div>

            <div class="box gso-air-inspection-card">
                <div class="box-header">
                    <div class="box-title">Items Delivered</div>
                </div>
                <div class="box-body">
                    <div class="mb-4 rounded bg-light p-3 text-sm text-[#8c9097] dark:bg-black/20 dark:text-white/50">
                        Save the receiving and quantity updates first after changing accepted quantities. Inspection units are managed against the last saved accepted quantity per AIR item.
                    </div>

                    <div id="gsoAirInspectionItems" class="gso-air-inspection-items"></div>
                </div>
            </div>
        </div>

        <div class="space-y-4">
            <div class="box gso-air-inspection-card">
                <div class="box-header">
                    <div class="box-title">Workflow Summary</div>
                </div>
                <div class="box-body text-sm space-y-3">
                    <div class="flex items-center justify-between gap-3">
                        <span class="text-[#8c9097]">Workflow Status</span>
                        <span id="gsoAirInspectionStatusText" class="font-medium">{{ $air['status_text'] ?? 'Unknown' }}</span>
                    </div>
                    <div class="flex items-center justify-between gap-3">
                        <span class="text-[#8c9097]">PO Number</span>
                        <span class="font-medium text-right">{{ $air['po_number'] ?? 'None' }}</span>
                    </div>
                    <div class="flex items-center justify-between gap-3">
                        <span class="text-[#8c9097]">AIR Number</span>
                        <span class="font-medium text-right">{{ $air['air_number'] ?? 'None' }}</span>
                    </div>
                    <div class="flex items-center justify-between gap-3">
                        <span class="text-[#8c9097]">Inspection Date</span>
                        <span id="gsoAirInspectionDateInspectedText" class="font-medium text-right">{{ $air['date_inspected_text'] ?? '-' }}</span>
                    </div>
                    <div class="flex items-center justify-between gap-3">
                        <span class="text-[#8c9097]">Inspection Verified</span>
                        <span id="gsoAirInspectionVerifiedText" class="font-medium text-right">{{ $air['inspection_verified_text'] ?? 'Pending' }}</span>
                    </div>
                    <div class="flex items-center justify-between gap-3">
                        <span class="text-[#8c9097]">Item Rows</span>
                        <span id="gsoAirInspectionItemCount" class="font-medium text-right">{{ count($items) }}</span>
                    </div>
                    <div class="flex items-center justify-between gap-3">
                        <span class="text-[#8c9097]">Encoded Units</span>
                        <span id="gsoAirInspectionUnitCount" class="font-medium text-right">{{ collect($items)->sum('units_count') }}</span>
                    </div>
                    @if($canPromoteInventory)
                        <div class="border-t border-defaultborder pt-4">
                            <button
                                type="button"
                                id="gsoAirInspectionPromoteBtn"
                                class="ti-btn btn-wave ti-btn-success-full w-full"
                                @disabled(($air['status'] ?? '') !== 'inspected')
                            >
                                Promote to Inventory
                            </button>
                            <p class="mb-0 mt-2 text-xs text-[#8c9097] dark:text-white/50">
                                Finalized property units will create inventory items and finalized consumables will post into stock.
                            </p>
                        </div>
                    @endif
                </div>
            </div>

            <div class="box gso-air-inspection-card">
                <div class="box-header">
                    <div class="box-title">Migration Boundary</div>
                </div>
                <div class="box-body text-sm text-[#8c9097] dark:text-white/50 space-y-3">
                    <p class="mb-0">
                        This slice now covers receiving capture, delivered and accepted quantities, inspection unit rows, per-unit component schedules, per-unit evidence uploads, AIR-to-inventory promotion, and AIR print preview.
                    </p>
                    <p class="mb-0">
                        Follow-up AIR document generation and downstream document generation still layer on top of this next.
                    </p>
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
                    <button type="button" id="gsoAirUnitSaveBtn" class="ti-btn btn-wave ti-btn-primary-full">Save Unit Rows</button>
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
                                <button type="button" id="gsoAirUnitFileUploadBtn" class="ti-btn btn-wave ti-btn-primary-full">
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
@endsection

@push('scripts')
    <script>
        window.__gsoAirInspection = {
            air: @json($air),
            items: @json($items),
            saveUrl: @json(route('gso.air.inspection.save', ['air' => $air['id'] ?? ''])),
            finalizeUrl: @json(route('gso.air.inspection.finalize', ['air' => $air['id'] ?? ''])),
            promoteEligibleUrl: @json(route('gso.air.inventory.eligible', ['air' => $air['id'] ?? ''])),
            promoteUrl: @json(route('gso.air.inventory.promote', ['air' => $air['id'] ?? ''])),
            unitsIndexUrlTemplate: @json(route('gso.air.inspection.units.index', ['air' => $air['id'] ?? '', 'airItem' => '__AIR_ITEM__'])),
            unitsSaveUrlTemplate: @json(route('gso.air.inspection.units.save', ['air' => $air['id'] ?? '', 'airItem' => '__AIR_ITEM__'])),
            unitsDestroyUrlTemplate: @json(route('gso.air.inspection.units.destroy', ['air' => $air['id'] ?? '', 'airItem' => '__AIR_ITEM__', 'unit' => '__UNIT__'])),
            unitFilesIndexUrlTemplate: @json(route('gso.air.inspection.unit-files.index', ['air' => $air['id'] ?? '', 'airItem' => '__AIR_ITEM__', 'unit' => '__UNIT__'])),
            unitFilesStoreUrlTemplate: @json(route('gso.air.inspection.unit-files.store', ['air' => $air['id'] ?? '', 'airItem' => '__AIR_ITEM__', 'unit' => '__UNIT__'])),
            unitFilesDestroyUrlTemplate: @json(route('gso.air.inspection.unit-files.destroy', ['air' => $air['id'] ?? '', 'airItem' => '__AIR_ITEM__', 'unit' => '__UNIT__', 'file' => '__FILE__'])),
            unitFilesPreviewUrlTemplate: @json(route('gso.air.inspection.unit-files.preview', ['air' => $air['id'] ?? '', 'airItem' => '__AIR_ITEM__', 'unit' => '__UNIT__', 'file' => '__FILE__'])),
            unitFilesPrimaryUrlTemplate: @json(route('gso.air.inspection.unit-files.set-primary', ['air' => $air['id'] ?? '', 'airItem' => '__AIR_ITEM__', 'unit' => '__UNIT__', 'file' => '__FILE__'])),
            editUrl: @json(route('gso.air.edit', ['air' => $air['id'] ?? ''])),
            indexUrl: @json(route('gso.air.index')),
            csrf: @json(csrf_token()),
            canManage: @json($canManageAir),
            canEditInspection: @json($canEditInspection),
            canPromoteInventory: @json($canPromoteInventory),
            conditionStatuses: @json($conditionStatuses),
        };
    </script>
@endpush
