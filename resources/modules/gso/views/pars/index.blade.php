@extends('layouts.master')

@section('styles')
  <link rel="stylesheet" href="{{ asset('build/assets/libs/tabulator-tables/css/tabulator.min.css') }}">
  <style>
    .box-header {
      overflow: visible !important;
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
      PAR
    </h3>
  </div>
</div>

<div class="box">
  <div class="box-header">
    <div class="datatable-toolbar">
      <h5 class="box-title">Property Acknowledgement Receipts</h5>

      <div class="datatable-toolbar-actions">
        <input
          id="par-search"
          type="text"
          class="form-control !w-[320px] !rounded-md"
          placeholder="Search PAR no./officer/remarks..."
        />

        <select id="par-status" class="form-control !w-[180px] !rounded-md">
          <option value="">All Status</option>
          <option value="draft">Draft</option>
          <option value="submitted">Submitted</option>
          <option value="finalized">Finalized</option>
          <option value="cancelled">Cancelled</option>
        </select>

        <div class="relative shrink-0">
          <button id="par-more-btn" type="button" class="ti-btn ti-btn-light">
            More Filters
            <span id="par-adv-count"
              class="hidden ms-2 inline-flex items-center justify-center text-[10px] leading-none px-2 py-1 rounded-full bg-primary/10 text-primary">
              0
            </span>
            <i class="ri-arrow-down-s-line ms-1"></i>
          </button>

          <div id="par-more-panel"
            class="hidden absolute right-0 mt-2 w-[360px] z-[9999] rounded-md border border-defaultborder bg-white dark:bg-bodybg shadow-lg">

            <div class="p-3 border-b border-defaultborder flex items-center justify-between">
              <div class="text-sm font-semibold text-defaulttextcolor dark:text-white">Advanced Filters</div>
              <button id="par-more-close" type="button" class="ti-btn ti-btn-sm ti-btn-light">
                <i class="ri-close-line"></i>
              </button>
            </div>

            <div class="p-3 space-y-3">
              <div>
                <label class="ti-form-label">Issued Date Range</label>
                <div class="grid grid-cols-2 gap-2">
                  <input id="par-date-from" type="date" class="ti-form-input w-full" />
                  <input id="par-date-to" type="date" class="ti-form-input w-full" />
                </div>
                <div class="text-xs text-[#8c9097] mt-1">Filters by issued date (from–to).</div>
              </div>

              <div>
                <label class="ti-form-label">Department</label>
                <select id="par-department" class="ti-form-input w-full">
                  <option value="">All Departments</option>
                  @foreach(($departments ?? collect()) as $dept)
                    <option value="{{ $dept->id }}">
                      {{ $dept->code }} - {{ $dept->name }}
                    </option>
                  @endforeach
                </select>
                <div class="text-xs text-[#8c9097] mt-1">Filters by receiving department.</div>
              </div>

              <div>
                <label class="ti-form-label">Record Status</label>
                <select id="par-record-status" class="ti-form-input w-full">
                  <option value="">Active</option>
                  <option value="archived">Archived</option>
                  <option value="all">All</option>
                </select>
                <div class="text-xs text-[#8c9097] mt-1">Filters by archived (soft-deleted) records.</div>
              </div>
            </div>

            <div class="p-3 border-t border-defaultborder flex items-center justify-end gap-2">
              <button id="par-adv-reset" type="button" class="ti-btn ti-btn-light">Reset</button>
              <button id="par-adv-apply" type="button" class="ti-btn ti-btn-primary">Apply</button>
            </div>
          </div>
        </div>

        <button id="par-clear" type="button" class="ti-btn ti-btn-light">Clear</button>

        <form method="POST" action="{{ route('gso.pars.create-draft') }}" class="inline-block">
          @csrf
          <button type="submit" class="ti-btn ti-btn-primary">
            <i class="ri-add-line"></i> Create PAR
          </button>
        </form>
      </div>
    </div>
  </div>

  <div class="box-body">
    <div class="overflow-auto table-bordered">
      <div id="par-table" class="ti-custom-table ti-striped-table ti-custom-table-hover"></div>
    </div>

    <div class="mt-2 flex items-center justify-between text-xs text-[#8c9097]">
      <div id="par-info"></div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
  <script src="{{ asset('build/assets/libs/tabulator-tables/js/tabulator.min.js') }}"></script>

  <script>
    window.__pars = {
      ajaxUrl: @json(route('gso.pars.data')),
      csrf: @json(csrf_token()),

      showUrlTemplate: @json(route('gso.pars.show', ['par' => '__PAR_ID__'])),
      deleteUrlTemplate: @json(route('gso.pars.destroy', ['par' => '__PAR_ID__'])),
      restoreUrlTemplate: @json(route('gso.pars.restore', ['par' => '__PAR_ID__'])),

      canDelete: @json(auth()->user()?->hasAnyRole(['Administrator', 'admin']) || auth()->user()?->can('modify PAR')),
      canRestore: @json(
        auth()->user()?->hasAnyRole(['Administrator', 'admin'])
        || auth()->user()?->can('modify Allow Data Restoration')
        || auth()->user()?->can('restore PAR')
      ),
    };
  </script>

@endpush

