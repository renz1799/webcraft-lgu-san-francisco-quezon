<aside class="app-sidebar" id="sidebar">
  <div class="main-sidebar-header">
    <a href="{{ url('index') }}" class="header-logo">
      <img src="{{ asset('build/assets/images/brand-logos/desktop-logo.png') }}" alt="logo" class="desktop-logo">
      <img src="{{ asset('build/assets/images/brand-logos/toggle-logo.png') }}" alt="logo" class="toggle-logo">
      <img src="{{ asset('build/assets/images/brand-logos/desktop-dark.png') }}" alt="logo" class="desktop-dark">
      <img src="{{ asset('build/assets/images/brand-logos/toggle-dark.png') }}" alt="logo" class="toggle-dark">
      <img src="{{ asset('build/assets/images/brand-logos/desktop-white.png') }}" alt="logo" class="desktop-white">
      <img src="{{ asset('build/assets/images/brand-logos/toggle-white.png') }}" alt="logo" class="toggle-white">
    </a>
  </div>

  <div class="main-sidebar" id="sidebar-scroll">
    <nav class="main-menu-container nav nav-pills flex-column sub-open">
      <div class="slide-left" id="slide-left">
        <svg xmlns="http://www.w3.org/2000/svg" fill="#7b8191" width="24" height="24" viewBox="0 0 24 24">
          <path d="M13.293 6.293 7.586 12l5.707 5.707 1.414-1.414L10.414 12l4.293-4.293z"></path>
        </svg>
      </div>

@php
  $user = auth()->user();
  $adminAuthorizer = app(\App\Core\Support\AdminContextAuthorizer::class);
  $canRegisterUser = $adminAuthorizer->canRegisterUsers($user);
  $canViewPlatformUsers = $adminAuthorizer->allowsAnyPermission($user, ['users.view', 'users.manage_access']);
  $canViewPlatformRoles = $adminAuthorizer->allowsAnyPermission($user, ['roles.view', 'roles.create', 'roles.update', 'roles.archive', 'roles.restore']);
  $canViewPlatformPermissions = $adminAuthorizer->allowsAnyPermission($user, ['permissions.view', 'permissions.create', 'permissions.update', 'permissions.archive', 'permissions.restore']);
  $canViewPlatformLoginLogs = $adminAuthorizer->canViewPlatformLoginLogs($user);
  $canViewPlatformAuditLogs = $adminAuthorizer->canViewCurrentContextAuditLogs($user);
  $canViewDriveWorkspace = $adminAuthorizer->allowsAnyPermission($user, [
      'drive_connections.view',
      'drive_connections.connect',
      'drive_connections.disconnect',
      'drive_files.create',
  ]);
  $showPlatformAdminSection = $canViewPlatformUsers
      || $canViewPlatformRoles
      || $canViewPlatformPermissions
      || $canRegisterUser
      || $canViewPlatformLoginLogs
      || $canViewPlatformAuditLogs
      || $canViewDriveWorkspace;
  $adminRoutes = $adminRoutes ?? app(\App\Core\Support\AdminRouteResolver::class);
  $moduleLinks = $accessibleModules ?? collect();
  $currentModuleCode = strtoupper((string) ($currentModule->code ?? ''));
  $currentRouteName = (string) (request()->route()?->getName() ?? '');
  $isSharedCapabilityRoute = collect((array) config('modules.shared_capability_route_names', []))
      ->contains(fn ($pattern) => is_string($pattern) && $pattern !== '' && \Illuminate\Support\Str::is($pattern, $currentRouteName));
  $hasCurrentModuleAccess = $currentModuleCode !== ''
      && $moduleLinks->contains(fn ($module) => strtoupper((string) $module->code) === $currentModuleCode);
  $moduleSidebarView = null;
  $showCoreAdminSection = ! $adminRoutes->isModuleScoped() && ! $isSharedCapabilityRoute;

  if ($hasCurrentModuleAccess && ! $isSharedCapabilityRoute) {
      $candidateView = strtolower($currentModuleCode) . '::layouts.components.sidebar-menu';
      $moduleSidebarView = view()->exists($candidateView) ? $candidateView : null;
  }
@endphp

<ul class="main-menu">
  @include('layouts.components.sidebar.sections.module-switcher', [
      'moduleLinks' => $moduleLinks,
      'currentModule' => $currentModule,
  ])

  @if($moduleSidebarView)
    @include($moduleSidebarView)
  @endif

  @if($showCoreAdminSection)
    @include('layouts.components.sidebar.sections.admin', [
        'showPlatformAdminSection' => $showPlatformAdminSection,
        'canRegisterUser' => $canRegisterUser,
        'canViewPlatformUsers' => $canViewPlatformUsers,
        'canViewPlatformRoles' => $canViewPlatformRoles,
        'canViewPlatformPermissions' => $canViewPlatformPermissions,
        'canViewPlatformLoginLogs' => $canViewPlatformLoginLogs,
        'canViewPlatformAuditLogs' => $canViewPlatformAuditLogs,
        'canViewDriveWorkspace' => $canViewDriveWorkspace,
    ])
  @endif

</ul>

      <div class="slide-right" id="slide-right">
        <svg xmlns="http://www.w3.org/2000/svg" fill="#7b8191" width="24" height="24" viewBox="0 0 24 24">
          <path d="M10.707 17.707 16.414 12l-5.707-5.707-1.414 1.414L13.586 12l-4.293 4.293z"></path>
        </svg>
      </div>
    </nav>
  </div>
</aside>
