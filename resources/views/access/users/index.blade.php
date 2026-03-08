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
      User Access
    </h3>
  </div>
</div>

<div class="box">
  <div class="box-header">
    <div class="items-header">
      <h5 class="box-title">Users</h5>

      <div class="items-actions">
        <input
          id="users-search"
          type="text"
          class="form-control !w-[320px] !rounded-md"
          placeholder="Search user/email/role..."
        />

        <div class="relative shrink-0">
          <button id="users-more-btn" type="button" class="ti-btn ti-btn-light">
            More Filters
            <span id="users-adv-count"
              class="hidden ms-2 inline-flex items-center justify-center text-[10px] leading-none px-2 py-1 rounded-full bg-primary/10 text-primary">
              0
            </span>
            <i class="ri-arrow-down-s-line ms-1"></i>
          </button>

          <div id="users-more-panel"
            class="hidden absolute right-0 mt-2 w-[380px] z-[9999] rounded-md border border-defaultborder bg-white dark:bg-bodybg shadow-lg">

            <div class="p-3 border-b border-defaultborder flex items-center justify-between">
              <div class="text-sm font-semibold text-defaulttextcolor dark:text-white">Advanced Filters</div>
              <button id="users-more-close" type="button" class="ti-btn ti-btn-sm ti-btn-light">
                <i class="ri-close-line"></i>
              </button>
            </div>

            <div class="p-3 space-y-3">
              <div>
                <label class="ti-form-label">Archive Scope</label>
                <select id="users-archived" class="ti-form-input w-full">
                  <option value="active" selected>Active Only</option>
                  <option value="archived">Archived Only</option>
                  <option value="all">Active + Archived</option>
                </select>
                <div class="text-xs text-[#8c9097] mt-1">Choose which records to include.</div>
              </div>

              <div>
                <label class="ti-form-label">Status</label>
                <select id="users-status" class="ti-form-input w-full">
                  <option value="">All</option>
                  <option value="active">Active</option>
                  <option value="inactive">Inactive</option>
                </select>
                <div class="text-xs text-[#8c9097] mt-1">Filter active or inactive users.</div>
              </div>

              <div>
                <label class="ti-form-label">Role</label>
                <input id="users-role" type="text" class="ti-form-input w-full" placeholder="Exact/partial role name" />
              </div>

              <div>
                <label class="ti-form-label">Username</label>
                <input id="users-username" type="text" class="ti-form-input w-full" placeholder="Exact/partial username" />
              </div>

              <div>
                <label class="ti-form-label">Email</label>
                <input id="users-email" type="text" class="ti-form-input w-full" placeholder="Exact/partial email" />
              </div>

              <div>
                <label class="ti-form-label">Created Date Range</label>
                <div class="grid grid-cols-2 gap-2">
                  <input id="users-date-from" type="date" class="ti-form-input w-full" />
                  <input id="users-date-to" type="date" class="ti-form-input w-full" />
                </div>
                <div class="text-xs text-[#8c9097] mt-1">Filter by account creation date.</div>
              </div>
            </div>

            <div class="p-3 border-t border-defaultborder flex items-center justify-end gap-2">
              <button id="users-adv-reset" type="button" class="ti-btn ti-btn-light">Reset</button>
              <button id="users-adv-apply" type="button" class="ti-btn ti-btn-primary">Apply</button>
            </div>
          </div>
        </div>

        <button id="users-clear" type="button" class="ti-btn ti-btn-light">Clear</button>
      </div>
    </div>
  </div>

  <div class="box-body">
    <div class="overflow-auto table-bordered">
      <div id="users-table" class="ti-custom-table ti-striped-table ti-custom-table-hover"></div>
    </div>

    <div class="mt-2 flex items-center justify-between text-xs text-[#8c9097]">
      <div id="users-info"></div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
  <script src="{{ asset('build/assets/libs/tabulator-tables/js/tabulator.min.js') }}"></script>

  <script>
    window.__accessUsers = {
      ajaxUrl: @json(route('access.users.data')),
    };
  </script>
@endpush
