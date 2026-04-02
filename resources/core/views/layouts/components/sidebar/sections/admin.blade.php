@if($showPlatformAdminSection ?? false)
  <li class="slide__category">
    <span class="category-name">Core Platform</span>
  </li>

  <li class="slide has-sub">
    <a href="javascript:void(0);" class="side-menu__item">
      <i class="bi bi-people side-menu__icon"></i>
      <span class="side-menu__label">Platform Administration</span>
      <i class="fe fe-chevron-right side-menu__angle"></i>
    </a>

    <ul class="slide-menu child1">
      <li class="slide side-menu__label1">
        <a href="javascript:void(0)">Platform Administration</a>
      </li>

      @if($canViewPlatformUsers ?? false)
        <li class="slide">
          <a href="{{ route('access.users.index') }}" class="side-menu__item">
            Users
          </a>
        </li>
      @endif

      @if($canViewPlatformRoles ?? false)
        <li class="slide">
          <a href="{{ route('access.roles.index') }}" class="side-menu__item">
            Roles
          </a>
        </li>
      @endif

      @if($canViewPlatformPermissions ?? false)
        <li class="slide">
          <a href="{{ route('access.permissions.index') }}" class="side-menu__item">
            Permissions
          </a>
        </li>
      @endif

      @if($canRegisterUser)
        <li class="slide">
          <a href="{{ route('access.users.create') }}" class="side-menu__item">
            Onboard User
          </a>
        </li>
      @endif

      @if($canViewPlatformLoginLogs ?? false)
        <li class="slide">
          <a href="{{ route('logs.index') }}" class="side-menu__item">
            Login Logs
          </a>
        </li>
      @endif

      @if($canViewPlatformAuditLogs ?? false)
        <li class="slide">
          <a href="{{ route('audit-logs.index') }}" class="side-menu__item">
            Audit Logs
          </a>
        </li>
      @endif

      @if($canViewDriveWorkspace ?? false)
        <li class="slide">
          <a href="{{ route('drive.index') }}" class="side-menu__item">
            Google Drive
          </a>
        </li>
      @endif

      @if($canViewWorkflowNotificationsWorkspace ?? false)
        <li class="slide">
          <a href="{{ route('workflow-notifications.index') }}" class="side-menu__item">
            Workflow Notifications
          </a>
        </li>
      @endif
    </ul>
  </li>
@endif
