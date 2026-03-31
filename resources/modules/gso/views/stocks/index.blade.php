@extends('layouts.master')

@php
    $isStockCardView = request('view') === 'stock-cards';
    $gsoUser = auth()->user();
    $gsoAuthorizer = app(\App\Core\Support\AdminContextAuthorizer::class);
    $canManageStocks = $gsoAuthorizer->allowsPermission($gsoUser, 'stocks.adjust');
    $pageTitle = $isStockCardView ? 'Stock Card' : 'Stocks';
    $pageCopy = $isStockCardView
        ? 'Choose a consumable item, then open its Appendix 58 stock card preview by fund source.'
        : 'Consumable stock balances, movement history, and stock card previews are now running inside the GSO platform module.';
    $boxTitle = $isStockCardView ? 'Stock Card Source Items' : 'Consumable Stock Register';
    $boxNote = $isStockCardView
        ? 'Select an item below and use the print action to open its stock card preview.'
        : 'Stock register, ledger, manual adjustments, and stock-related print previews are available here.';
    $searchPlaceholder = $isStockCardView
        ? 'Search stock card source item or stock no...'
        : 'Search consumable item or stock no...';
    $fundSourceOptions = $fundSources->map(fn ($fundSource) => [
        'id' => (string) $fundSource->id,
        'code' => (string) $fundSource->code,
        'name' => (string) $fundSource->name,
        'label' => trim((string) $fundSource->code . ' - ' . (string) $fundSource->name),
    ])->values();
@endphp

@section('styles')
    <link rel="stylesheet" href="{{ asset('build/assets/libs/tabulator-tables/css/tabulator.min.css') }}">
    <style>
        .box-header {
            overflow: visible !important;
        }

        .gso-stock-note {
            border: 1px solid rgba(59, 130, 246, 0.12);
            background: rgba(59, 130, 246, 0.06);
            border-radius: 1rem;
            padding: 14px 16px;
            color: #1e3a8a;
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
            {{ $pageTitle }}
        </h3>
        <p class="text-[#8c9097] dark:text-white/50 text-[0.813rem] mb-0">
            {{ $pageCopy }}
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
            {{ $pageTitle }}
        </li>
    </ol>
</div>

<div class="box">
    <div class="box-body">
        <div class="gso-stock-note text-sm">
            {{ $boxNote }}
        </div>
    </div>
</div>

<div class="box">
    <div class="box-header">
        <div class="datatable-toolbar">
            <h5 class="box-title">{{ $boxTitle }}</h5>

            <div class="datatable-toolbar-actions">
                <input
                    id="gso-stocks-search"
                    type="text"
                    class="form-control !w-[320px] !rounded-md"
                    placeholder="{{ $searchPlaceholder }}"
                />

                <div class="relative shrink-0">
                    <button id="gso-stocks-more-btn" type="button" class="ti-btn ti-btn-light">
                        More Filters
                        <span
                            id="gso-stocks-adv-count"
                            class="hidden ms-2 inline-flex items-center justify-center text-[10px] leading-none px-2 py-1 rounded-full bg-primary/10 text-primary"
                        >
                            0
                        </span>
                        <i class="ri-arrow-down-s-line ms-1"></i>
                    </button>

                    <div
                        id="gso-stocks-more-panel"
                        class="hidden absolute right-0 mt-2 w-[380px] z-[9999] rounded-md border border-defaultborder bg-white dark:bg-bodybg shadow-lg"
                    >
                        <div class="p-3 border-b border-defaultborder flex items-center justify-between">
                            <div class="text-sm font-semibold text-defaulttextcolor dark:text-white">Advanced Filters</div>
                            <button id="gso-stocks-more-close" type="button" class="ti-btn ti-btn-sm ti-btn-light">
                                <i class="ri-close-line"></i>
                            </button>
                        </div>

                        <div class="p-3 space-y-3">
                            <div>
                                <label class="ti-form-label">Record Status</label>
                                <select id="gso-stocks-archived-filter" class="ti-form-input w-full">
                                    <option value="active">Active</option>
                                    <option value="archived">Archived</option>
                                    <option value="all">All</option>
                                </select>
                                <div class="text-xs text-[#8c9097] mt-1">Switch between active, archived, and all stock records.</div>
                            </div>

                            <div>
                                <label class="ti-form-label">Fund Source</label>
                                <select id="gso-stocks-fund-filter" class="ti-form-input w-full">
                                    <option value="">All Fund Sources</option>
                                    @foreach($fundSources as $fundSource)
                                        <option value="{{ $fundSource->id }}">{{ $fundSource->code }} - {{ $fundSource->name }}</option>
                                    @endforeach
                                </select>
                                <div class="text-xs text-[#8c9097] mt-1">Limit the stock register to one fund source.</div>
                            </div>

                            <div>
                                <label class="ti-form-label">Last Movement Date Range</label>
                                <div class="grid grid-cols-2 gap-2">
                                    <input id="gso-stocks-date-from" type="date" class="ti-form-input w-full">
                                    <input id="gso-stocks-date-to" type="date" class="ti-form-input w-full">
                                </div>
                                <div class="text-xs text-[#8c9097] mt-1">Filters by the item’s most recent stock movement date.</div>
                            </div>

                            <div>
                                <label class="ti-form-label">On-hand Quantity</label>
                                <div class="grid grid-cols-2 gap-2">
                                    <input id="gso-stocks-onhand-min" type="number" min="0" class="ti-form-input w-full" placeholder="Min Qty">
                                    <input id="gso-stocks-onhand-max" type="number" min="0" class="ti-form-input w-full" placeholder="Max Qty">
                                </div>
                                <div class="text-xs text-[#8c9097] mt-1">Focus on low, high, or range-based stock balances.</div>
                            </div>
                        </div>

                        <div class="p-3 border-t border-defaultborder flex items-center justify-end gap-2">
                            <button id="gso-stocks-adv-reset" type="button" class="ti-btn ti-btn-light">Reset</button>
                            <button id="gso-stocks-adv-apply" type="button" class="ti-btn ti-btn-primary">Apply</button>
                        </div>
                    </div>
                </div>

                <button id="gso-stocks-clear" type="button" class="ti-btn ti-btn-light">Clear</button>
            </div>
        </div>
    </div>

    <div class="box-body">
        <div class="overflow-auto table-bordered">
            <div id="gso-stocks-table" class="ti-custom-table ti-striped-table ti-custom-table-hover"></div>
        </div>

        <div class="mt-2 flex items-center justify-between text-xs text-[#8c9097]">
            <div id="gso-stocks-info"></div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
    <script src="{{ asset('build/assets/libs/tabulator-tables/js/tabulator.min.js') }}"></script>
    <script>
        window.__gsoStocks = {
            ajaxUrl: @json(route('gso.stocks.data')),
            ledgerUrlTemplate: @json(url('/gso/stocks/__ITEM__/ledger')),
            cardUrlTemplate: @json(url('/gso/stocks/__ITEM__/card/print')),
            adjustUrl: @json(route('gso.stocks.adjust')),
            csrf: @json(csrf_token()),
            canManage: @json($canManageStocks),
            pageMode: @json($isStockCardView ? 'stock-cards' : 'stocks'),
            fundSources: @json($fundSourceOptions),
        };
    </script>
@endpush
