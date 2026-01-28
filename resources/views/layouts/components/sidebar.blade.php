<aside class="app-sidebar" id="sidebar">
  <!-- Header -->
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
      <!-- keep both arrows for the vendor menu scripts -->
      <div class="slide-left" id="slide-left">
        <svg xmlns="http://www.w3.org/2000/svg" fill="#7b8191" width="24" height="24" viewBox="0 0 24 24">
          <path d="M13.293 6.293 7.586 12l5.707 5.707 1.414-1.414L10.414 12l4.293-4.293z"></path>
        </svg>
      </div>


@php
  $u = auth()->user();
  $isAdmin = $u && $u->hasRole('Administrator');
  $canInventoryInspection = $u && $u->hasAnyRole(['Administrator', 'Inspector', 'Staff']);
  $canTasks = $u && $u->hasAnyRole(['Administrator', 'Staff']);
@endphp

<ul class="main-menu">

  @if($canTasks)
    <li class="slide__category">
      <span class="category-name">Tasks</span>
    </li>

    <!-- Start::slide -->
    <li class="slide has-sub">
      <a href="javascript:void(0);" class="side-menu__item">
        <i class="bx bx-task side-menu__icon"></i>
        <span class="side-menu__label">Tasks</span>
        <i class="fe fe-chevron-right side-menu__angle"></i>
      </a>

      <ul class="slide-menu child1">
        @php($canAll = auth()->user()?->hasRole('Administrator'))

        @if($canAll)
          <li class="slide">
            <a href="{{ route('tasks.index', ['scope' => 'all']) }}" class="side-menu__item">
              All (Admininstrator)
            </a>
          </li>
        @endif

        <li class="slide">
          <a href="{{ route('tasks.index', ['scope' => 'mine']) }}" class="side-menu__item">
            Assigned to Me
            @if(($taskCounts['my'] ?? 0) > 0)
              <span class="text-success text-[0.75em] badge !py-[0.25rem] !px-[0.45rem] rounded-sm bg-success/10 ms-2">
                {{ $taskCounts['my'] }}
              </span>
            @endif
          </a>
        </li>

        <li class="slide">
          <a href="{{ route('tasks.index', ['scope' => 'available']) }}" class="side-menu__item">
            Available to Claim
            @if(($taskCounts['claimable'] ?? 0) > 0)
              <span class="text-success text-[0.75em] badge !py-[0.25rem] !px-[0.45rem] rounded-sm bg-success/10 ms-2">
                {{ $taskCounts['claimable'] }}
              </span>
            @endif
          </a>
        </li>
      </ul>
    </li>
    <!-- End::slide -->
  @endif
                             
  @if ($isAdmin)
    <li class="slide__category">
      <span class="category-name">Users &amp; Permission</span>
    </li>

    <li class="slide has-sub">
      <a href="javascript:void(0);" class="side-menu__item">
        <i class="bi bi-people side-menu__icon"></i>
        <span class="side-menu__label">User / Permissions</span>
        <i class="fe fe-chevron-right side-menu__angle"></i>
      </a>

      <ul class="slide-menu child1">
        <li class="slide side-menu__label1">
          <a href="javascript:void(0)">User Permissions</a>
        </li>

        <li class="slide">
          <a href="{{ route('users.permissions.index') }}" class="side-menu__item">
            Users
          </a>
        </li>

        <li class="slide">
          <a href="{{ route('roles.index') }}" class="side-menu__item">
            Roles
          </a>
        </li>

        <li class="slide">
          <a href="{{ route('permissions.index') }}" class="side-menu__item">
            Permissions
          </a>
        </li>

        <li class="slide">
          <a href="{{ route('sign-up') }}" class="side-menu__item">
            User Registration
          </a>
        </li>

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
</ul>



      <div class="slide-right" id="slide-right">
        <svg xmlns="http://www.w3.org/2000/svg" fill="#7b8191" width="24" height="24" viewBox="0 0 24 24">
          <path d="M10.707 17.707 16.414 12l-5.707-5.707-1.414 1.414L13.586 12l-4.293 4.293z"></path>
        </svg>
      </div>
    </nav>
  </div>
</aside>
