@extends('layouts.master')

@php
    $gsoUser = auth()->user();
    $gsoAuthorizer = app(\App\Core\Support\AdminContextAuthorizer::class);
    $canManageDepartments = $gsoAuthorizer->allowsAnyPermission($gsoUser, [
        'departments.create',
        'departments.update',
        'departments.archive',
        'departments.restore',
    ]);
@endphp

@section('styles')
    <link rel="stylesheet" href="{{ asset('build/assets/libs/tabulator-tables/css/tabulator.min.css') }}">
    <style>
        .gso-reference-toolbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            width: 100%;
        }

        .gso-reference-actions {
            display: flex;
            align-items: center;
            gap: 8px;
            flex-wrap: wrap;
            justify-content: flex-end;
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
            Departments
        </h3>
        <p class="text-[#8c9097] dark:text-white/50 text-[0.813rem] mb-0">
            Core-backed department master records used by GSO workflows and accountability routing.
        </p>
    </div>
    <ol class="flex items-center whitespace-nowrap min-w-0">
        <li class="text-[0.813rem] ps-[0.5rem]">
            <a class="flex items-center text-primary hover:text-primary dark:text-primary truncate" href="{{ route('gso.dashboard') }}">
                GSO
                <i class="ti ti-chevrons-right flex-shrink-0 text-[#8c9097] dark:text-white/50 px-[0.5rem] overflow-visible rtl:rotate-180"></i>
            </a>
        </li>
        <li class="text-[0.813rem] text-defaulttextcolor font-semibold dark:text-white/50" aria-current="page">
            Departments
        </li>
    </ol>
</div>

<div class="box">
    <div class="box-header">
        <div class="gso-reference-toolbar">
            <h5 class="box-title">Departments</h5>

            <div class="gso-reference-actions">
                <input id="departments-search" type="text" class="form-control w-[300px] !rounded-md" placeholder="Search code, name, short name, or type...">

                <select id="departments-status" class="form-control w-[180px] !rounded-md">
                    <option value="active">Active</option>
                    <option value="archived">Archived</option>
                    <option value="all">All</option>
                </select>

                <button id="departments-clear" type="button" class="ti-btn ti-btn-light">
                    Clear
                </button>

                @if($canManageDepartments)
                    <button
                        type="button"
                        class="hs-dropdown-toggle ti-btn btn-wave ti-btn-primary-full"
                        data-hs-overlay="#departmentModal"
                        data-mode="create"
                    >
                        Add Department
                    </button>
                @endif
            </div>
        </div>
    </div>

    <div class="box-body">
        <div class="overflow-auto table-bordered">
            <div id="departments-table" class="ti-custom-table ti-striped-table ti-custom-table-hover"></div>
        </div>

        <div class="mt-2 flex items-center justify-between text-xs text-[#8c9097]">
            <div id="departments-info"></div>
        </div>
    </div>
</div>

@if($canManageDepartments)
    <div id="departmentModal" class="hs-overlay hidden ti-modal [--overlay-backdrop:static]">
        <div class="hs-overlay-open:mt-7 ti-modal-box mt-0 ease-out">
            <div class="ti-modal-content">
                <div class="ti-modal-header">
                    <h6 id="departmentModalTitle" class="modal-title text-[1rem] font-semibold">Add Department</h6>
                    <button type="button" class="hs-dropdown-toggle !text-[1rem] !font-semibold !text-defaulttextcolor" data-hs-overlay="#departmentModal">
                        <span class="sr-only">Close</span>
                        <i class="ri-close-line"></i>
                    </button>
                </div>

                <div class="ti-modal-body px-4">
                    <div id="departmentFormError" class="hidden mb-3 p-2 rounded bg-danger/10 text-danger text-sm"></div>

                    <input type="hidden" id="departmentId" value="">

                    <div class="grid grid-cols-1 gap-3">
                        <div>
                            <label class="text-sm text-[#8c9097]">Department Code</label>
                            <input id="departmentCode" type="text" class="ti-form-input w-full" placeholder="e.g. GSO">
                            <div id="departmentCodeErr" class="text-xs text-danger mt-1 hidden"></div>
                        </div>

                        <div>
                            <label class="text-sm text-[#8c9097]">Department Name</label>
                            <input id="departmentName" type="text" class="ti-form-input w-full" placeholder="e.g. General Services Office">
                            <div id="departmentNameErr" class="text-xs text-danger mt-1 hidden"></div>
                        </div>

                        <div>
                            <label class="text-sm text-[#8c9097]">Short Name</label>
                            <input id="departmentShortName" type="text" class="ti-form-input w-full" placeholder="Optional shorthand label">
                            <div id="departmentShortNameErr" class="text-xs text-danger mt-1 hidden"></div>
                        </div>

                        <div>
                            <label class="text-sm text-[#8c9097]">Type</label>
                            <input id="departmentType" type="text" class="ti-form-input w-full" placeholder="Optional type, e.g. office">
                            <div id="departmentTypeErr" class="text-xs text-danger mt-1 hidden"></div>
                        </div>

                        <div>
                            <label class="text-sm text-[#8c9097]">Record Status</label>
                            <select id="departmentIsActive" class="ti-form-select w-full">
                                <option value="1">Active</option>
                                <option value="0">Inactive</option>
                            </select>
                            <div id="departmentIsActiveErr" class="text-xs text-danger mt-1 hidden"></div>
                        </div>
                    </div>
                </div>

                <div class="ti-modal-footer">
                    <button type="button" class="hs-dropdown-toggle ti-btn btn-wave ti-btn-secondary-full align-middle" data-hs-overlay="#departmentModal">
                        Close
                    </button>
                    <button type="button" id="departmentSaveBtn" class="ti-btn btn-wave bg-primary text-white !font-medium">
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
        window.__gsoDepartments = {
            ajaxUrl: @json(route('gso.departments.data')),
            storeUrl: @json(route('gso.departments.store')),
            updateUrlTemplate: @json(route('gso.departments.update', ['department' => '__ID__'])),
            deleteUrlTemplate: @json(route('gso.departments.destroy', ['department' => '__ID__'])),
            restoreUrlTemplate: @json(route('gso.departments.restore', ['department' => '__ID__'])),
            csrf: @json(csrf_token()),
            canManage: @json($canManageDepartments),
        };
    </script>
@endpush
