@extends('layouts.master')

@php
    $canManageStocks = auth()->user()?->hasAnyRole(['Administrator', 'admin'])
        || auth()->user()?->can('modify Stocks');
@endphp

@section('styles')
    <link rel="stylesheet" href="{{ asset('build/assets/libs/tabulator-tables/css/tabulator.min.css') }}">
    <style>
        .gso-stocks-toolbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            width: 100%;
            flex-wrap: wrap;
        }

        .gso-stocks-actions {
            display: flex;
            align-items: center;
            gap: 8px;
            flex-wrap: wrap;
            justify-content: flex-end;
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
            Stocks
        </h3>
        <p class="text-[#8c9097] dark:text-white/50 text-[0.813rem] mb-0">
            Consumable stock balances, movement history, and stock card previews are now running inside the GSO platform module.
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
            Stocks
        </li>
    </ol>
</div>

<div class="box">
    <div class="box-body">
        <div class="gso-stock-note text-sm">
            Stock register, ledger, manual adjustments, and Appendix 58 stock card previews are available here.
            The legacy <strong>SSMI</strong> and <strong>RPCI</strong> financial print packs still depend on AIR and RIS migration, so they will be reconnected in the next document-flow waves.
        </div>
    </div>
</div>

<div class="box">
    <div class="box-header">
        <div class="gso-stocks-toolbar">
            <h5 class="box-title">Consumable Stock Register</h5>

            <div class="gso-stocks-actions">
                <input
                    id="gso-stocks-search"
                    type="text"
                    class="form-control w-[260px] !rounded-md"
                    placeholder="Search consumable item or stock no..."
                />

                <select id="gso-stocks-fund-filter" class="form-control w-[220px] !rounded-md">
                    <option value="">All Fund Sources</option>
                    @foreach($fundSources as $fundSource)
                        <option value="{{ $fundSource->id }}">{{ $fundSource->code }} - {{ $fundSource->name }}</option>
                    @endforeach
                </select>

                <input id="gso-stocks-date-from" type="date" class="form-control w-[170px] !rounded-md">
                <input id="gso-stocks-date-to" type="date" class="form-control w-[170px] !rounded-md">
                <input id="gso-stocks-onhand-min" type="number" min="0" class="form-control w-[120px] !rounded-md" placeholder="Min Qty">
                <input id="gso-stocks-onhand-max" type="number" min="0" class="form-control w-[120px] !rounded-md" placeholder="Max Qty">

                <button id="gso-stocks-clear" type="button" class="ti-btn ti-btn-light">
                    Clear
                </button>
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
            fundSources: @json(
                $fundSources->map(fn ($fundSource) => [
                    'id' => (string) $fundSource->id,
                    'code' => (string) $fundSource->code,
                    'name' => (string) $fundSource->name,
                    'label' => trim((string) $fundSource->code . ' - ' . (string) $fundSource->name),
                ])->values()
            ),
        };
    </script>
@endpush
