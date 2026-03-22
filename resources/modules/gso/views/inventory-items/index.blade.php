@extends('layouts.master')

@php
    $canManageInventoryItems = auth()->user()?->hasAnyRole(['Administrator', 'admin'])
        || auth()->user()?->can('modify Inventory Items');
@endphp

@section('styles')
    <link rel="stylesheet" href="{{ asset('build/assets/libs/tabulator-tables/css/tabulator.min.css') }}">
    <style>
        .gso-reference-toolbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            width: 100%;
        }

        .gso-reference-actions {
            display: flex;
            align-items: center;
            gap: 8px;
            flex-wrap: wrap;
            justify-content: flex-end;
        }

        .gso-inventory-modal-grid {
            display: grid;
            grid-template-columns: repeat(1, minmax(0, 1fr));
            gap: 12px;
        }

        .gso-inventory-panel-grid {
            display: grid;
            grid-template-columns: repeat(1, minmax(0, 1fr));
            gap: 16px;
        }

        .gso-inventory-file-card,
        .gso-inventory-event-card {
            border: 1px solid rgba(148, 163, 184, 0.2);
            border-radius: 16px;
            overflow: hidden;
            background: rgba(255, 255, 255, 0.95);
        }

        .dark .gso-inventory-file-card,
        .dark .gso-inventory-event-card {
            background: rgba(15, 23, 42, 0.85);
            border-color: rgba(148, 163, 184, 0.18);
        }

        .gso-inventory-file-preview {
            display: block;
            width: 100%;
            height: 220px;
            object-fit: cover;
            background: linear-gradient(135deg, rgba(59, 130, 246, 0.08), rgba(15, 23, 42, 0.12));
        }

        .gso-inventory-file-fallback {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 100%;
            height: 220px;
            color: #64748b;
            background: linear-gradient(135deg, rgba(148, 163, 184, 0.08), rgba(148, 163, 184, 0.18));
            font-size: 0.875rem;
            text-align: center;
            padding: 16px;
        }

        .gso-inventory-event-card {
            padding: 16px;
        }

        .tabulator .tabulator-loader,
        .tabulator .tabulator-loader-msg {
            display: none !important;
        }

        .tabulator.is-loading {
            opacity: 0.65;
            pointer-events: none;
        }

        @media (min-width: 1024px) {
            .gso-inventory-modal-grid {
                grid-template-columns: repeat(3, minmax(0, 1fr));
            }

            .gso-inventory-panel-grid {
                grid-template-columns: repeat(3, minmax(0, 1fr));
            }
        }
    </style>
@endsection

@section('content')
<div class="block justify-between page-header md:flex">
    <div>
        <h3 class="!text-defaulttextcolor dark:!text-defaulttextcolor/70 dark:text-white text-[1.125rem] font-semibold">
            Inventory Items
        </h3>
        <p class="text-[#8c9097] dark:text-white/50 text-[0.813rem] mb-0">
            Property register records built on top of the GSO item catalog, departments, fund sources, and accountable officers.
        </p>
    </div>
    <ol class="flex items-center whitespace-nowrap min-w-0">
        <li class="text-[0.813rem] ps-[0.5rem]">
            <a class="flex items-center text-primary hover:text-primary dark:text-primary truncate" href="{{ route('gso.dashboard') }}">
                GSO
                <i class="ti ti-chevrons-right flex-shrink-0 text-[#8c9097] dark:text-white/50 px-[0.5rem] overflow-visible rtl:rotate-180"></i>
            </a>
        </li>
        <li class="text-[0.813rem] text-defaulttextcolor font-semibold dark:text-white/50" aria-current="page">
            Inventory Items
        </li>
    </ol>
</div>

<div class="box">
    <div class="box-header">
        <div class="gso-reference-toolbar">
            <h5 class="box-title">Inventory Register</h5>

            <div class="gso-reference-actions">
                <input
                    id="gso-inventory-items-search"
                    type="text"
                    class="form-control w-[260px] !rounded-md"
                    placeholder="Search property no., item, PO, serial..."
                />

                <select id="gso-inventory-items-department-filter" class="form-control w-[220px] !rounded-md">
                    <option value="">All Departments</option>
                    @foreach($departments as $department)
                        <option value="{{ $department->id }}">{{ $department->code }} - {{ $department->name }}</option>
                    @endforeach
                </select>

                <select id="gso-inventory-items-classification-filter" class="form-control w-[150px] !rounded-md">
                    <option value="">All Classes</option>
                    <option value="ppe">PPE</option>
                    <option value="ics">ICS</option>
                </select>

                <select id="gso-inventory-items-custody-filter" class="form-control w-[170px] !rounded-md">
                    <option value="">All Custody</option>
                    @foreach($inventoryCustodyStates as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </select>

                <select id="gso-inventory-items-status-filter" class="form-control w-[180px] !rounded-md">
                    <option value="">All Statuses</option>
                    @foreach($inventoryStatuses as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </select>

                <select id="gso-inventory-items-record-status" class="form-control w-[160px] !rounded-md">
                    <option value="active">Active</option>
                    <option value="archived">Archived</option>
                    <option value="all">All</option>
                </select>

                <button id="gso-inventory-items-clear" type="button" class="ti-btn ti-btn-light">
                    Clear
                </button>

                <button id="gso-inventory-items-batch-print" type="button" class="ti-btn ti-btn-secondary-full">
                    Batch Cards
                </button>

                <a
                    href="{{ route('gso.inventory-items.regspi.print', ['preview' => 1]) }}"
                    target="_blank"
                    rel="noopener"
                    class="ti-btn ti-btn-light"
                >
                    RegSPI
                </a>

                <a
                    href="{{ route('gso.inventory-items.rpcppe.print', ['preview' => 1]) }}"
                    target="_blank"
                    rel="noopener"
                    class="ti-btn ti-btn-light"
                >
                    RPCPPE
                </a>

                <a
                    href="{{ route('gso.inventory-items.rpcsp.print', ['preview' => 1]) }}"
                    target="_blank"
                    rel="noopener"
                    class="ti-btn ti-btn-light"
                >
                    RPCSP
                </a>

                @if($canManageInventoryItems)
                    <button
                        type="button"
                        class="hs-dropdown-toggle ti-btn btn-wave ti-btn-primary-full"
                        data-hs-overlay="#gsoInventoryItemModal"
                        data-mode="create"
                    >
                        Add Inventory Item
                    </button>
                @endif
            </div>
        </div>
    </div>

    <div class="box-body">
        <div class="overflow-auto table-bordered">
            <div id="gso-inventory-items-table" class="ti-custom-table ti-striped-table ti-custom-table-hover"></div>
        </div>

        <div class="mt-2 flex items-center justify-between text-xs text-[#8c9097]">
            <div id="gso-inventory-items-info"></div>
        </div>
    </div>
</div>

@if($canManageInventoryItems)
    <div id="gsoInventoryItemModal" class="hs-overlay hidden ti-modal [--overlay-backdrop:static]">
        <div class="hs-overlay-open:mt-7 ti-modal-box mt-0 ease-out !max-w-6xl">
            <div class="ti-modal-content">
                <div class="ti-modal-header">
                    <h6 id="gsoInventoryItemModalTitle" class="modal-title text-[1rem] font-semibold">Add Inventory Item</h6>
                    <button type="button" class="hs-dropdown-toggle !text-[1rem] !font-semibold !text-defaulttextcolor" data-hs-overlay="#gsoInventoryItemModal">
                        <span class="sr-only">Close</span>
                        <i class="ri-close-line"></i>
                    </button>
                </div>

                <div class="ti-modal-body px-4">
                    <div id="gsoInventoryItemFormError" class="hidden mb-3 p-2 rounded bg-danger/10 text-danger text-sm"></div>

                    <input type="hidden" id="gsoInventoryItemId" value="">

                    <div class="gso-inventory-modal-grid">
                        <div>
                            <label class="text-sm text-[#8c9097]">Item</label>
                            <select id="gsoInventoryItemItemId" class="ti-form-select w-full">
                                @foreach($items as $item)
                                    <option
                                        value="{{ $item->id }}"
                                        data-base-unit="{{ $item->base_unit }}"
                                        data-requires-serial="{{ $item->requires_serial ? '1' : '0' }}"
                                    >
                                        {{ $item->item_name }}
                                        @if($item->item_identification)
                                            ({{ $item->item_identification }})
                                        @endif
                                        @if($item->asset)
                                            - {{ $item->asset->asset_code }} {{ $item->asset->asset_name }}
                                        @endif
                                    </option>
                                @endforeach
                            </select>
                            <div id="gsoInventoryItemItemErr" class="text-xs text-danger mt-1 hidden"></div>
                        </div>

                        <div>
                            <label class="text-sm text-[#8c9097]">Department</label>
                            <select id="gsoInventoryItemDepartmentId" class="ti-form-select w-full">
                                @foreach($departments as $department)
                                    <option value="{{ $department->id }}">{{ $department->code }} - {{ $department->name }}</option>
                                @endforeach
                            </select>
                            <div id="gsoInventoryItemDepartmentErr" class="text-xs text-danger mt-1 hidden"></div>
                        </div>

                        <div>
                            <label class="text-sm text-[#8c9097]">Fund Source</label>
                            <select id="gsoInventoryItemFundSourceId" class="ti-form-select w-full">
                                <option value="">No Fund Source</option>
                                @foreach($fundSources as $fundSource)
                                    <option value="{{ $fundSource->id }}">
                                        {{ $fundSource->code ? $fundSource->code . ' - ' : '' }}{{ $fundSource->name }}
                                    </option>
                                @endforeach
                            </select>
                            <div id="gsoInventoryItemFundSourceErr" class="text-xs text-danger mt-1 hidden"></div>
                        </div>

                        <div>
                            <label class="text-sm text-[#8c9097]">Property Number</label>
                            <input id="gsoInventoryItemPropertyNumber" type="text" class="ti-form-input w-full" placeholder="Optional property number">
                            <div id="gsoInventoryItemPropertyNumberErr" class="text-xs text-danger mt-1 hidden"></div>
                        </div>

                        <div>
                            <label class="text-sm text-[#8c9097]">PO / Reference Number</label>
                            <input id="gsoInventoryItemPoNumber" type="text" class="ti-form-input w-full" placeholder="Required PO or reference number">
                            <div id="gsoInventoryItemPoNumberErr" class="text-xs text-danger mt-1 hidden"></div>
                        </div>

                        <div>
                            <label class="text-sm text-[#8c9097]">Stock Number</label>
                            <input id="gsoInventoryItemStockNumber" type="text" class="ti-form-input w-full" placeholder="Optional stock number">
                            <div id="gsoInventoryItemStockNumberErr" class="text-xs text-danger mt-1 hidden"></div>
                        </div>

                        <div>
                            <label class="text-sm text-[#8c9097]">Acquisition Date</label>
                            <input id="gsoInventoryItemAcquisitionDate" type="date" class="ti-form-input w-full">
                            <div id="gsoInventoryItemAcquisitionDateErr" class="text-xs text-danger mt-1 hidden"></div>
                        </div>

                        <div>
                            <label class="text-sm text-[#8c9097]">Acquisition Cost</label>
                            <input id="gsoInventoryItemAcquisitionCost" type="number" min="0" step="0.01" class="ti-form-input w-full" placeholder="0.00">
                            <div id="gsoInventoryItemAcquisitionCostErr" class="text-xs text-danger mt-1 hidden"></div>
                        </div>

                        <div>
                            <label class="text-sm text-[#8c9097]">Quantity</label>
                            <input id="gsoInventoryItemQuantity" type="number" min="1" step="1" class="ti-form-input w-full" value="1">
                            <div id="gsoInventoryItemQuantityErr" class="text-xs text-danger mt-1 hidden"></div>
                        </div>

                        <div>
                            <label class="text-sm text-[#8c9097]">Unit</label>
                            <input id="gsoInventoryItemUnit" type="text" class="ti-form-input w-full" placeholder="e.g. piece">
                            <div id="gsoInventoryItemUnitErr" class="text-xs text-danger mt-1 hidden"></div>
                        </div>

                        <div>
                            <label class="text-sm text-[#8c9097]">Service Life</label>
                            <input id="gsoInventoryItemServiceLife" type="number" min="0" step="1" class="ti-form-input w-full" placeholder="Optional service life">
                            <div id="gsoInventoryItemServiceLifeErr" class="text-xs text-danger mt-1 hidden"></div>
                        </div>

                        <div>
                            <label class="text-sm text-[#8c9097]">Classification</label>
                            <select id="gsoInventoryItemClassification" class="ti-form-select w-full">
                                <option value="ppe">PPE</option>
                                <option value="ics">ICS</option>
                            </select>
                            <div id="gsoInventoryItemClassificationErr" class="text-xs text-danger mt-1 hidden"></div>
                        </div>

                        <div>
                            <label class="text-sm text-[#8c9097]">Custody State</label>
                            <select id="gsoInventoryItemCustodyState" class="ti-form-select w-full">
                                @foreach($inventoryCustodyStates as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </select>
                            <div id="gsoInventoryItemCustodyStateErr" class="text-xs text-danger mt-1 hidden"></div>
                        </div>

                        <div>
                            <label class="text-sm text-[#8c9097]">Status</label>
                            <select id="gsoInventoryItemStatus" class="ti-form-select w-full">
                                @foreach($inventoryStatuses as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </select>
                            <div id="gsoInventoryItemStatusErr" class="text-xs text-danger mt-1 hidden"></div>
                        </div>

                        <div>
                            <label class="text-sm text-[#8c9097]">Condition</label>
                            <select id="gsoInventoryItemCondition" class="ti-form-select w-full">
                                @foreach($inventoryConditions as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </select>
                            <div id="gsoInventoryItemConditionErr" class="text-xs text-danger mt-1 hidden"></div>
                        </div>

                        <div>
                            <label class="text-sm text-[#8c9097]">Accountable Officer</label>
                            <select id="gsoInventoryItemAccountableOfficerId" class="ti-form-select w-full">
                                <option value="">No Linked Officer</option>
                                @foreach($accountableOfficers as $accountableOfficer)
                                    <option value="{{ $accountableOfficer->id }}">{{ $accountableOfficer->full_name }}</option>
                                @endforeach
                            </select>
                            <div id="gsoInventoryItemAccountableOfficerIdErr" class="text-xs text-danger mt-1 hidden"></div>
                        </div>

                        <div>
                            <label class="text-sm text-[#8c9097]">Accountable Officer Name</label>
                            <input id="gsoInventoryItemAccountableOfficer" type="text" class="ti-form-input w-full" placeholder="Fallback free-text accountable officer">
                            <div id="gsoInventoryItemAccountableOfficerErr" class="text-xs text-danger mt-1 hidden"></div>
                        </div>

                        <div>
                            <label class="text-sm text-[#8c9097]">Brand</label>
                            <input id="gsoInventoryItemBrand" type="text" class="ti-form-input w-full" placeholder="Optional brand">
                            <div id="gsoInventoryItemBrandErr" class="text-xs text-danger mt-1 hidden"></div>
                        </div>

                        <div>
                            <label class="text-sm text-[#8c9097]">Model</label>
                            <input id="gsoInventoryItemModel" type="text" class="ti-form-input w-full" placeholder="Optional model">
                            <div id="gsoInventoryItemModelErr" class="text-xs text-danger mt-1 hidden"></div>
                        </div>

                        <div>
                            <label class="text-sm text-[#8c9097]">Serial Number</label>
                            <input id="gsoInventoryItemSerialNumber" type="text" class="ti-form-input w-full" placeholder="Required when the item tracks serials">
                            <div id="gsoInventoryItemSerialNumberErr" class="text-xs text-danger mt-1 hidden"></div>
                            <p id="gsoInventoryItemSerialHint" class="mt-1 text-xs text-[#8c9097]">Serial number requirement depends on the selected item.</p>
                        </div>

                        <div class="col-span-1 lg:col-span-3">
                            <label class="text-sm text-[#8c9097]">Description</label>
                            <textarea id="gsoInventoryItemDescription" class="ti-form-input w-full" rows="3" placeholder="Optional inventory item description"></textarea>
                            <div id="gsoInventoryItemDescriptionErr" class="text-xs text-danger mt-1 hidden"></div>
                        </div>

                        <div class="col-span-1 lg:col-span-3">
                            <label class="text-sm text-[#8c9097]">Remarks</label>
                            <textarea id="gsoInventoryItemRemarks" class="ti-form-input w-full" rows="3" placeholder="Optional remarks"></textarea>
                            <div id="gsoInventoryItemRemarksErr" class="text-xs text-danger mt-1 hidden"></div>
                        </div>
                    </div>
                </div>

                <div class="ti-modal-footer">
                    <button type="button" class="hs-dropdown-toggle ti-btn btn-wave ti-btn-secondary-full align-middle" data-hs-overlay="#gsoInventoryItemModal">
                        Close
                    </button>
                    <button type="button" id="gsoInventoryItemSaveBtn" class="ti-btn btn-wave bg-primary text-white !font-medium">
                        Save
                    </button>
                </div>
            </div>
        </div>
    </div>
@endif

<div id="gsoInventoryFilesModal" class="hs-overlay hidden ti-modal [--overlay-backdrop:static]">
    <div class="hs-overlay-open:mt-7 ti-modal-box mt-0 ease-out !max-w-6xl">
        <div class="ti-modal-content">
            <div class="ti-modal-header">
                <div>
                    <h6 id="gsoInventoryFilesModalTitle" class="modal-title text-[1rem] font-semibold">Inventory Files</h6>
                    <p id="gsoInventoryFilesModalSubtitle" class="text-sm text-[#8c9097] mt-1 mb-0">Review uploaded evidence and linked inspection files.</p>
                </div>
                <button type="button" class="hs-dropdown-toggle !text-[1rem] !font-semibold !text-defaulttextcolor" data-hs-overlay="#gsoInventoryFilesModal">
                    <span class="sr-only">Close</span>
                    <i class="ri-close-line"></i>
                </button>
            </div>

            <div class="ti-modal-body px-4">
                <div id="gsoInventoryFilesError" class="hidden mb-3 p-2 rounded bg-danger/10 text-danger text-sm"></div>

                @if($canManageInventoryItems)
                    <div id="gsoInventoryFilesUploadPanel" class="mb-4 rounded-lg border border-defaultborder p-3">
                        <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                            <div>
                                <div class="font-medium text-defaulttextcolor dark:text-white">Upload Inventory Files</div>
                                <div id="gsoInventoryFilesUploadHint" class="text-xs text-[#8c9097] mt-1">
                                    Images and PDFs are stored in the inventory item's Google Drive folder.
                                </div>
                            </div>

                            <div class="flex flex-col gap-2 sm:flex-row sm:items-center">
                                <select id="gsoInventoryFilesType" class="form-control w-full sm:w-[180px] !rounded-md">
                                    <option value="">Auto Type</option>
                                    @foreach($inventoryFileTypes as $value => $label)
                                        <option value="{{ $value }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                                <input id="gsoInventoryFilesInput" type="file" accept="image/*,application/pdf" multiple class="form-control w-full sm:w-[280px]">
                                <button type="button" id="gsoInventoryFilesUploadBtn" class="ti-btn btn-wave ti-btn-primary-full">
                                    Upload Files
                                </button>
                            </div>
                        </div>
                    </div>

                    <div id="gsoInventoryInspectionImportPanel" class="mb-4 rounded-lg border border-defaultborder p-3">
                        <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                            <div>
                                <div class="font-medium text-defaulttextcolor dark:text-white">Import Inspection Photos</div>
                                <div class="text-xs text-[#8c9097] mt-1">
                                    Copy Google Drive inspection photos into this inventory item's evidence library and history.
                                </div>
                            </div>

                            <div class="flex flex-col gap-2 sm:flex-row sm:items-center">
                                <select id="gsoInventoryInspectionSelect" class="form-control w-full sm:w-[320px] !rounded-md">
                                    <option value="">Select Inspection</option>
                                    @foreach($inspections as $inspection)
                                        <option value="{{ $inspection->id }}">
                                            {{ $inspection->item_name ?: 'Inspection' }}
                                            @if($inspection->po_number)
                                                (PO {{ $inspection->po_number }})
                                            @elseif($inspection->dv_number)
                                                (DV {{ $inspection->dv_number }})
                                            @endif
                                            - {{ \App\Modules\GSO\Support\InspectionStatuses::labels()[$inspection->status] ?? ucfirst($inspection->status) }}
                                        </option>
                                    @endforeach
                                </select>
                                <button type="button" id="gsoInventoryInspectionImportBtn" class="ti-btn btn-wave ti-btn-info-full">
                                    Import Photos
                                </button>
                            </div>
                        </div>
                    </div>
                @endif

                <div id="gsoInventoryFilesEmpty" class="hidden rounded-lg border border-dashed border-defaultborder p-6 text-center text-sm text-[#8c9097]">
                    No inventory files have been uploaded yet.
                </div>

                <div id="gsoInventoryFilesGrid" class="gso-inventory-panel-grid"></div>
            </div>

            <div class="ti-modal-footer">
                <button type="button" class="hs-dropdown-toggle ti-btn btn-wave ti-btn-secondary-full align-middle" data-hs-overlay="#gsoInventoryFilesModal">
                    Close
                </button>
            </div>
        </div>
    </div>
</div>

<div id="gsoInventoryEventsModal" class="hs-overlay hidden ti-modal [--overlay-backdrop:static]">
    <div class="hs-overlay-open:mt-7 ti-modal-box mt-0 ease-out !max-w-6xl">
        <div class="ti-modal-content">
            <div class="ti-modal-header">
                <div>
                    <h6 id="gsoInventoryEventsModalTitle" class="modal-title text-[1rem] font-semibold">Inventory History</h6>
                    <p id="gsoInventoryEventsModalSubtitle" class="text-sm text-[#8c9097] mt-1 mb-0">Track lifecycle and custody context for this inventory item.</p>
                </div>
                <button type="button" class="hs-dropdown-toggle !text-[1rem] !font-semibold !text-defaulttextcolor" data-hs-overlay="#gsoInventoryEventsModal">
                    <span class="sr-only">Close</span>
                    <i class="ri-close-line"></i>
                </button>
            </div>

            <div class="ti-modal-body px-4">
                <div id="gsoInventoryEventsError" class="hidden mb-3 p-2 rounded bg-danger/10 text-danger text-sm"></div>

                @if($canManageInventoryItems)
                    <div id="gsoInventoryEventsFormPanel" class="mb-4 rounded-lg border border-defaultborder p-3">
                        <div class="grid grid-cols-1 gap-3 lg:grid-cols-3">
                            <div>
                                <label class="text-sm text-[#8c9097]">Event Type</label>
                                <select id="gsoInventoryEventType" class="ti-form-select w-full">
                                    @foreach($inventoryEventTypes as $value => $label)
                                        <option value="{{ $value }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label class="text-sm text-[#8c9097]">Event Date</label>
                                <input id="gsoInventoryEventDate" type="datetime-local" class="ti-form-input w-full">
                            </div>

                            <div>
                                <label class="text-sm text-[#8c9097]">Quantity</label>
                                <input id="gsoInventoryEventQuantity" type="number" min="0" step="1" class="ti-form-input w-full" value="0">
                            </div>

                            <div>
                                <label class="text-sm text-[#8c9097]">Department</label>
                                <select id="gsoInventoryEventDepartmentId" class="ti-form-select w-full">
                                    <option value="">Use Current Department</option>
                                    @foreach($departments as $department)
                                        <option value="{{ $department->id }}">{{ $department->code }} - {{ $department->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label class="text-sm text-[#8c9097]">Status</label>
                                <select id="gsoInventoryEventStatus" class="ti-form-select w-full">
                                    <option value="">Use Current Status</option>
                                    @foreach($inventoryStatuses as $value => $label)
                                        <option value="{{ $value }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label class="text-sm text-[#8c9097]">Condition</label>
                                <select id="gsoInventoryEventCondition" class="ti-form-select w-full">
                                    <option value="">Use Current Condition</option>
                                    @foreach($inventoryConditions as $value => $label)
                                        <option value="{{ $value }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label class="text-sm text-[#8c9097]">Accountable Person</label>
                                <input id="gsoInventoryEventPersonAccountable" type="text" class="ti-form-input w-full" placeholder="Optional accountable person">
                            </div>

                            <div>
                                <label class="text-sm text-[#8c9097]">Reference Type</label>
                                <input id="gsoInventoryEventReferenceType" type="text" class="ti-form-input w-full" placeholder="PAR, ICS, Inspection, etc.">
                            </div>

                            <div>
                                <label class="text-sm text-[#8c9097]">Reference Number</label>
                                <input id="gsoInventoryEventReferenceNo" type="text" class="ti-form-input w-full" placeholder="Optional document number">
                            </div>

                            <div class="lg:col-span-3">
                                <label class="text-sm text-[#8c9097]">Notes</label>
                                <textarea id="gsoInventoryEventNotes" class="ti-form-input w-full" rows="3" placeholder="Optional event notes"></textarea>
                            </div>
                        </div>

                        <div class="mt-3 flex justify-end">
                            <button type="button" id="gsoInventoryEventSaveBtn" class="ti-btn btn-wave ti-btn-primary-full">
                                Add Event
                            </button>
                        </div>
                    </div>
                @endif

                <div id="gsoInventoryEventsEmpty" class="hidden rounded-lg border border-dashed border-defaultborder p-6 text-center text-sm text-[#8c9097]">
                    No inventory events have been recorded yet.
                </div>

                <div id="gsoInventoryEventsList" class="space-y-3"></div>
            </div>

            <div class="ti-modal-footer">
                <button type="button" class="hs-dropdown-toggle ti-btn btn-wave ti-btn-secondary-full align-middle" data-hs-overlay="#gsoInventoryEventsModal">
                    Close
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
    <script src="{{ asset('build/assets/libs/tabulator-tables/js/tabulator.min.js') }}"></script>
    <script>
        window.__gsoInventoryItems = {
            ajaxUrl: @json(route('gso.inventory-items.data')),
            showUrlTemplate: @json(route('gso.inventory-items.show', ['inventoryItem' => '__ID__'])),
            storeUrl: @json(route('gso.inventory-items.store')),
            updateUrlTemplate: @json(route('gso.inventory-items.update', ['inventoryItem' => '__ID__'])),
            deleteUrlTemplate: @json(route('gso.inventory-items.destroy', ['inventoryItem' => '__ID__'])),
            restoreUrlTemplate: @json(route('gso.inventory-items.restore', ['inventoryItem' => '__ID__'])),
            fileIndexUrlTemplate: @json(route('gso.inventory-items.files.index', ['inventoryItem' => '__ID__'])),
            fileStoreUrlTemplate: @json(route('gso.inventory-items.files.store', ['inventoryItem' => '__ID__'])),
            filePreviewUrlTemplate: @json(route('gso.inventory-items.files.preview', ['inventoryItem' => '__ID__', 'file' => '__FILE__'])),
            fileDestroyUrlTemplate: @json(route('gso.inventory-items.files.destroy', ['inventoryItem' => '__ID__', 'file' => '__FILE__'])),
            fileImportInspectionUrlTemplate: @json(route('gso.inventory-items.files.import-inspection', ['inventoryItem' => '__ID__'])),
            eventIndexUrlTemplate: @json(route('gso.inventory-items.events.index', ['inventoryItem' => '__ID__'])),
            eventStoreUrlTemplate: @json(route('gso.inventory-items.events.store', ['inventoryItem' => '__ID__'])),
            batchPropertyCardsUrl: @json(route('gso.inventory-items.property-cards.print-batch')),
            csrf: @json(csrf_token()),
            canManage: @json($canManageInventoryItems),
        };
    </script>
@endpush
