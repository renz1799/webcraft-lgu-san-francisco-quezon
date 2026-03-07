@extends('layouts.master')

@section('styles')
  <link rel="stylesheet" href="{{ asset('build/assets/libs/tabulator-tables/css/tabulator.min.css') }}">
  <style>
    .tasks-header{display:flex;align-items:center;justify-content:space-between;gap:12px;width:100%}
    .tasks-actions{display:flex;align-items:center;gap:8px;flex-wrap:wrap;justify-content:flex-end}
    .box-header{overflow:visible!important}
    .tabulator.is-loading{opacity:.65;pointer-events:none}
  </style>
@endsection

@section('content')
<div class="block justify-between page-header md:flex">
  <div>
    <h3 class="!text-defaulttextcolor dark:!text-defaulttextcolor/70 dark:text-white text-[1.125rem] font-semibold">
      Tasks
    </h3>
  </div>
</div>

@php($canViewAll = auth()->user()?->hasAnyRole(['Administrator', 'admin']) || auth()->user()?->can('view All Tasks'))
@php($canArchive = auth()->user()?->hasAnyRole(['Administrator', 'admin']))
@php($initialSearch = request('search', request('q', '')))
@php($initialArchivedRaw = request('archived', 'active'))
@php($initialArchived = in_array($initialArchivedRaw, ['active', 'archived', 'all'], true) ? $initialArchivedRaw : 'active')
@php($initialScopeRaw = request('scope', 'mine'))
@php($initialScope = (!$canViewAll && $initialScopeRaw === 'all') ? 'mine' : $initialScopeRaw)
@php($initialStatus = request('status', ''))
@php($initialAssignedTo = request('assigned_to', ''))
@php($initialDateFrom = request('date_from', ''))
@php($initialDateTo = request('date_to', ''))

<div class="box">
  <div class="box-header">
    <div class="tasks-header">
      <h5 class="box-title">Tasks</h5>

      <div class="tasks-actions">
        <input
          id="tasks-search"
          type="text"
          class="form-control !w-[320px] !rounded-md"
          placeholder="Search title/description/assignee..."
          value="{{ $initialSearch }}"
        />

        <div class="relative shrink-0">
          <button id="tasks-more-btn" type="button" class="ti-btn ti-btn-light">
            More Filters
            <span id="tasks-adv-count"
              class="hidden ms-2 inline-flex items-center justify-center text-[10px] leading-none px-2 py-1 rounded-full bg-primary/10 text-primary">
              0
            </span>
            <i class="ri-arrow-down-s-line ms-1"></i>
          </button>

          <div id="tasks-more-panel"
            class="hidden absolute right-0 mt-2 w-[380px] z-[9999] rounded-md border border-defaultborder bg-white dark:bg-bodybg shadow-lg">

            <div class="p-3 border-b border-defaultborder flex items-center justify-between">
              <div class="text-sm font-semibold text-defaulttextcolor dark:text-white">Advanced Filters</div>
              <button id="tasks-more-close" type="button" class="ti-btn ti-btn-sm ti-btn-light">
                <i class="ri-close-line"></i>
              </button>
            </div>

            <div class="p-3 space-y-3">
              <div>
                <label class="ti-form-label">Archive Scope</label>
                <select id="tasks-archived" class="ti-form-input w-full">
                  <option value="active" @selected($initialArchived === 'active')>Active Only</option>
                  <option value="archived" @selected($initialArchived === 'archived')>Archived Only</option>
                  <option value="all" @selected($initialArchived === 'all')>Active + Archived</option>
                </select>
                <div class="text-xs text-[#8c9097] mt-1">Choose which records to include.</div>
              </div>

              <div>
                <label class="ti-form-label">Visibility Scope</label>
                <select id="tasks-scope" class="ti-form-input w-full">
                  <option value="mine" @selected($initialScope === 'mine')>My Tasks</option>
                  <option value="available" @selected($initialScope === 'available')>Claimable Tasks</option>
                  @if($canViewAll)
                    <option value="all" @selected($initialScope === 'all')>All Tasks</option>
                  @endif
                </select>
              </div>

              <div>
                <label class="ti-form-label">Status</label>
                <select id="tasks-status" class="ti-form-input w-full">
                  <option value="" @selected($initialStatus === '')>All</option>
                  <option value="pending" @selected($initialStatus === 'pending')>Pending</option>
                  <option value="in_progress" @selected($initialStatus === 'in_progress')>In Progress</option>
                  <option value="done" @selected($initialStatus === 'done')>Done</option>
                  <option value="cancelled" @selected($initialStatus === 'cancelled')>Cancelled</option>
                </select>
              </div>

              <div>
                <label class="ti-form-label">Assigned To (username)</label>
                <input id="tasks-assigned-to" type="text" class="ti-form-input w-full" placeholder="Exact/partial username" value="{{ $initialAssignedTo }}" />
              </div>

              <div>
                <label class="ti-form-label">Created Date Range</label>
                <div class="grid grid-cols-2 gap-2">
                  <input id="tasks-date-from" type="date" class="ti-form-input w-full" value="{{ $initialDateFrom }}" />
                  <input id="tasks-date-to" type="date" class="ti-form-input w-full" value="{{ $initialDateTo }}" />
                </div>
                <div class="text-xs text-[#8c9097] mt-1">Filter by task creation date.</div>
              </div>
            </div>

            <div class="p-3 border-t border-defaultborder flex items-center justify-end gap-2">
              <button id="tasks-adv-reset" type="button" class="ti-btn ti-btn-light">Reset</button>
              <button id="tasks-adv-apply" type="button" class="ti-btn ti-btn-primary">Apply</button>
            </div>
          </div>
        </div>

        <button id="tasks-clear" type="button" class="ti-btn ti-btn-light">Clear</button>
      </div>
    </div>
  </div>

  <div class="box-body">
    <div class="overflow-auto table-bordered">
      <div id="tasks-table" class="ti-custom-table ti-striped-table ti-custom-table-hover"></div>
    </div>

    <div class="mt-2 flex items-center justify-between text-xs text-[#8c9097]">
      <div id="tasks-info"></div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
  <script src="{{ asset('build/assets/libs/tabulator-tables/js/tabulator.min.js') }}"></script>

  <script>
    window.__tasks = {
      ajaxUrl: @json(route('tasks.data')),
      showUrlTemplate: @json(route('tasks.show', ['id' => '__ID__'])),
      claimUrlTemplate: @json(route('tasks.claim', ['id' => '__ID__'])),
      archiveUrlTemplate: @json(route('tasks.destroy', ['id' => '__ID__'])),
      restoreUrlTemplate: @json(route('tasks.restore', ['id' => '__ID__'])),
      csrf: @json(csrf_token()),
      canViewAll: @json((bool) $canViewAll),
      canArchive: @json((bool) $canArchive),
    };
  </script>
@endpush
