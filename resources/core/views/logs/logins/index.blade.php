@extends('layouts.master')

@section('styles')
  <link rel="stylesheet" href="{{ asset('build/assets/libs/tabulator-tables/css/tabulator.min.css') }}">
  <style>
    .items-header{display:flex;align-items:center;justify-content:space-between;gap:12px;width:100%}
    .items-actions{display:flex;align-items:center;gap:8px;flex-wrap:wrap;justify-content:flex-end}
    .box-header{overflow:visible!important}
    .tabulator.is-loading{opacity:.65;pointer-events:none}
  </style>
@endsection

@section('content')
<div class="block justify-between page-header md:flex">
  <div>
    <h3 class="!text-defaulttextcolor dark:!text-defaulttextcolor/70 dark:text-white text-[1.125rem] font-semibold">
      Login Logs
    </h3>
    <p class="text-textmuted dark:text-textmuted/80 mb-0">
      Review platform-wide authentication activity across Core and active modules.
    </p>
  </div>
</div>

<div class="box">
  <div class="box-header">
    <div class="items-header">
      <h5 class="box-title">Recent Login Attempts</h5>

      <div class="items-actions">
        <input
          id="login-search"
          type="text"
          class="form-control !w-[320px] !rounded-md"
          placeholder="Search user/email/ip/device/address..."
        />

        <div class="relative shrink-0">
          <button id="login-more-btn" type="button" class="ti-btn ti-btn-light">
            More Filters
            <span id="login-adv-count"
              class="hidden ms-2 inline-flex items-center justify-center text-[10px] leading-none px-2 py-1 rounded-full bg-primary/10 text-primary">
              0
            </span>
            <i class="ri-arrow-down-s-line ms-1"></i>
          </button>

          <div id="login-more-panel"
            class="hidden absolute right-0 mt-2 w-[380px] z-[9999] rounded-md border border-defaultborder bg-white dark:bg-bodybg shadow-lg">

            <div class="p-3 border-b border-defaultborder flex items-center justify-between">
              <div class="text-sm font-semibold text-defaulttextcolor dark:text-white">Advanced Filters</div>
              <button id="login-more-close" type="button" class="ti-btn ti-btn-sm ti-btn-light">
                <i class="ri-close-line"></i>
              </button>
            </div>

            <div class="p-3 space-y-3">
              <div>
                <label class="ti-form-label">Status</label>
                <select id="login-status" class="ti-form-input w-full">
                  <option value="">All</option>
                  <option value="success">Success</option>
                  <option value="failed">Failed</option>
                </select>
                <div class="text-xs text-[#8c9097] mt-1">Filter successful or failed attempts.</div>
              </div>

              <div>
                <label class="ti-form-label">Module</label>
                <input id="login-module" type="text" class="ti-form-input w-full" placeholder="Code or name" />
                <div class="text-xs text-[#8c9097] mt-1">Matches module code or module name.</div>
              </div>

              <div>
                <label class="ti-form-label">Username</label>
                <input id="login-user" type="text" class="ti-form-input w-full" placeholder="Exact/partial username" />
              </div>

              <div>
                <label class="ti-form-label">Email</label>
                <input id="login-email" type="text" class="ti-form-input w-full" placeholder="Exact/partial email" />
              </div>

              <div>
                <label class="ti-form-label">IP Address</label>
                <input id="login-ip" type="text" class="ti-form-input w-full" placeholder="e.g. 192.168" />
              </div>

              <div>
                <label class="ti-form-label">Device</label>
                <input id="login-device" type="text" class="ti-form-input w-full" placeholder="Browser/device text" />
              </div>

              <div>
                <label class="ti-form-label">Date Range</label>
                <div class="grid grid-cols-2 gap-2">
                  <input id="login-date-from" type="date" class="ti-form-input w-full" />
                  <input id="login-date-to" type="date" class="ti-form-input w-full" />
                </div>
                <div class="text-xs text-[#8c9097] mt-1">Filter by login timestamp (from-to).</div>
              </div>
            </div>

            <div class="p-3 border-t border-defaultborder flex items-center justify-end gap-2">
              <button id="login-adv-reset" type="button" class="ti-btn ti-btn-light">Reset</button>
              <button id="login-adv-apply" type="button" class="ti-btn ti-btn-primary">Apply</button>
            </div>
          </div>
        </div>

        <button id="login-clear" type="button" class="ti-btn ti-btn-light">Clear</button>
      </div>
    </div>
  </div>

  <div class="box-body">
    <div class="overflow-auto table-bordered">
      <div id="login-table" class="ti-custom-table ti-striped-table ti-custom-table-hover"></div>
    </div>

    <div class="mt-2 flex items-center justify-between text-xs text-[#8c9097]">
      <div id="login-info"></div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
  <script src="{{ asset('build/assets/libs/tabulator-tables/js/tabulator.min.js') }}"></script>

  <script>
    window.__loginLogs = {
      ajaxUrl: @json(route('logs.data')),
    };
  </script>
@endpush
