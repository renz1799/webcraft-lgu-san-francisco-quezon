@extends('layouts.master')

@php
    $canManageItems = auth()->user()?->hasAnyRole(['Administrator', 'admin'])
        || auth()->user()?->can('modify Items');
@endphp

@section('styles')
    <link rel="stylesheet" href="{{ asset('build/assets/libs/tabulator-tables/css/tabulator.min.css') }}">
    <style>
        .box-header {
            overflow: visible !important;
        }

        .gso-item-modal-grid {
            display: grid;
            grid-template-columns: repeat(1, minmax(0, 1fr));
            gap: 12px;
        }

        .gso-item-conversions {
            border: 1px solid rgba(140, 144, 151, 0.25);
            border-radius: 0.75rem;
            padding: 12px;
            background: rgba(140, 144, 151, 0.04);
        }

        .gso-item-conversions-grid {
            display: grid;
            grid-template-columns: minmax(0, 1fr) 140px 48px;
            gap: 8px;
            align-items: start;
        }

        .gso-item-conversion-row + .gso-item-conversion-row {
            margin-top: 8px;
        }

        .tabulator .tabulator-loader,
        .tabulator .tabulator-loader-msg {
            display: none !important;
        }

        .tabulator.is-loading {
            opacity: 0.65;
            pointer-events: none;
        }

        @media (min-width: 1024px) {
            .gso-item-modal-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (max-width: 767px) {
            .gso-item-conversions-grid {
                grid-template-columns: repeat(1, minmax(0, 1fr));
            }
        }
    </style>
@endsection

@section('content')
<div class="block justify-between page-header md:flex">
    <div>
        <h3 class="!text-defaulttextcolor dark:!text-defaulttextcolor/70 dark:text-white text-[1.125rem] font-semibold">
            Items
        </h3>
        <p class="text-[#8c9097] dark:text-white/50 text-[0.813rem] mb-0">
            Core GSO item master data used by inventory, AIR, and downstream property document workflows.
        </p>
    </div>
    <ol class="flex items-center whitespace-nowrap min-w-0">
        <li class="text-[0.813rem] ps-[0.5rem]">
            <a class="flex items-center text-primary hover:text-primary dark:text-primary truncate" href="{{ route('gso.dashboard') }}">
                GSO
                <i class="ti ti-chevrons-right flex-shrink-0 text-[#8c9097] dark:text-white/50 px-[0.5rem] overflow-visible rtl:rotate-180"></i>
            </a>
        </li>
        <li class="text-[0.813rem] ps-[0.5rem]">
            <a class="flex items-center text-primary hover:text-primary dark:text-primary truncate" href="{{ route('gso.inventory.index') }}">
                Inventory
                <i class="ti ti-chevrons-right flex-shrink-0 text-[#8c9097] dark:text-white/50 px-[0.5rem] overflow-visible rtl:rotate-180"></i>
            </a>
        </li>
        <li class="text-[0.813rem] text-defaulttextcolor font-semibold dark:text-white/50" aria-current="page">
            Items
        </li>
    </ol>
</div>

<div class="box">
    <div class="box-header">
        <div class="datatable-toolbar">
            <h5 class="box-title">Item Catalog</h5>

            <div class="datatable-toolbar-actions">
                <input id="gso-items-search" type="text" class="form-control !w-[320px] !rounded-md" placeholder="Search item, asset, or identification...">

                <div class="relative shrink-0">
                    <button id="gso-items-more-btn" type="button" class="ti-btn ti-btn-light">
                        More Filters
                        <span id="gso-items-adv-count"
                            class="hidden ms-2 inline-flex items-center justify-center text-[10px] leading-none px-2 py-1 rounded-full bg-primary/10 text-primary">
                            0
                        </span>
                        <i class="ri-arrow-down-s-line ms-1"></i>
                    </button>

                    <div id="gso-items-more-panel"
                        class="hidden absolute right-0 mt-2 w-[380px] z-[9999] rounded-md border border-defaultborder bg-white dark:bg-bodybg shadow-lg">

                        <div class="p-3 border-b border-defaultborder flex items-center justify-between">
                            <div class="text-sm font-semibold text-defaulttextcolor dark:text-white">Advanced Filters</div>
                            <button id="gso-items-more-close" type="button" class="ti-btn ti-btn-sm ti-btn-light">
                                <i class="ri-close-line"></i>
                            </button>
                        </div>

                        <div class="p-3 space-y-3">
                            <div>
                                <label class="ti-form-label">Asset Category</label>
                                <select id="gso-items-asset-filter" class="ti-form-input w-full">
                                    <option value="">All Asset Categories</option>
                                    @foreach($assetCategories as $assetCategory)
                                        <option value="{{ $assetCategory->id }}">{{ $assetCategory->asset_code }} - {{ $assetCategory->asset_name }}</option>
                                    @endforeach
                                </select>
                                <div class="text-xs text-[#8c9097] mt-1">Limit the table to one asset classification.</div>
                            </div>

                            <div>
                                <label class="ti-form-label">Tracking Type</label>
                                <select id="gso-items-tracking-filter" class="ti-form-input w-full">
                                    <option value="">All Tracking Types</option>
                                    <option value="property">Property</option>
                                    <option value="consumable">Consumable</option>
                                </select>
                                <div class="text-xs text-[#8c9097] mt-1">Filter between property and consumable items.</div>
                            </div>

                            <div>
                                <label class="ti-form-label">Serial Mode</label>
                                <select id="gso-items-serial-filter" class="ti-form-input w-full">
                                    <option value="">All Serial Modes</option>
                                    <option value="1">Requires Serial</option>
                                    <option value="0">No Serial</option>
                                </select>
                                <div class="text-xs text-[#8c9097] mt-1">Show only items that require serial numbers or items that do not.</div>
                            </div>

                            <div>
                                <label class="ti-form-label">Item Class</label>
                                <select id="gso-items-semi-filter" class="ti-form-input w-full">
                                    <option value="">All Item Classes</option>
                                    <option value="1">Semi-Expendable</option>
                                    <option value="0">Not Semi-Expendable</option>
                                </select>
                                <div class="text-xs text-[#8c9097] mt-1">Filter semi-expendable items separately from the rest of the catalog.</div>
                            </div>

                            <div>
                                <label class="ti-form-label">Status</label>
                                <select id="gso-items-status" class="ti-form-input w-full">
                                    <option value="active">Active</option>
                                    <option value="archived">Archived</option>
                                    <option value="all">All</option>
                                </select>
                                <div class="text-xs text-[#8c9097] mt-1">Switch between active, archived, and all records.</div>
                            </div>
                        </div>

                        <div class="p-3 border-t border-defaultborder flex items-center justify-end gap-2">
                            <button id="gso-items-adv-reset" type="button" class="ti-btn ti-btn-light">Reset</button>
                            <button id="gso-items-adv-apply" type="button" class="ti-btn ti-btn-primary">Apply</button>
                        </div>
                    </div>
                </div>

                <button id="gso-items-clear" type="button" class="ti-btn ti-btn-light">
                    Clear
                </button>

                @if($canManageItems)
                    <button
                        type="button"
                        class="hs-dropdown-toggle ti-btn btn-wave ti-btn-primary-full"
                        data-hs-overlay="#gsoItemModal"
                        data-mode="create"
                    >
                        Add Item
                    </button>
                @endif
            </div>
        </div>
    </div>

    <div class="box-body">
        <div class="overflow-auto table-bordered">
            <div id="gso-items-table" class="ti-custom-table ti-striped-table ti-custom-table-hover"></div>
        </div>

        <div class="mt-2 flex items-center justify-between text-xs text-[#8c9097]">
            <div id="gso-items-info"></div>
        </div>
    </div>
</div>

@if($canManageItems)
    <div id="gsoItemModal" class="hs-overlay hidden ti-modal [--overlay-backdrop:static]">
        <div class="hs-overlay-open:mt-7 ti-modal-box mt-0 ease-out !max-w-5xl">
            <div class="ti-modal-content">
                <div class="ti-modal-header">
                    <h6 id="gsoItemModalTitle" class="modal-title text-[1rem] font-semibold">Add Item</h6>
                    <button type="button" class="hs-dropdown-toggle !text-[1rem] !font-semibold !text-defaulttextcolor" data-hs-overlay="#gsoItemModal">
                        <span class="sr-only">Close</span>
                        <i class="ri-close-line"></i>
                    </button>
                </div>

                <div class="ti-modal-body px-4">
                    <div id="gsoItemFormError" class="hidden mb-3 p-2 rounded bg-danger/10 text-danger text-sm"></div>

                    <input type="hidden" id="gsoItemId" value="">

                    <div class="gso-item-modal-grid">
                        <div>
                            <label class="text-sm text-[#8c9097]">Asset Category</label>
                            <select id="gsoItemAssetId" class="ti-form-select w-full">
                                @foreach($assetCategories as $assetCategory)
                                    <option value="{{ $assetCategory->id }}">{{ $assetCategory->asset_code }} - {{ $assetCategory->asset_name }}</option>
                                @endforeach
                            </select>
                            <div id="gsoItemAssetErr" class="text-xs text-danger mt-1 hidden"></div>
                        </div>

                        <div>
                            <label class="text-sm text-[#8c9097]">Item Name</label>
                            <input id="gsoItemName" type="text" class="ti-form-input w-full" placeholder="e.g. Laptop Computer">
                            <div id="gsoItemNameErr" class="text-xs text-danger mt-1 hidden"></div>
                        </div>

                        <div>
                            <label class="text-sm text-[#8c9097]">Item Identification</label>
                            <input id="gsoItemIdentification" type="text" class="ti-form-input w-full" placeholder="Legacy item identification or reference">
                            <div id="gsoItemIdentificationErr" class="text-xs text-danger mt-1 hidden"></div>
                        </div>

                        <div>
                            <label class="text-sm text-[#8c9097]">Base Unit</label>
                            <input id="gsoItemBaseUnit" type="text" class="ti-form-input w-full" placeholder="e.g. piece">
                            <div id="gsoItemBaseUnitErr" class="text-xs text-danger mt-1 hidden"></div>
                        </div>

                        <div>
                            <label class="text-sm text-[#8c9097]">Tracking Type</label>
                            <select id="gsoItemTrackingType" class="ti-form-select w-full">
                                <option value="property">Property</option>
                                <option value="consumable">Consumable</option>
                            </select>
                            <div id="gsoItemTrackingTypeErr" class="text-xs text-danger mt-1 hidden"></div>
                            <p class="mt-1 text-xs text-[#8c9097]">Consumable items cannot require serial numbers.</p>
                        </div>

                        <div>
                            <label class="text-sm text-[#8c9097]">Major Sub-Account Group</label>
                            <input id="gsoItemMajorSubAccountGroup" type="text" class="ti-form-input w-full" placeholder="Optional account grouping">
                            <div id="gsoItemMajorSubAccountGroupErr" class="text-xs text-danger mt-1 hidden"></div>
                        </div>

                        <div class="col-span-1 lg:col-span-2">
                            <label class="text-sm text-[#8c9097]">Description</label>
                            <textarea id="gsoItemDescription" class="ti-form-input w-full" rows="3" placeholder="Optional item description"></textarea>
                            <div id="gsoItemDescriptionErr" class="text-xs text-danger mt-1 hidden"></div>
                        </div>

                        <div class="col-span-1 lg:col-span-2">
                            <div class="flex flex-wrap gap-4">
                                <label class="inline-flex items-center gap-2 text-sm text-defaulttextcolor">
                                    <input id="gsoItemRequiresSerial" type="checkbox" class="form-check-input">
                                    <span>Requires Serial Number</span>
                                </label>

                                <label class="inline-flex items-center gap-2 text-sm text-defaulttextcolor">
                                    <input id="gsoItemSemiExpendable" type="checkbox" class="form-check-input">
                                    <span>Semi-Expendable</span>
                                </label>
                            </div>
                            <div id="gsoItemRequiresSerialErr" class="text-xs text-danger mt-1 hidden"></div>
                            <div id="gsoItemSemiExpendableErr" class="text-xs text-danger mt-1 hidden"></div>
                        </div>
                    </div>

                    <div class="mt-4 gso-item-conversions">
                        <div class="flex items-center justify-between gap-3 flex-wrap">
                            <div>
                                <h6 class="text-sm font-semibold text-defaulttextcolor mb-1">Unit Conversions</h6>
                                <p class="text-xs text-[#8c9097] mb-0">
                                    Define alternative units and how many base units each one represents.
                                </p>
                            </div>

                            <button type="button" id="gsoItemAddConversionBtn" class="ti-btn ti-btn-light">
                                Add Conversion
                            </button>
                        </div>

                        <div id="gsoItemConversionRows" class="mt-3"></div>
                        <div id="gsoItemConversionsErr" class="text-xs text-danger mt-2 hidden"></div>
                    </div>
                </div>

                <div class="ti-modal-footer">
                    <button type="button" class="hs-dropdown-toggle ti-btn btn-wave ti-btn-secondary-full align-middle" data-hs-overlay="#gsoItemModal">
                        Close
                    </button>
                    <button type="button" id="gsoItemSaveBtn" class="ti-btn btn-wave bg-primary text-white !font-medium">
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
        window.__gsoItems = {
            ajaxUrl: @json(route('gso.items.data')),
            showUrlTemplate: @json(route('gso.items.show', ['item' => '__ID__'])),
            storeUrl: @json(route('gso.items.store')),
            updateUrlTemplate: @json(route('gso.items.update', ['item' => '__ID__'])),
            deleteUrlTemplate: @json(route('gso.items.destroy', ['item' => '__ID__'])),
            restoreUrlTemplate: @json(route('gso.items.restore', ['item' => '__ID__'])),
            csrf: @json(csrf_token()),
            canManage: @json($canManageItems),
        };
    </script>
@endpush
