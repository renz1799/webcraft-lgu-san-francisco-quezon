@extends('layouts.master')

@php
    $canManageFundSources = auth()->user()?->hasAnyRole(['Administrator', 'admin'])
        || auth()->user()?->can('modify Fund Sources');
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
            Fund Sources
        </h3>
        <p class="text-[#8c9097] dark:text-white/50 text-[0.813rem] mb-0">
            Named funding sources used throughout GSO inventory, AIR, and downstream document workflows.
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
            Fund Sources
        </li>
    </ol>
</div>

<div class="box">
    <div class="box-header">
        <div class="gso-reference-toolbar">
            <h5 class="box-title">Fund Sources</h5>

            <div class="gso-reference-actions">
                <input id="fund-sources-search" type="text" class="form-control w-[280px] !rounded-md" placeholder="Search cluster, code, or name...">

                <select id="fund-sources-cluster-filter" class="form-control w-[240px] !rounded-md">
                    <option value="">All Fund Clusters</option>
                    @foreach($fundClusters as $fundCluster)
                        <option value="{{ $fundCluster->id }}">{{ $fundCluster->code }} - {{ $fundCluster->name }}</option>
                    @endforeach
                </select>

                <select id="fund-sources-status" class="form-control w-[180px] !rounded-md">
                    <option value="active">Active</option>
                    <option value="archived">Archived</option>
                    <option value="all">All</option>
                </select>

                <button id="fund-sources-clear" type="button" class="ti-btn ti-btn-light">
                    Clear
                </button>

                @if($canManageFundSources)
                    <button
                        type="button"
                        class="hs-dropdown-toggle ti-btn btn-wave ti-btn-primary-full"
                        data-hs-overlay="#fundSourceModal"
                        data-mode="create"
                    >
                        Add Fund Source
                    </button>
                @endif
            </div>
        </div>
    </div>

    <div class="box-body">
        <div class="overflow-auto table-bordered">
            <div id="fund-sources-table" class="ti-custom-table ti-striped-table ti-custom-table-hover"></div>
        </div>

        <div class="mt-2 flex items-center justify-between text-xs text-[#8c9097]">
            <div id="fund-sources-info"></div>
        </div>
    </div>
</div>

@if($canManageFundSources)
    <div id="fundSourceModal" class="hs-overlay hidden ti-modal [--overlay-backdrop:static]">
        <div class="hs-overlay-open:mt-7 ti-modal-box mt-0 ease-out">
            <div class="ti-modal-content">
                <div class="ti-modal-header">
                    <h6 id="fundSourceModalTitle" class="modal-title text-[1rem] font-semibold">Add Fund Source</h6>
                    <button type="button" class="hs-dropdown-toggle !text-[1rem] !font-semibold !text-defaulttextcolor" data-hs-overlay="#fundSourceModal">
                        <span class="sr-only">Close</span>
                        <i class="ri-close-line"></i>
                    </button>
                </div>

                <div class="ti-modal-body px-4">
                    <div id="fundSourceFormError" class="hidden mb-3 p-2 rounded bg-danger/10 text-danger text-sm"></div>

                    <input type="hidden" id="fundSourceId" value="">

                    <div class="grid grid-cols-1 gap-3">
                        <div>
                            <label class="text-sm text-[#8c9097]">Fund Cluster</label>
                            <select id="fundSourceFundClusterId" class="ti-form-select w-full">
                                <option value="">No Cluster</option>
                                @foreach($fundClusters as $fundCluster)
                                    <option value="{{ $fundCluster->id }}">{{ $fundCluster->code }} - {{ $fundCluster->name }}</option>
                                @endforeach
                            </select>
                            <div id="fundSourceFundClusterErr" class="text-xs text-danger mt-1 hidden"></div>
                        </div>

                        <div>
                            <label class="text-sm text-[#8c9097]">Source Code</label>
                            <input id="fundSourceCode" type="text" class="ti-form-input w-full" placeholder="Optional code, e.g. GF">
                            <div id="fundSourceCodeErr" class="text-xs text-danger mt-1 hidden"></div>
                        </div>

                        <div>
                            <label class="text-sm text-[#8c9097]">Source Name</label>
                            <input id="fundSourceName" type="text" class="ti-form-input w-full" placeholder="e.g. General Fund">
                            <div id="fundSourceNameErr" class="text-xs text-danger mt-1 hidden"></div>
                        </div>

                        <div>
                            <label class="text-sm text-[#8c9097]">Record Status</label>
                            <select id="fundSourceIsActive" class="ti-form-select w-full">
                                <option value="1">Active</option>
                                <option value="0">Inactive</option>
                            </select>
                            <div id="fundSourceIsActiveErr" class="text-xs text-danger mt-1 hidden"></div>
                        </div>
                    </div>
                </div>

                <div class="ti-modal-footer">
                    <button type="button" class="hs-dropdown-toggle ti-btn btn-wave ti-btn-secondary-full align-middle" data-hs-overlay="#fundSourceModal">
                        Close
                    </button>
                    <button type="button" id="fundSourceSaveBtn" class="ti-btn btn-wave bg-primary text-white !font-medium">
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
        window.__gsoFundSources = {
            ajaxUrl: @json(route('gso.fund-sources.data')),
            storeUrl: @json(route('gso.fund-sources.store')),
            updateUrlTemplate: @json(route('gso.fund-sources.update', ['fundSource' => '__ID__'])),
            deleteUrlTemplate: @json(route('gso.fund-sources.destroy', ['fundSource' => '__ID__'])),
            restoreUrlTemplate: @json(route('gso.fund-sources.restore', ['fundSource' => '__ID__'])),
            csrf: @json(csrf_token()),
            canManage: @json($canManageFundSources),
        };
    </script>
@endpush
