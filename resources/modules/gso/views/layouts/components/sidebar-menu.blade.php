<li class="slide__category">
  <span class="category-name">GSO Shell</span>
</li>

@php
  $sidebarUser = auth()->user();
  $adminAuthorizer = app(\App\Core\Support\AdminContextAuthorizer::class);
  $canManageGsoAccess = $adminAuthorizer->canManageCurrentContextAccess($sidebarUser);
  $canViewGsoAuditLogs = $adminAuthorizer->canViewCurrentContextAuditLogs($sidebarUser);
@endphp

<li class="slide has-sub">
  <a href="javascript:void(0);" class="side-menu__item">
    <i class="bx bx-buildings side-menu__icon"></i>
    <span class="side-menu__label">General Services Office</span>
    <i class="fe fe-chevron-right side-menu__angle"></i>
  </a>

  <ul class="slide-menu child1">
    <li class="slide">
      <a href="{{ route('gso.dashboard') }}" class="side-menu__item">Dashboard</a>
    </li>
    <li class="slide">
      <a href="{{ route('gso.items.index') }}" class="side-menu__item">Items</a>
    </li>
    <li class="slide">
      <a href="{{ route('gso.inventory-items.index') }}" class="side-menu__item">Inventory Items</a>
    </li>
    <li class="slide">
      <a href="{{ route('gso.inspections.index') }}" class="side-menu__item">Inspections</a>
    </li>
    <li class="slide">
      <a href="{{ route('gso.stocks.index') }}" class="side-menu__item">Stocks</a>
    </li>
    <li class="slide">
      <a href="{{ route('gso.accountable-officers.index') }}" class="side-menu__item">Accountable Officers</a>
    </li>
  </ul>
</li>

<li class="slide__category">
  <span class="category-name">Documents</span>
</li>

<li class="slide has-sub">
  <a href="javascript:void(0);" class="side-menu__item">
    <i class="bi bi-file-earmark-text side-menu__icon"></i>
    <span class="side-menu__label">Documents</span>
    <i class="fe fe-chevron-right side-menu__angle"></i>
  </a>

  <ul class="slide-menu child1">
    <li class="slide">
      <a href="{{ route('gso.air.index') }}" class="side-menu__item">AIR</a>
    </li>
    <li class="slide">
      <a href="{{ route('gso.ris.index') }}" class="side-menu__item">RIS</a>
    </li>
    <li class="slide">
      <a href="{{ route('gso.pars.index') }}" class="side-menu__item">PAR</a>
    </li>
    <li class="slide">
      <a href="{{ route('gso.ics.index') }}" class="side-menu__item">ICS</a>
    </li>
    <li class="slide">
      <a href="{{ route('gso.ptrs.index') }}" class="side-menu__item">PTR</a>
    </li>
    <li class="slide">
      <a href="{{ route('gso.itrs.index') }}" class="side-menu__item">ITR</a>
    </li>
    <li class="slide">
      <a href="{{ route('gso.wmrs.index') }}" class="side-menu__item">WMR</a>
    </li>
  </ul>
</li>

<li class="slide__category">
  <span class="category-name">Reference Data</span>
</li>

<li class="slide has-sub">
  <a href="javascript:void(0);" class="side-menu__item">
    <i class="bi bi-diagram-3 side-menu__icon"></i>
    <span class="side-menu__label">Reference Data</span>
    <i class="fe fe-chevron-right side-menu__angle"></i>
  </a>

  <ul class="slide-menu child1">
    <li class="slide">
      <a href="{{ route('gso.asset-types.index') }}" class="side-menu__item">Asset Types</a>
    </li>
    <li class="slide">
      <a href="{{ route('gso.asset-categories.index') }}" class="side-menu__item">Asset Categories</a>
    </li>
    <li class="slide">
      <a href="{{ route('gso.departments.index') }}" class="side-menu__item">Departments</a>
    </li>
    <li class="slide">
      <a href="{{ route('gso.fund-clusters.index') }}" class="side-menu__item">Fund Clusters</a>
    </li>
    <li class="slide">
      <a href="{{ route('gso.fund-sources.index') }}" class="side-menu__item">Fund Sources</a>
    </li>
  </ul>
</li>

@if ($canManageGsoAccess || $canViewGsoAuditLogs)
  <li class="slide__category">
    <span class="category-name">GSO Access</span>
  </li>

  <li class="slide has-sub">
    <a href="javascript:void(0);" class="side-menu__item">
      <i class="bx bx-shield-quarter side-menu__icon"></i>
      <span class="side-menu__label">GSO Access</span>
      <i class="fe fe-chevron-right side-menu__angle"></i>
    </a>

    <ul class="slide-menu child1">
      <li class="slide side-menu__label1">
        <a href="javascript:void(0)">Module Access Control</a>
      </li>

      @if ($canManageGsoAccess)
        <li class="slide">
          <a href="{{ route('gso.access.users.index') }}" class="side-menu__item">Assigned Users</a>
        </li>
        <li class="slide">
          <a href="{{ route('gso.access.roles.index') }}" class="side-menu__item">Roles</a>
        </li>
        <li class="slide">
          <a href="{{ route('gso.access.permissions.index') }}" class="side-menu__item">Permission Matrix</a>
        </li>
      @endif

      @if ($canViewGsoAuditLogs)
        <li class="slide">
          <a href="{{ route('gso.audit-logs.index') }}" class="side-menu__item">Audit Logs</a>
        </li>
      @endif
    </ul>
  </li>
@endif
