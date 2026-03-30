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
    $canForceDeleteAir = $airAuthorizer->allowsPermission($airViewer, 'air.archive');
    $status = (string) ($air['status'] ?? '');
    $isArchived = (bool) ($air['is_archived'] ?? false);
    $isLocked = ! $canManageAir || $isArchived || $status !== 'draft';
    $canPrintAir = ! $isArchived
        && in_array($status, ['submitted', 'inspected', 'in_progress'], true)
        && $airAuthorizer->allowsAnyPermission($airViewer, ['air.print', 'air.view', 'air.update']);
    $selectedFundId = trim((string) old('fund_source_id', (string) ($air['fund_source_id'] ?? '')));
    if ($selectedFundId === '') {
        $legacyFundValue = trim((string) ($air['fund'] ?? ''));
        $matchedLegacyFund = $legacyFundValue !== ''
            ? $fundSources->first(function ($fund) use ($legacyFundValue) {
                return strcasecmp(trim((string) ($fund->name ?? '')), $legacyFundValue) === 0
                    || strcasecmp(trim((string) ($fund->code ?? '')), $legacyFundValue) === 0;
            })
            : null;
        $selectedFundId = (string) (($matchedLegacyFund?->id) ?? ($fundSources->first()?->id ?? ''));
    }
    $persistedValues = [
        'po_number' => (string) ($air['po_number'] ?? ''),
        'po_date' => (string) ($air['po_date'] ?? ''),
        'air_number' => (string) ($air['air_number'] ?? ''),
        'air_date' => (string) ($air['air_date'] ?? ''),
        'invoice_number' => (string) ($air['invoice_number'] ?? ''),
        'invoice_date' => (string) ($air['invoice_date'] ?? ''),
        'supplier_name' => (string) ($air['supplier_name'] ?? ''),
        'requesting_department_id' => (string) ($air['requesting_department_id'] ?? ''),
        'fund_source_id' => (string) ($air['fund_source_id'] ?? ''),
        'inspected_by_name' => (string) ($air['inspected_by_name'] ?? ''),
        'accepted_by_name' => (string) ($air['accepted_by_name'] ?? ''),
        'remarks' => (string) ($air['remarks'] ?? ''),
    ];
@endphp

@section('styles')
  <meta name="csrf-token" content="{{ csrf_token() }}">
@endsection

@section('content')
<div id="gso-air-edit-page">
  <div class="page-header md:flex items-start justify-between gap-4">
    <div>
      <h3 class="!text-defaulttextcolor dark:!text-defaulttextcolor/70 dark:text-white text-[1.125rem] font-semibold">
        Edit AIR Draft
      </h3>
      <p class="text-xs text-[#8c9097] mt-1">
        This draft already exists. Saving will update the same record (no reload).
      </p>
      @if((int) ($air['continuation_no'] ?? 1) > 1)
        <p class="text-xs text-info mt-2">
          This is follow-up AIR #{{ (int) $air['continuation_no'] }} for unresolved items carried over from an earlier partial inspection.
        </p>
      @endif
    </div>

    <div class="mt-3 md:mt-0 flex items-center gap-2">
      @if($status === 'draft' && $canManageAir && ! $isArchived)
        <button
          id="gsoAirSaveBtn"
          type="button"
          class="ti-btn ti-btn-primary"
        >
          Save
        </button>

        <button
          id="gsoAirSubmitBtn"
          type="button"
          class="ti-btn ti-btn-success"
          @if($isLocked) disabled @endif
        >
          Submit
        </button>
      @endif

      @if(in_array($status, ['submitted', 'in_progress'], true) && ! $isArchived)
        <a href="{{ route('gso.air.inspect', ['air' => $air['id'] ?? '']) }}" class="ti-btn ti-btn-warning">
          Inspect
        </a>
      @endif

      @if($status === 'inspected' && ! $isArchived)
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

      @if($canManageAir && $isArchived)
        <button
          id="gsoAirRestoreBtn"
          type="button"
          class="ti-btn ti-btn-success"
        >
          Restore
        </button>

        @if($canForceDeleteAir)
          <button
            id="gsoAirForceDeleteBtn"
            type="button"
            class="ti-btn ti-btn-danger"
          >
            Force Delete
          </button>
        @endif
      @elseif($status === 'draft' && $canManageAir && ! $isArchived)
        <button
          id="gsoAirArchiveBtn"
          type="button"
          class="ti-btn ti-btn-danger"
        >
          Archive
        </button>
      @endif

      <a href="{{ route('gso.air.index') }}" class="ti-btn ti-btn-light">
        Back
      </a>
    </div>
  </div>

  <div class="max-w-7xl w-full mx-auto">
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 items-start">
      <div>
        <div class="box">
          <div class="box-header flex items-start justify-between gap-3">
            <div>
              <h5 class="box-title">AIR Draft Header</h5>
              <div class="text-xs text-[#8c9097] mt-1">
                Status: <b>{{ strtoupper($status !== '' ? $status : 'draft') }}</b>
              </div>
            </div>
          </div>

          <div class="box-body">
            @if($isLocked)
              <div class="mb-4 p-3 rounded border border-warning text-warning text-sm">
                This AIR is no longer in <b>draft</b> status. Editing is disabled.
              </div>
            @endif

            <div id="gsoAirFormError" class="hidden mb-4 rounded bg-danger/10 p-3 text-sm text-danger"></div>

            <input type="hidden" id="airId" value="{{ (string) ($air['id'] ?? '') }}">
            <input type="hidden" id="gsoAirInvoiceNumber" value="{{ (string) ($air['invoice_number'] ?? '') }}">
            <input type="hidden" id="gsoAirInvoiceDate" value="{{ (string) ($air['invoice_date'] ?? '') }}">

            <form id="gsoAirEditForm" class="space-y-5">
              @csrf

              <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                  <label class="ti-form-label">PO Number <span class="text-danger">*</span></label>
                  <input
                    id="gsoAirPoNumber"
                    name="po_number"
                    value="{{ (string) ($air['po_number'] ?? '') }}"
                    class="ti-form-input w-full"
                    placeholder="e.g. 2026-PO-001"
                    @if($isLocked) disabled @endif
                  />
                  <div id="gsoAirPoNumberErr" class="hidden mt-1 text-xs text-danger"></div>
                </div>

                <div>
                  <label class="ti-form-label">PO Date <span class="text-danger">*</span></label>
                  <input
                    id="gsoAirPoDate"
                    type="date"
                    name="po_date"
                    value="{{ (string) ($air['po_date'] ?? now()->toDateString()) }}"
                    class="ti-form-input w-full"
                    @if($isLocked) disabled @endif
                  />
                  <div id="gsoAirPoDateErr" class="hidden mt-1 text-xs text-danger"></div>
                </div>

                <div>
                  <label class="ti-form-label">
                    Fund <span class="text-red-500">*</span>
                  </label>

                  <select
                    id="gsoAirFundSourceId"
                    name="fund_source_id"
                    class="ti-form-input w-full"
                    @if($isLocked) disabled @endif
                  >
                    <option value="">-- Select Fund --</option>

                    @foreach($fundSources as $fund)
                      <option
                        value="{{ $fund->id }}"
                        @selected($selectedFundId === (string) $fund->id)
                      >
                        {{ $fund->name }}
                        @if($fund->code)
                          ({{ $fund->code }})
                        @endif
                        @if($fund->deleted_at)
                          (Archived)
                        @endif
                      </option>
                    @endforeach
                  </select>
                  <div id="gsoAirFundSourceIdErr" class="hidden mt-1 text-xs text-danger"></div>
                </div>
              </div>

              <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                  <label class="ti-form-label">AIR Number</label>

                  <input
                    id="gsoAirAirNumber"
                    name="air_number"
                    value="{{ (string) ($air['air_number'] ?? '') }}"
                    class="ti-form-input w-full bg-gray-100"
                    placeholder="Automatically Generated"
                    readonly
                  />

                  <p class="text-xs text-gray-500 mt-1">
                    This number will be automatically generated when the AIR is submitted.
                  </p>
                  <div id="gsoAirAirNumberErr" class="hidden mt-1 text-xs text-danger"></div>
                </div>

                <div>
                  <label class="ti-form-label">AIR Date <span class="text-danger">*</span></label>
                  <input
                    id="gsoAirAirDate"
                    type="date"
                    name="air_date"
                    value="{{ (string) ($air['air_date'] ?? now()->toDateString()) }}"
                    class="ti-form-input w-full"
                    @if($isLocked) disabled @endif
                  />
                  <div id="gsoAirAirDateErr" class="hidden mt-1 text-xs text-danger"></div>
                </div>

                <div>
                  <label class="ti-form-label">Requesting Department <span class="text-danger">*</span></label>
                  <select
                    id="gsoAirDepartmentId"
                    name="requesting_department_id"
                    class="ti-form-select w-full"
                    @if($isLocked) disabled @endif
                  >
                    <option value="">Select Department</option>
                    @foreach($departments as $department)
                      <option
                        value="{{ $department->id }}"
                        data-department-name="{{ $department->name }}"
                        @selected((string) ($air['requesting_department_id'] ?? '') === (string) $department->id)
                      >
                        {{ $department->name }}
                      </option>
                    @endforeach
                  </select>
                  <div id="gsoAirDepartmentIdErr" class="hidden mt-1 text-xs text-danger"></div>
                </div>
              </div>

              <div>
                <label class="ti-form-label">Supplier Name <span class="text-danger">*</span></label>
                <input
                  id="gsoAirSupplierName"
                  name="supplier_name"
                  value="{{ (string) ($air['supplier_name'] ?? '') }}"
                  class="ti-form-input w-full"
                  placeholder="Supplier name"
                  @if($isLocked) disabled @endif
                />
                <div id="gsoAirSupplierNameErr" class="hidden mt-1 text-xs text-danger"></div>
              </div>

              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                  <label class="ti-form-label">Inspected By (name) <span class="text-danger">*</span></label>
                  <input
                    id="gsoAirInspectedByName"
                    name="inspected_by_name"
                    value="{{ (string) ($air['inspected_by_name'] ?? '________________________') }}"
                    class="ti-form-input w-full"
                    @if($isLocked) disabled @endif
                  />
                  <div id="gsoAirInspectedByNameErr" class="hidden mt-1 text-xs text-danger"></div>
                </div>

                <div>
                  <label class="ti-form-label">Accepted By (name) <span class="text-danger">*</span></label>
                  <input
                    id="gsoAirAcceptedByName"
                    name="accepted_by_name"
                    value="{{ (string) ($air['accepted_by_name'] ?? '________________________') }}"
                    class="ti-form-input w-full"
                    @if($isLocked) disabled @endif
                  />
                  <div id="gsoAirAcceptedByNameErr" class="hidden mt-1 text-xs text-danger"></div>
                </div>
              </div>

              <div>
                <label class="ti-form-label">Remarks (optional)</label>
                <textarea
                  id="gsoAirRemarks"
                  name="remarks"
                  class="ti-form-input w-full"
                  rows="3"
                  placeholder="Notes..."
                  @if($isLocked) disabled @endif
                >{{ (string) ($air['remarks'] ?? '') }}</textarea>
                <div id="gsoAirRemarksErr" class="hidden mt-1 text-xs text-danger"></div>
              </div>

              <div class="text-xs text-[#8c9097]">
                Tip: Use Save in the toolbar to keep header and item changes together.
              </div>
            </form>
          </div>
        </div>
      </div>

      <div>
        <div class="box">
          <div class="box-header flex items-start justify-between gap-3">
            <h5 class="box-title">Items to Inspect</h5>
          </div>

          <div class="box-body space-y-4">
            @if($isLocked)
              <div class="p-2 rounded border border-warning text-warning text-xs">
                Items cannot be changed (AIR is not draft).
              </div>
            @endif

            <div class="space-y-2">
              <label class="ti-form-label">Search Item</label>

              <div class="relative">
                <input
                  id="gsoAirItemSearch"
                  class="ti-form-input w-full"
                  placeholder="Type item name / description / asset..."
                  autocomplete="off"
                  @if($isLocked) disabled @endif
                />

                <div
                  id="gsoAirItemSuggestions"
                  class="hidden absolute right-0 mt-2 w-full z-[9999] rounded-md border border-defaultborder bg-white dark:bg-bodybg shadow-lg"
                >
                  <div class="p-2 border-b border-defaultborder flex items-center justify-between">
                    <div class="text-xs font-semibold text-defaulttextcolor dark:text-white">Suggestions</div>
                    <button type="button" id="gsoAirItemSuggestClose" class="ti-btn ti-btn-sm ti-btn-light">
                      <i class="ri-close-line"></i>
                    </button>
                  </div>
                  <div id="gsoAirItemSuggestList" class="max-h-[320px] overflow-auto"></div>
                  <div class="p-2 border-t border-defaultborder text-xs text-[#8c9097]">
                    Type at least 2 characters. Click a suggestion card to add the item to this AIR draft.
                  </div>
                </div>
              </div>

              <div class="text-[11px] text-[#8c9097]">
                Tip: click a suggestion to add it. Unsaved item edits are included in the toolbar Save button.
              </div>
            </div>

            <div id="gsoAirItemError" class="hidden rounded bg-danger/10 p-3 text-sm text-danger"></div>

            <div class="pt-2 border-t border-defaultborder">
              <div class="flex items-center justify-between mb-2">
                <div class="font-semibold text-sm">Selected</div>
                <div class="text-xs text-[#8c9097]">
                  <span id="gsoAirItemCount">0</span>
                  <span id="gsoAirItemCountSummary" class="hidden">0</span>
                </div>
              </div>

              <div id="gsoAirItemList" class="space-y-2">
                <div class="text-xs text-[#8c9097]">Loading items...</div>
              </div>
            </div>
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
            accountableOfficerSuggestUrl: @json(route('gso.accountable-persons.suggest')),
            accountableOfficerResolveUrl: @json(route('gso.accountable-persons.resolve')),
            filesIndexUrl: @json(route('gso.air.files.index', ['air' => $air['id'] ?? ''])),
            filesStoreUrl: @json(route('gso.air.files.store', ['air' => $air['id'] ?? ''])),
            fileDestroyUrlTemplate: @json(route('gso.air.files.destroy', ['air' => $air['id'] ?? '', 'file' => '__FILE__'])),
            filePrimaryUrlTemplate: @json(route('gso.air.files.set-primary', ['air' => $air['id'] ?? '', 'file' => '__FILE__'])),
            indexUrl: @json(route('gso.air.index')),
            editUrl: @json(route('gso.air.edit', ['air' => $air['id'] ?? ''])),
            csrf: @json(csrf_token()),
            canManage: @json($canManageAir),
            canEditDraft: @json(! $isLocked),
            isArchived: @json($isArchived),
            canForceDelete: @json($canForceDeleteAir),
            persistedValues: @json($persistedValues),
        };
    </script>
@endpush
