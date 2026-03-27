@extends('layouts.master')

@section('styles')
<meta name="csrf-token" content="{{ csrf_token() }}">
@endsection

@section('content')
@php
  $status = strtolower((string) ($par->status ?? 'draft'));
  $isArchived = !empty($par->deleted_at);
  $isDraft = $status === 'draft';
  $itemCount = (int) ($par->items?->count() ?? 0);
  $canModify = auth()->user()?->hasAnyRole(['Administrator', 'admin'])
    || auth()->user()?->hasRole('Staff')
    || auth()->user()?->can('modify PAR');
  $canEditDraft = !$isArchived && $isDraft && $canModify;

  $currentFundClusterCode = trim((string) optional(optional($par->fundSource)->fundCluster)->code);
@endphp

<div id="parShowPage">
  <div class="page-header md:flex items-start justify-between gap-4">
    <div>
      <h3 class="text-[1.125rem] font-semibold">Edit PAR</h3>
      <p class="text-xs text-[#8c9097] mt-1">
        Draft PARs can be completed here, saved without leaving the page, and submitted once the issuance details are ready.
      </p>
      <div class="text-xs text-[#8c9097] mt-1">
        Status: <b>{{ strtoupper((string) ($par->status ?? 'draft')) }}</b>
        @if($par->updated_at)
          | Last updated: <b>{{ optional($par->updated_at)->format('M d, Y h:i A') }}</b>
        @endif
        @if($isArchived)
          | <span class="text-danger font-semibold">Archived</span>
        @endif
      </div>
    </div>

    <div class="flex items-center gap-2 flex-wrap">
      @if($canEditDraft)
        <button id="parSaveBtn" type="button" class="ti-btn ti-btn-primary">Save</button>
      @endif

      @if(!$isArchived && $status === 'draft')
        <form
          id="par-submit-form"
          method="POST"
          action="{{ route('gso.pars.submit', $par->id) }}"
          data-action="par-submit-form"
          data-endpoint="{{ route('gso.pars.submit', $par->id) }}"
        >
          @csrf
          <button id="parSubmitBtn" type="submit" class="ti-btn ti-btn-secondary" data-action="par-submit">Submit</button>
        </form>
      @endif

      @if(!$isArchived && $status === 'submitted')
        <form
          id="par-reopen-form"
          method="POST"
          action="{{ route('gso.pars.reopen', $par->id) }}"
          data-action="par-reopen-form"
          data-endpoint="{{ route('gso.pars.reopen', $par->id) }}"
        >
          @csrf
          <button id="parReopenBtn" type="submit" class="ti-btn ti-btn-light" data-action="par-reopen">Reopen PAR</button>
        </form>

        <form
          id="par-finalize-form"
          method="POST"
          action="{{ route('gso.pars.finalize', $par->id) }}"
          data-action="par-finalize-form"
          data-endpoint="{{ route('gso.pars.finalize', $par->id) }}"
        >
          @csrf
          <button type="submit" class="ti-btn ti-btn-primary" data-action="par-finalize">Finalize</button>
        </form>
      @endif

      @if(!$isArchived && !in_array($status, ['finalized', 'cancelled'], true))
        <form method="POST" action="{{ route('gso.pars.cancel', $par->id) }}">
          @csrf
          <button type="submit" class="ti-btn ti-btn-light">Cancel</button>
        </form>
      @endif

      @if(!$isArchived && $status === 'finalized')
        <a href="{{ route('gso.pars.print', $par->id) }}" target="_blank" rel="noopener" class="ti-btn ti-btn-light">Print PAR</a>
      @endif

      <a href="{{ route('gso.pars.index') }}" class="ti-btn ti-btn-light">Back</a>
    </div>
  </div>

  @if($isArchived)
    <div class="alert alert-danger mb-4">
      This PAR is archived (soft-deleted). Restore it from the index table in Archived view.
    </div>
  @endif

  <div class="max-w-[92rem] w-full mx-auto">
    <div class="grid grid-cols-1 xl:grid-cols-[minmax(0,1.55fr)_minmax(430px,1fr)] gap-4 items-start">
      <div class="space-y-4">
        <div class="box">
          <div class="box-header">
            <h5 class="box-title">PAR Header</h5>
          </div>

          <div class="box-body">
            <form id="parForm" class="space-y-5">
              @csrf

              <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                  <label class="ti-form-label">PAR Number</label>
                  <input
                    name="par_number_display"
                    value="{{ old('par_number_display', (string) ($par->par_number ?? '')) }}"
                    class="ti-form-input w-full"
                    placeholder="Will be generated on finalize"
                    readonly
                  />
                </div>

                <div>
                  <label class="ti-form-label">Issued Date <span class="text-danger">*</span></label>
                  <input
                    type="date"
                    name="issued_date"
                    value="{{ old('issued_date', optional($par->issued_date)->toDateString()) }}"
                    class="ti-form-input w-full"
                    required
                    @disabled(!$canEditDraft)
                  />
                </div>

                <div>
                  <label class="ti-form-label">Fund Cluster</label>
                  <input
                    id="parFundClusterDisplay"
                    value="{{ $currentFundClusterCode !== '' ? $currentFundClusterCode : 'Will follow selected fund source code' }}"
                    class="ti-form-input w-full"
                    readonly
                  />
                </div>
              </div>

              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                  <label class="ti-form-label">Department <span class="text-danger">*</span></label>
                  <select name="department_id" class="ti-form-select w-full" required @disabled(!$canEditDraft)>
                    <option value="">- Select Department -</option>
                    @foreach(($departments ?? collect()) as $department)
                      <option
                        value="{{ (string) $department->id }}"
                        data-department-name="{{ $department->name }}"
                        @selected((string) old('department_id', (string) ($par->department_id ?? '')) === (string) $department->id)
                      >
                        {{ !empty($department->code) ? $department->code.' - ' : '' }}{{ $department->name }}
                      </option>
                    @endforeach
                  </select>
                </div>

                <div>
                  <label class="ti-form-label">Fund Source <span class="text-danger">*</span></label>
                  <select name="fund_source_id" id="parFundSourceSelect" class="ti-form-select w-full" required @disabled(!$canEditDraft || $itemCount > 0)>
                    <option value="">- Select Fund Source -</option>
                    @foreach(($fundSources ?? collect()) as $fundSource)
                      @php
                        $clusterCode = trim((string) optional($fundSource->fundCluster)->code);
                      @endphp
                      <option
                        value="{{ (string) $fundSource->id }}"
                        data-fund-cluster-label="{{ $clusterCode }}"
                        @selected((string) old('fund_source_id', (string) ($par->fund_source_id ?? '')) === (string) $fundSource->id)
                      >
                        {{ $fundSource->code }} - {{ $fundSource->name }}
                      </option>
                    @endforeach
                  </select>
                  <div class="text-[11px] text-[#8c9097] mt-1">
                    @if($itemCount > 0)
                      Remove all PAR items first to change the Fund Source.
                    @else
                      Only items from the same Fund Cluster as the selected Fund Source can be added.
                    @endif
                  </div>
                </div>
              </div>

              <div>
                <label class="ti-form-label">Remarks</label>
                <textarea
                  name="remarks"
                  class="ti-form-input w-full"
                  rows="3"
                  placeholder="Internal notes for this PAR draft..."
                  @disabled(!$canEditDraft)
                >{{ old('remarks', (string) ($par->remarks ?? '')) }}</textarea>
              </div>
            </form>
          </div>
        </div>

        <div class="box">
          <div class="box-header">
            <h5 class="box-title">Signatories</h5>
          </div>

          <div class="box-body space-y-5">
            <div class="border border-defaultborder rounded p-3">
              <div class="font-semibold text-sm mb-2">Received By (End User)</div>
              <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                <div>
                  <label class="ti-form-label">Printed Name <span class="text-danger">*</span></label>
                  <input id="parPersonAccountableInput" name="person_accountable" form="parForm" value="{{ old('person_accountable', (string) ($par->person_accountable ?? '')) }}" class="ti-form-input w-full" required @disabled(!$canEditDraft) />
                </div>
                <div>
                  <label class="ti-form-label">Position / Office <span class="text-danger">*</span></label>
                  <input id="parReceivedByPositionInput" name="received_by_position" form="parForm" value="{{ old('received_by_position', (string) ($par->received_by_position ?? '')) }}" class="ti-form-input w-full" required @disabled(!$canEditDraft) />
                </div>
                <div>
                  <label class="ti-form-label">Date <span class="text-danger">*</span></label>
                  <input type="date" name="received_by_date" form="parForm" value="{{ old('received_by_date', optional($par->received_by_date)->toDateString()) }}" class="ti-form-input w-full" required @disabled(!$canEditDraft) />
                </div>
              </div>
            </div>

            <div class="border border-defaultborder rounded p-3">
              <div class="font-semibold text-sm mb-2">Issued By (Custodian)</div>
              <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-3">
                <div>
                  <label class="ti-form-label">Printed Name <span class="text-danger">*</span></label>
                  <input id="parIssuedByNameInput" name="issued_by_name" form="parForm" value="{{ old('issued_by_name', (string) ($par->issued_by_name ?? '')) }}" class="ti-form-input w-full" required @disabled(!$canEditDraft) />
                </div>
                <div>
                  <label class="ti-form-label">Position <span class="text-danger">*</span></label>
                  <input id="parIssuedByPositionInput" name="issued_by_position" form="parForm" value="{{ old('issued_by_position', (string) ($par->issued_by_position ?? '')) }}" class="ti-form-input w-full" required @disabled(!$canEditDraft) />
                </div>
                <div>
                  <label class="ti-form-label">Office <span class="text-danger">*</span></label>
                  <input id="parIssuedByOfficeInput" name="issued_by_office" form="parForm" value="{{ old('issued_by_office', (string) ($par->issued_by_office ?? '')) }}" class="ti-form-input w-full" required @disabled(!$canEditDraft) />
                </div>
                <div>
                  <label class="ti-form-label">Date <span class="text-danger">*</span></label>
                  <input type="date" name="issued_by_date" form="parForm" value="{{ old('issued_by_date', optional($par->issued_by_date)->toDateString()) }}" class="ti-form-input w-full" required @disabled(!$canEditDraft) />
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="space-y-4 xl:min-w-[430px]">
        <div class="box">
          <div class="box-header">
            <div class="flex flex-col gap-3 xl:flex-row xl:items-start xl:justify-between w-full">
              <div class="flex items-center gap-2">
                <h5 class="box-title">Items</h5>
                <span id="par-items-count" class="text-xs text-[#8c9097]">{{ $itemCount }} item(s)</span>
              </div>

              @if($canEditDraft)
                <div class="relative w-full max-w-[420px] xl:min-w-[380px]">
                  <input
                    id="par-item-search"
                    type="text"
                    class="form-control w-full !rounded-md"
                    placeholder="Search property number / item..."
                    autocomplete="off"
                  />

                  <input type="hidden" id="par-suggest-endpoint" value="{{ route('gso.pars.items.suggest', $par->id) }}">
                  <input type="hidden" id="par-add-item-endpoint" value="{{ route('gso.pars.items.store', $par->id) }}">

                  <div
                    id="par-item-suggest"
                    class="hidden absolute right-0 mt-2 w-full z-[9999] rounded-md border border-defaultborder bg-white dark:bg-bodybg shadow-lg"
                  >
                    <div class="p-2 border-b border-defaultborder flex items-center justify-between">
                      <div class="text-xs font-semibold text-defaulttextcolor dark:text-white">Suggestions</div>
                      <button type="button" id="par-suggest-close" class="ti-btn ti-btn-sm ti-btn-light">
                        <i class="ri-close-line"></i>
                      </button>
                    </div>

                    <div id="par-suggest-list" class="max-h-[320px] overflow-auto"></div>

                    <div id="parItemSearchHelp" class="p-2 border-t border-defaultborder text-xs text-[#8c9097]">
                      Type at least 2 characters. Only items from the GSO pool under the same Fund Cluster are suggested.
                    </div>
                  </div>
                </div>
              @endif
            </div>
          </div>

          <div class="box-body">
            <p id="par-items-empty" class="text-sm text-[#8c9097] {{ $itemCount === 0 ? '' : 'hidden' }}">
              No items yet. Add property items from the GSO pool under the selected Fund Cluster.
            </p>

            <div id="parItemsList" class="space-y-3 {{ $itemCount === 0 ? 'hidden' : '' }}">
              @foreach($par->items as $pi)
                @php
                  $inv = $pi->inventoryItem;
                  $itemName = $pi->item_name_snapshot
                    ?? $inv?->item?->item_name
                    ?? $inv?->item_name
                    ?? '-';
                  $propertyNumber = $pi->property_number_snapshot ?? $inv?->property_number ?? '-';
                  $description = trim((string) ($inv?->description ?? ''));
                  $descriptionLabel = $description !== '' ? $description : 'No description available.';
                  $unit = $pi->unit_snapshot ?? $inv?->unit ?? '-';
                  $unitCost = is_null($pi->amount_snapshot) ? null : (float) $pi->amount_snapshot;
                  $totalCost = is_null($unitCost) ? null : ((int) ($pi->quantity ?? 1) * $unitCost);
                @endphp

                <div class="rounded border border-defaultborder p-3" data-par-item-row="{{ $pi->id }}">
                  <div class="flex items-start justify-between gap-3">
                    <div class="min-w-0">
                      <div class="text-xs text-[#8c9097]">
                        Property No.: <b>{{ $propertyNumber }}</b>
                      </div>
                      <div class="text-sm font-semibold mt-1 break-words leading-5">{{ $itemName }}</div>
                      <div class="text-xs text-[#8c9097] mt-1 whitespace-pre-wrap">{{ $descriptionLabel }}</div>
                      <div class="grid grid-cols-2 gap-x-4 gap-y-1 text-xs mt-3 text-[#4b5563]">
                        <div>Quantity: <b>{{ (int) ($pi->quantity ?? 1) }}</b></div>
                        <div>Unit: <b>{{ $unit }}</b></div>
                        <div>Unit Cost: <b>{{ is_null($unitCost) ? '-' : number_format($unitCost, 2) }}</b></div>
                        <div>Total Cost: <b>{{ is_null($totalCost) ? '-' : number_format($totalCost, 2) }}</b></div>
                      </div>
                    </div>

                    @if($canEditDraft)
                      <div class="shrink-0">
                        <button
                          type="button"
                          class="ti-btn ti-btn-light"
                          data-action="par-item-remove"
                          data-par-item-id="{{ $pi->id }}"
                          data-delete-url="{{ route('gso.pars.items.destroy', ['par' => $par->id, 'parItem' => $pi->id]) }}"
                        >
                          Remove
                        </button>
                      </div>
                    @endif
                  </div>
                </div>
              @endforeach
            </div>

            <div class="mt-3 text-xs text-[#8c9097] leading-5">
              Quantity, unit cost, and total cost are shown from the selected property item when it is added to this PAR.
            </div>
          </div>
        </div>

        <div class="box">
          <div class="box-header">
            <h5 class="box-title">Document Notes</h5>
          </div>
          <div class="box-body text-sm text-[#8c9097] space-y-2">
            <p>Draft PARs stay editable while the status is <b>DRAFT</b>.</p>
            <p>If there are unsaved header changes, <b>Submit</b> will save them first before submitting the PAR.</p>
            <p>Submitted PARs can be reopened back to draft before finalizing.</p>
            <p>Finalize generates the PAR number and creates issuance events for the selected items.</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
  <script>
    window.__parShow = {
      csrf: @json(csrf_token()),
      updateUrl: @json(route('gso.pars.update', ['par' => $par->id])),
      submitUrl: @json(route('gso.pars.submit', ['par' => $par->id])),
      reopenUrl: @json(route('gso.pars.reopen', ['par' => $par->id])),
      finalizeUrl: @json(route('gso.pars.finalize', ['par' => $par->id])),
      cancelUrl: @json(route('gso.pars.cancel', ['par' => $par->id])),
      suggestUrl: @json(route('gso.pars.items.suggest', ['par' => $par->id])),
      addItemUrl: @json(route('gso.pars.items.store', ['par' => $par->id])),
      itemDeleteUrlTemplate: @json(route('gso.pars.items.destroy', ['par' => $par->id, 'parItem' => '__PAR_ITEM_ID__'])),
      canModify: @json($canEditDraft),
      status: @json((string) ($par->status ?? 'draft')),
      accountableOfficerSuggestUrl: @json(route('gso.accountable-persons.suggest')),
      accountableOfficerStoreUrl: @json(route('gso.accountable-persons.store')),
    };
  </script>
@endpush
