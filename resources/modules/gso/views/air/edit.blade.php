@extends('layouts.master')

@php
    $canManageAir = auth()->user()?->hasAnyRole(['Administrator', 'admin'])
        || auth()->user()?->can('modify AIR');
    $canForceDeleteAir = auth()->user()?->hasAnyRole(['Administrator', 'admin']);
    $isArchived = (bool) ($air['is_archived'] ?? false);
    $status = (string) ($air['status'] ?? '');
    $isDraft = $status === 'draft';
    $canEditDraft = $canManageAir && ! $isArchived && $isDraft;
    $canManageFiles = $canManageAir && ! $isArchived;
    $canInspect = ! $isArchived && in_array($status, ['submitted', 'in_progress'], true);
    $canViewInspection = ! $isArchived && $status === 'inspected';
    $canPrintAir = ! $isArchived && in_array($status, ['submitted', 'in_progress', 'inspected'], true);
    $continuationNo = max(1, (int) ($air['continuation_no'] ?? 1));
@endphp

@section('styles')
    <style>
        .gso-air-legacy-shell {
            width: 100%;
            max-width: 84rem;
            margin: 0 auto;
        }

        .gso-air-legacy-grid {
            display: grid;
            grid-template-columns: repeat(1, minmax(0, 1fr));
            gap: 1rem;
            align-items: start;
        }

        .gso-air-legacy-meta {
            display: grid;
            grid-template-columns: repeat(1, minmax(0, 1fr));
            gap: 1rem;
        }

        .gso-air-legacy-file-grid {
            display: grid;
            grid-template-columns: repeat(1, minmax(0, 1fr));
            gap: 1rem;
        }

        .gso-air-file-card {
            overflow: hidden;
            border: 1px solid rgba(15, 23, 42, 0.08);
            border-radius: 0.85rem;
            background: rgba(255, 255, 255, 0.92);
            box-shadow: 0 16px 36px -28px rgba(15, 23, 42, 0.45);
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

        @media (min-width: 1024px) {
            .gso-air-legacy-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .gso-air-legacy-meta {
                grid-template-columns: repeat(3, minmax(0, 1fr));
            }
        }

        @media (min-width: 768px) {
            .gso-air-legacy-file-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }
    </style>
@endsection

@section('content')
    <div id="gso-air-edit-page">
        <div class="page-header md:flex items-start justify-between gap-4">
            <div>
                <h3 class="!text-defaulttextcolor dark:!text-defaulttextcolor/70 dark:text-white text-[1.125rem] font-semibold">
                    Edit AIR Draft
                </h3>
                <p class="text-xs text-[#8c9097] mt-1">
                    This draft already exists. Saving will update the same record without leaving the page.
                </p>
                <div class="text-xs text-[#8c9097] mt-2">
                    Status: <b>{{ strtoupper($status !== '' ? $status : 'draft') }}</b>
                    @if(!empty($air['label']))
                        <span>&middot;</span> AIR: <b>{{ $air['label'] }}</b>
                    @endif
                </div>
                @if($continuationNo > 1)
                    <p class="text-xs text-info mt-2">
                        This is follow-up AIR #{{ $continuationNo }} for unresolved items carried over from an earlier partial inspection.
                    </p>
                @endif
            </div>

            <div class="mt-3 md:mt-0 flex flex-wrap items-center gap-2">
                @if($canEditDraft)
                    <button type="button" id="gsoAirSaveBtn" class="ti-btn ti-btn-primary">
                        Save
                    </button>
                    <button type="button" id="gsoAirSubmitBtn" class="ti-btn ti-btn-success">
                        Submit
                    </button>
                    <button type="button" id="gsoAirArchiveBtn" class="ti-btn ti-btn-danger">
                        Archive
                    </button>
                @endif

                @if($canManageAir && $isArchived)
                    <button type="button" id="gsoAirRestoreBtn" class="ti-btn ti-btn-success">
                        Restore
                    </button>
                    @if($canForceDeleteAir)
                        <button type="button" id="gsoAirForceDeleteBtn" class="ti-btn ti-btn-danger">
                            Force Delete
                        </button>
                    @endif
                @endif

                @if($canInspect)
                    <a href="{{ route('gso.air.inspect', ['air' => $air['id'] ?? '']) }}" class="ti-btn ti-btn-warning">
                        Inspect
                    </a>
                @elseif($canViewInspection)
                    <a href="{{ route('gso.air.inspect', ['air' => $air['id'] ?? '']) }}" class="ti-btn ti-btn-warning">
                        Inspection Report
                    </a>
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

                <a href="{{ route('gso.air.index') }}" class="ti-btn ti-btn-light">
                    Back
                </a>
            </div>
        </div>

        @if($isArchived)
            <div class="mb-4 rounded border border-warning bg-warning/10 px-4 py-3 text-sm text-warning">
                This AIR is archived. Restore it before editing the draft or its supporting documents.
            </div>
        @elseif(! $isDraft)
            <div class="mb-4 rounded border border-primary bg-primary/10 px-4 py-3 text-sm text-primary">
                This AIR is already in <b>{{ $air['status_text'] ?? 'workflow' }}</b> status. Draft header editing is locked, but the record remains available here for review, printing, and inspection continuity.
            </div>
        @endif

        <div class="gso-air-legacy-shell">
            <div class="gso-air-legacy-grid">
                <div>
                    <div class="box">
                        <div class="box-header flex items-start justify-between gap-3">
                            <div>
                                <h5 class="box-title">AIR Draft Header</h5>
                                <div class="text-xs text-[#8c9097] mt-1">
                                    Status: <b>{{ strtoupper($status !== '' ? $status : 'draft') }}</b>
                                </div>
                            </div>
                            @if(!empty($air['continuation_label']))
                                <span class="rounded-full bg-light px-3 py-1 text-xs text-[#8c9097] dark:bg-black/20 dark:text-white/50">
                                    {{ $air['continuation_label'] }}
                                </span>
                            @endif
                        </div>

                        <div class="box-body">
                            <div id="gsoAirFormError" class="hidden mb-4 rounded bg-danger/10 p-3 text-sm text-danger"></div>

                            <form id="gsoAirEditForm" class="space-y-5">
                                <div class="gso-air-legacy-meta">
                                    <div>
                                        <label class="ti-form-label">PO Number <span class="text-danger">*</span></label>
                                        <input id="gsoAirPoNumber" type="text" class="ti-form-input w-full" value="{{ $air['po_number'] ?? '' }}" placeholder="e.g. 2026-PO-001" @disabled(! $canEditDraft)>
                                        <div id="gsoAirPoNumberErr" class="hidden mt-1 text-xs text-danger"></div>
                                    </div>

                                    <div>
                                        <label class="ti-form-label">PO Date <span class="text-danger">*</span></label>
                                        <input id="gsoAirPoDate" type="date" class="ti-form-input w-full" value="{{ $air['po_date'] ?? '' }}" @disabled(! $canEditDraft)>
                                        <div id="gsoAirPoDateErr" class="hidden mt-1 text-xs text-danger"></div>
                                    </div>

                                    <div>
                                        <label class="ti-form-label">Fund Source <span class="text-danger">*</span></label>
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
                                        <label class="ti-form-label">AIR Number</label>
                                        <input id="gsoAirAirNumber" type="text" class="ti-form-input w-full bg-gray-100 dark:bg-black/20" value="{{ $air['air_number'] ?? '' }}" placeholder="Automatically generated on submission" readonly>
                                        <div id="gsoAirAirNumberErr" class="hidden mt-1 text-xs text-danger"></div>
                                        <p class="text-[11px] text-[#8c9097] mt-1">
                                            This number is generated once the draft is submitted.
                                        </p>
                                    </div>

                                    <div>
                                        <label class="ti-form-label">AIR Date <span class="text-danger">*</span></label>
                                        <input id="gsoAirAirDate" type="date" class="ti-form-input w-full" value="{{ $air['air_date'] ?? '' }}" @disabled(! $canEditDraft)>
                                        <div id="gsoAirAirDateErr" class="hidden mt-1 text-xs text-danger"></div>
                                    </div>

                                    <div>
                                        <label class="ti-form-label">Requesting Department <span class="text-danger">*</span></label>
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
                                </div>

                                <div>
                                    <label class="ti-form-label">Supplier Name <span class="text-danger">*</span></label>
                                    <input id="gsoAirSupplierName" type="text" class="ti-form-input w-full" value="{{ $air['supplier_name'] ?? '' }}" placeholder="Supplier name" @disabled(! $canEditDraft)>
                                    <div id="gsoAirSupplierNameErr" class="hidden mt-1 text-xs text-danger"></div>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label class="ti-form-label">Inspected By (name) <span class="text-danger">*</span></label>
                                        <input id="gsoAirInspectedByName" type="text" class="ti-form-input w-full" value="{{ $air['inspected_by_name'] ?? '________________________' }}" @disabled(! $canEditDraft)>
                                        <div id="gsoAirInspectedByNameErr" class="hidden mt-1 text-xs text-danger"></div>
                                    </div>

                                    <div>
                                        <label class="ti-form-label">Accepted By (name) <span class="text-danger">*</span></label>
                                        <input id="gsoAirAcceptedByName" type="text" class="ti-form-input w-full" value="{{ $air['accepted_by_name'] ?? '________________________' }}" @disabled(! $canEditDraft)>
                                        <div id="gsoAirAcceptedByNameErr" class="hidden mt-1 text-xs text-danger"></div>
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label class="ti-form-label">Invoice / DR / SI Number</label>
                                        <input id="gsoAirInvoiceNumber" type="text" class="ti-form-input w-full" value="{{ $air['invoice_number'] ?? '' }}" placeholder="Optional while draft is open" @disabled(! $canEditDraft)>
                                        <div id="gsoAirInvoiceNumberErr" class="hidden mt-1 text-xs text-danger"></div>
                                    </div>

                                    <div>
                                        <label class="ti-form-label">Invoice / DR / SI Date</label>
                                        <input id="gsoAirInvoiceDate" type="date" class="ti-form-input w-full" value="{{ $air['invoice_date'] ?? '' }}" @disabled(! $canEditDraft)>
                                        <div id="gsoAirInvoiceDateErr" class="hidden mt-1 text-xs text-danger"></div>
                                    </div>
                                </div>

                                <div>
                                    <label class="ti-form-label">Remarks (optional)</label>
                                    <textarea id="gsoAirRemarks" class="ti-form-input w-full" rows="3" placeholder="Notes..." @disabled(! $canEditDraft)>{{ $air['remarks'] ?? '' }}</textarea>
                                    <div id="gsoAirRemarksErr" class="hidden mt-1 text-xs text-danger"></div>
                                </div>

                                <div class="text-xs text-[#8c9097]">
                                    Tip: use the toolbar Save button to keep header changes and AIR item edits together.
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <div>
                    <div class="box">
                        <div class="box-header flex items-start justify-between gap-3">
                            <div>
                                <h5 class="box-title">Items to Inspect</h5>
                                <div class="text-xs text-[#8c9097] mt-1">
                                    Build the AIR draft item list from the current catalog, then submit when the draft is ready for inspection.
                                </div>
                            </div>
                            <div class="text-xs text-[#8c9097] bg-light px-3 py-2 rounded dark:bg-black/20 dark:text-white/50">
                                Selected: <b id="gsoAirItemCount">0</b>
                            </div>
                        </div>

                        <div class="box-body space-y-4">
                            @if(! $canEditDraft)
                                <div class="rounded border border-warning bg-warning/10 px-3 py-2 text-xs text-warning">
                                    Items can no longer be changed because this AIR is no longer in draft status.
                                </div>
                            @endif

                            @if($canEditDraft)
                                <div class="space-y-2">
                                    <label for="gsoAirItemSearch" class="ti-form-label">Search Item</label>

                                    <div class="relative">
                                        <input
                                            id="gsoAirItemSearch"
                                            type="search"
                                            class="ti-form-input w-full"
                                            placeholder="Type item name, description, or stock number..."
                                            autocomplete="off"
                                        >

                                        <div
                                            id="gsoAirItemSuggestions"
                                            class="hidden absolute right-0 mt-2 w-full z-[9999] rounded-md border border-defaultborder bg-white dark:bg-bodybg shadow-lg"
                                        ></div>
                                    </div>

                                    <div class="text-[11px] text-[#8c9097]">
                                        Tip: click a suggestion to add it. Unsaved row edits are included in the toolbar Save button.
                                    </div>
                                </div>
                            @endif

                            <div id="gsoAirItemError" class="hidden rounded bg-danger/10 p-3 text-sm text-danger"></div>

                            <div class="pt-2 border-t border-defaultborder">
                                <div class="flex items-center justify-between mb-2">
                                    <div class="font-semibold text-sm">Selected</div>
                                    <div class="text-xs text-[#8c9097]">
                                        <span id="gsoAirItemCountSummary">0</span>
                                    </div>
                                </div>

                                <div id="gsoAirItemList" class="space-y-3">
                                    <div class="text-xs text-[#8c9097]">Loading items...</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-4">
                <div class="box">
                    <div class="box-header flex items-start justify-between gap-3">
                        <div>
                            <h5 class="box-title">Supporting Documents</h5>
                            <div class="text-xs text-[#8c9097] mt-1">
                                AIR-level supporting files are stored in the platform and linked to the same canonical AIR record.
                            </div>
                        </div>
                        <div class="text-xs text-[#8c9097] bg-light px-3 py-2 rounded dark:bg-black/20 dark:text-white/50">
                            Files: <b id="gsoAirFileCount">0</b>
                            <span class="mx-1">&middot;</span>
                            Drive: <b id="gsoAirDriveFolderStatus">{{ !empty($air['drive_folder_id']) ? 'Ready' : 'Pending' }}</b>
                        </div>
                    </div>

                    <div class="box-body space-y-4">
                        <div id="gsoAirFileError" class="hidden rounded bg-danger/10 p-3 text-sm text-danger"></div>

                        <div
                            id="gsoAirFileUploadPanel"
                            class="{{ $canManageAir ? '' : 'hidden ' }}rounded-xl border border-dashed border-defaultborder p-4"
                        >
                            <div class="grid gap-3 lg:grid-cols-[minmax(0,1fr)_220px_auto]">
                                <div>
                                    <label for="gsoAirFilesInput" class="ti-form-label !mb-1">Upload Files</label>
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
                                    <label for="gsoAirFilesType" class="ti-form-label !mb-1">Document Type</label>
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
                                        class="ti-btn ti-btn-primary w-full lg:w-auto"
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

                        <div id="gsoAirFileGrid" class="gso-air-legacy-file-grid"></div>
                        <div
                            id="gsoAirFileEmpty"
                            class="hidden rounded border border-dashed border-defaultborder p-4 text-sm text-[#8c9097] dark:text-white/50"
                        >
                            No AIR documents uploaded yet.
                        </div>
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
