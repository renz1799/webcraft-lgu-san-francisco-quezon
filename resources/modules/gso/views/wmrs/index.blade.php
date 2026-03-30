@extends('layouts.master')

@section('styles')
  <link rel="stylesheet" href="{{ asset('build/assets/libs/tabulator-tables/css/tabulator.min.css') }}">
  <style>
    .items-header{display:flex;align-items:center;justify-content:space-between;gap:12px;width:100%}
    .items-actions{display:flex;align-items:center;gap:8px;flex-wrap:wrap;justify-content:flex-end}
    .box-header{overflow:visible!important}
  </style>
@endsection

@section('content')
@php
  $gsoUser = auth()->user();
  $gsoAuthorizer = app(\App\Core\Support\AdminContextAuthorizer::class);
  $canCreateWmr = $gsoAuthorizer->allowsPermission($gsoUser, 'wmr.create');
  $canManageWmr = $gsoAuthorizer->allowsAnyPermission($gsoUser, [
    'wmr.create',
    'wmr.update',
    'wmr.submit',
    'wmr.approve',
    'wmr.finalize',
    'wmr.reopen',
    'wmr.manage_items',
  ]);
  $canDeleteWmr = $gsoAuthorizer->allowsPermission($gsoUser, 'wmr.archive');
  $canRestoreWmr = $gsoAuthorizer->allowsAnyPermission($gsoUser, [
    'wmr.restore',
    'audit_logs.restore_data',
  ]);
@endphp

<div class="block justify-between page-header md:flex">
  <div>
    <h3 class="!text-defaulttextcolor dark:!text-defaulttextcolor/70 dark:text-white text-[1.125rem] font-semibold">
      WMR
    </h3>
  </div>
</div>

<div class="box">
  <div class="box-header">
    <div class="items-header">
      <h5 class="box-title">Waste Materials Reports</h5>

      <div class="items-actions">
        <input
          id="wmr-search"
          type="text"
          class="form-control w-[320px] !rounded-md"
          placeholder="Search WMR no./storage/custodian/remarks..."
        />

        <select id="wmr-status" class="form-control w-[180px] !rounded-md">
          <option value="">All Status</option>
          <option value="draft">Draft</option>
          <option value="submitted">Submitted</option>
          <option value="approved">Approved</option>
          <option value="disposed">Disposed</option>
          <option value="cancelled">Cancelled</option>
        </select>

        <div class="relative shrink-0">
          <button id="wmr-more-btn" type="button" class="ti-btn ti-btn-light">
            More Filters
            <span id="wmr-adv-count"
              class="hidden ms-2 inline-flex items-center justify-center text-[10px] leading-none px-2 py-1 rounded-full bg-primary/10 text-primary">
              0
            </span>
            <i class="ri-arrow-down-s-line ms-1"></i>
          </button>

          <div id="wmr-more-panel"
            class="hidden absolute right-0 mt-2 w-[360px] z-[9999] rounded-md border border-defaultborder bg-white dark:bg-bodybg shadow-lg">

            <div class="p-3 border-b border-defaultborder flex items-center justify-between">
              <div class="text-sm font-semibold text-defaulttextcolor dark:text-white">Advanced Filters</div>
              <button id="wmr-more-close" type="button" class="ti-btn ti-btn-sm ti-btn-light">
                <i class="ri-close-line"></i>
              </button>
            </div>

            <div class="p-3 space-y-3">
              <div>
                <label class="ti-form-label">Report Date Range</label>
                <div class="grid grid-cols-2 gap-2">
                  <input id="wmr-date-from" type="date" class="ti-form-input w-full" />
                  <input id="wmr-date-to" type="date" class="ti-form-input w-full" />
                </div>
              </div>

              <div>
                <label class="ti-form-label">Fund Cluster</label>
                <select id="wmr-fund-cluster" class="ti-form-input w-full">
                  <option value="">All Fund Clusters</option>
                  @foreach(($fundClusters ?? collect()) as $cluster)
                    <option value="{{ $cluster->id }}">
                      {{ $cluster->code }} - {{ $cluster->name }}
                    </option>
                  @endforeach
                </select>
              </div>

              <div>
                <label class="ti-form-label">Record Status</label>
                <select id="wmr-record-status" class="ti-form-input w-full">
                  <option value="">Active</option>
                  <option value="archived">Archived</option>
                  <option value="all">All</option>
                </select>
              </div>
            </div>

            <div class="p-3 border-t border-defaultborder flex items-center justify-end gap-2">
              <button id="wmr-adv-reset" type="button" class="ti-btn ti-btn-light">Reset</button>
              <button id="wmr-adv-apply" type="button" class="ti-btn ti-btn-primary">Apply</button>
            </div>
          </div>
        </div>

        <button id="wmr-clear" type="button" class="ti-btn ti-btn-light">Clear</button>

        @if($canCreateWmr)
          <button id="wmr-create" type="button" class="ti-btn ti-btn-primary">
            <i class="ri-add-line"></i> Create WMR
          </button>
        @endif
      </div>
    </div>
  </div>

  <div class="box-body">
    <div class="overflow-auto table-bordered">
      <div id="wmr-table" class="ti-custom-table ti-striped-table ti-custom-table-hover"></div>
    </div>

    <div class="mt-2 flex items-center justify-between text-xs text-[#8c9097]">
      <div id="wmr-info"></div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
  <script src="{{ asset('build/assets/libs/tabulator-tables/js/tabulator.min.js') }}"></script>
  <script>
    window.__wmr = {
      ajaxUrl: @json(route('gso.wmrs.data')),
      csrf: @json(csrf_token()),
      createDraftUrl: @json(route('gso.wmrs.createDraft')),
      editUrlTemplate: @json(route('gso.wmrs.edit', ['wmr' => '__WMR_ID__'])),
      deleteUrlTemplate: @json(route('gso.wmrs.destroy', ['wmr' => '__WMR_ID__'])),
      restoreUrlTemplate: @json(route('gso.wmrs.restore', ['wmr' => '__WMR_ID__'])),
      canManage: @json($canManageWmr),
      canDelete: @json($canDeleteWmr),
      canRestore: @json($canRestoreWmr),
    };
  </script>
@endpush

