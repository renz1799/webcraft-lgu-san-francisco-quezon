@extends('layouts.master')

@section('styles')
<meta name="csrf-token" content="{{ csrf_token() }}">
@endsection

@section('content')
@php
  $status = strtolower((string) ($itr->status ?? 'draft'));
  $isDraft = $status === 'draft';
  $itemCount = (int) ($itr->items_count ?? 0);

  $fromFundClusterCode = trim((string) optional(optional($itr->fromFundSource)->fundCluster)->code);
  $toFundClusterCode = trim((string) optional(optional($itr->toFundSource)->fundCluster)->code);
@endphp

<div class="page-header md:flex items-start justify-between gap-4">
  <div>
    <h3 class="text-[1.125rem] font-semibold">Edit ITR</h3>
    <p class="text-xs text-[#8c9097] mt-1">
      Draft ITRs can be completed here, saved without leaving the page, and moved through the transfer workflow once the details are ready.
    </p>
    <div class="text-xs text-[#8c9097] mt-1">
      Status: <b>{{ strtoupper((string) ($itr->status ?? 'draft')) }}</b>
      @if($itr->updated_at)
        | Last updated: <b>{{ optional($itr->updated_at)->format('M d, Y h:i A') }}</b>
      @endif
    </div>
  </div>

  <div class="flex items-center gap-2 flex-wrap">
    @if($isDraft)
      <button id="itrSaveBtn" type="button" class="ti-btn ti-btn-primary">Save</button>
      <button id="itrSubmitBtn" type="button" class="ti-btn ti-btn-secondary">Submit</button>
      <button id="itrCancelBtn" type="button" class="ti-btn ti-btn-light">Cancel</button>
    @elseif($status === 'submitted')
      <button id="itrReopenBtn" type="button" class="ti-btn ti-btn-light">Reopen ITR</button>
      <button id="itrFinalizeBtn" type="button" class="ti-btn ti-btn-primary">Finalize</button>
      <button id="itrCancelBtn" type="button" class="ti-btn ti-btn-light">Cancel</button>
    @elseif($status === 'finalized')
      <a href="{{ route('gso.itrs.print', ['itr' => $itr->id]) }}" target="_blank" rel="noopener" class="ti-btn ti-btn-primary">Print ITR</a>
    @endif
    <a href="{{ route('gso.itrs.index') }}" class="ti-btn ti-btn-light">Back</a>
  </div>
</div>

<div class="max-w-[92rem] w-full mx-auto">
  <div class="grid grid-cols-1 xl:grid-cols-[minmax(0,1.55fr)_minmax(430px,1fr)] gap-4 items-start">
    <div class="space-y-4">
      <div class="box">
        <div class="box-header">
          <h5 class="box-title">ITR Header</h5>
        </div>

        <div class="box-body">
          <form id="itrForm" class="space-y-5">
            @csrf
            <input type="hidden" id="itrId" value="{{ (string) $itr->id }}">

            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
              <div>
                <label class="ti-form-label">ITR Number</label>
                <input
                  name="itr_number_display"
                  value="{{ old('itr_number_display', (string) ($itr->itr_number ?? '')) }}"
                  class="ti-form-input w-full"
                  placeholder="Will be generated on finalize"
                  readonly
                />
              </div>

              <div>
                <label class="ti-form-label">Transfer Date <span class="text-danger">*</span></label>
                <input type="date" name="transfer_date" value="{{ old('transfer_date', optional($itr->transfer_date)->toDateString()) }}" class="ti-form-input w-full" required @disabled(!$isDraft) />
              </div>

              <div>
                <label class="ti-form-label">Transfer Type <span class="text-danger">*</span></label>
                <select name="transfer_type" id="itrTransferTypeSelect" class="ti-form-select w-full" required @disabled(!$isDraft)>
                  <option value="">- Select Transfer Type -</option>
                  <option value="donation" @selected(old('transfer_type', (string) ($itr->transfer_type ?? '')) === 'donation')>Donation</option>
                  <option value="relocate" @selected(old('transfer_type', (string) ($itr->transfer_type ?? '')) === 'relocate')>Relocate</option>
                  <option value="reassignment" @selected(old('transfer_type', (string) ($itr->transfer_type ?? '')) === 'reassignment')>Reassignment</option>
                  <option value="others" @selected(old('transfer_type', (string) ($itr->transfer_type ?? '')) === 'others')>Others</option>
                </select>
              </div>

              <div>
                <label class="ti-form-label">Others (Specify)</label>
                <input name="transfer_type_other" id="itrTransferTypeOther" value="{{ old('transfer_type_other', (string) ($itr->transfer_type_other ?? '')) }}" class="ti-form-input w-full" placeholder="Specify transfer type" @disabled(!$isDraft) />
              </div>
            </div>

            <div class="border border-defaultborder rounded p-3 space-y-4">
              <div class="font-semibold text-sm">From Accountable Officer / Agency / Fund Cluster</div>
              <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4">
                <div>
                  <label class="ti-form-label">Department <span class="text-danger">*</span></label>
                  <select name="from_department_id" class="ti-form-select w-full" required @disabled(!$isDraft)>
                    <option value="">- Select Department -</option>
                    @foreach(($departments ?? collect()) as $department)
                      <option value="{{ (string) $department->id }}" data-department-name="{{ $department->name }}" @selected((string) old('from_department_id', (string) ($itr->from_department_id ?? '')) === (string) $department->id)>
                        {{ $department->name }}{{ !empty($department->code) ? ' - '.$department->code : '' }}
                      </option>
                    @endforeach
                  </select>
                </div>

                <div>
                  <label class="ti-form-label">Accountable Officer <span class="text-danger">*</span></label>
                  <input id="itrFromAccountableOfficerInput" name="from_accountable_officer" value="{{ old('from_accountable_officer', (string) ($itr->from_accountable_officer ?? '')) }}" class="ti-form-input w-full" required @disabled(!$isDraft) />
                </div>

                <div>
                  <label class="ti-form-label">Fund Source <span class="text-danger">*</span></label>
                  <select name="from_fund_source_id" id="itrFromFundSourceSelect" class="ti-form-select w-full" required @disabled(!$isDraft)>
                    <option value="">- Select Fund Source -</option>
                    @foreach(($fundSources ?? collect()) as $fundSource)
                      @php $clusterCode = trim((string) optional($fundSource->fundCluster)->code); @endphp
                      <option value="{{ (string) $fundSource->id }}" data-fund-cluster-label="{{ $clusterCode }}" @selected((string) old('from_fund_source_id', (string) ($itr->from_fund_source_id ?? '')) === (string) $fundSource->id)>
                        {{ $fundSource->code }} - {{ $fundSource->name }}
                      </option>
                    @endforeach
                  </select>
                </div>

                <div>
                  <label class="ti-form-label">Fund Cluster</label>
                  <input id="itrFromFundClusterDisplay" value="{{ $fromFundClusterCode !== '' ? $fromFundClusterCode : 'Will follow selected fund source code' }}" class="ti-form-input w-full" readonly />
                </div>
              </div>
            </div>

            <div class="border border-defaultborder rounded p-3 space-y-4">
              <div class="font-semibold text-sm">To Accountable Officer / Agency / Fund Cluster</div>
              <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4">
                <div>
                  <label class="ti-form-label">Department <span class="text-danger">*</span></label>
                  <select name="to_department_id" class="ti-form-select w-full" required @disabled(!$isDraft)>
                    <option value="">- Select Department -</option>
                    @foreach(($departments ?? collect()) as $department)
                      <option value="{{ (string) $department->id }}" data-department-name="{{ $department->name }}" @selected((string) old('to_department_id', (string) ($itr->to_department_id ?? '')) === (string) $department->id)>
                        {{ $department->name }}{{ !empty($department->code) ? ' - '.$department->code : '' }}
                      </option>
                    @endforeach
                  </select>
                </div>

                <div>
                  <label class="ti-form-label">Accountable Officer <span class="text-danger">*</span></label>
                  <input id="itrToAccountableOfficerInput" name="to_accountable_officer" value="{{ old('to_accountable_officer', (string) ($itr->to_accountable_officer ?? '')) }}" class="ti-form-input w-full" required @disabled(!$isDraft) />
                </div>

                <div>
                  <label class="ti-form-label">Fund Source <span class="text-danger">*</span></label>
                  <select name="to_fund_source_id" id="itrToFundSourceSelect" class="ti-form-select w-full" required @disabled(!$isDraft)>
                    <option value="">- Select Fund Source -</option>
                    @foreach(($fundSources ?? collect()) as $fundSource)
                      @php $clusterCode = trim((string) optional($fundSource->fundCluster)->code); @endphp
                      <option value="{{ (string) $fundSource->id }}" data-fund-cluster-label="{{ $clusterCode }}" @selected((string) old('to_fund_source_id', (string) ($itr->to_fund_source_id ?? '')) === (string) $fundSource->id)>
                        {{ $fundSource->code }} - {{ $fundSource->name }}
                      </option>
                    @endforeach
                  </select>
                </div>

                <div>
                  <label class="ti-form-label">Fund Cluster</label>
                  <input id="itrToFundClusterDisplay" value="{{ $toFundClusterCode !== '' ? $toFundClusterCode : 'Will follow selected fund source code' }}" class="ti-form-input w-full" readonly />
                </div>
              </div>
            </div>

            <div>
              <label class="ti-form-label">Reason for Transfer <span class="text-danger">*</span></label>
              <textarea name="reason_for_transfer" class="ti-form-input w-full" rows="4" placeholder="State the reason for transfer..." required @disabled(!$isDraft)>{{ old('reason_for_transfer', (string) ($itr->reason_for_transfer ?? '')) }}</textarea>
            </div>

            <div>
              <label class="ti-form-label">Remarks</label>
              <textarea name="remarks" class="ti-form-input w-full" rows="3" placeholder="Internal notes for this ITR draft..." @disabled(!$isDraft)>{{ old('remarks', (string) ($itr->remarks ?? '')) }}</textarea>
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
            <div class="font-semibold text-sm mb-2">Approved by</div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
              <div><label class="ti-form-label">Printed Name <span class="text-danger">*</span></label><input id="itrApprovedByNameInput" name="approved_by_name" form="itrForm" value="{{ old('approved_by_name', (string) ($itr->approved_by_name ?? '')) }}" class="ti-form-input w-full" required @disabled(!$isDraft) /></div>
              <div><label class="ti-form-label">Designation <span class="text-danger">*</span></label><input id="itrApprovedByDesignationInput" name="approved_by_designation" form="itrForm" value="{{ old('approved_by_designation', (string) ($itr->approved_by_designation ?? '')) }}" class="ti-form-input w-full" required @disabled(!$isDraft) /></div>
              <div><label class="ti-form-label">Date <span class="text-danger">*</span></label><input type="date" name="approved_by_date" form="itrForm" value="{{ old('approved_by_date', optional($itr->approved_by_date)->toDateString()) }}" class="ti-form-input w-full" required @disabled(!$isDraft) /></div>
            </div>
          </div>

          <div class="border border-defaultborder rounded p-3">
            <div class="font-semibold text-sm mb-2">Released / Issued by</div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
              <div><label class="ti-form-label">Printed Name <span class="text-danger">*</span></label><input id="itrReleasedByNameInput" name="released_by_name" form="itrForm" value="{{ old('released_by_name', (string) ($itr->released_by_name ?? '')) }}" class="ti-form-input w-full" required @disabled(!$isDraft) /></div>
              <div><label class="ti-form-label">Designation <span class="text-danger">*</span></label><input id="itrReleasedByDesignationInput" name="released_by_designation" form="itrForm" value="{{ old('released_by_designation', (string) ($itr->released_by_designation ?? '')) }}" class="ti-form-input w-full" required @disabled(!$isDraft) /></div>
              <div><label class="ti-form-label">Date <span class="text-danger">*</span></label><input type="date" name="released_by_date" form="itrForm" value="{{ old('released_by_date', optional($itr->released_by_date)->toDateString()) }}" class="ti-form-input w-full" required @disabled(!$isDraft) /></div>
            </div>
          </div>

          <div class="border border-defaultborder rounded p-3">
            <div class="font-semibold text-sm mb-2">Received by</div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
              <div><label class="ti-form-label">Printed Name <span class="text-danger">*</span></label><input id="itrReceivedByNameInput" name="received_by_name" form="itrForm" value="{{ old('received_by_name', (string) ($itr->received_by_name ?? '')) }}" class="ti-form-input w-full" required @disabled(!$isDraft) /></div>
              <div><label class="ti-form-label">Designation <span class="text-danger">*</span></label><input id="itrReceivedByDesignationInput" name="received_by_designation" form="itrForm" value="{{ old('received_by_designation', (string) ($itr->received_by_designation ?? '')) }}" class="ti-form-input w-full" required @disabled(!$isDraft) /></div>
              <div><label class="ti-form-label">Date <span class="text-danger">*</span></label><input type="date" name="received_by_date" form="itrForm" value="{{ old('received_by_date', optional($itr->received_by_date)->toDateString()) }}" class="ti-form-input w-full" required @disabled(!$isDraft) /></div>
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
              <span id="itrItemsCount" class="text-xs text-[#8c9097]">{{ $itemCount }} item(s)</span>
            </div>

            @if($isDraft)
              <div class="relative w-full max-w-[420px] xl:min-w-[380px]">
                <input
                  id="itrItemSearch"
                  type="text"
                  class="form-control w-full !rounded-md"
                  placeholder="Search inventory no./item..."
                  autocomplete="off"
                />

                <div id="itrItemSuggestions" class="hidden absolute right-0 mt-2 w-full z-[9999] rounded-md border border-defaultborder bg-white dark:bg-bodybg shadow-lg">
                  <div class="p-2 border-b border-defaultborder flex items-center justify-between">
                    <div class="text-xs font-semibold text-defaulttextcolor dark:text-white">Suggestions</div>
                    <button type="button" id="itrItemSuggestClose" class="ti-btn ti-btn-sm ti-btn-light">
                      <i class="ri-close-line"></i>
                    </button>
                  </div>
                  <div id="itrItemSuggestList" class="max-h-[320px] overflow-auto"></div>
                  <div class="p-2 border-t border-defaultborder text-xs text-[#8c9097]">
                    Type at least 2 characters. Only issued ICS / semi-expendable items already assigned to the saved source side of this ITR are suggested.
                  </div>
                </div>
              </div>
            @endif
          </div>
        </div>
        <div class="box-body">
          <p id="itrItemsEmpty" class="text-sm text-[#8c9097] {{ $itemCount === 0 ? '' : 'hidden' }}">
            No items yet. Add issued ICS / semi-expendable inventory items from the saved source department and fund cluster.
          </p>

          <div id="itrItemsList" class="space-y-3 {{ $itemCount === 0 ? 'hidden' : '' }}">
            @if($itemCount > 0)
              <div class="text-xs text-[#8c9097]">Loading items...</div>
            @endif
          </div>

          <div id="itrItemsHelp" class="mt-3 text-xs text-[#8c9097] leading-5">
            Save the source department and fund source first. If a source accountable officer is provided, suggestions are narrowed to that officer.
          </div>
        </div>
      </div>

      <div class="box">
        <div class="box-header"><h5 class="box-title">Document Notes</h5></div>
        <div class="box-body text-sm text-[#8c9097] space-y-2">
          <p>Draft ITRs stay editable while the status is <b>DRAFT</b>.</p>
          <p>ITR is for issued ICS / semi-expendable inventory items, not PPE/property items in PTR and not items still in the GSO pool.</p>
          <p>Submitted ITRs can be reopened back to draft, finalized to post the transfer, or cancelled if the transfer should not proceed.</p>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
  window.__itrEdit = {
    updateUrl: @json(route('gso.itrs.update', ['itr' => $itr->id])),
    itemSuggestUrl: @json(route('gso.itrs.items.suggest', ['itr' => $itr->id])),
    itemListUrl: @json(route('gso.itrs.items.list', ['itr' => $itr->id])),
    itemStoreUrl: @json(route('gso.itrs.items.store', ['itr' => $itr->id])),
    itemDeleteUrlTemplate: @json(route('gso.itrs.items.destroy', ['itr' => $itr->id, 'itrItem' => '__ITR_ITEM_ID__'])),
    status: @json((string) ($itr->status ?? 'draft')),
    canModify: @json($isDraft),
    initialItemCount: @json($itemCount),
    accountableOfficerSuggestUrl: @json(route('gso.accountable-persons.suggest')),
    accountableOfficerStoreUrl: @json(route('gso.accountable-persons.store')),
    submitUrl: @json(route('gso.itrs.submit', ['itr' => $itr->id])),
    reopenUrl: @json(route('gso.itrs.reopen', ['itr' => $itr->id])),
    finalizeUrl: @json(route('gso.itrs.finalize', ['itr' => $itr->id])),
    cancelUrl: @json(route('gso.itrs.cancel', ['itr' => $itr->id])),
  };
</script>
@endpush





