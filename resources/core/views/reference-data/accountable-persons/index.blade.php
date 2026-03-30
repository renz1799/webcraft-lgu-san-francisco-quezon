@extends('layouts.master')

@php($adminRoutes = $adminRoutes ?? app(\App\Core\Support\AdminRouteResolver::class))
@php($moduleScopedAccess = $adminRoutes->isModuleScoped())
@php($moduleContextName = trim((string) ($currentModule->name ?? $adminRoutes->scopedModuleCode() ?? 'Module')) ?: 'Module')
@php($accountablePersonViewer = auth()->user())
@php($accountablePersonAuthorizer = app(\App\Core\Support\AdminContextAuthorizer::class))
@php(
    $canManageAccountablePersons = $accountablePersonAuthorizer->allowsAnyPermission($accountablePersonViewer, [
        'accountable_persons.create',
        'accountable_persons.update',
        'accountable_persons.archive',
        'accountable_persons.restore',
    ])
)

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
            Accountable Persons
        </h3>
        <p class="text-[#8c9097] dark:text-white/50 text-[0.813rem] mb-0">
            {{ $moduleScopedAccess
                ? 'Shared accountable person records reused by ' . $moduleContextName . ' documents, inventory flows, and signatory selections.'
                : 'Shared accountable person records reused across modules for accountability, custody, and signatory workflows.' }}
        </p>
    </div>
</div>

<div class="box">
    <div class="box-header">
        <div class="datatable-toolbar">
            <h5 class="box-title">Accountable Persons</h5>

            <div class="datatable-toolbar-actions">
                <input id="accountable-persons-search" type="text" class="form-control !w-[320px] !rounded-md" placeholder="Search name, designation, office, or department...">

                <div class="relative shrink-0">
                    <button id="accountable-persons-more-btn" type="button" class="ti-btn ti-btn-light">
                        More Filters
                        <span id="accountable-persons-adv-count"
                            class="hidden ms-2 inline-flex items-center justify-center text-[10px] leading-none px-2 py-1 rounded-full bg-primary/10 text-primary">
                            0
                        </span>
                        <i class="ri-arrow-down-s-line ms-1"></i>
                    </button>

                    <div id="accountable-persons-more-panel"
                        class="hidden absolute right-0 mt-2 w-[360px] z-[9999] rounded-md border border-defaultborder bg-white dark:bg-bodybg shadow-lg">

                        <div class="p-3 border-b border-defaultborder flex items-center justify-between">
                            <div class="text-sm font-semibold text-defaulttextcolor dark:text-white">Advanced Filters</div>
                            <button id="accountable-persons-more-close" type="button" class="ti-btn ti-btn-sm ti-btn-light">
                                <i class="ri-close-line"></i>
                            </button>
                        </div>

                        <div class="p-3 space-y-3">
                            <div>
                                <label class="ti-form-label">Status</label>
                                <select id="accountable-persons-status" class="ti-form-input w-full">
                                    <option value="active">Active</option>
                                    <option value="archived">Archived</option>
                                    <option value="all">All</option>
                                </select>
                                <div class="text-xs text-[#8c9097] mt-1">Filter active, archived, or all records.</div>
                            </div>

                            <div>
                                <label class="ti-form-label">Department</label>
                                <select id="accountable-persons-department-filter" class="ti-form-input w-full">
                                    <option value="">All Departments</option>
                                    @foreach($departments as $department)
                                        <option value="{{ $department->id }}">{{ $department->code }} - {{ $department->name }}</option>
                                    @endforeach
                                </select>
                                <div class="text-xs text-[#8c9097] mt-1">Limit the register to one department.</div>
                            </div>
                        </div>

                        <div class="p-3 border-t border-defaultborder flex items-center justify-end gap-2">
                            <button id="accountable-persons-adv-reset" type="button" class="ti-btn ti-btn-light">Reset</button>
                            <button id="accountable-persons-adv-apply" type="button" class="ti-btn ti-btn-primary">Apply</button>
                        </div>
                    </div>
                </div>

                <button id="accountable-persons-clear" type="button" class="ti-btn ti-btn-light">
                    Clear
                </button>

                @if($canManageAccountablePersons)
                    <button
                        type="button"
                        class="hs-dropdown-toggle ti-btn btn-wave ti-btn-primary-full"
                        data-hs-overlay="#accountablePersonModal"
                        data-mode="create"
                    >
                        Add Accountable Person
                    </button>
                @endif
            </div>
        </div>
    </div>

    <div class="box-body">
        <div class="overflow-auto table-bordered">
            <div id="accountable-persons-table" class="ti-custom-table ti-striped-table ti-custom-table-hover"></div>
        </div>

        <div class="mt-2 flex items-center justify-between text-xs text-[#8c9097]">
            <div id="accountable-persons-info"></div>
        </div>
    </div>
</div>

@if($canManageAccountablePersons)
    <div id="accountablePersonModal" class="hs-overlay hidden ti-modal [--overlay-backdrop:static]">
        <div class="hs-overlay-open:mt-7 ti-modal-box mt-0 ease-out">
            <div class="ti-modal-content">
                <div class="ti-modal-header">
                    <h6 id="accountablePersonModalTitle" class="modal-title text-[1rem] font-semibold">Add Accountable Person</h6>
                    <button type="button" class="hs-dropdown-toggle !text-[1rem] !font-semibold !text-defaulttextcolor" data-hs-overlay="#accountablePersonModal">
                        <span class="sr-only">Close</span>
                        <i class="ri-close-line"></i>
                    </button>
                </div>

                <div class="ti-modal-body px-4">
                    <div id="accountablePersonFormError" class="hidden mb-3 p-2 rounded bg-danger/10 text-danger text-sm"></div>

                    <input type="hidden" id="accountablePersonId" value="">

                    <div class="grid grid-cols-1 gap-3">
                        <div>
                            <label class="text-sm text-[#8c9097]">Full Name</label>
                            <input id="accountablePersonFullName" type="text" class="ti-form-input w-full" placeholder="e.g. Juan Dela Cruz">
                            <div id="accountablePersonFullNameErr" class="text-xs text-danger mt-1 hidden"></div>
                        </div>

                        <div>
                            <label class="text-sm text-[#8c9097]">Designation</label>
                            <input id="accountablePersonDesignation" type="text" class="ti-form-input w-full" placeholder="Optional designation">
                            <div id="accountablePersonDesignationErr" class="text-xs text-danger mt-1 hidden"></div>
                        </div>

                        <div>
                            <label class="text-sm text-[#8c9097]">Office</label>
                            <input id="accountablePersonOffice" type="text" class="ti-form-input w-full" placeholder="Optional office">
                            <div id="accountablePersonOfficeErr" class="text-xs text-danger mt-1 hidden"></div>
                        </div>

                        <div>
                            <label class="text-sm text-[#8c9097]">Department</label>
                            <select id="accountablePersonDepartmentId" class="ti-form-select w-full">
                                <option value="">No Department</option>
                                @foreach($departments as $department)
                                    <option value="{{ $department->id }}">{{ $department->code }} - {{ $department->name }}</option>
                                @endforeach
                            </select>
                            <div id="accountablePersonDepartmentErr" class="text-xs text-danger mt-1 hidden"></div>
                        </div>

                        <div>
                            <label class="text-sm text-[#8c9097]">Record Status</label>
                            <select id="accountablePersonIsActive" class="ti-form-select w-full">
                                <option value="1">Active</option>
                                <option value="0">Inactive</option>
                            </select>
                            <div id="accountablePersonIsActiveErr" class="text-xs text-danger mt-1 hidden"></div>
                        </div>
                    </div>
                </div>

                <div class="ti-modal-footer">
                    <button type="button" class="hs-dropdown-toggle ti-btn btn-wave ti-btn-secondary-full align-middle" data-hs-overlay="#accountablePersonModal">
                        Close
                    </button>
                    <button type="button" id="accountablePersonSaveBtn" class="ti-btn btn-wave bg-primary text-white !font-medium">
                        Save
                    </button>
                </div>
            </div>
        </div>
    </div>
@endif
@endsection

@push('scripts')
    <script src="{{ asset('build/assets/libs/tabulator-tables/js/tabulator.min.js') }}"></script>
    <script>
        window.__accountablePersons = {
            ajaxUrl: @json($adminRoutes->route('accountable-persons.data')),
            storeUrl: @json($adminRoutes->route('accountable-persons.store')),
            updateUrlTemplate: @json($adminRoutes->route('accountable-persons.update', ['accountablePerson' => '__ID__'])),
            deleteUrlTemplate: @json($adminRoutes->route('accountable-persons.destroy', ['accountablePerson' => '__ID__'])),
            restoreUrlTemplate: @json($adminRoutes->route('accountable-persons.restore', ['accountablePerson' => '__ID__'])),
            suggestUrl: @json($adminRoutes->route('accountable-persons.suggest')),
            csrf: @json(csrf_token()),
            canManage: @json($canManageAccountablePersons),
        };
    </script>
@endpush
