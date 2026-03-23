@extends('layouts.master')

@section('styles')
  <link rel="stylesheet" href="{{ asset('build/assets/libs/tabulator-tables/css/tabulator.min.css') }}">
  <style>
    .tasks-page-grid {
      align-items: start;
    }

    .tasks-header {
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 12px;
      width: 100%;
    }

    .tasks-actions {
      display: flex;
      align-items: center;
      gap: 8px;
      flex-wrap: wrap;
      justify-content: flex-end;
    }

    .box-header {
      overflow: visible !important;
    }

    .tabulator.is-loading {
      opacity: 0.65;
      pointer-events: none;
    }

    .task-stats-card {
      display: flex;
      align-items: flex-start;
      gap: 1rem;
    }

    .task-stats-icon {
      width: 3rem;
      height: 3rem;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      border-radius: 9999px;
      flex-shrink: 0;
      font-size: 1.125rem;
    }

    .task-stats-value {
      font-size: 1.25rem;
      font-weight: 700;
      line-height: 1;
    }

    .task-stats-note {
      color: #8c9097;
      font-size: 0.75rem;
      margin-top: 0.375rem;
    }

    .task-stats-chart {
      min-height: 260px;
    }

    .admin-task-card {
      display: flex;
      align-items: flex-start;
      justify-content: space-between;
      gap: 1rem;
      padding: 1.25rem 1.5rem;
      border-bottom: 1px dashed #f2f5f7;
    }

    .admin-task-card:last-child {
      border-bottom: 0;
    }

    .admin-task-main {
      display: flex;
      align-items: flex-start;
      gap: 1rem;
      min-width: 0;
      flex: 1 1 auto;
    }

    .admin-task-icon {
      width: 3rem;
      height: 3rem;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      border-radius: 0.875rem;
      flex-shrink: 0;
      font-size: 1.125rem;
    }

    .admin-task-label {
      font-size: 0.8125rem;
      font-weight: 500;
      color: #4b5563;
      margin-bottom: 0.25rem;
    }

    .admin-task-value {
      font-size: 1.75rem;
      line-height: 1;
      font-weight: 700;
      color: #111827;
    }

    .admin-task-trend {
      display: flex;
      align-items: center;
      gap: 0.25rem;
      font-size: 0.75rem;
      margin-top: 0.5rem;
      line-height: 1;
      flex-wrap: wrap;
    }

    .admin-task-side {
      min-width: 92px;
      text-align: right;
      flex-shrink: 0;
    }

    .admin-task-side-note {
      font-size: 0.75rem;
      color: #8c9097;
      margin-top: 0.375rem;
    }

    .task-admin-chart {
      min-height: 290px;
    }

    @media (max-width: 991px) {
      .admin-task-card {
        flex-direction: column;
      }

      .admin-task-side {
        width: 100%;
        text-align: left;
      }
    }

    @media (max-width: 767px) {
      .tasks-header {
        flex-direction: column;
        align-items: stretch;
      }

      .tasks-actions {
        width: 100%;
        justify-content: stretch;
      }

      #tasks-search {
        width: 100% !important;
      }
    }
  </style>
@endsection

@section('content')
<div class="block justify-between page-header md:flex">
  <div>
    <h3 class="!text-defaulttextcolor dark:!text-defaulttextcolor/70 dark:text-white text-[1.125rem] font-semibold">
      Work Queue
    </h3>
    <p class="text-[#8c9097] text-sm mt-1">Track assigned work, claimable tasks, and archived records from every accessible module in one shared queue.</p>
  </div>
  <ol class="flex items-center whitespace-nowrap min-w-0 mt-2 md:mt-0">
    <li class="text-[0.813rem] ps-[0.5rem]">
      <a class="flex items-center text-primary hover:text-primary truncate" href="javascript:void(0);">
        Shared Workspace
        <i class="ti ti-chevrons-right flex-shrink-0 text-[#8c9097] px-[0.5rem] overflow-visible rtl:rotate-180"></i>
      </a>
    </li>
    <li class="text-[0.813rem] text-defaulttextcolor font-semibold" aria-current="page">
      My Tasks
    </li>
  </ol>
</div>

@php($canViewAll = auth()->user()?->hasAnyRole(['Administrator', 'admin']) || auth()->user()?->can('view All Tasks'))
@php($canArchive = auth()->user()?->hasAnyRole(['Administrator', 'admin']))
@php($isAdminStatsUser = auth()->user()?->hasAnyRole(['Administrator', 'admin']))
@php($initialSearch = request('search', request('q', '')))
@php($initialArchivedRaw = request('archived', 'active'))
@php($initialArchived = in_array($initialArchivedRaw, ['active', 'archived', 'all'], true) ? $initialArchivedRaw : 'active')
@php($initialScopeRaw = request('scope', 'mine'))
@php($initialScope = (!$canViewAll && $initialScopeRaw === 'all') ? 'mine' : $initialScopeRaw)
@php($initialStatus = request('status', ''))
@php($initialAssignedTo = request('assigned_to', ''))
@php($initialDateFrom = request('date_from', ''))
@php($initialDateTo = request('date_to', ''))
@php($initialModuleId = request('module_id', ''))
@php($taskOwnerModules = collect($ownerModules ?? []))
@php($sidebarTaskCounts = is_array($taskCounts ?? null) ? $taskCounts : ['my' => 0, 'claimable' => 0])
@php($adminTaskCards = is_array(data_get($adminTaskStats ?? null, 'cards')) ? data_get($adminTaskStats, 'cards') : [])
@php($adminPeriodLabel = (string) data_get($adminTaskStats ?? null, 'period_label', 'Last 6 months'))
@php($adminCardMeta = [
  'new' => [
    'label' => 'New Tasks',
    'icon' => 'ri-file-list-3-line',
    'icon_class' => 'bg-primary/10 text-primary',
    'badge_class' => 'bg-primary text-white',
  ],
  'completed' => [
    'label' => 'Completed Tasks',
    'icon' => 'ri-checkbox-circle-line',
    'icon_class' => 'bg-success/10 text-success',
    'badge_class' => 'bg-success text-white',
  ],
  'pending' => [
    'label' => 'Pending Tasks',
    'icon' => 'ri-loader-4-line',
    'icon_class' => 'bg-warning/10 text-warning',
    'badge_class' => 'bg-warning text-white',
  ],
  'in_progress' => [
    'label' => 'Inprogress Tasks',
    'icon' => 'ri-timer-flash-line',
    'icon_class' => 'bg-info/10 text-info',
    'badge_class' => 'bg-info text-white',
  ],
])

<div class="grid grid-cols-12 gap-6 tasks-page-grid">
  <div class="xl:col-span-9 col-span-12">
    <div class="box">
      <div class="box-header">
        <div class="tasks-header">
          <div>
            <h5 class="box-title">Task Queue</h5>
            <p class="text-[#8c9097] text-xs mt-1">Tasks remain owned by their origin modules, but the inbox itself is shared across your accessible workflows.</p>
          </div>

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
                    <label class="ti-form-label">Origin Module</label>
                    <select id="tasks-module" class="ti-form-input w-full">
                      <option value="" @selected($initialModuleId === '')>All accessible modules</option>
                      @foreach($taskOwnerModules as $ownerModule)
                        <option
                          value="{{ (string) data_get($ownerModule, 'id') }}"
                          @selected($initialModuleId === (string) data_get($ownerModule, 'id'))
                        >
                          {{ (string) data_get($ownerModule, 'name') }}
                        </option>
                      @endforeach
                    </select>
                    <div class="text-xs text-[#8c9097] mt-1">Tasks keep their owner module even though this queue is shared.</div>
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
  </div>

  <div class="xl:col-span-3 col-span-12">
    @if($isAdminStatsUser)
      <div id="tasks-admin-stats-panel" class="box">
        <div class="box-body !p-0">
          @foreach(['new', 'completed', 'pending', 'in_progress'] as $cardKey)
            @php($meta = $adminCardMeta[$cardKey])
            @php($card = $adminTaskCards[$cardKey] ?? [
              'value' => 0,
              'comparison_value' => 0,
              'comparison_label' => 'Prev Month',
              'context_label' => 'this month',
              'delta_percent' => 0,
              'direction' => 'flat',
            ])
            @php($trendDirection = (string) ($card['direction'] ?? 'flat'))
            @php($trendClass = $trendDirection === 'up' ? 'text-success' : ($trendDirection === 'down' ? 'text-danger' : 'text-[#8c9097]'))
            <div class="admin-task-card">
              <div class="admin-task-main">
                <div class="admin-task-icon {{ $meta['icon_class'] }}">
                  <i class="{{ $meta['icon'] }}"></i>
                </div>

                <div>
                  <div class="admin-task-label">{{ $meta['label'] }}</div>
                  <div class="admin-task-value">{{ number_format((int) ($card['value'] ?? 0)) }}</div>
                  <div class="admin-task-trend {{ $trendClass }}">
                    @if($trendDirection === 'up')
                      <i class="ri-arrow-up-s-line"></i>
                    @elseif($trendDirection === 'down')
                      <i class="ri-arrow-down-s-line"></i>
                    @else
                      <i class="ri-subtract-line"></i>
                    @endif
                    <span>{{ number_format((float) ($card['delta_percent'] ?? 0), 2) }}%</span>
                    <span class="text-[#8c9097]">{{ (string) ($card['context_label'] ?? 'vs previous month') }}</span>
                  </div>
                </div>
              </div>

              <div class="admin-task-side">
                <span class="badge {{ $meta['badge_class'] }} font-semibold">{{ number_format((int) ($card['comparison_value'] ?? 0)) }}</span>
                <div class="admin-task-side-note">{{ (string) ($card['comparison_label'] ?? 'Prev Month') }}</div>
              </div>
            </div>
          @endforeach

          <div class="p-6 pt-5">
            <div class="flex items-start justify-between gap-3">
              <div>
                <p class="text-[.9375rem] font-semibold mb-1">Task Statistics <span class="font-normal text-[#8c9097]">({{ $adminPeriodLabel }})</span></p>
                <p class="text-[#8c9097] text-xs">Administrator view of monthly task flow and open-task snapshots.</p>
              </div>
              <span class="badge bg-light text-defaulttextcolor">Admin</span>
            </div>
            <div id="task-admin-stats-chart" class="task-admin-chart mt-4"></div>
          </div>
        </div>
      </div>
    @else
      <div id="tasks-stats-panel" class="box">
        <div class="box-body !p-0">
          <div class="p-6 border-b dark:border-defaultborder/10 border-dashed task-stats-card">
            <div class="task-stats-icon bg-primary/10 text-primary">
              <i class="ri-task-line"></i>
            </div>
            <div class="flex-grow">
              <div class="flex items-center justify-between gap-3">
                <h6 class="!mb-1 text-[0.75rem]">Visible Tasks</h6>
                <span class="badge bg-primary text-white font-semibold">Live</span>
              </div>
              <div class="task-stats-value" id="tasks-visible-total">0</div>
              <p class="task-stats-note">Updates with the current filter set.</p>
            </div>
          </div>

          <div class="p-6 border-b dark:border-defaultborder/10 border-dashed task-stats-card">
            <div class="task-stats-icon bg-success/10 text-success">
              <i class="ri-user-star-line"></i>
            </div>
            <div class="flex-grow">
              <div class="flex items-center justify-between gap-3">
                <h6 class="!mb-1 text-[0.75rem]">My Active Tasks</h6>
                <span class="badge bg-success text-white font-semibold" id="tasks-my-badge">{{ (int) ($sidebarTaskCounts['my'] ?? 0) }}</span>
              </div>
              <div class="task-stats-value" id="tasks-my-total">{{ (int) ($sidebarTaskCounts['my'] ?? 0) }}</div>
              <p class="task-stats-note">Shared from the global task count composer.</p>
            </div>
          </div>

          <div class="p-6 border-b dark:border-defaultborder/10 border-dashed task-stats-card">
            <div class="task-stats-icon bg-warning/10 text-warning">
              <i class="ri-hand-heart-line"></i>
            </div>
            <div class="flex-grow">
              <div class="flex items-center justify-between gap-3">
                <h6 class="!mb-1 text-[0.75rem]">Claimable Tasks</h6>
                <span class="badge bg-warning text-white font-semibold" id="tasks-claimable-badge">{{ (int) ($sidebarTaskCounts['claimable'] ?? 0) }}</span>
              </div>
              <div class="task-stats-value" id="tasks-claimable-total">{{ (int) ($sidebarTaskCounts['claimable'] ?? 0) }}</div>
              <p class="task-stats-note">Open tasks currently eligible for your roles.</p>
            </div>
          </div>

          <div class="p-6 border-b dark:border-defaultborder/10 border-dashed task-stats-card">
            <div class="task-stats-icon bg-secondary/10 text-secondary">
              <i class="ri-layout-row-line"></i>
            </div>
            <div class="flex-grow">
              <div class="flex items-center justify-between gap-3">
                <h6 class="!mb-1 text-[0.75rem]">Rows On Page</h6>
                <span class="badge bg-secondary text-white font-semibold">Page</span>
              </div>
              <div class="task-stats-value" id="tasks-page-total">0</div>
              <p class="task-stats-note">Helps compare the current page with the total result set.</p>
            </div>
          </div>

          <div class="p-6 pb-2">
            <div class="flex items-start justify-between gap-3">
              <div>
                <p class="text-[.9375rem] font-semibold mb-1">Task Statistics</p>
                <p id="tasks-stats-caption" class="text-[#8c9097] text-xs">Live snapshot from the current table filters.</p>
              </div>
              <span id="tasks-filters-active" class="badge bg-primary/10 text-primary">0 filters</span>
            </div>
            <div id="task-list-stats" class="task-stats-chart mt-4"></div>
          </div>
        </div>
      </div>
    @endif
  </div>
</div>
@endsection

@push('scripts')
  <script src="{{ asset('build/assets/libs/tabulator-tables/js/tabulator.min.js') }}"></script>
  <script src="{{ asset('build/assets/libs/apexcharts/apexcharts.min.js') }}"></script>

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
      isAdminStatsUser: @json((bool) $isAdminStatsUser),
      ownerModules: @json($taskOwnerModules->values()->all()),
      sidebarCounts: @json([
        'my' => (int) ($sidebarTaskCounts['my'] ?? 0),
        'claimable' => (int) ($sidebarTaskCounts['claimable'] ?? 0),
      ]),
      adminStats: @json($isAdminStatsUser ? $adminTaskStats : null),
    };
  </script>
@endpush
