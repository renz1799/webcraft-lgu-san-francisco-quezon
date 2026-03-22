@extends('layouts.master')

@php($adminRoutes = $adminRoutes ?? app(\App\Core\Support\AdminRouteResolver::class))
@php($moduleScopedAccess = $adminRoutes->isModuleScoped())
@php($moduleContextName = trim((string) ($currentModule->name ?? $adminRoutes->scopedModuleCode() ?? 'Module')) ?: 'Module')
@php($auditAuthorizer = app(\App\Core\Support\AdminContextAuthorizer::class))

@section('styles')
  <link rel="stylesheet" href="{{ asset('build/assets/libs/tabulator-tables/css/tabulator.min.css') }}">
  <style>
    .items-header{display:flex;align-items:center;justify-content:space-between;gap:12px;width:100%}
    .items-actions{display:flex;align-items:center;gap:8px;flex-wrap:wrap;justify-content:flex-end}
    .box-header{overflow:visible!important}
    .tabulator.is-loading{opacity:.65;pointer-events:none}
    .audit-message-cell{white-space:normal;line-height:1.45}
  </style>
@endsection

@section('content')
<div class="block justify-between page-header md:flex">
  <div>
    <h3 class="!text-defaulttextcolor dark:!text-defaulttextcolor/70 dark:text-white text-[1.125rem] font-semibold">
      {{ $moduleScopedAccess ? $moduleContextName . ' Audit Logs' : 'Audit Logs' }}
    </h3>
    <p class="text-textmuted dark:text-textmuted/80 mb-0">
      {{ $moduleScopedAccess
          ? 'Showing activity recorded inside ' . $moduleContextName . ' only.'
          : 'Review platform-wide audit activity and restoration events.' }}
    </p>
  </div>
</div>

<div class="box">
  <div class="box-header">
    <div class="items-header">
      <h5 class="box-title">{{ $moduleScopedAccess ? 'Module Activity' : 'System Activity' }}</h5>

      <div class="items-actions">
        <input
          id="audit-search"
          type="text"
          class="form-control !w-[320px] !rounded-md"
          placeholder="Search message/action/user/ip..."
        />

        <div class="relative shrink-0">
          <button id="audit-more-btn" type="button" class="ti-btn ti-btn-light">
            More Filters
            <span id="audit-adv-count"
              class="hidden ms-2 inline-flex items-center justify-center text-[10px] leading-none px-2 py-1 rounded-full bg-primary/10 text-primary">
              0
            </span>
            <i class="ri-arrow-down-s-line ms-1"></i>
          </button>

          <div id="audit-more-panel"
            class="hidden absolute right-0 mt-2 w-[380px] z-[9999] rounded-md border border-defaultborder bg-white dark:bg-bodybg shadow-lg">

            <div class="p-3 border-b border-defaultborder flex items-center justify-between">
              <div class="text-sm font-semibold text-defaulttextcolor dark:text-white">Advanced Filters</div>
              <button id="audit-more-close" type="button" class="ti-btn ti-btn-sm ti-btn-light">
                <i class="ri-close-line"></i>
              </button>
            </div>

            <div class="p-3 space-y-3">
              <div>
                <label class="ti-form-label">Module</label>
                @if ($adminRoutes->isModuleScoped())
                  <input
                    id="audit-module"
                    type="text"
                    class="ti-form-input w-full"
                    value="{{ strtoupper((string) $adminRoutes->scopedModuleCode()) }}"
                    readonly
                  />
                  <div class="text-xs text-[#8c9097] mt-1">Locked to the active module context.</div>
                @else
                  <input id="audit-module" type="text" class="ti-form-input w-full" placeholder="Code or name" />
                  <div class="text-xs text-[#8c9097] mt-1">Matches module code or module name.</div>
                @endif
              </div>

              <div>
                <label class="ti-form-label">Action</label>
                <input id="audit-action" type="text" class="ti-form-input w-full" placeholder="e.g. user.role.changed" />
                <div class="text-xs text-[#8c9097] mt-1">Matches audit action text.</div>
              </div>

              <div>
                <label class="ti-form-label">Actor ID</label>
                <input id="audit-actor-id" type="text" class="ti-form-input w-full" placeholder="User UUID" />
                <div class="text-xs text-[#8c9097] mt-1">Exact actor UUID filter.</div>
              </div>

              <div>
                <label class="ti-form-label">Subject Type</label>
                <select id="audit-subject-type" class="ti-form-input w-full">
                  <option value="">All</option>
                  <option value="user">User</option>
                  <option value="permission">Permission</option>
                  <option value="role">Role</option>
                </select>
                <div class="text-xs text-[#8c9097] mt-1">Filter by subject model type.</div>
              </div>

              <div>
                <label class="ti-form-label">Date Range</label>
                <div class="grid grid-cols-2 gap-2">
                  <input id="audit-date-from" type="date" class="ti-form-input w-full" />
                  <input id="audit-date-to" type="date" class="ti-form-input w-full" />
                </div>
                <div class="text-xs text-[#8c9097] mt-1">Filters by audit timestamp (from-to).</div>
              </div>
            </div>

            <div class="p-3 border-t border-defaultborder flex items-center justify-end gap-2">
              <button id="audit-adv-reset" type="button" class="ti-btn ti-btn-light">Reset</button>
              <button id="audit-adv-apply" type="button" class="ti-btn ti-btn-primary">Apply</button>
            </div>
          </div>
        </div>

        <button id="audit-clear" type="button" class="ti-btn ti-btn-light">Clear</button>
      </div>
    </div>
  </div>

  <div class="box-body">
    <div class="overflow-auto table-bordered">
      <div id="audit-table" class="ti-custom-table ti-striped-table ti-custom-table-hover"></div>
    </div>

    <div class="mt-2 flex items-center justify-between text-xs text-[#8c9097]">
      <div id="audit-info"></div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
  <script src="{{ asset('build/assets/libs/tabulator-tables/js/tabulator.min.js') }}"></script>

  <script>
    window.__audit = {
      ajaxUrl: @json($adminRoutes->route('audit-logs.data')),
      restoreEndpoint: @json($adminRoutes->route('audit.restore')),
      csrf: @json(csrf_token()),
      moduleLocked: @json($adminRoutes->isModuleScoped()),
      canRestore: @json($auditAuthorizer->canRestoreCurrentContextAuditData(auth()->user())),
    };
  </script>
@endpush
