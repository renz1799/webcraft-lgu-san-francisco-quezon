@extends('layouts.master')

@php
    $canManageAir = auth()->user()?->hasAnyRole(['Administrator', 'admin'])
        || auth()->user()?->can('modify AIR');
@endphp

@section('styles')
    <link rel="stylesheet" href="{{ asset('build/assets/libs/tabulator-tables/css/tabulator.min.css') }}">
    <style>
        .box-header {
            overflow: visible !important;
        }

        .tabulator .tabulator-loader,
        .tabulator .tabulator-loader-msg {
            display: none !important;
        }

        .tabulator.is-loading {
            opacity: 0.65;
            pointer-events: none;
        }
    </style>
@endsection

@section('content')
    <div class="block justify-between page-header md:flex">
        <div>
            <h3 class="!text-defaulttextcolor dark:!text-defaulttextcolor/70 dark:text-white text-[1.125rem] font-semibold">
                AIR
            </h3>
        </div>
    </div>

    <div class="box">
        <div class="box-header">
            <div class="datatable-toolbar">
                <h5 class="box-title">Acceptance &amp; Inspection Reports</h5>

                <div class="datatable-toolbar-actions">
                    <input
                        id="gso-air-search"
                        type="text"
                        class="form-control !w-[320px] !rounded-md"
                        placeholder="Search AIR no./PO/supplier/inspectors..."
                    />

                    <select id="gso-air-archived-filter" class="form-control !w-[180px] !rounded-md">
                        <option value="active">Active</option>
                        <option value="archived">Archived</option>
                        <option value="all">All</option>
                    </select>

                    <div class="relative shrink-0">
                        <button id="gso-air-more-btn" type="button" class="ti-btn ti-btn-light">
                            More Filters
                            <span
                                id="gso-air-adv-count"
                                class="hidden ms-2 inline-flex items-center justify-center text-[10px] leading-none px-2 py-1 rounded-full bg-primary/10 text-primary"
                            >
                                0
                            </span>
                            <i class="ri-arrow-down-s-line ms-1"></i>
                        </button>

                        <div
                            id="gso-air-more-panel"
                            class="hidden absolute right-0 mt-2 w-[360px] z-[9999] rounded-md border border-defaultborder bg-white dark:bg-bodybg shadow-lg"
                        >
                            <div class="p-3 border-b border-defaultborder flex items-center justify-between">
                                <div class="text-sm font-semibold text-defaulttextcolor dark:text-white">Advanced Filters</div>
                                <button id="gso-air-more-close" type="button" class="ti-btn ti-btn-sm ti-btn-light">
                                    <i class="ri-close-line"></i>
                                </button>
                            </div>

                            <div class="p-3 space-y-3">
                                <div>
                                    <label class="ti-form-label">Date Range</label>
                                    <div class="grid grid-cols-2 gap-2">
                                        <input id="gso-air-date-from" type="date" class="ti-form-input w-full" />
                                        <input id="gso-air-date-to" type="date" class="ti-form-input w-full" />
                                    </div>
                                    <div class="text-xs text-[#8c9097] mt-1">Filters AIR date from and to.</div>
                                </div>

                                <div>
                                    <label class="ti-form-label">Supplier</label>
                                    <input id="gso-air-supplier-filter" type="text" class="ti-form-input w-full" placeholder="e.g., ABC Trading" />
                                </div>

                                <div>
                                    <label class="ti-form-label">Department</label>
                                    <input id="gso-air-department-filter" type="text" class="ti-form-input w-full" placeholder="e.g., GSO / Mayor's Office" />
                                </div>

                                <div>
                                    <label class="ti-form-label">Inspection Status</label>
                                    <select id="gso-air-status-filter" class="ti-form-select w-full">
                                        <option value="">Any</option>
                                        <option value="draft">Draft</option>
                                        <option value="submitted">Submitted</option>
                                        <option value="in_progress">In Progress</option>
                                        <option value="inspected">Inspected</option>
                                    </select>
                                </div>

                                <div>
                                    <label class="ti-form-label">Received Completeness</label>
                                    <select id="gso-air-completeness-filter" class="ti-form-select w-full">
                                        <option value="">Any</option>
                                        <option value="complete">Complete</option>
                                        <option value="partial">Partial</option>
                                    </select>
                                </div>
                            </div>

                            <div class="p-3 border-t border-defaultborder flex items-center justify-end gap-2">
                                <button id="gso-air-adv-reset" type="button" class="ti-btn ti-btn-light">Reset</button>
                                <button id="gso-air-adv-apply" type="button" class="ti-btn ti-btn-primary">Apply</button>
                            </div>
                        </div>
                    </div>

                    <button id="gso-air-clear" type="button" class="ti-btn ti-btn-light">Clear</button>

                    @if($canManageAir)
                        <a href="{{ route('gso.air.create') }}" class="ti-btn ti-btn-primary">
                            Create Draft
                        </a>
                    @endif
                </div>
            </div>
        </div>

        <div class="box-body">
            <div class="overflow-auto table-bordered">
                <div id="gso-air-table" class="ti-custom-table ti-striped-table ti-custom-table-hover"></div>
            </div>

            <div class="mt-2 flex items-center justify-between text-xs text-[#8c9097]">
                <div id="gso-air-info"></div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('build/assets/libs/tabulator-tables/js/tabulator.min.js') }}"></script>
    <script>
        window.__gsoAir = {
            ajaxUrl: @json(route('gso.air.data')),
            editUrlTemplate: @json(route('gso.air.edit', ['air' => '__AIR_ID__'])),
            deleteUrlTemplate: @json(route('gso.air.destroy', ['air' => '__AIR_ID__'])),
            restoreUrlTemplate: @json(route('gso.air.restore', ['air' => '__AIR_ID__'])),
            forceDeleteUrlTemplate: @json(route('gso.air.force-destroy', ['air' => '__AIR_ID__'])),
            csrf: @json(csrf_token()),
            canManage: @json($canManageAir),
        };
    </script>
@endpush
