@extends('layouts.master')

@section('styles')
    <meta name="csrf-token" content="{{ csrf_token() }}">
@endsection

@section('content')
    @php
        $status = strtolower((string) ($ris->status ?? 'draft'));
        $isDraft = $status === 'draft';
        $isSubmitted = $status === 'submitted';
        $isIssued = $status === 'issued';
        $isRejected = $status === 'rejected';
    @endphp

    <div class="page-header md:flex items-start justify-between gap-4">
        <div>
            <h3 class="text-[1.125rem] font-semibold">Edit RIS</h3>
            <p class="text-xs text-[#8c9097] mt-1">
                Updating this record will not reload the page.
            </p>

            <div class="text-xs text-[#8c9097] mt-1">
                Status: <b>{{ strtoupper((string) ($ris->status ?? 'draft')) }}</b>
                @if($isSubmitted && $ris->submitted_at)
                    | Submitted: <b>{{ optional($ris->submitted_at)->format('M d, Y h:i A') }}</b>
                    @if(!empty($ris->submitted_by_name))
                        by <b>{{ $ris->submitted_by_name }}</b>
                    @endif
                @endif

                @if($isRejected && $ris->rejected_at)
                    | Rejected: <b>{{ optional($ris->rejected_at)->format('M d, Y h:i A') }}</b>
                    @if(!empty($ris->rejected_by_name))
                        by <b>{{ $ris->rejected_by_name }}</b>
                    @endif
                    @if(!empty($ris->rejected_reason))
                        | Reason: <b>{{ $ris->rejected_reason }}</b>
                    @endif
                @endif
            </div>
        </div>

        <div class="flex items-center gap-2 flex-wrap">
            @if($isDraft)
                <button id="risSaveBtn" type="button" class="ti-btn ti-btn-primary">Save</button>
                <button id="risSubmitBtn" type="button" class="ti-btn ti-btn-secondary">Submit</button>
            @endif

            @if($isSubmitted)
                <button id="risReopenBtn" type="button" class="ti-btn ti-btn-light">Reopen RIS</button>
                <button id="risApproveBtn" type="button" class="ti-btn ti-btn-primary">Issue RIS</button>
                <button id="risRejectBtn" type="button" class="ti-btn ti-btn-danger">Reject</button>
            @endif

            @if($isRejected)
                <button id="risReopenBtn" type="button" class="ti-btn ti-btn-light">Reopen RIS</button>
            @endif

            @if($isIssued)
                <button id="risRevertBtn" type="button" class="ti-btn ti-btn-danger">Revert to Draft</button>
            @endif

            <a href="{{ route('gso.ris.print', ['ris' => $ris->id]) }}" target="_blank" rel="noopener" class="ti-btn ti-btn-secondary">
                Print RIS
            </a>

            <a href="{{ $ris->air_id ? route('gso.air.inspect', ['air' => $ris->air_id]) : route('gso.ris.index') }}" class="ti-btn ti-btn-light">
                Back
            </a>
        </div>
    </div>

    <div class="max-w-[92rem] w-full mx-auto">
        <div class="grid grid-cols-1 xl:grid-cols-[minmax(0,1.55fr)_minmax(430px,1fr)] gap-4 items-start">
            <div class="space-y-4">
                <div class="box">
                    <div class="box-header">
                        <h5 class="box-title">RIS Header</h5>
                    </div>

                    <div class="box-body">
                        <form id="risForm" class="space-y-5">
                            @csrf

                            <input type="hidden" id="risId" value="{{ (string) $ris->id }}">

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="ti-form-label">RIS Number</label>
                                    <input
                                        name="ris_number"
                                        value="{{ old('ris_number', (string) ($ris->ris_number ?? '')) }}"
                                        class="ti-form-input w-full"
                                        placeholder="Will be generated upon approval"
                                        readonly
                                    />
                                </div>

                                <div>
                                    <label class="ti-form-label">RIS Date <span class="text-red-500">*</span></label>
                                    <input
                                        type="date"
                                        name="ris_date"
                                        value="{{ old('ris_date', optional($ris->ris_date)->toDateString() ?: now()->toDateString()) }}"
                                        class="ti-form-input w-full"
                                        required
                                        @disabled(!$isDraft)
                                    />
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="ti-form-label">Fund Source <span class="text-red-500">*</span></label>
                                    <select name="fund_source_id" class="ti-form-select w-full" required @disabled(!$isDraft)>
                                        <option value="">- Select Fund Source -</option>
                                        @foreach($fundSources as $fundSource)
                                            <option value="{{ $fundSource->id }}" @selected(old('fund_source_id', $ris->fund_source_id) == $fundSource->id)>
                                                {{ $fundSource->code }} - {{ $fundSource->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div>
                                    <label class="ti-form-label">FPP Code</label>
                                    <input
                                        name="fpp_code"
                                        value="{{ old('fpp_code', (string) ($ris->fpp_code ?? '')) }}"
                                        class="ti-form-input w-full"
                                        placeholder="e.g. 200"
                                        @disabled(!$isDraft)
                                    />
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="ti-form-label">Division</label>
                                    <input
                                        name="division"
                                        value="{{ old('division', (string) ($ris->division ?? '')) }}"
                                        class="ti-form-input w-full"
                                        @disabled(!$isDraft)
                                    />
                                </div>

                                <div>
                                    <label class="ti-form-label">Responsibility Center Code</label>
                                    <input
                                        name="responsibility_center_code"
                                        value="{{ old('responsibility_center_code', (string) ($ris->responsibility_center_code ?? $ris->requesting_department_code_snapshot ?? '')) }}"
                                        class="ti-form-input w-full"
                                        placeholder="RCC / Cost Center"
                                        @disabled(!$isDraft)
                                    />
                                    <div class="text-[11px] text-[#8c9097] mt-1">
                                        Accounting cost center code. This often matches the department code.
                                    </div>
                                </div>
                            </div>

                            <div>
                                <label class="ti-form-label">Requesting Department <span class="text-red-500">*</span></label>
                                <select
                                    name="requesting_department_id"
                                    form="risForm"
                                    class="ti-form-select w-full"
                                    required
                                    @disabled(!$isDraft)
                                >
                                    <option value="">- Select Department -</option>
                                    @foreach(($departments ?? []) as $department)
                                        <option
                                            value="{{ (string) $department->id }}"
                                            data-department-name="{{ (string) $department->name }}"
                                            @selected((string) old('requesting_department_id', (string) ($ris->requesting_department_id ?? '')) === (string) $department->id)
                                        >
                                            {{ (string) $department->name }}{{ !empty($department->code) ? ' - ' . $department->code : '' }}
                                        </option>
                                    @endforeach
                                </select>
                                <div class="text-[11px] text-[#8c9097] mt-1">
                                    Saving updates the stored department snapshot for this RIS.
                                </div>
                            </div>

                            <div>
                                <label class="ti-form-label">Purpose <span class="text-red-500">*</span></label>
                                <textarea
                                    name="purpose"
                                    class="ti-form-input w-full"
                                    rows="3"
                                    placeholder="Purpose of the requisition"
                                    required
                                    @disabled(!$isDraft)
                                >{{ old('purpose', (string) ($ris->purpose ?? '')) }}</textarea>
                            </div>

                            <div>
                                <label class="ti-form-label">Remarks</label>
                                <textarea
                                    name="remarks"
                                    class="ti-form-input w-full"
                                    rows="2"
                                    placeholder="Internal notes"
                                    @disabled(!$isDraft)
                                >{{ old('remarks', (string) ($ris->remarks ?? '')) }}</textarea>
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
                            <div class="font-semibold text-sm mb-2">Requested by</div>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                                <div>
                                    <label class="ti-form-label">Name <span class="text-red-500">*</span></label>
                                    <input name="requested_by_name" form="risForm" value="{{ old('requested_by_name', (string) ($ris->requested_by_name ?? '')) }}" class="ti-form-input w-full" required @disabled(!$isDraft) />
                                </div>
                                <div>
                                    <label class="ti-form-label">Designation <span class="text-red-500">*</span></label>
                                    <input name="requested_by_designation" form="risForm" value="{{ old('requested_by_designation', (string) ($ris->requested_by_designation ?? '')) }}" class="ti-form-input w-full" required @disabled(!$isDraft) />
                                </div>
                                <div>
                                    <label class="ti-form-label">Date <span class="text-red-500">*</span></label>
                                    <input type="date" name="requested_by_date" form="risForm" value="{{ old('requested_by_date', optional($ris->requested_by_date)->toDateString()) }}" class="ti-form-input w-full" required @disabled(!$isDraft) />
                                </div>
                            </div>
                        </div>

                        <div class="border border-defaultborder rounded p-3">
                            <div class="font-semibold text-sm mb-2">Approved by</div>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                                <div>
                                    <label class="ti-form-label">Name <span class="text-red-500">*</span></label>
                                    <input name="approved_by_name" form="risForm" value="{{ old('approved_by_name', $ris->approved_by_name ?? '') }}" class="ti-form-input w-full" required @disabled(!$isDraft) />
                                </div>
                                <div>
                                    <label class="ti-form-label">Designation <span class="text-red-500">*</span></label>
                                    <input name="approved_by_designation" form="risForm" value="{{ old('approved_by_designation', $ris->approved_by_designation ?? '') }}" class="ti-form-input w-full" required @disabled(!$isDraft) />
                                </div>
                                <div>
                                    <label class="ti-form-label">Date <span class="text-red-500">*</span></label>
                                    <input type="date" name="approved_by_date" form="risForm" value="{{ old('approved_by_date', optional($ris->approved_by_date)->toDateString()) }}" class="ti-form-input w-full" required @disabled(!$isDraft) />
                                </div>
                            </div>
                        </div>

                        <div class="border border-defaultborder rounded p-3">
                            <div class="font-semibold text-sm mb-2">Issued by</div>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                                <div>
                                    <label class="ti-form-label">Name <span class="text-red-500">*</span></label>
                                    <input name="issued_by_name" form="risForm" value="{{ old('issued_by_name', $ris->issued_by_name ?? '') }}" class="ti-form-input w-full" required @disabled(!$isDraft) />
                                </div>
                                <div>
                                    <label class="ti-form-label">Designation <span class="text-red-500">*</span></label>
                                    <input name="issued_by_designation" form="risForm" value="{{ old('issued_by_designation', $ris->issued_by_designation ?? '') }}" class="ti-form-input w-full" required @disabled(!$isDraft) />
                                </div>
                                <div>
                                    <label class="ti-form-label">Date <span class="text-red-500">*</span></label>
                                    <input type="date" name="issued_by_date" form="risForm" value="{{ old('issued_by_date', optional($ris->issued_by_date)->toDateString()) }}" class="ti-form-input w-full" required @disabled(!$isDraft) />
                                </div>
                            </div>
                        </div>

                        <div class="border border-defaultborder rounded p-3">
                            <div class="font-semibold text-sm mb-2">Received by</div>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                                <div>
                                    <label class="ti-form-label">Name <span class="text-red-500">*</span></label>
                                    <input name="received_by_name" form="risForm" value="{{ old('received_by_name', (string) ($ris->received_by_name ?? '')) }}" class="ti-form-input w-full" required @disabled(!$isDraft) />
                                </div>
                                <div>
                                    <label class="ti-form-label">Designation <span class="text-red-500">*</span></label>
                                    <input name="received_by_designation" form="risForm" value="{{ old('received_by_designation', (string) ($ris->received_by_designation ?? '')) }}" class="ti-form-input w-full" required @disabled(!$isDraft) />
                                </div>
                                <div>
                                    <label class="ti-form-label">Date <span class="text-red-500">*</span></label>
                                    <input type="date" name="received_by_date" form="risForm" value="{{ old('received_by_date', optional($ris->received_by_date)->toDateString()) }}" class="ti-form-input w-full" required @disabled(!$isDraft) />
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
                                <h5 class="box-title">RIS Items</h5>
                                <span id="risItemsCount" class="text-xs text-[#8c9097]">0 item(s)</span>
                            </div>

                            @if($isDraft)
                                <div class="relative w-full max-w-[420px] xl:min-w-[380px]">
                                    <input
                                        id="risItemSearch"
                                        type="text"
                                        class="form-control w-full !rounded-md"
                                        placeholder="Search stock no./item..."
                                        autocomplete="off"
                                    />

                                    <div id="risItemSuggestions" class="hidden absolute right-0 mt-2 w-full z-[9999] rounded-md border border-defaultborder bg-white dark:bg-bodybg shadow-lg">
                                        <div class="p-2 border-b border-defaultborder flex items-center justify-between">
                                            <div class="text-xs font-semibold text-defaulttextcolor dark:text-white">Suggestions</div>
                                            <button type="button" id="risItemSuggestClose" class="ti-btn ti-btn-sm ti-btn-light">
                                                <i class="ri-close-line"></i>
                                            </button>
                                        </div>
                                        <div id="risItemSuggestList" class="max-h-[320px] overflow-auto"></div>
                                        <div class="p-2 border-t border-defaultborder text-xs text-[#8c9097]">
                                            Type at least 2 characters. Save the Fund Source first so matching consumables can be suggested.
                                        </div>
                                    </div>
                                </div>
                            @else
                                <div class="text-xs text-[#8c9097] mt-1">
                                    Items are locked while this RIS is <b>{{ strtoupper($status) }}</b>.
                                </div>
                            @endif
                        </div>
                    </div>

                    <div class="box-body">
                        <p id="risItemsEmpty" class="text-sm text-[#8c9097] hidden">
                            No items yet. Add consumable items that match the saved Fund Source.
                        </p>

                        <div id="risItemsList" class="space-y-3">
                            <div class="text-xs text-[#8c9097]">Loading items...</div>
                        </div>

                        <div id="risItemsHelp" class="mt-3 text-xs text-[#8c9097] leading-5">
                            Requested quantity and available stock are shown in base unit. Only consumables from the saved Fund Source are suggested.
                        </div>
                    </div>
                </div>

                <div class="box">
                    <div class="box-header">
                        <h5 class="box-title">Document Notes</h5>
                    </div>
                    <div class="box-body text-sm text-[#8c9097] space-y-2">
                        <p>Draft RIS documents stay editable while the status is <b>DRAFT</b>.</p>
                        <p>Submit moves the document to <b>SUBMITTED</b>. Issue RIS deducts stock and moves the document to <b>ISSUED</b>.</p>
                        <p>Reopen RIS returns submitted or rejected records to <b>DRAFT</b>. Revert to Draft restores stock for previously issued RIS records.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        window.__ris = {
            updateUrl: @json(route('gso.ris.update', ['ris' => $ris->id])),
            csrf: @json(csrf_token()),
            itemSuggestUrl: @json(route('gso.ris.items.suggest', ['ris' => $ris->id])),
            itemAddUrl: @json(route('gso.ris.items.add', ['ris' => $ris->id])),
            itemListUrl: @json(route('gso.ris.items.list', ['ris' => $ris->id])),
            itemRemoveUrlTemplate: @json(route('gso.ris.items.remove', ['ris' => $ris->id, 'risItem' => '__ID__'])),
            itemUpdateUrlTemplate: @json(route('gso.ris.items.update', ['ris' => $ris->id, 'risItem' => '__ID__'])),
            itemBulkUpdateUrl: @json(route('gso.ris.items.bulk-update', ['ris' => $ris->id])),
            submitUrl: @json(route('gso.ris.submit', ['ris' => $ris->id])),
            approveUrl: @json(route('gso.ris.approve', ['ris' => $ris->id])),
            rejectUrl: @json(route('gso.ris.reject', ['ris' => $ris->id])),
            reopenUrl: @json(route('gso.ris.reopen', ['ris' => $ris->id])),
            revertUrl: @json(route('gso.ris.revert-to-draft', ['ris' => $ris->id])),
            accountableOfficerSuggestUrl: @json(route('gso.accountable-persons.suggest')),
            accountableOfficerResolveUrl: @json(route('gso.accountable-persons.resolve')),
            status: @json($status),
        };
    </script>
@endpush
