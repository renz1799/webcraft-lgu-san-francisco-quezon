@extends('layouts.master')

@php
    $canManageInspections = auth()->user()?->hasAnyRole(['Administrator', 'admin'])
        || auth()->user()?->can('modify Inspections');
@endphp

@section('styles')
    <link rel="stylesheet" href="{{ asset('build/assets/libs/tabulator-tables/css/tabulator.min.css') }}">
    <style>
        .gso-inspections-toolbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            width: 100%;
        }

        .gso-inspections-actions {
            display: flex;
            align-items: center;
            gap: 8px;
            flex-wrap: wrap;
            justify-content: flex-end;
        }

        .gso-inspections-modal-grid {
            display: grid;
            grid-template-columns: repeat(1, minmax(0, 1fr));
            gap: 12px;
        }

        .gso-inspection-photos-grid {
            display: grid;
            grid-template-columns: repeat(1, minmax(0, 1fr));
            gap: 16px;
        }

        .gso-inspection-photo-card {
            border: 1px solid rgba(148, 163, 184, 0.2);
            border-radius: 16px;
            overflow: hidden;
            background: rgba(255, 255, 255, 0.95);
        }

        .dark .gso-inspection-photo-card {
            background: rgba(15, 23, 42, 0.85);
            border-color: rgba(148, 163, 184, 0.18);
        }

        .gso-inspection-photo-preview {
            display: block;
            width: 100%;
            height: 220px;
            object-fit: cover;
            background: linear-gradient(135deg, rgba(59, 130, 246, 0.08), rgba(15, 23, 42, 0.12));
        }

        .gso-inspection-photo-fallback {
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

        .tabulator .tabulator-loader,
        .tabulator .tabulator-loader-msg {
            display: none !important;
        }

        .tabulator.is-loading {
            opacity: 0.65;
            pointer-events: none;
        }

        @media (min-width: 1024px) {
            .gso-inspections-modal-grid {
                grid-template-columns: repeat(3, minmax(0, 1fr));
            }

            .gso-inspection-photos-grid {
                grid-template-columns: repeat(3, minmax(0, 1fr));
            }
        }
    </style>
@endsection

@section('content')
<div class="block justify-between page-header md:flex">
    <div>
        <h3 class="!text-defaulttextcolor dark:!text-defaulttextcolor/70 dark:text-white text-[1.125rem] font-semibold">
            Inspections
        </h3>
        <p class="text-[#8c9097] dark:text-white/50 text-[0.813rem] mb-0">
            Draft and archived inspection records tied to GSO inventory, item references, and department context.
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
            Inspections
        </li>
    </ol>
</div>

<div class="box">
    <div class="box-header">
        <div class="gso-inspections-toolbar">
            <h5 class="box-title">Inspection Register</h5>

            <div class="gso-inspections-actions">
                <input
                    id="gso-inspections-search"
                    type="text"
                    class="form-control w-[260px] !rounded-md"
                    placeholder="Search PO, item, serial, office..."
                />

                <select id="gso-inspections-status-filter" class="form-control w-[170px] !rounded-md">
                    <option value="">All Statuses</option>
                    @foreach($inspectionStatuses as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </select>

                <select id="gso-inspections-department-filter" class="form-control w-[220px] !rounded-md">
                    <option value="">All Departments</option>
                    @foreach($departments as $department)
                        <option value="{{ $department->id }}">{{ $department->code }} - {{ $department->name }}</option>
                    @endforeach
                </select>

                <select id="gso-inspections-record-status" class="form-control w-[160px] !rounded-md">
                    <option value="active">Active</option>
                    <option value="archived">Archived</option>
                    <option value="all">All</option>
                </select>

                <button id="gso-inspections-clear" type="button" class="ti-btn ti-btn-light">
                    Clear
                </button>

                @if($canManageInspections)
                    <button
                        type="button"
                        class="hs-dropdown-toggle ti-btn btn-wave ti-btn-primary-full"
                        data-hs-overlay="#gsoInspectionModal"
                        data-mode="create"
                    >
                        Add Inspection
                    </button>
                @endif
            </div>
        </div>
    </div>

    <div class="box-body">
        <div class="overflow-auto table-bordered">
            <div id="gso-inspections-table" class="ti-custom-table ti-striped-table ti-custom-table-hover"></div>
        </div>

        <div class="mt-2 flex items-center justify-between text-xs text-[#8c9097]">
            <div id="gso-inspections-info"></div>
        </div>
    </div>
</div>

@if($canManageInspections)
    <div id="gsoInspectionModal" class="hs-overlay hidden ti-modal [--overlay-backdrop:static]">
        <div class="hs-overlay-open:mt-7 ti-modal-box mt-0 ease-out !max-w-6xl">
            <div class="ti-modal-content">
                <div class="ti-modal-header">
                    <h6 id="gsoInspectionModalTitle" class="modal-title text-[1rem] font-semibold">Add Inspection</h6>
                    <button type="button" class="hs-dropdown-toggle !text-[1rem] !font-semibold !text-defaulttextcolor" data-hs-overlay="#gsoInspectionModal">
                        <span class="sr-only">Close</span>
                        <i class="ri-close-line"></i>
                    </button>
                </div>

                <div class="ti-modal-body px-4">
                    <div id="gsoInspectionFormError" class="hidden mb-3 p-2 rounded bg-danger/10 text-danger text-sm"></div>

                    <input type="hidden" id="gsoInspectionId" value="">

                    <div class="gso-inspections-modal-grid">
                        <div>
                            <label class="text-sm text-[#8c9097]">Linked Item</label>
                            <select id="gsoInspectionItemId" class="ti-form-select w-full">
                                <option value="">No Linked Item</option>
                                @foreach($items as $item)
                                    <option value="{{ $item->id }}" data-item-name="{{ $item->item_name }}">
                                        {{ $item->item_name }}
                                        @if($item->item_identification)
                                            ({{ $item->item_identification }})
                                        @endif
                                    </option>
                                @endforeach
                            </select>
                            <div id="gsoInspectionItemErr" class="text-xs text-danger mt-1 hidden"></div>
                        </div>

                        <div>
                            <label class="text-sm text-[#8c9097]">Linked Department</label>
                            <select id="gsoInspectionDepartmentId" class="ti-form-select w-full">
                                <option value="">No Linked Department</option>
                                @foreach($departments as $department)
                                    <option value="{{ $department->id }}" data-department-label="{{ $department->code }} - {{ $department->name }}">
                                        {{ $department->code }} - {{ $department->name }}
                                    </option>
                                @endforeach
                            </select>
                            <div id="gsoInspectionDepartmentErr" class="text-xs text-danger mt-1 hidden"></div>
                        </div>

                        <div>
                            <label class="text-sm text-[#8c9097]">Status</label>
                            <select id="gsoInspectionStatus" class="ti-form-select w-full">
                                @foreach($inspectionStatuses as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </select>
                            <div id="gsoInspectionStatusErr" class="text-xs text-danger mt-1 hidden"></div>
                        </div>

                        <div>
                            <label class="text-sm text-[#8c9097]">Item Snapshot</label>
                            <input id="gsoInspectionItemName" type="text" class="ti-form-input w-full" placeholder="Observed or inspected item name">
                            <div id="gsoInspectionItemNameErr" class="text-xs text-danger mt-1 hidden"></div>
                        </div>

                        <div>
                            <label class="text-sm text-[#8c9097]">Office / Department Snapshot</label>
                            <input id="gsoInspectionOfficeDepartment" type="text" class="ti-form-input w-full" placeholder="Observed office or department">
                            <div id="gsoInspectionOfficeDepartmentErr" class="text-xs text-danger mt-1 hidden"></div>
                        </div>

                        <div>
                            <label class="text-sm text-[#8c9097]">Accountable Officer</label>
                            <input id="gsoInspectionAccountableOfficer" type="text" class="ti-form-input w-full" placeholder="Optional accountable officer">
                            <div id="gsoInspectionAccountableOfficerErr" class="text-xs text-danger mt-1 hidden"></div>
                        </div>

                        <div>
                            <label class="text-sm text-[#8c9097]">PO Number</label>
                            <input id="gsoInspectionPoNumber" type="text" class="ti-form-input w-full" placeholder="Optional for draft inspections">
                            <div id="gsoInspectionPoNumberErr" class="text-xs text-danger mt-1 hidden"></div>
                        </div>

                        <div>
                            <label class="text-sm text-[#8c9097]">DV Number</label>
                            <input id="gsoInspectionDvNumber" type="text" class="ti-form-input w-full" placeholder="Optional disbursement voucher number">
                            <div id="gsoInspectionDvNumberErr" class="text-xs text-danger mt-1 hidden"></div>
                        </div>

                        <div>
                            <label class="text-sm text-[#8c9097]">Quantity</label>
                            <input id="gsoInspectionQuantity" type="number" min="1" step="1" class="ti-form-input w-full" value="1">
                            <div id="gsoInspectionQuantityErr" class="text-xs text-danger mt-1 hidden"></div>
                        </div>

                        <div>
                            <label class="text-sm text-[#8c9097]">Condition</label>
                            <select id="gsoInspectionCondition" class="ti-form-select w-full">
                                @foreach($inventoryConditions as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </select>
                            <div id="gsoInspectionConditionErr" class="text-xs text-danger mt-1 hidden"></div>
                        </div>

                        <div>
                            <label class="text-sm text-[#8c9097]">Acquisition Date</label>
                            <input id="gsoInspectionAcquisitionDate" type="date" class="ti-form-input w-full">
                            <div id="gsoInspectionAcquisitionDateErr" class="text-xs text-danger mt-1 hidden"></div>
                        </div>

                        <div>
                            <label class="text-sm text-[#8c9097]">Acquisition Cost</label>
                            <input id="gsoInspectionAcquisitionCost" type="number" min="0" step="0.01" class="ti-form-input w-full" placeholder="0.00">
                            <div id="gsoInspectionAcquisitionCostErr" class="text-xs text-danger mt-1 hidden"></div>
                        </div>

                        <div>
                            <label class="text-sm text-[#8c9097]">Brand</label>
                            <input id="gsoInspectionBrand" type="text" class="ti-form-input w-full" placeholder="Optional brand">
                            <div id="gsoInspectionBrandErr" class="text-xs text-danger mt-1 hidden"></div>
                        </div>

                        <div>
                            <label class="text-sm text-[#8c9097]">Model</label>
                            <input id="gsoInspectionModel" type="text" class="ti-form-input w-full" placeholder="Optional model">
                            <div id="gsoInspectionModelErr" class="text-xs text-danger mt-1 hidden"></div>
                        </div>

                        <div>
                            <label class="text-sm text-[#8c9097]">Serial Number</label>
                            <input id="gsoInspectionSerialNumber" type="text" class="ti-form-input w-full" placeholder="Optional serial number">
                            <div id="gsoInspectionSerialNumberErr" class="text-xs text-danger mt-1 hidden"></div>
                        </div>

                        <div class="col-span-1 lg:col-span-3">
                            <label class="text-sm text-[#8c9097]">Observed Description</label>
                            <textarea id="gsoInspectionObservedDescription" class="ti-form-input w-full" rows="3" placeholder="Optional inspection description"></textarea>
                            <div id="gsoInspectionObservedDescriptionErr" class="text-xs text-danger mt-1 hidden"></div>
                        </div>

                        <div class="col-span-1 lg:col-span-3">
                            <label class="text-sm text-[#8c9097]">Remarks</label>
                            <textarea id="gsoInspectionRemarks" class="ti-form-input w-full" rows="3" placeholder="Optional remarks"></textarea>
                            <div id="gsoInspectionRemarksErr" class="text-xs text-danger mt-1 hidden"></div>
                        </div>
                    </div>
                </div>

                <div class="ti-modal-footer">
                    <button type="button" class="hs-dropdown-toggle ti-btn btn-wave ti-btn-secondary-full align-middle" data-hs-overlay="#gsoInspectionModal">
                        Close
                    </button>
                    <button type="button" id="gsoInspectionSaveBtn" class="ti-btn btn-wave bg-primary text-white !font-medium">
                        Save
                    </button>
                </div>
            </div>
        </div>
    </div>
@endif

<div id="gsoInspectionPhotoModal" class="hs-overlay hidden ti-modal [--overlay-backdrop:static]">
    <div class="hs-overlay-open:mt-7 ti-modal-box mt-0 ease-out !max-w-6xl">
        <div class="ti-modal-content">
            <div class="ti-modal-header">
                <div>
                    <h6 id="gsoInspectionPhotoModalTitle" class="modal-title text-[1rem] font-semibold">Inspection Photos</h6>
                    <p id="gsoInspectionPhotoModalSubtitle" class="text-sm text-[#8c9097] mt-1 mb-0">
                        Review uploaded inspection evidence.
                    </p>
                </div>
                <button type="button" class="hs-dropdown-toggle !text-[1rem] !font-semibold !text-defaulttextcolor" data-hs-overlay="#gsoInspectionPhotoModal">
                    <span class="sr-only">Close</span>
                    <i class="ri-close-line"></i>
                </button>
            </div>

            <div class="ti-modal-body px-4">
                <div id="gsoInspectionPhotoError" class="hidden mb-3 p-2 rounded bg-danger/10 text-danger text-sm"></div>

                @if($canManageInspections)
                    <div id="gsoInspectionPhotoUploadPanel" class="mb-4 rounded-lg border border-defaultborder p-3">
                        <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                            <div>
                                <div class="font-medium text-defaulttextcolor dark:text-white">Upload New Photos</div>
                                <div id="gsoInspectionPhotoUploadHint" class="text-xs text-[#8c9097] mt-1">
                                    Images are stored in the inspection's Google Drive folder.
                                </div>
                            </div>

                            <div class="flex flex-col gap-2 sm:flex-row sm:items-center">
                                <input
                                    id="gsoInspectionPhotoFiles"
                                    type="file"
                                    accept="image/*"
                                    multiple
                                    class="form-control w-full sm:w-[320px]"
                                >
                                <button type="button" id="gsoInspectionPhotoUploadBtn" class="ti-btn btn-wave ti-btn-primary-full">
                                    Upload Photos
                                </button>
                            </div>
                        </div>
                    </div>
                @endif

                <div id="gsoInspectionPhotoEmpty" class="hidden rounded-lg border border-dashed border-defaultborder p-6 text-center text-sm text-[#8c9097]">
                    No inspection photos have been uploaded yet.
                </div>

                <div id="gsoInspectionPhotoGrid" class="gso-inspection-photos-grid"></div>
            </div>

            <div class="ti-modal-footer">
                <button type="button" class="hs-dropdown-toggle ti-btn btn-wave ti-btn-secondary-full align-middle" data-hs-overlay="#gsoInspectionPhotoModal">
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
        window.__gsoInspections = {
            ajaxUrl: @json(route('gso.inspections.data')),
            showUrlTemplate: @json(route('gso.inspections.show', ['inspection' => '__ID__'])),
            storeUrl: @json(route('gso.inspections.store')),
            updateUrlTemplate: @json(route('gso.inspections.update', ['inspection' => '__ID__'])),
            deleteUrlTemplate: @json(route('gso.inspections.destroy', ['inspection' => '__ID__'])),
            restoreUrlTemplate: @json(route('gso.inspections.restore', ['inspection' => '__ID__'])),
            photoIndexUrlTemplate: @json(route('gso.inspections.photos.index', ['inspection' => '__INSPECTION__'])),
            photoStoreUrlTemplate: @json(route('gso.inspections.photos.store', ['inspection' => '__INSPECTION__'])),
            photoDestroyUrlTemplate: @json(route('gso.inspections.photos.destroy', ['inspection' => '__INSPECTION__', 'photo' => '__PHOTO__'])),
            csrf: @json(csrf_token()),
            canManage: @json($canManageInspections),
        };
    </script>
@endpush
