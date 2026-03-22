@if($isAdmin)
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

      <li class="slide">
        <a href="{{ route('access.users.index') }}" class="side-menu__item">
          Users
        </a>
      </li>

      <li class="slide">
        <a href="{{ route('access.roles.index') }}" class="side-menu__item">
          Roles
        </a>
      </li>

      <li class="slide">
        <a href="{{ route('access.permissions.index') }}" class="side-menu__item">
          Permissions
        </a>
      </li>

      @if($canRegisterUser)
        <li class="slide">
          <a href="javascript:void(0);" class="side-menu__item hs-dropdown-toggle" data-hs-overlay="#registerUserModal">
            User Registration
          </a>
        </li>
      @endif

      <li class="slide">
        <a href="{{ route('logs.index') }}" class="side-menu__item">
          Login Logs
        </a>
      </li>

      <li class="slide">
        <a href="{{ route('audit-logs.index') }}" class="side-menu__item">
          Audit Logs
        </a>
      </li>
    </ul>
  </li>
@endif
