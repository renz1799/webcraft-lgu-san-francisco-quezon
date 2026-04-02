@extends('layouts.master')

@section('styles')
<meta name="csrf-token" content="{{ csrf_token() }}">
@endsection

@section('content')
@php
  $status = strtolower((string) ($wmr->status ?? 'draft'));
  $isDraft = $status === 'draft';
  $isSubmitted = $status === 'submitted';
  $isApproved = $status === 'approved';
  $isDisposed = $status === 'disposed';
  $canCancel = in_array($status, ['draft', 'submitted', 'approved'], true);
  $itemCount = (int) ($wmr->items_count ?? 0);
  $canPrint = $itemCount > 0;
@endphp

<div class="page-header md:flex items-start justify-between gap-4">
  <div>
    <h3 class="text-[1.125rem] font-semibold">Edit WMR</h3>
    <p class="text-xs text-[#8c9097] mt-1">
      Waste Materials Reports can be prepared, reviewed, approved, and finalized from this page. Disposal lines and signatories stay tied to the current workflow status.
    </p>
    <div class="text-xs text-[#8c9097] mt-1">
      Status: <b>{{ strtoupper((string) ($wmr->status ?? 'draft')) }}</b>
      @if($wmr->updated_at)
        | Last updated: <b>{{ optional($wmr->updated_at)->format('M d, Y h:i A') }}</b>
      @endif
    </div>
  </div>

  <div class="flex items-center gap-2 flex-wrap">
    @if($isDraft)
      <button id="wmrSaveBtn" type="button" class="ti-btn ti-btn-primary">Save</button>
      <button id="wmrSubmitBtn" type="button" class="ti-btn ti-btn-success">Submit</button>
    @endif

    @if($isSubmitted)
      <button id="wmrApproveBtn" type="button" class="ti-btn ti-btn-success">Approve Disposal</button>
      <button id="wmrReopenBtn" type="button" class="ti-btn ti-btn-warning">Reopen WMR</button>
    @endif

    @if($isApproved)
      <button id="wmrFinalizeBtn" type="button" class="ti-btn ti-btn-danger">Finalize Disposal</button>
      <button id="wmrReopenBtn" type="button" class="ti-btn ti-btn-warning">Reopen WMR</button>
    @endif

    @if($canCancel)
      <button id="wmrCancelBtn" type="button" class="ti-btn ti-btn-outline-danger">Cancel</button>
    @endif

    @if($canPrint)
      <a href="{{ route('gso.wmrs.print', ['wmr' => $wmr->id]) }}" target="_blank" rel="noopener" class="ti-btn ti-btn-secondary">Print WMR</a>
    @endif

    <a href="{{ route('gso.wmrs.index') }}" class="ti-btn ti-btn-light">Back</a>
  </div>
</div>

<div class="max-w-[92rem] w-full mx-auto">
  <div class="grid grid-cols-1 xl:grid-cols-[minmax(0,1.55fr)_minmax(430px,1fr)] gap-4 items-start">
    <div class="space-y-4">
      <div class="box">
        <div class="box-header">
          <h5 class="box-title">WMR Header</h5>
        </div>

        <div class="box-body">
          <form id="wmrForm" class="space-y-5">
            @csrf
            <input type="hidden" id="wmrId" value="{{ (string) $wmr->id }}">

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
              <div>
                <label class="ti-form-label">WMR Number</label>
                <input
                  name="wmr_number_display"
                  value="{{ old('wmr_number_display', (string) ($wmr->wmr_number ?? '')) }}"
                  class="ti-form-input w-full"
                  placeholder="Will be generated later"
                  readonly
                />
              </div>

              <div>
                <label class="ti-form-label">Report Date</label>
                <input
                  type="date"
                  name="report_date"
                  value="{{ old('report_date', optional($wmr->report_date)->toDateString()) }}"
                  class="ti-form-input w-full"
                  @disabled(!$isDraft)
                />
              </div>

              <div>
                <label class="ti-form-label">Fund Cluster</label>
                <select name="fund_cluster_id" class="ti-form-select w-full" @disabled(!$isDraft)>
                  <option value="">- Select Fund Cluster -</option>
                  @foreach(($fundClusters ?? collect()) as $fundCluster)
                    <option
                      value="{{ (string) $fundCluster->id }}"
                      @selected((string) old('fund_cluster_id', (string) ($wmr->fund_cluster_id ?? '')) === (string) $fundCluster->id)
                    >
                      {{ $fundCluster->name }}
                    </option>
                  @endforeach
                </select>
              </div>
            </div>

            <div>
              <label class="ti-form-label">Place of Storage</label>
              <input
                name="place_of_storage"
                value="{{ old('place_of_storage', (string) ($wmr->place_of_storage ?? '')) }}"
                class="ti-form-input w-full"
                placeholder="Where the items are currently stored..."
                @disabled(!$isDraft)
              />
            </div>

            <div class="border border-defaultborder rounded p-3">
              <div class="font-semibold text-sm mb-3">Certified Correct</div>
              <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                <div>
                  <label class="ti-form-label">Printed Name</label>
                  <input id="wmrCustodianNameInput" name="custodian_name" value="{{ old('custodian_name', (string) ($wmr->custodian_name ?? '')) }}" class="ti-form-input w-full" @disabled(!$isDraft) />
                </div>
                <div>
                  <label class="ti-form-label">Designation</label>
                  <input id="wmrCustodianDesignationInput" name="custodian_designation" value="{{ old('custodian_designation', (string) ($wmr->custodian_designation ?? '')) }}" class="ti-form-input w-full" @disabled(!$isDraft) />
                </div>
                <div>
                  <label class="ti-form-label">Date</label>
                  <input type="date" name="custodian_date" value="{{ old('custodian_date', optional($wmr->custodian_date)->toDateString()) }}" class="ti-form-input w-full" @disabled(!$isDraft) />
                </div>
              </div>
            </div>

            <div class="border border-defaultborder rounded p-3">
              <div class="font-semibold text-sm mb-3">Disposal Approved</div>
              <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                <div>
                  <label class="ti-form-label">Printed Name</label>
                  <input id="wmrApprovedByNameInput" name="approved_by_name" value="{{ old('approved_by_name', (string) ($wmr->approved_by_name ?? '')) }}" class="ti-form-input w-full" @disabled(!$isDraft) />
                </div>
                <div>
                  <label class="ti-form-label">Designation</label>
                  <input id="wmrApprovedByDesignationInput" name="approved_by_designation" value="{{ old('approved_by_designation', (string) ($wmr->approved_by_designation ?? '')) }}" class="ti-form-input w-full" @disabled(!$isDraft) />
                </div>
                <div>
                  <label class="ti-form-label">Date</label>
                  <input type="date" name="approved_by_date" value="{{ old('approved_by_date', optional($wmr->approved_by_date)->toDateString()) }}" class="ti-form-input w-full" @disabled(!$isDraft) />
                </div>
              </div>
            </div>

            <div class="border border-defaultborder rounded p-3">
              <div class="font-semibold text-sm mb-3">Certificate of Inspection</div>
              <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                <div>
                  <label class="ti-form-label">Inspection Officer</label>
                  <input id="wmrInspectionOfficerNameInput" name="inspection_officer_name" value="{{ old('inspection_officer_name', (string) ($wmr->inspection_officer_name ?? '')) }}" class="ti-form-input w-full" @disabled(!$isDraft) />
                </div>
                <div>
                  <label class="ti-form-label">Designation</label>
                  <input id="wmrInspectionOfficerDesignationInput" name="inspection_officer_designation" value="{{ old('inspection_officer_designation', (string) ($wmr->inspection_officer_designation ?? '')) }}" class="ti-form-input w-full" @disabled(!$isDraft) />
                </div>
                <div>
                  <label class="ti-form-label">Date</label>
                  <input type="date" name="inspection_officer_date" value="{{ old('inspection_officer_date', optional($wmr->inspection_officer_date)->toDateString()) }}" class="ti-form-input w-full" @disabled(!$isDraft) />
                </div>
              </div>
            </div>

            <div class="border border-defaultborder rounded p-3">
              <div class="font-semibold text-sm mb-3">Witness to Disposal</div>
              <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                <div>
                  <label class="ti-form-label">Printed Name</label>
                  <input id="wmrWitnessNameInput" name="witness_name" value="{{ old('witness_name', (string) ($wmr->witness_name ?? '')) }}" class="ti-form-input w-full" @disabled(!$isDraft) />
                </div>
                <div>
                  <label class="ti-form-label">Designation</label>
                  <input id="wmrWitnessDesignationInput" name="witness_designation" value="{{ old('witness_designation', (string) ($wmr->witness_designation ?? '')) }}" class="ti-form-input w-full" @disabled(!$isDraft) />
                </div>
                <div>
                  <label class="ti-form-label">Date</label>
                  <input type="date" name="witness_date" value="{{ old('witness_date', optional($wmr->witness_date)->toDateString()) }}" class="ti-form-input w-full" @disabled(!$isDraft) />
                </div>
              </div>
            </div>

            <div>
              <label class="ti-form-label">Remarks</label>
              <textarea
                name="remarks"
                class="ti-form-input w-full"
                rows="3"
                placeholder="Internal notes for this WMR draft..."
                @disabled(!$isDraft)
              >{{ old('remarks', (string) ($wmr->remarks ?? '')) }}</textarea>
            </div>
          </form>
        </div>
      </div>
    </div>

    <div class="space-y-4 xl:min-w-[430px]">
      <div class="box">
        <div class="box-header">
          <div class="flex items-center gap-2">
            <h5 class="box-title">Items for Disposal</h5>
            <span id="wmrItemsCount" class="text-xs text-[#8c9097]">{{ $itemCount }} item(s)</span>
          </div>
        </div>
        <div class="box-body space-y-3">
          <p id="wmrItemsHelp" class="text-sm text-[#8c9097]">
            @if($itemCount > 0)
              Remove all disposal items first to change the WMR fund cluster.
            @else
              Save the WMR fund cluster first before managing disposal items.
            @endif
          </p>

          @if($isDraft)
            <div class="space-y-2">
              <label for="wmrItemSearch" class="ti-form-label !mb-0">Search Disposal Items</label>
              <div class="relative">
                <input id="wmrItemSearch" type="text" class="ti-form-input w-full" placeholder="Search by ref no., description, model, serial...">
                <div id="wmrItemSuggestions" class="hidden absolute left-0 right-0 top-full z-20 mt-2 rounded border border-defaultborder bg-white dark:bg-bodybg shadow-lg overflow-hidden">
                  <div class="flex items-center justify-between px-3 py-2 border-b border-defaultborder text-xs text-[#8c9097]">
                    <span>Matching disposal items</span>
                    <button id="wmrItemSuggestClose" type="button" class="ti-btn ti-btn-sm ti-btn-light">Close</button>
                  </div>
                  <div id="wmrItemSuggestList" class="max-h-[20rem] overflow-y-auto"></div>
                </div>
              </div>
            </div>
          @endif

          <div id="wmrItemsEmpty" class="rounded border border-dashed border-defaultborder px-4 py-5 text-sm text-[#8c9097] {{ $itemCount > 0 ? 'hidden' : '' }}">
            No disposal items added yet. Search and add tracked inventory items that are ready for disposal under this fund cluster.
          </div>

          <div id="wmrItemsList" class="space-y-3 {{ $itemCount > 0 ? '' : 'hidden' }}"></div>
        </div>
      </div>

      <div class="box">
        <div class="box-header">
          <h5 class="box-title">Document Notes</h5>
        </div>
        <div class="box-body text-sm text-[#8c9097] space-y-2">
          @if($isDraft)
            <p>Draft WMRs stay editable while the status is <b>DRAFT</b>.</p>
            <p>Each disposal line stores its own disposal method and official receipt details.</p>
            <p>If a line is marked <b>Transferred without cost</b>, enter the receiving agency or entity on that line.</p>
            <p>The WMR fund cluster is locked once disposal items are added so the selected lines stay consistent.</p>
            <p>Once disposal lines are present, <b>Print WMR</b> can be used to preview the Appendix 65 layout before submission.</p>
          @elseif($isSubmitted)
            <p>This WMR is now <b>SUBMITTED</b> and waiting for disposal approval.</p>
            <p>Use <b>Approve Disposal</b> once the report details and signatories are ready.</p>
            <p><b>Print WMR</b> can already be used to preview the Appendix 65 layout before final disposal.</p>
            <p>If anything needs correction, use <b>Reopen WMR</b> to move it back to draft.</p>
          @elseif($isApproved)
            <p>This WMR is <b>APPROVED</b> and ready for final disposal certification.</p>
            <p><b>Finalize Disposal</b> will generate the WMR number and update the selected inventory items.</p>
            <p><b>Print WMR</b> can be used to review the current print layout before final records output.</p>
            <p>If details still need adjustment, use <b>Reopen WMR</b> to return it to draft.</p>
          @elseif($isDisposed)
            <p>This WMR is <b>DISPOSED</b> and ready for final printing.</p>
            <p>Use <b>Print WMR</b> to generate the Appendix 65 document for records and signatures.</p>
          @else
            <p>This WMR is no longer editable in its current status.</p>
            <p>Use the document log and print view for reference, or return to the list to continue with other reports.</p>
          @endif
        </div>
      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
  window.__wmrEdit = {
    updateUrl: @json(route('gso.wmrs.update', ['wmr' => $wmr->id])),
    submitUrl: @json(route('gso.wmrs.submit', ['wmr' => $wmr->id])),
    approveUrl: @json(route('gso.wmrs.approve', ['wmr' => $wmr->id])),
    reopenUrl: @json(route('gso.wmrs.reopen', ['wmr' => $wmr->id])),
    finalizeUrl: @json(route('gso.wmrs.finalize', ['wmr' => $wmr->id])),
    cancelUrl: @json(route('gso.wmrs.cancel', ['wmr' => $wmr->id])),
    status: @json((string) ($wmr->status ?? 'draft')),
    canModify: @json($isDraft),
    initialItemCount: @json($itemCount),
    itemSuggestUrl: @json(route('gso.wmrs.items.suggest', ['wmr' => $wmr->id])),
    itemListUrl: @json(route('gso.wmrs.items.list', ['wmr' => $wmr->id])),
    itemStoreUrl: @json(route('gso.wmrs.items.store', ['wmr' => $wmr->id])),
    itemUpdateUrlTemplate: @json(route('gso.wmrs.items.update', ['wmr' => $wmr->id, 'wmrItem' => '__WMR_ITEM_ID__'])),
    itemDeleteUrlTemplate: @json(route('gso.wmrs.items.destroy', ['wmr' => $wmr->id, 'wmrItem' => '__WMR_ITEM_ID__'])),
    accountableOfficerSuggestUrl: @json(route('gso.accountable-persons.suggest')),
    accountableOfficerStoreUrl: @json(route('gso.accountable-persons.store')),
  };
</script>
@endpush

