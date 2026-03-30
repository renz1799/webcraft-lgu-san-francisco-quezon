@extends('layouts.master')

@php
    $gsoUser = auth()->user();
    $gsoAuthorizer = app(\App\Core\Support\AdminContextAuthorizer::class);
    $canCreateIcs = $gsoAuthorizer->allowsPermission($gsoUser, 'ics.create');
    $canManageIcs = $gsoAuthorizer->allowsAnyPermission($gsoUser, [
        'ics.create',
        'ics.update',
        'ics.submit',
        'ics.finalize',
        'ics.reopen',
        'ics.manage_items',
    ]);
    $canDeleteIcs = $gsoAuthorizer->allowsPermission($gsoUser, 'ics.archive');
    $canRestoreIcs = $gsoAuthorizer->allowsAnyPermission($gsoUser, [
        'ics.restore',
        'audit_logs.restore_data',
    ]);
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
                ICS
            </h3>
        </div>
    </div>

    <div class="box">
        <div class="box-header">
            <div class="datatable-toolbar">
                <h5 class="box-title">Inventory Custodian Slips</h5>

                <div class="datatable-toolbar-actions">
                    <input
                        id="ics-search"
                        type="text"
                        class="form-control !w-[320px] !rounded-md"
                        placeholder="Search ICS no./receiver/remarks..."
                    />

                    <div class="relative shrink-0">
                        <button id="ics-more-btn" type="button" class="ti-btn ti-btn-light">
                            More Filters
                            <span
                                id="ics-adv-count"
                                class="hidden ms-2 inline-flex items-center justify-center text-[10px] leading-none px-2 py-1 rounded-full bg-primary/10 text-primary"
                            >
                                0
                            </span>
                            <i class="ri-arrow-down-s-line ms-1"></i>
                        </button>

                        <div
                            id="ics-more-panel"
                            class="hidden absolute right-0 mt-2 w-[360px] z-[9999] rounded-md border border-defaultborder bg-white dark:bg-bodybg shadow-lg"
                        >
                            <div class="p-3 border-b border-defaultborder flex items-center justify-between">
                                <div class="text-sm font-semibold text-defaulttextcolor dark:text-white">Advanced Filters</div>
                                <button id="ics-more-close" type="button" class="ti-btn ti-btn-sm ti-btn-light">
                                    <i class="ri-close-line"></i>
                                </button>
                            </div>

                            <div class="p-3 space-y-3">
                                <div>
                                    <label class="ti-form-label">Issued Date Range</label>
                                    <div class="grid grid-cols-2 gap-2">
                                        <input id="ics-date-from" type="date" class="ti-form-input w-full" />
                                        <input id="ics-date-to" type="date" class="ti-form-input w-full" />
                                    </div>
                                </div>

                                <div>
                                    <label class="ti-form-label">Workflow Status</label>
                                    <select id="ics-status" class="ti-form-input w-full">
                                        <option value="">All Status</option>
                                        <option value="draft">Draft</option>
                                        <option value="submitted">Submitted</option>
                                        <option value="finalized">Finalized</option>
                                        <option value="cancelled">Cancelled</option>
                                    </select>
                                </div>

                                <div>
                                    <label class="ti-form-label">Department</label>
                                    <select id="ics-department" class="ti-form-input w-full">
                                        <option value="">All Departments</option>
                                        @foreach(($departments ?? collect()) as $department)
                                            @php
                                                $departmentLabel = trim(($department->code ? $department->code . ' - ' : '') . $department->name);
                                            @endphp
                                            <option value="{{ $department->id }}">
                                                {{ $departmentLabel !== '' ? $departmentLabel : $department->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div>
                                    <label class="ti-form-label">Fund Source</label>
                                    <select id="ics-fund-source" class="ti-form-input w-full">
                                        <option value="">All Fund Sources</option>
                                        @foreach(($fundSources ?? collect()) as $fundSource)
                                            <option value="{{ $fundSource->id }}">
                                                {{ trim(($fundSource->code ? $fundSource->code . ' - ' : '') . $fundSource->name) }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div>
                                    <label class="ti-form-label">Record Status</label>
                                    <select id="ics-record-status" class="ti-form-input w-full">
                                        <option value="">Active</option>
                                        <option value="archived">Archived</option>
                                        <option value="all">All</option>
                                    </select>
                                </div>
                            </div>

                            <div class="p-3 border-t border-defaultborder flex items-center justify-end gap-2">
                                <button id="ics-adv-reset" type="button" class="ti-btn ti-btn-light">Reset</button>
                                <button id="ics-adv-apply" type="button" class="ti-btn ti-btn-primary">Apply</button>
                            </div>
                        </div>
                    </div>

                    <button id="ics-clear" type="button" class="ti-btn ti-btn-light">Clear</button>

                    @if($canCreateIcs)
                        <form method="POST" action="{{ route('gso.ics.create-draft') }}" class="inline-flex">
                            @csrf
                            <button type="submit" class="ti-btn ti-btn-primary">
                                Create ICS
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        </div>

        <div class="box-body">
            <div class="overflow-auto table-bordered">
                <div id="ics-table" class="ti-custom-table ti-striped-table ti-custom-table-hover"></div>
            </div>

            <div class="mt-2 flex items-center justify-between text-xs text-[#8c9097]">
                <div id="ics-info"></div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('build/assets/libs/tabulator-tables/js/tabulator.min.js') }}"></script>
    <script>
        window.__ics = {
            ajaxUrl: @json(route('gso.ics.data')),
            csrf: @json(csrf_token()),
            editUrlTemplate: @json(route('gso.ics.edit', ['ics' => '__ICS_ID__'])),
            deleteUrlTemplate: @json(route('gso.ics.destroy', ['ics' => '__ICS_ID__'])),
            restoreUrlTemplate: @json(route('gso.ics.restore', ['ics' => '__ICS_ID__'])),
            canEdit: @json($canManageIcs),
            canDelete: @json($canDeleteIcs),
            canRestore: @json($canRestoreIcs),
        };
    </script>
@endpush
