@extends('layouts.master')

@php($adminRoutes = $adminRoutes ?? app(\App\Core\Support\AdminRouteResolver::class))
@php($moduleScopedAccess = $adminRoutes->isModuleScoped())
@php($moduleContextName = trim((string) ($currentModule->name ?? $adminRoutes->scopedModuleCode() ?? 'Module')) ?: 'Module')
@php($canCreateUser = \Illuminate\Support\Facades\Route::has($adminRoutes->routeName('access.users.create')))
@php($onboardingCta = $moduleScopedAccess ? 'Add Staff' : 'Onboard User')
@php($platformModules = $platformAccessModules ?? [])

@section('styles')
  <link rel="stylesheet" href="{{ asset('build/assets/libs/tabulator-tables/css/tabulator.min.css') }}">
  <style>
    .items-header{display:flex;align-items:center;justify-content:space-between;gap:12px;width:100%}
    .items-actions{display:flex;align-items:center;gap:8px;flex-wrap:wrap;justify-content:flex-end}
    .box-header{overflow:visible!important}
    .tabulator.is-loading{opacity:.65;pointer-events:none}
    .users-access-panel{position:fixed;inset:0;z-index:1050;display:none}
    .users-access-panel.is-open{display:block}
    .users-access-panel__backdrop{position:absolute;inset:0;background:rgba(15,23,42,.45)}
    .users-access-panel__drawer{position:absolute;top:0;right:0;height:100%;width:min(560px,100%);background:#fff;box-shadow:-12px 0 32px rgba(15,23,42,.18);display:flex;flex-direction:column}
    .dark .users-access-panel__drawer{background:rgb(var(--body-bg,255 255 255))}
    .users-access-panel__header{padding:18px 20px;border-bottom:1px solid rgba(148,163,184,.18);display:flex;align-items:flex-start;justify-content:space-between;gap:12px}
    .users-access-panel__body{padding:20px;overflow:auto;display:flex;flex-direction:column;gap:16px}
    .users-access-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:12px}
    .users-access-stat{border:1px solid rgba(148,163,184,.18);border-radius:12px;padding:14px;background:#fff}
    .dark .users-access-stat{background:transparent}
    .users-access-modules{display:flex;flex-direction:column;gap:12px}
    .users-access-module{border:1px solid rgba(148,163,184,.18);border-radius:14px;padding:14px;background:#fff}
    .dark .users-access-module{background:transparent}
    .users-access-module__meta{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:10px;margin-top:12px}
    .users-access-label{display:block;font-size:11px;letter-spacing:.08em;text-transform:uppercase;color:#64748b;margin-bottom:4px}
    .users-access-value{font-size:14px;color:#0f172a}
    .dark .users-access-value{color:#e5e7eb}
    .users-access-chip{display:inline-flex;align-items:center;gap:6px;padding:4px 10px;border-radius:999px;font-size:11px;font-weight:600;background:rgba(99,102,241,.08);color:#4f46e5}
    .users-access-chip--success{background:rgba(34,197,94,.12);color:#15803d}
    .users-access-chip--danger{background:rgba(239,68,68,.12);color:#b91c1c}
    .users-access-chip--muted{background:rgba(148,163,184,.14);color:#475569}
    .users-access-empty{border:1px dashed rgba(148,163,184,.35);border-radius:14px;padding:18px;text-align:center;color:#64748b}
    @media (max-width: 767px){
      .items-header{flex-direction:column;align-items:stretch}
      .items-actions{justify-content:stretch}
      .items-actions > *{width:100%}
      .users-access-grid,.users-access-module__meta{grid-template-columns:1fr}
    }
  </style>
@endsection

@section('content')
<div class="block justify-between page-header md:flex">
  <div>
    <h3 class="!text-defaulttextcolor dark:!text-defaulttextcolor/70 dark:text-white text-[1.125rem] font-semibold">
      {{ $moduleScopedAccess ? 'Assigned Users' : 'Platform Users' }}
    </h3>
    <p class="text-textmuted dark:text-textmuted/80 mb-0">
      {{ $moduleScopedAccess
          ? $moduleContextName . ' only. Global identities and account lifecycle stay in Core Platform.'
          : 'Review shared platform identities, cross-module access, and lifecycle status from one Core workspace.' }}
    </p>
  </div>
</div>

<div
  id="users-flash-state"
  class="hidden"
  data-kind="{{ session('success') ? 'success' : (session('info') ? 'info' : '') }}"
  data-message="{{ session('success') ?: session('info') ?: '' }}"
></div>

<div class="box">
  <div class="box-header">
    <div class="items-header">
      <h5 class="box-title">{{ $moduleScopedAccess ? 'Assigned Users' : 'Identity & Access Overview' }}</h5>

      <div class="items-actions">
        <input
          id="users-search"
          type="text"
          class="form-control !w-[320px] !rounded-md"
          placeholder="{{ $moduleScopedAccess ? 'Search user/email/role...' : 'Search name, email, username, module, or role...' }}"
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

              @if ($moduleScopedAccess)
                <div>
                  <label class="ti-form-label">Status</label>
                  <select id="users-status" class="ti-form-input w-full">
                    <option value="">All</option>
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                  </select>
                  <div class="text-xs text-[#8c9097] mt-1">Filter by module access status.</div>
                </div>

                <div>
                  <label class="ti-form-label">Role</label>
                  <input id="users-role" type="text" class="ti-form-input w-full" placeholder="Exact/partial role name" />
                </div>
              @else
                <div>
                  <label class="ti-form-label">Platform Status</label>
                  <select id="users-platform-status" class="ti-form-input w-full">
                    <option value="">All</option>
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                  </select>
                  <div class="text-xs text-[#8c9097] mt-1">Core lifecycle status for the shared platform identity.</div>
                </div>

                <div>
                  <label class="ti-form-label">Module Access</label>
                  <select id="users-module-access" class="ti-form-input w-full">
                    <option value="">All modules</option>
                    @foreach ($platformModules as $module)
                      <option value="{{ $module['id'] }}">{{ $module['label'] }}</option>
                    @endforeach
                  </select>
                  <div class="text-xs text-[#8c9097] mt-1">Filter identities with active access to a specific module.</div>
                </div>

                <div class="grid grid-cols-2 gap-2">
                  <div>
                    <label class="ti-form-label">No Module Access</label>
                    <select id="users-no-module-access" class="ti-form-input w-full">
                      <option value="">All</option>
                      <option value="1">Only unassigned</option>
                    </select>
                  </div>

                  <div>
                    <label class="ti-form-label">Multi-Module Users</label>
                    <select id="users-multi-module-only" class="ti-form-input w-full">
                      <option value="">All</option>
                      <option value="1">Only multi-module</option>
                    </select>
                  </div>
                </div>

                <div>
                  <label class="ti-form-label">Module Role</label>
                  <input id="users-module-role" type="text" class="ti-form-input w-full" placeholder="Exact/partial role across modules" />
                  <div class="text-xs text-[#8c9097] mt-1">Search for users holding a role in any active module assignment.</div>
                </div>
              @endif

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

        @if ($canCreateUser)
          <a href="{{ $adminRoutes->route('access.users.create') }}" class="ti-btn ti-btn-primary">
            {{ $onboardingCta }}
          </a>
        @endif
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

@unless ($moduleScopedAccess)
  <div id="users-access-panel" class="users-access-panel" aria-hidden="true">
    <div class="users-access-panel__backdrop" data-users-access-close></div>

    <aside class="users-access-panel__drawer" role="dialog" aria-modal="true" aria-labelledby="users-access-title">
      <div class="users-access-panel__header">
        <div>
          <div class="text-[11px] uppercase tracking-[0.12em] text-primary font-semibold">Platform Access</div>
          <h5 id="users-access-title" class="text-lg font-semibold text-defaulttextcolor dark:text-white mb-1">User Access Overview</h5>
          <p id="users-access-subtitle" class="text-sm text-textmuted mb-0">Cross-module identity, department scope, and role assignments.</p>
        </div>

        <button id="users-access-close" type="button" class="ti-btn ti-btn-icon ti-btn-light" aria-label="Close access overview">
          <i class="ri-close-line"></i>
        </button>
      </div>

      <div class="users-access-panel__body">
        <div id="users-access-loading" class="users-access-empty">Loading access details...</div>
        <div id="users-access-body" class="hidden"></div>
      </div>
    </aside>
  </div>
@endunless
@endsection

@push('scripts')
  <script src="{{ asset('build/assets/libs/tabulator-tables/js/tabulator.min.js') }}"></script>

  <script>
    window.__accessUsers = {
      ajaxUrl: @json($adminRoutes->route('access.users.data')),
      moduleScoped: @json($moduleScopedAccess),
      moduleContextName: @json($moduleContextName),
      platformModules: @json($platformModules),
    };
  </script>
@endpush
