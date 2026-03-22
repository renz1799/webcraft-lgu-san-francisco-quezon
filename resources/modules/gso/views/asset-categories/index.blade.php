@extends('layouts.master')

@php
    $canManageAssetCategories = auth()->user()?->hasAnyRole(['Administrator', 'admin'])
        || auth()->user()?->can('modify Asset Categories');
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
            Asset Categories
        </h3>
        <p class="text-[#8c9097] dark:text-white/50 text-[0.813rem] mb-0">
            Detailed category codes that sit under each GSO asset type.
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
            Asset Categories
        </li>
    </ol>
</div>

<div class="box">
    <div class="box-header">
        <div class="gso-reference-toolbar">
            <h5 class="box-title">Asset Categories</h5>

            <div class="gso-reference-actions">
                <input
                    id="asset-categories-search"
                    type="text"
                    class="form-control w-[300px] !rounded-md"
                    placeholder="Search type, code, name, or account group..."
                />

                <select id="asset-categories-type-filter" class="form-control w-[220px] !rounded-md">
                    <option value="">All Asset Types</option>
                    @foreach($assetTypes as $assetType)
                        <option value="{{ $assetType->id }}">{{ $assetType->type_code }} - {{ $assetType->type_name }}</option>
                    @endforeach
                </select>

                <select id="asset-categories-status" class="form-control w-[180px] !rounded-md">
                    <option value="active">Active</option>
                    <option value="archived">Archived</option>
                    <option value="all">All</option>
                </select>

                <button id="asset-categories-clear" type="button" class="ti-btn ti-btn-light">
                    Clear
                </button>

                @if($canManageAssetCategories)
                    <button
                        type="button"
                        class="hs-dropdown-toggle ti-btn btn-wave ti-btn-primary-full"
                        data-hs-overlay="#assetCategoryModal"
                        data-mode="create"
                    >
                        Add Asset Category
                    </button>
                @endif
            </div>
        </div>
    </div>

    <div class="box-body">
        <div class="overflow-auto table-bordered">
            <div id="asset-categories-table" class="ti-custom-table ti-striped-table ti-custom-table-hover"></div>
        </div>

        <div class="mt-2 flex items-center justify-between text-xs text-[#8c9097]">
            <div id="asset-categories-info"></div>
        </div>
    </div>
</div>

@if($canManageAssetCategories)
    <div id="assetCategoryModal" class="hs-overlay hidden ti-modal [--overlay-backdrop:static]">
        <div class="hs-overlay-open:mt-7 ti-modal-box mt-0 ease-out">
            <div class="ti-modal-content">
                <div class="ti-modal-header">
                    <h6 id="assetCategoryModalTitle" class="modal-title text-[1rem] font-semibold">Add Asset Category</h6>
                    <button type="button" class="hs-dropdown-toggle !text-[1rem] !font-semibold !text-defaulttextcolor" data-hs-overlay="#assetCategoryModal">
                        <span class="sr-only">Close</span>
                        <i class="ri-close-line"></i>
                    </button>
                </div>

                <div class="ti-modal-body px-4">
                    <div id="assetCategoryFormError" class="hidden mb-3 p-2 rounded bg-danger/10 text-danger text-sm"></div>

                    <input type="hidden" id="assetCategoryId" value="">

                    <div class="grid grid-cols-1 gap-3">
                        <div>
                            <label class="text-sm text-[#8c9097]">Asset Type</label>
                            <select id="assetCategoryAssetTypeId" class="ti-form-select w-full">
                                @foreach($assetTypes as $assetType)
                                    <option value="{{ $assetType->id }}">{{ $assetType->type_code }} - {{ $assetType->type_name }}</option>
                                @endforeach
                            </select>
                            <div id="assetCategoryAssetTypeErr" class="text-xs text-danger mt-1 hidden"></div>
                        </div>

                        <div>
                            <label class="text-sm text-[#8c9097]">Asset Code</label>
                            <input id="assetCategoryCode" type="text" class="ti-form-input w-full" placeholder="e.g. 10604010">
                            <div id="assetCategoryCodeErr" class="text-xs text-danger mt-1 hidden"></div>
                        </div>

                        <div>
                            <label class="text-sm text-[#8c9097]">Asset Name</label>
                            <input id="assetCategoryName" type="text" class="ti-form-input w-full" placeholder="e.g. Office Equipment">
                            <div id="assetCategoryNameErr" class="text-xs text-danger mt-1 hidden"></div>
                        </div>

                        <div>
                            <label class="text-sm text-[#8c9097]">Account Group</label>
                            <input id="assetCategoryAccountGroup" type="text" class="ti-form-input w-full" placeholder="Optional account grouping">
                            <div id="assetCategoryAccountGroupErr" class="text-xs text-danger mt-1 hidden"></div>
                        </div>
                    </div>
                </div>

                <div class="ti-modal-footer">
                    <button type="button" class="hs-dropdown-toggle ti-btn btn-wave ti-btn-secondary-full align-middle" data-hs-overlay="#assetCategoryModal">
                        Close
                    </button>
                    <button type="button" id="assetCategorySaveBtn" class="ti-btn btn-wave bg-primary text-white !font-medium">
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
        window.__gsoAssetCategories = {
            ajaxUrl: @json(route('gso.asset-categories.data')),
            storeUrl: @json(route('gso.asset-categories.store')),
            updateUrlTemplate: @json(route('gso.asset-categories.update', ['assetCategory' => '__ID__'])),
            deleteUrlTemplate: @json(route('gso.asset-categories.destroy', ['assetCategory' => '__ID__'])),
            restoreUrlTemplate: @json(route('gso.asset-categories.restore', ['assetCategory' => '__ID__'])),
            csrf: @json(csrf_token()),
            canManage: @json($canManageAssetCategories),
        };
    </script>
@endpush
