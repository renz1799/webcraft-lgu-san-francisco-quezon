@extends('layouts.master')

@section('styles')
  <link rel="stylesheet" href="{{ asset('build/assets/libs/tabulator-tables/css/tabulator.min.css') }}">
  <style>
    .box-header{overflow:visible!important}
  </style>
@endsection

@section('content')
@php
  $gsoUser = auth()->user();
  $gsoAuthorizer = app(\App\Core\Support\AdminContextAuthorizer::class);
  $canCreatePtr = $gsoAuthorizer->allowsPermission($gsoUser, 'ptr.create');
  $canManagePtr = $gsoAuthorizer->allowsAnyPermission($gsoUser, [
    'ptr.create',
    'ptr.update',
    'ptr.submit',
    'ptr.finalize',
    'ptr.reopen',
    'ptr.manage_items',
  ]);
  $canDeletePtr = $gsoAuthorizer->allowsPermission($gsoUser, 'ptr.archive');
  $canRestorePtr = $gsoAuthorizer->allowsAnyPermission($gsoUser, [
    'ptr.restore',
    'audit_logs.restore_data',
  ]);
@endphp

<div class="block justify-between page-header md:flex">
  <div>
    <h3 class="!text-defaulttextcolor dark:!text-defaulttextcolor/70 dark:text-white text-[1.125rem] font-semibold">
      PTR
    </h3>
  </div>
</div>

<div class="box">
  <div class="box-header">
    <div class="datatable-toolbar">
      <h5 class="box-title">Property Transfer Reports</h5>

      <div class="datatable-toolbar-actions">
        <input
          id="ptr-search"
          type="text"
          class="form-control !w-[320px] !rounded-md"
          placeholder="Search PTR no./officer/type/remarks..."
        />

        <select id="ptr-status" class="form-control !w-[180px] !rounded-md">
          <option value="">All Status</option>
          <option value="draft">Draft</option>
          <option value="submitted">Submitted</option>
          <option value="finalized">Finalized</option>
          <option value="cancelled">Cancelled</option>
        </select>

        <div class="relative shrink-0">
          <button id="ptr-more-btn" type="button" class="ti-btn ti-btn-light">
            More Filters
            <span id="ptr-adv-count"
              class="hidden ms-2 inline-flex items-center justify-center text-[10px] leading-none px-2 py-1 rounded-full bg-primary/10 text-primary">
              0
            </span>
            <i class="ri-arrow-down-s-line ms-1"></i>
          </button>

          <div id="ptr-more-panel"
            class="hidden absolute right-0 mt-2 w-[360px] z-[9999] rounded-md border border-defaultborder bg-white dark:bg-bodybg shadow-lg">

            <div class="p-3 border-b border-defaultborder flex items-center justify-between">
              <div class="text-sm font-semibold text-defaulttextcolor dark:text-white">Advanced Filters</div>
              <button id="ptr-more-close" type="button" class="ti-btn ti-btn-sm ti-btn-light">
                <i class="ri-close-line"></i>
              </button>
            </div>

            <div class="p-3 space-y-3">
              <div>
                <label class="ti-form-label">Transfer Date Range</label>
                <div class="grid grid-cols-2 gap-2">
                  <input id="ptr-date-from" type="date" class="ti-form-input w-full" />
                  <input id="ptr-date-to" type="date" class="ti-form-input w-full" />
                </div>
              </div>

              <div>
                <label class="ti-form-label">From Department</label>
                <select id="ptr-from-department" class="ti-form-input w-full">
                  <option value="">All Departments</option>
                  @foreach(($departments ?? collect()) as $dept)
                    <option value="{{ $dept->id }}">
                      {{ $dept->code }} - {{ $dept->name }}
                    </option>
                  @endforeach
                </select>
              </div>

              <div>
                <label class="ti-form-label">To Department</label>
                <select id="ptr-to-department" class="ti-form-input w-full">
                  <option value="">All Departments</option>
                  @foreach(($departments ?? collect()) as $dept)
                    <option value="{{ $dept->id }}">
                      {{ $dept->code }} - {{ $dept->name }}
                    </option>
                  @endforeach
                </select>
              </div>

              <div>
                <label class="ti-form-label">From Fund Source</label>
                <select id="ptr-from-fund-source" class="ti-form-input w-full">
                  <option value="">All Fund Sources</option>
                  @foreach(($fundSources ?? collect()) as $fs)
                    <option value="{{ $fs->id }}">
                      {{ $fs->code }} - {{ $fs->name }}
                    </option>
                  @endforeach
                </select>
              </div>

              <div>
                <label class="ti-form-label">To Fund Source</label>
                <select id="ptr-to-fund-source" class="ti-form-input w-full">
                  <option value="">All Fund Sources</option>
                  @foreach(($fundSources ?? collect()) as $fs)
                    <option value="{{ $fs->id }}">
                      {{ $fs->code }} - {{ $fs->name }}
                    </option>
                  @endforeach
                </select>
              </div>

              <div>
                <label class="ti-form-label">Record Status</label>
                <select id="ptr-record-status" class="ti-form-input w-full">
                  <option value="">Active</option>
                  <option value="archived">Archived</option>
                  <option value="all">All</option>
                </select>
              </div>
            </div>

            <div class="p-3 border-t border-defaultborder flex items-center justify-end gap-2">
              <button id="ptr-adv-reset" type="button" class="ti-btn ti-btn-light">Reset</button>
              <button id="ptr-adv-apply" type="button" class="ti-btn ti-btn-primary">Apply</button>
            </div>
          </div>
        </div>

        <button id="ptr-clear" type="button" class="ti-btn ti-btn-light">Clear</button>

        @if($canCreatePtr)
          <button id="ptr-create" type="button" class="ti-btn ti-btn-primary">
            <i class="ri-add-line"></i> Create PTR
          </button>
        @endif
      </div>
    </div>
  </div>

  <div class="box-body">
    <div class="overflow-auto table-bordered">
      <div id="ptr-table" class="ti-custom-table ti-striped-table ti-custom-table-hover"></div>
    </div>

    <div class="mt-2 flex items-center justify-between text-xs text-[#8c9097]">
      <div id="ptr-info"></div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
  <script src="{{ asset('build/assets/libs/tabulator-tables/js/tabulator.min.js') }}"></script>
  <script>
    window.__ptr = {
      ajaxUrl: @json(route('gso.ptrs.data')),
      csrf: @json(csrf_token()),
      createDraftUrl: @json(route('gso.ptrs.create-draft')),
      editUrlTemplate: @json(route('gso.ptrs.edit', ['ptr' => '__PTR_ID__'])),
      deleteUrlTemplate: @json(route('gso.ptrs.destroy', ['ptr' => '__PTR_ID__'])),
      restoreUrlTemplate: @json(route('gso.ptrs.restore', ['ptr' => '__PTR_ID__'])),
      canManage: @json($canManagePtr),
      canDelete: @json($canDeletePtr),
      canRestore: @json($canRestorePtr),
    };
  </script>
@endpush
