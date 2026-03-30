@extends('layouts.master')

@php
    $gsoUser = auth()->user();
    $gsoAuthorizer = app(\App\Core\Support\AdminContextAuthorizer::class);
    $canManageAssetTypes = $gsoAuthorizer->allowsAnyPermission($gsoUser, [
        'asset_types.create',
        'asset_types.update',
        'asset_types.archive',
        'asset_types.restore',
    ]);
@endphp

@section('styles')
    <link rel="stylesheet" href="{{ asset('build/assets/libs/tabulator-tables/css/tabulator.min.css') }}">
    <style>
        .asset-types-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            width: 100%;
        }

        .asset-types-actions {
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
            Asset Types
        </h3>
    </div>
    <ol class="flex items-center whitespace-nowrap min-w-0">
        <li class="text-[0.813rem] ps-[0.5rem]">
            <a class="flex items-center text-primary hover:text-primary dark:text-primary truncate" href="{{ route('gso.dashboard') }}">
                Dashboard
                <i class="ti ti-chevrons-right flex-shrink-0 text-[#8c9097] dark:text-white/50 px-[0.5rem] overflow-visible rtl:rotate-180"></i>
            </a>
        </li>
        <li class="text-[0.813rem] text-defaulttextcolor font-semibold dark:text-white/50" aria-current="page">
            Asset Types
        </li>
    </ol>
</div>

<div class="box">
    <div class="box-header">
        <div class="asset-types-header">
            <h5 class="box-title">Asset Types</h5>

            <div class="asset-types-actions">
                <input
                    id="asset-types-search"
                    type="text"
                    class="form-control w-[280px] !rounded-md"
                    placeholder="Search type code or name..."
                />

                <select id="asset-types-status" class="form-control w-[180px] !rounded-md">
                    <option value="active">Active</option>
                    <option value="archived">Archived</option>
                </select>

                <button id="asset-types-clear" type="button" class="ti-btn ti-btn-light">
                    Clear
                </button>

                @if($canManageAssetTypes)
                    <button
                        type="button"
                        class="hs-dropdown-toggle ti-btn btn-wave ti-btn-primary-full"
                        data-hs-overlay="#assetTypeModal"
                        data-mode="create"
                    >
                        Add Asset Type
                    </button>
                @endif
            </div>
        </div>
    </div>

    <div class="box-body">
        <div class="overflow-auto table-bordered">
            <div id="asset-types-table" class="ti-custom-table ti-striped-table ti-custom-table-hover"></div>
        </div>

        <div class="mt-2 flex items-center justify-between text-xs text-[#8c9097]">
            <div id="asset-types-info"></div>
        </div>
    </div>
</div>

@if($canManageAssetTypes)
    <div id="assetTypeModal" class="hs-overlay hidden ti-modal [--overlay-backdrop:static]">
        <div class="hs-overlay-open:mt-7 ti-modal-box mt-0 ease-out">
            <div class="ti-modal-content">
                <div class="ti-modal-header">
                    <h6 id="assetTypeModalTitle" class="modal-title text-[1rem] font-semibold">Add Asset Type</h6>
                    <button type="button" class="hs-dropdown-toggle !text-[1rem] !font-semibold !text-defaulttextcolor" data-hs-overlay="#assetTypeModal">
                        <span class="sr-only">Close</span>
                        <i class="ri-close-line"></i>
                    </button>
                </div>

                <div class="ti-modal-body px-4">
                    <div id="assetTypeFormError" class="hidden mb-3 p-2 rounded bg-danger/10 text-danger text-sm"></div>

                    <input type="hidden" id="assetTypeId" value="">

                    <div class="grid grid-cols-1 gap-3">
                        <div>
                            <label class="text-sm text-[#8c9097]">Type Code</label>
                            <input id="assetTypeCode" type="text" class="ti-form-input w-full" placeholder="e.g. PPE">
                            <div id="assetTypeCodeErr" class="text-xs text-danger mt-1 hidden"></div>
                        </div>

                        <div>
                            <label class="text-sm text-[#8c9097]">Type Name</label>
                            <input id="assetTypeName" type="text" class="ti-form-input w-full" placeholder="e.g. Property, Plant and Equipment">
                            <div id="assetTypeNameErr" class="text-xs text-danger mt-1 hidden"></div>
                        </div>
                    </div>
                </div>

                <div class="ti-modal-footer">
                    <button type="button" class="hs-dropdown-toggle ti-btn btn-wave ti-btn-secondary-full align-middle" data-hs-overlay="#assetTypeModal">
                        Close
                    </button>
                    <button type="button" id="assetTypeSaveBtn" class="ti-btn btn-wave bg-primary text-white !font-medium">
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
        window.__gsoAssetTypes = {
            ajaxUrl: @json(route('gso.asset-types.data')),
            storeUrl: @json(route('gso.asset-types.store')),
            updateUrlTemplate: @json(route('gso.asset-types.update', ['assetType' => '__ID__'])),
            deleteUrlTemplate: @json(route('gso.asset-types.destroy', ['assetType' => '__ID__'])),
            restoreUrlTemplate: @json(route('gso.asset-types.restore', ['assetType' => '__ID__'])),
            csrf: @json(csrf_token()),
            canManage: @json($canManageAssetTypes),
        };
    </script>
@endpush
