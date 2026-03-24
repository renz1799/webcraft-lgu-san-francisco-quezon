@extends('layouts.master')

@section('styles')
    <link rel="stylesheet" href="{{ asset('build/assets/libs/tabulator-tables/css/tabulator.min.css') }}">
    <style>
        .items-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            width: 100%;
        }

        .items-actions {
            display: flex;
            align-items: center;
            gap: 8px;
            flex-wrap: wrap;
            justify-content: flex-end;
        }

        .box-header {
            overflow: visible !important;
        }
    </style>
@endsection

@section('content')
    <div class="block justify-between page-header md:flex">
        <div>
            <h3 class="!text-defaulttextcolor dark:!text-defaulttextcolor/70 dark:text-white text-[1.125rem] font-semibold">
                RIS
            </h3>
        </div>
    </div>

    <div class="box">
        <div class="box-header">
            <div class="items-header">
                <h5 class="box-title">Requisition and Issue Slips</h5>

                <div class="items-actions">
                    <input
                        id="ris-search"
                        type="text"
                        class="form-control !w-[320px] !rounded-md"
                        placeholder="Search RIS no./purpose..."
                    />

                    <select id="ris-status" class="form-control !w-[180px] !rounded-md">
                        <option value="">All Status</option>
                        <option value="draft">Draft</option>
                        <option value="submitted">Submitted</option>
                        <option value="issued">Issued</option>
                        <option value="rejected">Rejected</option>
                    </select>

                    <div class="relative shrink-0">
                        <button id="ris-more-btn" type="button" class="ti-btn ti-btn-light">
                            More Filters
                            <span
                                id="ris-adv-count"
                                class="hidden ms-2 inline-flex items-center justify-center text-[10px] leading-none px-2 py-1 rounded-full bg-primary/10 text-primary"
                            >
                                0
                            </span>
                            <i class="ri-arrow-down-s-line ms-1"></i>
                        </button>

                        <div
                            id="ris-more-panel"
                            class="hidden absolute right-0 mt-2 w-[360px] z-[9999] rounded-md border border-defaultborder bg-white dark:bg-bodybg shadow-lg"
                        >
                            <div class="p-3 border-b border-defaultborder flex items-center justify-between">
                                <div class="text-sm font-semibold text-defaulttextcolor dark:text-white">Advanced Filters</div>
                                <button id="ris-more-close" type="button" class="ti-btn ti-btn-sm ti-btn-light">
                                    <i class="ri-close-line"></i>
                                </button>
                            </div>

                            <div class="p-3 space-y-3">
                                <div>
                                    <label class="ti-form-label">RIS Date Range</label>
                                    <div class="grid grid-cols-2 gap-2">
                                        <input id="ris-date-from" type="date" class="ti-form-input w-full" />
                                        <input id="ris-date-to" type="date" class="ti-form-input w-full" />
                                    </div>
                                    <div class="text-xs text-[#8c9097] mt-1">Filters by RIS date range.</div>
                                </div>

                                <div>
                                    <label class="ti-form-label">Fund</label>
                                    <input id="ris-fund" type="text" class="ti-form-input w-full" placeholder="e.g. General Fund" />
                                    <div class="text-xs text-[#8c9097] mt-1">Matches fund text.</div>
                                </div>

                                <div>
                                    <label class="ti-form-label">Record Status</label>
                                    <select id="ris-record-status" class="ti-form-input w-full">
                                        <option value="">Active</option>
                                        <option value="archived">Archived</option>
                                        <option value="all">All</option>
                                    </select>
                                    <div class="text-xs text-[#8c9097] mt-1">Includes archived RIS records when needed.</div>
                                </div>
                            </div>

                            <div class="p-3 border-t border-defaultborder flex items-center justify-end gap-2">
                                <button id="ris-adv-reset" type="button" class="ti-btn ti-btn-light">Reset</button>
                                <button id="ris-adv-apply" type="button" class="ti-btn ti-btn-primary">Apply</button>
                            </div>
                        </div>
                    </div>

                    <button id="ris-clear" type="button" class="ti-btn ti-btn-light">Clear</button>

                    <form method="POST" action="{{ route('gso.ris.create-draft') }}">
                        @csrf
                        <button type="submit" class="ti-btn ti-btn-primary">
                            <i class="ri-add-line"></i> Create RIS
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="box-body">
            <div class="overflow-auto table-bordered">
                <div id="ris-table" class="ti-custom-table ti-striped-table ti-custom-table-hover"></div>
            </div>

            <div class="mt-2 flex items-center justify-between text-xs text-[#8c9097]">
                <div id="ris-info"></div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('build/assets/libs/tabulator-tables/js/tabulator.min.js') }}"></script>
    <script>
        window.__ris = {
            ajaxUrl: @json(route('gso.ris.data')),
            csrf: @json(csrf_token()),
            editUrlTemplate: @json(route('gso.ris.edit', ['ris' => '__RIS_ID__'])),
            deleteUrlTemplate: @json(route('gso.ris.destroy', ['ris' => '__RIS_ID__'])),
            restoreUrlTemplate: @json(route('gso.ris.restore', ['ris' => '__RIS_ID__'])),
            canEdit: @json(auth()->user()?->hasRole('Administrator') || auth()->user()?->can('modify RIS')),
            canDelete: @json(auth()->user()?->hasRole('Administrator') || auth()->user()?->can('delete RIS') || auth()->user()?->can('modify RIS')),
            canRestore: @json(
                auth()->user()?->hasRole('Administrator')
                || auth()->user()?->can('modify Allow Data Restoration')
                || auth()->user()?->can('restore RIS')
            ),
        };
    </script>
@endpush
