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
      Manage Permissions
    </h3>
  </div>
</div>

@if (session('success'))
  <div class="alert alert-success mb-4">{{ session('success') }}</div>
@endif

@if ($errors->any())
  <div class="alert alert-danger mb-4">
    <ul class="list-disc ms-6">
      @foreach($errors->all() as $e)
        <li>{{ $e }}</li>
      @endforeach
    </ul>
  </div>
@endif

<div class="box mb-[3rem]">
  <div class="box-header">
    <div class="items-header">
      <h5 class="box-title">Existing Permissions</h5>

      <div class="items-actions">
        <input
          id="permissions-search"
          type="text"
          class="form-control !w-[320px] !rounded-md"
          placeholder="Search permission/module/role..."
        />

        <button type="button" class="ti-btn ti-btn-primary" data-hs-overlay="#addPermissionModal">
          Add Permission
        </button>

        <div class="relative shrink-0">
          <button id="permissions-more-btn" type="button" class="ti-btn ti-btn-light">
            More Filters
            <span id="permissions-adv-count"
              class="hidden ms-2 inline-flex items-center justify-center text-[10px] leading-none px-2 py-1 rounded-full bg-primary/10 text-primary">
              0
            </span>
            <i class="ri-arrow-down-s-line ms-1"></i>
          </button>

          <div id="permissions-more-panel"
            class="hidden absolute right-0 mt-2 w-[380px] z-[9999] rounded-md border border-defaultborder bg-white dark:bg-bodybg shadow-lg">

            <div class="p-3 border-b border-defaultborder flex items-center justify-between">
              <div class="text-sm font-semibold text-defaulttextcolor dark:text-white">Advanced Filters</div>
              <button id="permissions-more-close" type="button" class="ti-btn ti-btn-sm ti-btn-light">
                <i class="ri-close-line"></i>
              </button>
            </div>

            <div class="p-3 space-y-3">
              <div>
                <label class="ti-form-label">Archive Scope</label>
                <select id="permissions-archived" class="ti-form-input w-full">
                  <option value="active" selected>Active Only</option>
                  <option value="archived">Archived Only</option>
                  <option value="all">Active + Archived</option>
                </select>
                <div class="text-xs text-[#8c9097] mt-1">Choose which records to include.</div>
              </div>

              <div>
                <label class="ti-form-label">Page / Module</label>
                <input id="permissions-module" type="text" class="ti-form-input w-full" placeholder="Exact/partial module" />
              </div>

              <div>
                <label class="ti-form-label">Guard</label>
                <select id="permissions-guard" class="ti-form-input w-full">
                  <option value="">All</option>
                  <option value="web">Web</option>
                  <option value="api">API</option>
                </select>
              </div>

              <div>
                <label class="ti-form-label">Role</label>
                <input id="permissions-role" type="text" class="ti-form-input w-full" placeholder="Role using this permission" />
              </div>

              <div>
                <label class="ti-form-label">Created Date Range</label>
                <div class="grid grid-cols-2 gap-2">
                  <input id="permissions-date-from" type="date" class="ti-form-input w-full" />
                  <input id="permissions-date-to" type="date" class="ti-form-input w-full" />
                </div>
                <div class="text-xs text-[#8c9097] mt-1">Filter by permission creation date.</div>
              </div>
            </div>

            <div class="p-3 border-t border-defaultborder flex items-center justify-end gap-2">
              <button id="permissions-adv-reset" type="button" class="ti-btn ti-btn-light">Reset</button>
              <button id="permissions-adv-apply" type="button" class="ti-btn ti-btn-primary">Apply</button>
            </div>
          </div>
        </div>

        <button id="permissions-clear" type="button" class="ti-btn ti-btn-light">Clear</button>
      </div>
    </div>
  </div>

  <div class="box-body">
    <div class="overflow-auto table-bordered">
      <div id="permissions-table" class="ti-custom-table ti-striped-table ti-custom-table-hover"></div>
    </div>

    <div class="mt-2 flex items-center justify-between text-xs text-[#8c9097]">
      <div id="permissions-info"></div>
    </div>
  </div>
</div>

<div id="addPermissionModal" class="hs-overlay hidden ti-modal [--overlay-backdrop:static]">
  <div class="hs-overlay-open:mt-7 ti-modal-box mt-0 ease-out">
    <div class="ti-modal-content">
      <div class="ti-modal-header">
        <h6 class="modal-title text-[1rem] font-semibold">Add Permission</h6>
        <button type="button" class="hs-dropdown-toggle text-[1rem] font-semibold text-defaulttextcolor"
                data-hs-overlay="#addPermissionModal"><i class="ri-close-line"></i></button>
      </div>

      <div class="ti-modal-body px-4">
        <form id="addPermissionForm" method="POST" action="{{ route('access.permissions.store') }}" class="space-y-4">
          @csrf

          <div>
            <label for="addPermissionName" class="form-label">Permission Name</label>
            <input
              type="text"
              id="addPermissionName"
              name="name"
              class="form-control w-full !rounded-md"
              placeholder='e.g., "view Login Logs" or "modify User Lists"'
              required
            >
          </div>

          <div>
            <label for="addPermissionPage" class="form-label">Page / Module</label>
            <input
              type="text"
              id="addPermissionPage"
              name="page"
              class="form-control w-full !rounded-md"
              placeholder='e.g., "Login Logs" or "Manage Users"'
              required
            >
          </div>

          <div>
            <label for="addPermissionGuard" class="form-label">Guard</label>
            <select id="addPermissionGuard" name="guard_name" class="form-control w-full !rounded-md">
              <option value="web">Web</option>
              <option value="api">API</option>
            </select>
          </div>

          <div class="text-end mt-2">
            <button type="submit" class="ti-btn btn-wave bg-primary text-white !font-medium">Save Permission</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<div id="editPermissionModal" class="hs-overlay hidden ti-modal [--overlay-backdrop:static]">
  <div class="hs-overlay-open:mt-7 ti-modal-box mt-0 ease-out">
    <div class="ti-modal-content">
      <div class="ti-modal-header">
        <h6 class="modal-title text-[1rem] font-semibold">Edit Permission</h6>
        <button type="button" class="hs-dropdown-toggle text-[1rem] font-semibold text-defaulttextcolor"
                data-hs-overlay="#editPermissionModal"><i class="ri-close-line"></i></button>
      </div>

      <div class="ti-modal-body px-4">
        <form id="editPermissionForm" method="POST" action="" class="space-y-4">
          @csrf
          @method('PATCH')
          <input type="hidden" id="editPermissionId" name="permission_id">

          <div>
            <label for="editPermissionName" class="form-label">Permission Name</label>
            <input
              type="text"
              id="editPermissionName"
              name="name"
              class="form-control w-full !rounded-md"
              required
            >
          </div>

          <div>
            <label for="editPermissionPage" class="form-label">Page / Module</label>
            <input
              type="text"
              id="editPermissionPage"
              name="page"
              class="form-control w-full !rounded-md"
              required
            >
          </div>

          <div>
            <label for="editPermissionGuard" class="form-label">Guard</label>
            <select id="editPermissionGuard" name="guard_name" class="form-control w-full !rounded-md">
              <option value="web">Web</option>
              <option value="api">API</option>
            </select>
          </div>

          <div class="text-end mt-2">
            <button type="submit" class="ti-btn btn-wave bg-primary text-white !font-medium">Update Permission</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
  <script src="{{ asset('build/assets/libs/tabulator-tables/js/tabulator.min.js') }}"></script>

  <script>
    window.__accessPermissions = {
      ajaxUrl: @json(route('access.permissions.data')),
    };
  </script>
@endpush
