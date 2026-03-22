@extends('layouts.master')

@php
    $canManageAir = auth()->user()?->hasAnyRole(['Administrator', 'admin'])
        || auth()->user()?->can('modify AIR');
@endphp

@section('styles')
    <link rel="stylesheet" href="{{ asset('build/assets/libs/tabulator-tables/css/tabulator.min.css') }}">
    <style>
        .gso-air-toolbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            width: 100%;
            flex-wrap: wrap;
        }

        .gso-air-actions {
            display: flex;
            align-items: center;
            gap: 8px;
            flex-wrap: wrap;
            justify-content: flex-end;
        }

        .gso-air-note {
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
            AIR
        </h3>
        <p class="text-[#8c9097] dark:text-white/50 text-[0.813rem] mb-0">
            Acceptance and Inspection Report register and draft document headers now run inside the GSO platform module.
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
            AIR
        </li>
    </ol>
</div>

<div class="box">
    <div class="box-body">
        <div class="gso-air-note text-sm">
            This Wave 2 slice now covers the AIR register, draft document metadata, inspection workspace, inventory promotion,
            and AIR print preview inside the platform.
        </div>
    </div>
</div>

<div class="box">
    <div class="box-header">
        <div class="gso-air-toolbar">
            <h5 class="box-title">AIR Register</h5>

            <div class="gso-air-actions">
                <input
                    id="gso-air-search"
                    type="text"
                    class="form-control w-[250px] !rounded-md"
                    placeholder="Search PO, AIR no, supplier..."
                />

                <select id="gso-air-status-filter" class="form-control w-[160px] !rounded-md">
                    <option value="">All Workflow Status</option>
                    <option value="draft">Draft</option>
                    <option value="submitted">Submitted</option>
                    <option value="in_progress">In Progress</option>
                    <option value="inspected">Inspected</option>
                </select>

                <select id="gso-air-department-filter" class="form-control w-[220px] !rounded-md">
                    <option value="">All Departments</option>
                    @foreach($departments as $department)
                        <option value="{{ $department->id }}">
                            {{ trim((string) $department->code) !== '' ? $department->code . ' - ' : '' }}{{ $department->name }}
                            @if($department->deleted_at)
                                (Archived)
                            @endif
                        </option>
                    @endforeach
                </select>

                <select id="gso-air-fund-filter" class="form-control w-[220px] !rounded-md">
                    <option value="">All Fund Sources</option>
                    @foreach($fundSources as $fundSource)
                        <option value="{{ $fundSource->id }}">
                            {{ trim((string) $fundSource->code) !== '' ? $fundSource->code . ' - ' : '' }}{{ $fundSource->name }}
                            @if($fundSource->deleted_at)
                                (Archived)
                            @endif
                        </option>
                    @endforeach
                </select>

                <input id="gso-air-supplier-filter" type="text" class="form-control w-[220px] !rounded-md" placeholder="Supplier filter">
                <input id="gso-air-date-from" type="date" class="form-control w-[170px] !rounded-md">
                <input id="gso-air-date-to" type="date" class="form-control w-[170px] !rounded-md">

                <select id="gso-air-record-status" class="form-control w-[150px] !rounded-md">
                    <option value="active">Active</option>
                    <option value="archived">Archived</option>
                    <option value="all">All</option>
                </select>

                <button id="gso-air-clear" type="button" class="ti-btn ti-btn-light">
                    Clear
                </button>

                @if($canManageAir)
                    <a href="{{ route('gso.air.create') }}" class="ti-btn btn-wave ti-btn-primary-full">
                        Create Draft AIR
                    </a>
                @endif
            </div>
        </div>
    </div>

    <div class="box-body">
        <div class="overflow-auto table-bordered">
            <div id="gso-air-table" class="ti-custom-table ti-striped-table ti-custom-table-hover"></div>
        </div>

        <div class="mt-2 flex items-center justify-between text-xs text-[#8c9097]">
            <div id="gso-air-info"></div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
    <script src="{{ asset('build/assets/libs/tabulator-tables/js/tabulator.min.js') }}"></script>
    <script>
        window.__gsoAir = {
            ajaxUrl: @json(route('gso.air.data')),
            editUrlTemplate: @json(route('gso.air.edit', ['air' => '__ID__'])),
            inspectUrlTemplate: @json(route('gso.air.inspect', ['air' => '__ID__'])),
            printUrlTemplate: @json(route('gso.air.print', ['air' => '__ID__', 'preview' => 1])),
            deleteUrlTemplate: @json(route('gso.air.destroy', ['air' => '__ID__'])),
            restoreUrlTemplate: @json(route('gso.air.restore', ['air' => '__ID__'])),
            csrf: @json(csrf_token()),
            canManage: @json($canManageAir),
        };
    </script>
@endpush
