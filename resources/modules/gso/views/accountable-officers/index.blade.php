@extends('layouts.master')

@php
    $gsoUser = auth()->user();
    $gsoAuthorizer = app(\App\Core\Support\AdminContextAuthorizer::class);
    $canManageAccountableOfficers = $gsoAuthorizer->allowsAnyPermission($gsoUser, [
        'accountable_persons.create',
        'accountable_persons.update',
        'accountable_persons.archive',
        'accountable_persons.restore',
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
            Accountable Officers
        </h3>
        <p class="text-[#8c9097] dark:text-white/50 text-[0.813rem] mb-0">
            Officer master records used for GSO inventory accountability, custody, and document signatures.
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
            Accountable Officers
        </li>
    </ol>
</div>

<div class="box">
    <div class="box-header">
        <div class="gso-reference-toolbar">
            <h5 class="box-title">Accountable Officers</h5>

            <div class="gso-reference-actions">
                <input id="accountable-officers-search" type="text" class="form-control !w-[300px] !rounded-md" placeholder="Search name, designation, office, or department...">

                <select id="accountable-officers-department-filter" class="form-control w-[240px] !rounded-md">
                    <option value="">All Departments</option>
                    @foreach($departments as $department)
                        <option value="{{ $department->id }}">{{ $department->code }} - {{ $department->name }}</option>
                    @endforeach
                </select>

                <select id="accountable-officers-status" class="form-control w-[180px] !rounded-md">
                    <option value="active">Active</option>
                    <option value="archived">Archived</option>
                    <option value="all">All</option>
                </select>

                <button id="accountable-officers-clear" type="button" class="ti-btn ti-btn-light">
                    Clear
                </button>

                @if($canManageAccountableOfficers)
                    <button
                        type="button"
                        class="hs-dropdown-toggle ti-btn btn-wave ti-btn-primary-full"
                        data-hs-overlay="#accountableOfficerModal"
                        data-mode="create"
                    >
                        Add Officer
                    </button>
                @endif
            </div>
        </div>
    </div>

    <div class="box-body">
        <div class="overflow-auto table-bordered">
            <div id="accountable-officers-table" class="ti-custom-table ti-striped-table ti-custom-table-hover"></div>
        </div>

        <div class="mt-2 flex items-center justify-between text-xs text-[#8c9097]">
            <div id="accountable-officers-info"></div>
        </div>
    </div>
</div>

@if($canManageAccountableOfficers)
    <div id="accountableOfficerModal" class="hs-overlay hidden ti-modal [--overlay-backdrop:static]">
        <div class="hs-overlay-open:mt-7 ti-modal-box mt-0 ease-out">
            <div class="ti-modal-content">
                <div class="ti-modal-header">
                    <h6 id="accountableOfficerModalTitle" class="modal-title text-[1rem] font-semibold">Add Accountable Officer</h6>
                    <button type="button" class="hs-dropdown-toggle !text-[1rem] !font-semibold !text-defaulttextcolor" data-hs-overlay="#accountableOfficerModal">
                        <span class="sr-only">Close</span>
                        <i class="ri-close-line"></i>
                    </button>
                </div>

                <div class="ti-modal-body px-4">
                    <div id="accountableOfficerFormError" class="hidden mb-3 p-2 rounded bg-danger/10 text-danger text-sm"></div>

                    <input type="hidden" id="accountableOfficerId" value="">

                    <div class="grid grid-cols-1 gap-3">
                        <div>
                            <label class="text-sm text-[#8c9097]">Full Name</label>
                            <input id="accountableOfficerFullName" type="text" class="ti-form-input w-full" placeholder="e.g. Juan Dela Cruz">
                            <div id="accountableOfficerFullNameErr" class="text-xs text-danger mt-1 hidden"></div>
                        </div>

                        <div>
                            <label class="text-sm text-[#8c9097]">Designation</label>
                            <input id="accountableOfficerDesignation" type="text" class="ti-form-input w-full" placeholder="Optional designation">
                            <div id="accountableOfficerDesignationErr" class="text-xs text-danger mt-1 hidden"></div>
                        </div>

                        <div>
                            <label class="text-sm text-[#8c9097]">Office</label>
                            <input id="accountableOfficerOffice" type="text" class="ti-form-input w-full" placeholder="Optional office">
                            <div id="accountableOfficerOfficeErr" class="text-xs text-danger mt-1 hidden"></div>
                        </div>

                        <div>
                            <label class="text-sm text-[#8c9097]">Department</label>
                            <select id="accountableOfficerDepartmentId" class="ti-form-select w-full">
                                <option value="">No Department</option>
                                @foreach($departments as $department)
                                    <option value="{{ $department->id }}">{{ $department->code }} - {{ $department->name }}</option>
                                @endforeach
                            </select>
                            <div id="accountableOfficerDepartmentErr" class="text-xs text-danger mt-1 hidden"></div>
                        </div>

                        <div>
                            <label class="text-sm text-[#8c9097]">Record Status</label>
                            <select id="accountableOfficerIsActive" class="ti-form-select w-full">
                                <option value="1">Active</option>
                                <option value="0">Inactive</option>
                            </select>
                            <div id="accountableOfficerIsActiveErr" class="text-xs text-danger mt-1 hidden"></div>
                        </div>
                    </div>
                </div>

                <div class="ti-modal-footer">
                    <button type="button" class="hs-dropdown-toggle ti-btn btn-wave ti-btn-secondary-full align-middle" data-hs-overlay="#accountableOfficerModal">
                        Close
                    </button>
                    <button type="button" id="accountableOfficerSaveBtn" class="ti-btn btn-wave bg-primary text-white !font-medium">
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
        window.__gsoAccountableOfficers = {
            ajaxUrl: @json(route('gso.accountable-officers.data')),
            storeUrl: @json(route('gso.accountable-officers.store')),
            updateUrlTemplate: @json(route('gso.accountable-officers.update', ['accountableOfficer' => '__ID__'])),
            deleteUrlTemplate: @json(route('gso.accountable-officers.destroy', ['accountableOfficer' => '__ID__'])),
            restoreUrlTemplate: @json(route('gso.accountable-officers.restore', ['accountableOfficer' => '__ID__'])),
            suggestUrl: @json(route('gso.accountable-officers.suggest')),
            csrf: @json(csrf_token()),
            canManage: @json($canManageAccountableOfficers),
        };
    </script>
@endpush
