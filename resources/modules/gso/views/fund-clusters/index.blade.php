@extends('layouts.master')

@php
    $gsoUser = auth()->user();
    $gsoAuthorizer = app(\App\Core\Support\AdminContextAuthorizer::class);
    $canManageFundClusters = $gsoAuthorizer->allowsAnyPermission($gsoUser, [
        'fund_clusters.create',
        'fund_clusters.update',
        'fund_clusters.archive',
        'fund_clusters.restore',
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
            Fund Clusters
        </h3>
        <p class="text-[#8c9097] dark:text-white/50 text-[0.813rem] mb-0">
            COA-style cluster records that group GSO fund sources and document flows.
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
            Fund Clusters
        </li>
    </ol>
</div>

<div class="box">
    <div class="box-header">
        <div class="gso-reference-toolbar">
            <h5 class="box-title">Fund Clusters</h5>

            <div class="gso-reference-actions">
                <input id="fund-clusters-search" type="text" class="form-control w-[280px] !rounded-md" placeholder="Search code or name...">

                <select id="fund-clusters-status" class="form-control w-[180px] !rounded-md">
                    <option value="active">Active</option>
                    <option value="archived">Archived</option>
                    <option value="all">All</option>
                </select>

                <button id="fund-clusters-clear" type="button" class="ti-btn ti-btn-light">
                    Clear
                </button>

                @if($canManageFundClusters)
                    <button
                        type="button"
                        class="hs-dropdown-toggle ti-btn btn-wave ti-btn-primary-full"
                        data-hs-overlay="#fundClusterModal"
                        data-mode="create"
                    >
                        Add Fund Cluster
                    </button>
                @endif
            </div>
        </div>
    </div>

    <div class="box-body">
        <div class="overflow-auto table-bordered">
            <div id="fund-clusters-table" class="ti-custom-table ti-striped-table ti-custom-table-hover"></div>
        </div>

        <div class="mt-2 flex items-center justify-between text-xs text-[#8c9097]">
            <div id="fund-clusters-info"></div>
        </div>
    </div>
</div>

@if($canManageFundClusters)
    <div id="fundClusterModal" class="hs-overlay hidden ti-modal [--overlay-backdrop:static]">
        <div class="hs-overlay-open:mt-7 ti-modal-box mt-0 ease-out">
            <div class="ti-modal-content">
                <div class="ti-modal-header">
                    <h6 id="fundClusterModalTitle" class="modal-title text-[1rem] font-semibold">Add Fund Cluster</h6>
                    <button type="button" class="hs-dropdown-toggle !text-[1rem] !font-semibold !text-defaulttextcolor" data-hs-overlay="#fundClusterModal">
                        <span class="sr-only">Close</span>
                        <i class="ri-close-line"></i>
                    </button>
                </div>

                <div class="ti-modal-body px-4">
                    <div id="fundClusterFormError" class="hidden mb-3 p-2 rounded bg-danger/10 text-danger text-sm"></div>

                    <input type="hidden" id="fundClusterId" value="">

                    <div class="grid grid-cols-1 gap-3">
                        <div>
                            <label class="text-sm text-[#8c9097]">Cluster Code</label>
                            <input id="fundClusterCode" type="text" class="ti-form-input w-full" placeholder="e.g. 01">
                            <div id="fundClusterCodeErr" class="text-xs text-danger mt-1 hidden"></div>
                        </div>

                        <div>
                            <label class="text-sm text-[#8c9097]">Cluster Name</label>
                            <input id="fundClusterName" type="text" class="ti-form-input w-full" placeholder="e.g. General Fund">
                            <div id="fundClusterNameErr" class="text-xs text-danger mt-1 hidden"></div>
                        </div>

                        <div>
                            <label class="text-sm text-[#8c9097]">Record Status</label>
                            <select id="fundClusterIsActive" class="ti-form-select w-full">
                                <option value="1">Active</option>
                                <option value="0">Inactive</option>
                            </select>
                            <div id="fundClusterIsActiveErr" class="text-xs text-danger mt-1 hidden"></div>
                        </div>
                    </div>
                </div>

                <div class="ti-modal-footer">
                    <button type="button" class="hs-dropdown-toggle ti-btn btn-wave ti-btn-secondary-full align-middle" data-hs-overlay="#fundClusterModal">
                        Close
                    </button>
                    <button type="button" id="fundClusterSaveBtn" class="ti-btn btn-wave bg-primary text-white !font-medium">
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
        window.__gsoFundClusters = {
            ajaxUrl: @json(route('gso.fund-clusters.data')),
            storeUrl: @json(route('gso.fund-clusters.store')),
            updateUrlTemplate: @json(route('gso.fund-clusters.update', ['fundCluster' => '__ID__'])),
            deleteUrlTemplate: @json(route('gso.fund-clusters.destroy', ['fundCluster' => '__ID__'])),
            restoreUrlTemplate: @json(route('gso.fund-clusters.restore', ['fundCluster' => '__ID__'])),
            csrf: @json(csrf_token()),
            canManage: @json($canManageFundClusters),
        };
    </script>
@endpush
