@extends('layouts.master')

@section('styles')
  <link rel="stylesheet" href="{{ asset('build/assets/libs/tabulator-tables/css/tabulator.min.css') }}">
  <style>
    .items-header{display:flex;align-items:center;justify-content:space-between;gap:12px;width:100%}
    .items-actions{display:flex;align-items:center;gap:8px;flex-wrap:wrap;justify-content:flex-end}
    .box-header{overflow:visible!important}
    .tabulator.is-loading{opacity:.65;pointer-events:none}
    .permission-chip{display:inline-flex;align-items:center;gap:8px}
  </style>
@endsection

@section('content')
<div class="block justify-between page-header md:flex">
  <div>
    <h3 class="!text-defaulttextcolor dark:!text-defaulttextcolor/70 dark:text-white text-[1.125rem] font-semibold">
      Roles Management
    </h3>
  </div>
</div>

@if (session('success'))
  <div class="alert alert-success mb-4">{{ session('success') }}</div>
@endif

<div class="box">
  <div class="box-header">
    <div class="items-header">
      <h5 class="box-title">Roles</h5>

      <div class="items-actions">
        <button type="button" class="ti-btn ti-btn-primary" data-hs-overlay="#addRoleModal">
          Add New Role
        </button>

        <input
          id="roles-search"
          type="text"
          class="form-control !w-[320px] !rounded-md"
          placeholder="Search role/permission..."
        />

        <div class="relative shrink-0">
          <button id="roles-more-btn" type="button" class="ti-btn ti-btn-light">
            More Filters
            <span id="roles-adv-count"
              class="hidden ms-2 inline-flex items-center justify-center text-[10px] leading-none px-2 py-1 rounded-full bg-primary/10 text-primary">
              0
            </span>
            <i class="ri-arrow-down-s-line ms-1"></i>
          </button>

          <div id="roles-more-panel"
            class="hidden absolute right-0 mt-2 w-[380px] z-[9999] rounded-md border border-defaultborder bg-white dark:bg-bodybg shadow-lg">

            <div class="p-3 border-b border-defaultborder flex items-center justify-between">
              <div class="text-sm font-semibold text-defaulttextcolor dark:text-white">Advanced Filters</div>
              <button id="roles-more-close" type="button" class="ti-btn ti-btn-sm ti-btn-light">
                <i class="ri-close-line"></i>
              </button>
            </div>

            <div class="p-3 space-y-3">
              <div>
                <label class="ti-form-label">Archive Scope</label>
                <select id="roles-archived" class="ti-form-input w-full">
                  <option value="active" selected>Active Only</option>
                  <option value="archived">Archived Only</option>
                  <option value="all">Active + Archived</option>
                </select>
                <div class="text-xs text-[#8c9097] mt-1">Choose which records to include.</div>
              </div>

              <div>
                <label class="ti-form-label">Role Name</label>
                <input id="roles-name" type="text" class="ti-form-input w-full" placeholder="Exact/partial role name" />
              </div>

              <div>
                <label class="ti-form-label">Permission</label>
                <input id="roles-permission" type="text" class="ti-form-input w-full" placeholder="Permission name or page" />
              </div>

              <div>
                <label class="ti-form-label">Created Date Range</label>
                <div class="grid grid-cols-2 gap-2">
                  <input id="roles-date-from" type="date" class="ti-form-input w-full" />
                  <input id="roles-date-to" type="date" class="ti-form-input w-full" />
                </div>
                <div class="text-xs text-[#8c9097] mt-1">Filter by role creation date.</div>
              </div>
            </div>

            <div class="p-3 border-t border-defaultborder flex items-center justify-end gap-2">
              <button id="roles-adv-reset" type="button" class="ti-btn ti-btn-light">Reset</button>
              <button id="roles-adv-apply" type="button" class="ti-btn ti-btn-primary">Apply</button>
            </div>
          </div>
        </div>

        <button id="roles-clear" type="button" class="ti-btn ti-btn-light">Clear</button>
      </div>
    </div>
  </div>

  <div class="box-body">
    <div class="overflow-auto table-bordered">
      <div id="roles-table" class="ti-custom-table ti-striped-table ti-custom-table-hover"></div>
    </div>

    <div class="mt-2 flex items-center justify-between text-xs text-[#8c9097]">
      <div id="roles-info"></div>
    </div>
  </div>
</div>

@php
  $permByPage = $permissions->groupBy(fn($p) => $p->page ?: 'Uncategorized')->sortKeys();
@endphp

<div id="addRoleModal" class="hs-overlay hidden ti-modal [--overlay-backdrop:static]">
  <div class="hs-overlay-open:mt-7 ti-modal-box mt-0 ease-out">
    <div class="ti-modal-content">
      <div class="ti-modal-header">
        <h6 class="modal-title text-[1rem] font-semibold">Add New Role</h6>
        <button type="button" class="hs-dropdown-toggle text-[1rem] font-semibold text-defaulttextcolor"
                data-hs-overlay="#addRoleModal"><i class="ri-close-line"></i></button>
      </div>

      <div class="ti-modal-body px-4">
        <form id="addRoleForm" method="POST" action="{{ route('access.roles.store') }}">
          @csrf

          <div class="mb-4">
            <label for="roleName" class="form-label">Role Name</label>
            <input type="text" id="roleName" name="name" class="form-control" placeholder="Enter role name" required>
          </div>

          <div class="mb-3 flex items-center justify-between gap-3">
            <h6 class="font-semibold">Assign Permissions</h6>
            <div class="flex gap-2">
              <button type="button" class="ti-btn ti-btn-xs ti-btn-light" data-bulk="check-all" data-scope="add">Check all</button>
              <button type="button" class="ti-btn ti-btn-xs ti-btn-light" data-bulk="uncheck-all" data-scope="add">Uncheck all</button>
            </div>
          </div>

          <div class="space-y-3 max-h-[460px] overflow-auto pr-1">
            @foreach ($permByPage as $pageName => $items)
              @php $groupKey = Str::slug($pageName); @endphp
              <div class="border rounded-lg p-3">
                <div class="font-medium">
                  {{ $pageName }} <span class="text-xs text-muted">({{ $items->count() }})</span>
                </div>

                <div id="add-group-{{ $groupKey }}" class="grid md:grid-cols-2 gap-x-6 gap-y-2 mt-3">
                  @foreach ($items->sortBy('name') as $p)
                    <label class="permission-chip" data-label="{{ strtolower($p->name . ' ' . ($p->page ?: '')) }}">
                      <input type="checkbox" class="form-check-input" name="permissions[]" value="{{ $p->id }}">
                      <span>{{ $p->name }}</span>
                    </label>
                  @endforeach
                </div>
              </div>
            @endforeach
          </div>

          <div class="text-end mt-4">
            <button type="submit" class="ti-btn btn-wave bg-primary text-white !font-medium">Save Role</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<div id="editRoleModal" class="hs-overlay hidden ti-modal [--overlay-backdrop:static]">
  <div class="hs-overlay-open:mt-7 ti-modal-box mt-0 ease-out">
    <div class="ti-modal-content">
      <div class="ti-modal-header">
        <h6 class="modal-title text-[1rem] font-semibold">Edit Role</h6>
        <button type="button" class="hs-dropdown-toggle text-[1rem] font-semibold text-defaulttextcolor"
                data-hs-overlay="#editRoleModal"><i class="ri-close-line"></i></button>
      </div>

      <div class="ti-modal-body px-4">
        <form id="editRoleForm" method="POST" action="">
          @csrf
          @method('PUT')
          <input type="hidden" id="editRoleId" name="role_id">

          <div class="mb-4">
            <label for="editRoleName" class="form-label">Role Name</label>
            <input type="text" id="editRoleName" name="name" class="form-control" required>
          </div>

          <div class="mb-3 flex items-center justify-between gap-3">
            <h6 class="font-semibold">Assign Permissions</h6>
            <div class="flex gap-2">
              <button type="button" class="ti-btn ti-btn-xs ti-btn-light" data-bulk="check-all" data-scope="edit">Check all</button>
              <button type="button" class="ti-btn ti-btn-xs ti-btn-light" data-bulk="uncheck-all" data-scope="edit">Uncheck all</button>
            </div>
          </div>

          <div class="space-y-3 max-h-[460px] overflow-auto pr-1">
            @foreach ($permByPage as $pageName => $items)
              @php $groupKey = Str::slug($pageName); @endphp
              <div class="border rounded-lg p-3">
                <div class="font-medium">
                  {{ $pageName }} <span class="text-xs text-muted">({{ $items->count() }})</span>
                </div>

                <div id="edit-group-{{ $groupKey }}" class="grid md:grid-cols-2 gap-x-6 gap-y-2 mt-3">
                  @foreach ($items->sortBy('name') as $p)
                    <label class="permission-chip" data-label="{{ strtolower($p->name . ' ' . ($p->page ?: '')) }}">
                      <input type="checkbox" class="form-check-input" name="permissions[]" value="{{ $p->id }}">
                      <span>{{ $p->name }}</span>
                    </label>
                  @endforeach
                </div>
              </div>
            @endforeach
          </div>

          <div class="text-end mt-4">
            <button type="submit" class="ti-btn btn-wave bg-primary text-white !font-medium">Update Role</button>
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
    window.__accessRoles = {
      ajaxUrl: @json(route('access.roles.data')),
    };
  </script>
@endpush
