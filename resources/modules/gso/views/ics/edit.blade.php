@extends('layouts.master')

@section('styles')
    <meta name="csrf-token" content="{{ csrf_token() }}">
@endsection

@section('content')
@php
    $status = strtolower((string) ($ics->status ?? 'draft'));
    $isDraft = $status === 'draft';
    $itemCount = (int) ($ics->items_count ?? 0);

    $currentFundClusterCode = optional(optional($ics->fundSource)->fundCluster)->code;
    $currentFundClusterDisplay = trim((string) $currentFundClusterCode);
@endphp

<div class="page-header md:flex items-start justify-between gap-4">
    <div>
        <h3 class="text-[1.125rem] font-semibold">Edit ICS</h3>
        <p class="text-xs text-[#8c9097] mt-1">
            Header changes save without leaving the page. Drafts can be submitted here, then finalized once the issue details are ready.
        </p>
        <div class="text-xs text-[#8c9097] mt-1">
            Status: <b>{{ strtoupper((string) ($ics->status ?? 'draft')) }}</b>
            @if($ics->updated_at)
                | Last updated: <b>{{ optional($ics->updated_at)->format('M d, Y h:i A') }}</b>
            @endif
        </div>
    </div>

    <div class="flex items-center gap-2 flex-wrap">
        @if($isDraft)
            <button id="icsSaveBtn" type="button" class="ti-btn ti-btn-primary">Save</button>
            <button id="icsSubmitBtn" type="button" class="ti-btn ti-btn-secondary">Submit</button>
            <button id="icsCancelBtn" type="button" class="ti-btn ti-btn-light">Cancel</button>
        @elseif($status === 'submitted')
            <button id="icsReopenBtn" type="button" class="ti-btn ti-btn-light">Reopen ICS</button>
            <button id="icsFinalizeBtn" type="button" class="ti-btn ti-btn-primary">Finalize</button>
            <button id="icsCancelBtn" type="button" class="ti-btn ti-btn-light">Cancel</button>
        @elseif($status === 'finalized')
            <a href="{{ route('gso.ics.print', $ics->id) }}" target="_blank" rel="noopener" class="ti-btn ti-btn-light">Print ICS</a>
        @endif

        <a href="{{ route('gso.ics.index') }}" class="ti-btn ti-btn-light">Back</a>
    </div>
</div>

<div class="max-w-[92rem] w-full mx-auto">
    <div class="grid grid-cols-1 xl:grid-cols-[minmax(0,1.55fr)_minmax(430px,1fr)] gap-4 items-start">
        <div class="space-y-4">
            <div class="box">
                <div class="box-header">
                    <h5 class="box-title">ICS Header</h5>
                </div>

                <div class="box-body">
                    <form id="icsForm" class="space-y-5">
                        @csrf

                        <input type="hidden" id="icsId" value="{{ (string) $ics->id }}">

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label class="ti-form-label">ICS Number</label>
                                <input
                                    name="ics_number_display"
                                    value="{{ old('ics_number_display', (string) ($ics->ics_number ?? '')) }}"
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
                                    value="{{ old('issued_date', optional($ics->issued_date)->toDateString()) }}"
                                    class="ti-form-input w-full"
                                    required
                                    @disabled(!$isDraft)
                                />
                            </div>

                            <div>
                                <label class="ti-form-label">Fund Cluster</label>
                                <input
                                    id="icsFundClusterDisplay"
                                    value="{{ $currentFundClusterDisplay !== '' ? $currentFundClusterDisplay : 'Will follow selected fund source code' }}"
                                    class="ti-form-input w-full"
                                    readonly
                                />
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="ti-form-label">Department <span class="text-danger">*</span></label>
                                <select name="department_id" class="ti-form-select w-full" required @disabled(!$isDraft)>
                                    <option value="">- Select Department -</option>
                                    @foreach(($departments ?? collect()) as $department)
                                        @php
                                            $departmentLabel = trim(($department->code ? $department->code . ' - ' : '') . $department->name);
                                        @endphp
                                        <option
                                            value="{{ (string) $department->id }}"
                                            data-department-name="{{ $department->name }}"
                                            @selected((string) old('department_id', (string) ($ics->department_id ?? '')) === (string) $department->id)
                                        >
                                            {{ $departmentLabel !== '' ? $departmentLabel : $department->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label class="ti-form-label">Fund Source <span class="text-danger">*</span></label>
                                <select name="fund_source_id" id="icsFundSourceSelect" class="ti-form-select w-full" required @disabled(!$isDraft || $itemCount > 0)>
                                    <option value="">- Select Fund Source -</option>
                                    @foreach(($fundSources ?? collect()) as $fundSource)
                                        @php
                                            $clusterCode = trim((string) optional($fundSource->fundCluster)->code);
                                        @endphp
                                        <option
                                            value="{{ (string) $fundSource->id }}"
                                            data-fund-cluster-label="{{ $clusterCode }}"
                                            @selected((string) old('fund_source_id', (string) ($ics->fund_source_id ?? '')) === (string) $fundSource->id)
                                        >
                                            {{ trim(($fundSource->code ? $fundSource->code . ' - ' : '') . $fundSource->name) }}
                                        </option>
                                    @endforeach
                                </select>
                                <div id="icsFundSourceHelp" class="text-[11px] text-[#8c9097] mt-1">
                                    @if($itemCount > 0)
                                        Remove all ICS items first to change the Fund Source.
                                    @else
                                        Only ICS items from the same Fund Cluster code as the selected Fund Source can be added.
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
                                placeholder="Internal notes for this ICS draft..."
                                @disabled(!$isDraft)
                            >{{ old('remarks', (string) ($ics->remarks ?? '')) }}</textarea>
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
                        <div class="font-semibold text-sm mb-2">Received from</div>
                        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-3">
                            <div>
                                <label class="ti-form-label">Name <span class="text-danger">*</span></label>
                                <input id="icsReceivedFromNameInput" name="received_from_name" form="icsForm" value="{{ old('received_from_name', (string) ($ics->received_from_name ?? '')) }}" class="ti-form-input w-full" required @disabled(!$isDraft) />
                            </div>
                            <div>
                                <label class="ti-form-label">Position <span class="text-danger">*</span></label>
                                <input id="icsReceivedFromPositionInput" name="received_from_position" form="icsForm" value="{{ old('received_from_position', (string) ($ics->received_from_position ?? '')) }}" class="ti-form-input w-full" required @disabled(!$isDraft) />
                            </div>
                            <div>
                                <label class="ti-form-label">Office <span class="text-danger">*</span></label>
                                <input id="icsReceivedFromOfficeInput" name="received_from_office" form="icsForm" value="{{ old('received_from_office', (string) ($ics->received_from_office ?? '')) }}" class="ti-form-input w-full" required @disabled(!$isDraft) />
                            </div>
                            <div>
                                <label class="ti-form-label">Date <span class="text-danger">*</span></label>
                                <input type="date" name="received_from_date" form="icsForm" value="{{ old('received_from_date', optional($ics->received_from_date)->toDateString()) }}" class="ti-form-input w-full" required @disabled(!$isDraft) />
                            </div>
                        </div>
                    </div>

                    <div class="border border-defaultborder rounded p-3">
                        <div class="font-semibold text-sm mb-2">Received by</div>
                        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-3">
                            <div>
                                <label class="ti-form-label">Name <span class="text-danger">*</span></label>
                                <input id="icsReceivedByNameInput" name="received_by_name" form="icsForm" value="{{ old('received_by_name', (string) ($ics->received_by_name ?? '')) }}" class="ti-form-input w-full" required @disabled(!$isDraft) />
                            </div>
                            <div>
                                <label class="ti-form-label">Position <span class="text-danger">*</span></label>
                                <input id="icsReceivedByPositionInput" name="received_by_position" form="icsForm" value="{{ old('received_by_position', (string) ($ics->received_by_position ?? '')) }}" class="ti-form-input w-full" required @disabled(!$isDraft) />
                            </div>
                            <div>
                                <label class="ti-form-label">Office <span class="text-danger">*</span></label>
                                <input id="icsReceivedByOfficeInput" name="received_by_office" form="icsForm" value="{{ old('received_by_office', (string) ($ics->received_by_office ?? '')) }}" class="ti-form-input w-full" required @disabled(!$isDraft) />
                            </div>
                            <div>
                                <label class="ti-form-label">Date <span class="text-danger">*</span></label>
                                <input type="date" name="received_by_date" form="icsForm" value="{{ old('received_by_date', optional($ics->received_by_date)->toDateString()) }}" class="ti-form-input w-full" required @disabled(!$isDraft) />
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
                            <span id="icsItemsCount" class="text-xs text-[#8c9097]">{{ $itemCount }} item(s)</span>
                        </div>

                        @if($isDraft)
                            <div class="relative w-full max-w-[420px] xl:min-w-[380px]">
                                <input
                                    id="icsItemSearch"
                                    type="text"
                                    class="form-control w-full !rounded-md"
                                    placeholder="Search inventory no./item..."
                                    autocomplete="off"
                                />

                                <div id="icsItemSuggestions" class="hidden absolute right-0 mt-2 w-full z-[9999] rounded-md border border-defaultborder bg-white dark:bg-bodybg shadow-lg">
                                    <div class="p-2 border-b border-defaultborder flex items-center justify-between">
                                        <div class="text-xs font-semibold text-defaulttextcolor dark:text-white">Suggestions</div>
                                        <button type="button" id="icsItemSuggestClose" class="ti-btn ti-btn-sm ti-btn-light">
                                            <i class="ri-close-line"></i>
                                        </button>
                                    </div>
                                    <div id="icsItemSuggestList" class="max-h-[320px] overflow-auto"></div>
                                    <div class="p-2 border-t border-defaultborder text-xs text-[#8c9097]">
                                        Type at least 2 characters. Only ICS items from the GSO pool and same Fund Cluster are suggested.
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                <div class="box-body">
                    <p id="icsItemsEmpty" class="text-sm text-[#8c9097] {{ $itemCount === 0 ? '' : 'hidden' }}">
                        No items yet. Add ICS-classified inventory items from the GSO pool.
                    </p>

                    <div id="icsItemsList" class="space-y-3 {{ $itemCount === 0 ? 'hidden' : '' }}">
                        @if($itemCount > 0)
                            <div class="text-xs text-[#8c9097]">Loading items...</div>
                        @endif
                    </div>

                    <div class="mt-3 text-xs text-[#8c9097] leading-5">
                        Quantities, unit cost, total cost, and estimated useful life are snapshotted from the selected inventory item.
                    </div>
                </div>
            </div>

            <div class="box">
                <div class="box-header">
                    <h5 class="box-title">Document Notes</h5>
                </div>
                <div class="box-body text-sm text-[#8c9097] space-y-2">
                    <p>Drafts stay editable while the status is <b>DRAFT</b>.</p>
                    <p>Submit moves the document to <b>SUBMITTED</b>. Finalize generates the ICS number and issues the selected inventory items.</p>
                    <p>Finalized ICS documents can now be printed from this page.</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    window.__icsEdit = {
        updateUrl: @json(route('gso.ics.update', ['ics' => $ics->id])),
        itemSuggestUrl: @json(route('gso.ics.items.suggest', ['ics' => $ics->id])),
        itemListUrl: @json(route('gso.ics.items.list', ['ics' => $ics->id])),
        itemStoreUrl: @json(route('gso.ics.items.store', ['ics' => $ics->id])),
        itemDeleteUrlTemplate: @json(route('gso.ics.items.destroy', ['ics' => $ics->id, 'icsItem' => '__ICS_ITEM_ID__'])),
        submitUrl: @json(route('gso.ics.submit', ['ics' => $ics->id])),
        reopenUrl: @json(route('gso.ics.reopen', ['ics' => $ics->id])),
        finalizeUrl: @json(route('gso.ics.finalize', ['ics' => $ics->id])),
        cancelUrl: @json(route('gso.ics.cancel', ['ics' => $ics->id])),
        status: @json((string) ($ics->status ?? 'draft')),
        canModify: @json($isDraft),
        initialItemCount: @json($itemCount),
        accountableOfficerSuggestUrl: @json(route('gso.accountable-persons.suggest')),
        accountableOfficerStoreUrl: @json(route('gso.accountable-persons.store')),
    };
</script>
@endpush
