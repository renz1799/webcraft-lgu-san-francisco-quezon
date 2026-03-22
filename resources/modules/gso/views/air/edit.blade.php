@extends('layouts.master')

@php
    $canManageAir = auth()->user()?->hasAnyRole(['Administrator', 'admin'])
        || auth()->user()?->can('modify AIR');
    $canForceDeleteAir = auth()->user()?->hasAnyRole(['Administrator', 'admin']);
    $isArchived = (bool) ($air['is_archived'] ?? false);
    $isDraft = (string) ($air['status'] ?? '') === 'draft';
    $canEditDraft = $canManageAir && ! $isArchived && $isDraft;
    $canManageFiles = $canManageAir && ! $isArchived;
@endphp

@section('styles')
    <style>
        .gso-air-edit-grid {
            display: grid;
            grid-template-columns: repeat(1, minmax(0, 1fr));
            gap: 16px;
        }

        .gso-air-edit-meta {
            display: grid;
            grid-template-columns: repeat(1, minmax(0, 1fr));
            gap: 12px;
        }

        @media (min-width: 1024px) {
            .gso-air-edit-grid {
                grid-template-columns: minmax(0, 2fr) minmax(320px, 1fr);
            }

            .gso-air-edit-meta {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        .gso-air-file-grid {
            display: grid;
            grid-template-columns: repeat(1, minmax(0, 1fr));
            gap: 16px;
        }

        @media (min-width: 768px) {
            .gso-air-file-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        .gso-air-file-card {
            overflow: hidden;
            border: 1px solid rgba(15, 23, 42, 0.08);
            border-radius: 0.85rem;
            background: rgba(255, 255, 255, 0.9);
            box-shadow: 0 18px 40px -30px rgba(15, 23, 42, 0.45);
        }

        .dark .gso-air-file-card {
            background: rgba(15, 23, 42, 0.45);
            border-color: rgba(148, 163, 184, 0.14);
        }

        .gso-air-file-preview {
            display: block;
            width: 100%;
            height: 208px;
            object-fit: cover;
            background: linear-gradient(135deg, rgba(226, 232, 240, 0.72), rgba(241, 245, 249, 0.98));
        }

        .gso-air-file-fallback {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 100%;
            height: 208px;
            padding: 1rem;
            text-align: center;
            font-size: 0.875rem;
            font-weight: 600;
            color: rgb(71 85 105 / 1);
            background:
                radial-gradient(circle at top left, rgba(56, 189, 248, 0.16), transparent 42%),
                linear-gradient(135deg, rgba(226, 232, 240, 0.88), rgba(248, 250, 252, 1));
        }

        .dark .gso-air-file-fallback {
            color: rgb(226 232 240 / 1);
            background:
                radial-gradient(circle at top left, rgba(56, 189, 248, 0.22), transparent 38%),
                linear-gradient(135deg, rgba(30, 41, 59, 0.92), rgba(15, 23, 42, 0.98));
        }
    </style>
@endsection

@section('content')
<div id="gso-air-edit-page">
    <div class="block justify-between page-header md:flex">
        <div>
            <h3 class="!text-defaulttextcolor dark:!text-defaulttextcolor/70 dark:text-white text-[1.125rem] font-semibold">
                {{ $air['label'] ?? 'AIR Record' }}
            </h3>
            <p class="text-[#8c9097] dark:text-white/50 text-[0.813rem] mb-0">
                AIR header metadata and workflow state now live inside the platform.
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
                Edit
            </li>
        </ol>
    </div>

    @if($isArchived)
        <div class="box border border-warning/20">
            <div class="box-body text-warning">
                This AIR is archived. Restore it to continue editing.
            </div>
        </div>
    @elseif(! $isDraft)
        <div class="box border border-primary/20">
            <div class="box-body text-primary">
                This AIR is already in <strong>{{ $air['status_text'] ?? 'workflow' }}</strong> status.
                Draft header editing is locked in this migration slice, but the record remains visible here for continuity.
            </div>
        </div>
    @endif

    <div class="gso-air-edit-grid">
        <div class="space-y-4">
            <div class="box">
                <div class="box-header">
                    <div class="box-title">AIR Header</div>
                </div>
                <div class="box-body">
                    <div id="gsoAirFormError" class="hidden mb-3 rounded bg-danger/10 p-3 text-sm text-danger"></div>

                    <form id="gsoAirEditForm" class="space-y-4">
                        <div class="gso-air-edit-meta">
                            <div>
                                <label class="text-sm text-[#8c9097]">PO Number</label>
                                <input id="gsoAirPoNumber" type="text" class="ti-form-input w-full" value="{{ $air['po_number'] ?? '' }}" @disabled(! $canEditDraft)>
                                <div id="gsoAirPoNumberErr" class="hidden mt-1 text-xs text-danger"></div>
                            </div>

                            <div>
                                <label class="text-sm text-[#8c9097]">PO Date</label>
                                <input id="gsoAirPoDate" type="date" class="ti-form-input w-full" value="{{ $air['po_date'] ?? '' }}" @disabled(! $canEditDraft)>
                                <div id="gsoAirPoDateErr" class="hidden mt-1 text-xs text-danger"></div>
                            </div>

                            <div>
                                <label class="text-sm text-[#8c9097]">AIR Number</label>
                                <input id="gsoAirAirNumber" type="text" class="ti-form-input w-full" value="{{ $air['air_number'] ?? '' }}" @disabled(! $canEditDraft)>
                                <div id="gsoAirAirNumberErr" class="hidden mt-1 text-xs text-danger"></div>
                            </div>

                            <div>
                                <label class="text-sm text-[#8c9097]">AIR Date</label>
                                <input id="gsoAirAirDate" type="date" class="ti-form-input w-full" value="{{ $air['air_date'] ?? '' }}" @disabled(! $canEditDraft)>
                                <div id="gsoAirAirDateErr" class="hidden mt-1 text-xs text-danger"></div>
                            </div>

                            <div>
                                <label class="text-sm text-[#8c9097]">Invoice Number</label>
                                <input id="gsoAirInvoiceNumber" type="text" class="ti-form-input w-full" value="{{ $air['invoice_number'] ?? '' }}" @disabled(! $canEditDraft)>
                                <div id="gsoAirInvoiceNumberErr" class="hidden mt-1 text-xs text-danger"></div>
                            </div>

                            <div>
                                <label class="text-sm text-[#8c9097]">Invoice Date</label>
                                <input id="gsoAirInvoiceDate" type="date" class="ti-form-input w-full" value="{{ $air['invoice_date'] ?? '' }}" @disabled(! $canEditDraft)>
                                <div id="gsoAirInvoiceDateErr" class="hidden mt-1 text-xs text-danger"></div>
                            </div>

                            <div class="lg:col-span-2">
                                <label class="text-sm text-[#8c9097]">Supplier Name</label>
                                <input id="gsoAirSupplierName" type="text" class="ti-form-input w-full" value="{{ $air['supplier_name'] ?? '' }}" @disabled(! $canEditDraft)>
                                <div id="gsoAirSupplierNameErr" class="hidden mt-1 text-xs text-danger"></div>
                            </div>

                            <div>
                                <label class="text-sm text-[#8c9097]">Requesting Department</label>
                                <select id="gsoAirDepartmentId" class="ti-form-select w-full" @disabled(! $canEditDraft)>
                                    <option value="">Select department</option>
                                    @foreach($departments as $department)
                                        <option value="{{ $department->id }}" @selected((string) ($air['requesting_department_id'] ?? '') === (string) $department->id)>
                                            {{ trim((string) $department->code) !== '' ? $department->code . ' - ' : '' }}{{ $department->name }}
                                            @if($department->deleted_at)
                                                (Archived)
                                            @endif
                                        </option>
                                    @endforeach
                                </select>
                                <div id="gsoAirDepartmentIdErr" class="hidden mt-1 text-xs text-danger"></div>
                            </div>

                            <div>
                                <label class="text-sm text-[#8c9097]">Fund Source</label>
                                <select id="gsoAirFundSourceId" class="ti-form-select w-full" @disabled(! $canEditDraft)>
                                    <option value="">Select fund source</option>
                                    @foreach($fundSources as $fundSource)
                                        <option value="{{ $fundSource->id }}" @selected((string) ($air['fund_source_id'] ?? '') === (string) $fundSource->id)>
                                            {{ trim((string) $fundSource->code) !== '' ? $fundSource->code . ' - ' : '' }}{{ $fundSource->name }}
                                            @if($fundSource->deleted_at)
                                                (Archived)
                                            @endif
                                        </option>
                                    @endforeach
                                </select>
                                <div id="gsoAirFundSourceIdErr" class="hidden mt-1 text-xs text-danger"></div>
                            </div>

                            <div>
                                <label class="text-sm text-[#8c9097]">Inspected By</label>
                                <input id="gsoAirInspectedByName" type="text" class="ti-form-input w-full" value="{{ $air['inspected_by_name'] ?? '' }}" @disabled(! $canEditDraft)>
                                <div id="gsoAirInspectedByNameErr" class="hidden mt-1 text-xs text-danger"></div>
                            </div>

                            <div>
                                <label class="text-sm text-[#8c9097]">Accepted By</label>
                                <input id="gsoAirAcceptedByName" type="text" class="ti-form-input w-full" value="{{ $air['accepted_by_name'] ?? '' }}" @disabled(! $canEditDraft)>
                                <div id="gsoAirAcceptedByNameErr" class="hidden mt-1 text-xs text-danger"></div>
                            </div>

                            <div class="lg:col-span-2">
                                <label class="text-sm text-[#8c9097]">Remarks</label>
                                <textarea id="gsoAirRemarks" class="ti-form-input w-full" rows="5" @disabled(! $canEditDraft)>{{ $air['remarks'] ?? '' }}</textarea>
                                <div id="gsoAirRemarksErr" class="hidden mt-1 text-xs text-danger"></div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="ti-modal-footer !justify-between">
                    <a href="{{ route('gso.air.index') }}" class="ti-btn ti-btn-light">Back to Register</a>
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
                        @if($canEditDraft)
                            <button type="button" id="gsoAirSaveBtn" class="ti-btn btn-wave ti-btn-primary-full">
                                Save Draft
                            </button>
                            <button type="button" id="gsoAirSubmitBtn" class="ti-btn btn-wave ti-btn-success-full">
                                Submit AIR
                            </button>
                            <button type="button" id="gsoAirArchiveBtn" class="ti-btn btn-wave ti-btn-danger-full">
                                Archive
                            </button>
                        @elseif(! $isArchived && ! $isDraft)
                            <a href="{{ route('gso.air.inspect', ['air' => $air['id'] ?? '']) }}" class="ti-btn btn-wave ti-btn-primary-full">
                                Open Inspection Workspace
                            </a>
                        @elseif($canManageAir && $isArchived)
                            <button type="button" id="gsoAirRestoreBtn" class="ti-btn btn-wave ti-btn-success-full">
                                Restore
                            </button>
                            @if($canForceDeleteAir)
                                <button type="button" id="gsoAirForceDeleteBtn" class="ti-btn btn-wave ti-btn-danger-full">
                                    Force Delete
                                </button>
                            @endif
                        @endif
                    </div>
                </div>
            </div>

            <div class="box">
                <div class="box-header">
                    <div class="box-title">AIR Documents</div>
                    <div class="rounded bg-light px-3 py-2 text-sm dark:bg-black/20">
                        <span class="text-[#8c9097]">Files:</span>
                        <span id="gsoAirFileCount" class="font-semibold">0</span>
                    </div>
                </div>
                <div class="box-body space-y-4">
                    <div class="flex flex-wrap items-start justify-between gap-3">
                        <div>
                            <p class="mb-1 text-sm font-medium">Document Attachments</p>
                            <p class="mb-0 text-xs text-[#8c9097] dark:text-white/50">
                                AIR-level supporting files now stay inside the platform and reuse the shared Google Drive connection.
                            </p>
                        </div>
                        <div class="rounded bg-light px-3 py-2 text-xs text-[#8c9097] dark:bg-black/20 dark:text-white/50">
                            Images and PDFs only
                        </div>
                    </div>

                    <div id="gsoAirFileError" class="hidden rounded bg-danger/10 p-3 text-sm text-danger"></div>

                    <div
                        id="gsoAirFileUploadPanel"
                        class="{{ $canManageAir ? '' : 'hidden ' }}rounded-xl border border-dashed border-defaultborder p-4"
                    >
                        <div class="grid gap-3 lg:grid-cols-[minmax(0,1fr)_220px_auto]">
                            <div>
                                <label for="gsoAirFilesInput" class="mb-1 block text-sm text-[#8c9097]">Upload Files</label>
                                <input
                                    id="gsoAirFilesInput"
                                    type="file"
                                    class="ti-form-input w-full"
                                    accept="image/*,.pdf,application/pdf"
                                    multiple
                                    @disabled(! $canManageFiles)
                                >
                            </div>
                            <div>
                                <label for="gsoAirFilesType" class="mb-1 block text-sm text-[#8c9097]">Document Type</label>
                                <select id="gsoAirFilesType" class="ti-form-select w-full" @disabled(! $canManageFiles)>
                                    <option value="">Auto detect</option>
                                    <option value="photo">Photo</option>
                                    <option value="pdf">PDF</option>
                                    <option value="document">Document</option>
                                    <option value="receipt">Receipt</option>
                                    <option value="property_card">Property Card</option>
                                    <option value="other">Other</option>
                                </select>
                            </div>
                            <div class="flex items-end">
                                <button
                                    type="button"
                                    id="gsoAirFileUploadBtn"
                                    class="ti-btn btn-wave ti-btn-primary-full w-full lg:w-auto"
                                    @disabled(! $canManageFiles)
                                >
                                    Upload
                                </button>
                            </div>
                        </div>
                        <p id="gsoAirFileUploadHint" class="mt-3 mb-0 text-xs text-[#8c9097] dark:text-white/50">
                            Save a PO number in the AIR header before uploading documents.
                        </p>
                    </div>

                    <div id="gsoAirFileGrid" class="gso-air-file-grid"></div>
                    <div
                        id="gsoAirFileEmpty"
                        class="hidden rounded border border-dashed border-defaultborder p-4 text-sm text-[#8c9097] dark:text-white/50"
                    >
                        No AIR documents uploaded yet.
                    </div>
                </div>
            </div>

            <div class="box">
                <div class="box-header">
                    <div class="box-title">AIR Items</div>
                </div>
                <div class="box-body space-y-4">
                    <div class="flex flex-wrap items-start justify-between gap-3">
                        <div>
                            <p class="mb-1 text-sm font-medium">Selected Rows</p>
                            <p class="mb-0 text-xs text-[#8c9097] dark:text-white/50">
                                Item rows now live in-platform. Inspection units and inventory promotion will layer on top of these saved draft rows next.
                            </p>
                        </div>
                        <div class="rounded bg-light px-3 py-2 text-sm dark:bg-black/20">
                            <span class="text-[#8c9097]">Rows:</span>
                            <span id="gsoAirItemCount" class="font-semibold">0</span>
                        </div>
                    </div>

                    @if($canEditDraft)
                        <div class="space-y-2">
                            <label for="gsoAirItemSearch" class="text-sm text-[#8c9097]">Add Item From Catalog</label>
                            <div class="relative">
                                <input id="gsoAirItemSearch" type="search" class="ti-form-input w-full" placeholder="Search by item name, description, or stock number">
                                <div id="gsoAirItemSuggestions" class="hidden absolute left-0 right-0 z-10 mt-2 max-h-80 overflow-auto rounded-md border border-defaultborder bg-white shadow-lg dark:bg-bodybg"></div>
                            </div>
                            <p class="mb-0 text-xs text-[#8c9097] dark:text-white/50">
                                Item row edits are saved together with the draft toolbar actions. Remove rows immediately from the card actions when needed.
                            </p>
                        </div>
                    @else
                        <div class="rounded bg-light p-3 text-sm text-[#8c9097] dark:bg-black/20 dark:text-white/50">
                            AIR item rows are read-only because this record is no longer an editable draft.
                        </div>
                    @endif

                    <div id="gsoAirItemError" class="hidden rounded bg-danger/10 p-3 text-sm text-danger"></div>
                    <div id="gsoAirItemList" class="space-y-3">
                        <div class="rounded border border-dashed border-defaultborder p-4 text-sm text-[#8c9097] dark:text-white/50">
                            Loading AIR items...
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="space-y-4">
            <div class="box">
                <div class="box-header">
                    <div class="box-title">Record Summary</div>
                </div>
                <div class="box-body text-sm space-y-3">
                    <div class="flex items-center justify-between gap-3">
                        <span class="text-[#8c9097]">Workflow Status</span>
                        <span class="font-medium">{{ $air['status_text'] ?? 'Unknown' }}</span>
                    </div>
                    <div class="flex items-center justify-between gap-3">
                        <span class="text-[#8c9097]">Continuation</span>
                        <span class="font-medium">{{ $air['continuation_label'] ?? 'Root AIR' }}</span>
                    </div>
                    <div class="flex items-center justify-between gap-3">
                        <span class="text-[#8c9097]">Created By</span>
                        <span class="font-medium text-right">{{ $air['created_by_label'] ?? 'System User' }}</span>
                    </div>
                    <div class="flex items-center justify-between gap-3">
                        <span class="text-[#8c9097]">Created At</span>
                        <span class="font-medium text-right">{{ $air['created_at_text'] ?? '-' }}</span>
                    </div>
                    @if(!empty($air['deleted_at_text']))
                        <div class="flex items-center justify-between gap-3">
                            <span class="text-[#8c9097]">Archived At</span>
                            <span class="font-medium text-right">{{ $air['deleted_at_text'] }}</span>
                        </div>
                    @endif
                    <div class="flex items-center justify-between gap-3">
                        <span class="text-[#8c9097]">Department Snapshot</span>
                        <span class="font-medium text-right">{{ $air['department_label'] ?? 'None' }}</span>
                    </div>
                    <div class="flex items-center justify-between gap-3">
                        <span class="text-[#8c9097]">Fund Source Snapshot</span>
                        <span class="font-medium text-right">{{ $air['fund_source_label'] ?? 'None' }}</span>
                    </div>
                    <div class="flex items-center justify-between gap-3">
                        <span class="text-[#8c9097]">Item Rows</span>
                        <span id="gsoAirItemCountSummary" class="font-medium text-right">0</span>
                    </div>
                    <div class="flex items-center justify-between gap-3">
                        <span class="text-[#8c9097]">Document Files</span>
                        <span id="gsoAirFileCountSummary" class="font-medium text-right">0</span>
                    </div>
                    <div class="flex items-center justify-between gap-3">
                        <span class="text-[#8c9097]">Drive Folder</span>
                        <span id="gsoAirDriveFolderStatus" class="font-medium text-right">
                            {{ !empty($air['drive_folder_id']) ? 'Ready' : 'Pending' }}
                        </span>
                    </div>
                </div>
            </div>

            <div class="box">
                <div class="box-header">
                    <div class="box-title">Migration Boundary</div>
                </div>
                <div class="box-body text-sm text-[#8c9097] dark:text-white/50 space-y-3">
                    <p class="mb-0">
                        AIR headers, AIR item rows, AIR document files, and AIR print preview now run inside the platform on the same canonical document URL.
                    </p>
                    <p class="mb-0">
                        Follow-up AIR generation and AIR component tracking still layer on top of this saved AIR data next.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
    <script>
        window.__gsoAirEdit = {
            id: @json($air['id'] ?? ''),
            updateUrl: @json(route('gso.air.update', ['air' => $air['id'] ?? ''])),
            submitUrl: @json(route('gso.air.submit', ['air' => $air['id'] ?? ''])),
            deleteUrl: @json(route('gso.air.destroy', ['air' => $air['id'] ?? ''])),
            restoreUrl: @json(route('gso.air.restore', ['air' => $air['id'] ?? ''])),
            forceDeleteUrl: @json(route('gso.air.force-destroy', ['air' => $air['id'] ?? ''])),
            itemListUrl: @json(route('gso.air.items.index', ['air' => $air['id'] ?? ''])),
            itemSuggestUrl: @json(route('gso.air.items.suggest', ['air' => $air['id'] ?? ''])),
            itemStoreUrl: @json(route('gso.air.items.store', ['air' => $air['id'] ?? ''])),
            itemBulkUpdateUrl: @json(route('gso.air.items.bulk-update', ['air' => $air['id'] ?? ''])),
            itemUpdateUrlTemplate: @json(route('gso.air.items.update', ['air' => $air['id'] ?? '', 'airItem' => '__ID__'])),
            itemDeleteUrlTemplate: @json(route('gso.air.items.destroy', ['air' => $air['id'] ?? '', 'airItem' => '__ID__'])),
            filesIndexUrl: @json(route('gso.air.files.index', ['air' => $air['id'] ?? ''])),
            filesStoreUrl: @json(route('gso.air.files.store', ['air' => $air['id'] ?? ''])),
            fileDestroyUrlTemplate: @json(route('gso.air.files.destroy', ['air' => $air['id'] ?? '', 'file' => '__FILE__'])),
            filePrimaryUrlTemplate: @json(route('gso.air.files.set-primary', ['air' => $air['id'] ?? '', 'file' => '__FILE__'])),
            indexUrl: @json(route('gso.air.index')),
            editUrl: @json(route('gso.air.edit', ['air' => $air['id'] ?? ''])),
            csrf: @json(csrf_token()),
            canManage: @json($canManageAir),
            canEditDraft: @json($canEditDraft),
            isArchived: @json($isArchived),
            canForceDelete: @json($canForceDeleteAir),
        };
    </script>
@endpush
