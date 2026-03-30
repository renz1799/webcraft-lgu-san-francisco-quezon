<li class="slide__category">
  <span class="category-name">GSO Shell</span>
</li>

@php
  $sidebarUser = auth()->user();
  $adminAuthorizer = app(\App\Core\Support\AdminContextAuthorizer::class);
  $canManageGsoAccess = $adminAuthorizer->canManageCurrentContextAccess($sidebarUser);
  $canViewGsoAuditLogs = $adminAuthorizer->canViewCurrentContextAuditLogs($sidebarUser);
  $gsoSidebarTaskCounts = is_array($gsoTaskCounts ?? null) ? $gsoTaskCounts : [];
  $canTasksMenu = $adminAuthorizer->allowsAnyPermission($sidebarUser, [
      'tasks.view',
      'tasks.view_all',
      'tasks.claim',
      'tasks.comment',
      'tasks.update_status',
      'tasks.reassign',
  ]);
  $canAllTasks = $adminAuthorizer->allowsPermission($sidebarUser, 'tasks.view_all');
  $canViewAir = $adminAuthorizer->allowsAnyPermission($sidebarUser, [
      'air.view', 'air.create', 'air.update', 'air.inspect', 'air.manage_items',
      'air.manage_files', 'air.promote_inventory', 'air.finalize_inspection',
      'air.reopen_inspection', 'air.archive', 'air.restore', 'air.print',
  ]);
  $canViewRis = $adminAuthorizer->allowsAnyPermission($sidebarUser, [
      'ris.view', 'ris.create', 'ris.update', 'ris.submit', 'ris.approve',
      'ris.reject', 'ris.reopen', 'ris.revert', 'ris.archive', 'ris.restore',
      'ris.manage_items', 'ris.generate_from_air', 'ris.print',
  ]);
  $canViewPar = $adminAuthorizer->allowsAnyPermission($sidebarUser, [
      'par.view', 'par.create', 'par.update', 'par.submit', 'par.finalize',
      'par.reopen', 'par.archive', 'par.restore', 'par.manage_items', 'par.print',
  ]);
  $canViewIcs = $adminAuthorizer->allowsAnyPermission($sidebarUser, [
      'ics.view', 'ics.create', 'ics.update', 'ics.submit', 'ics.finalize',
      'ics.reopen', 'ics.archive', 'ics.restore', 'ics.manage_items', 'ics.print',
  ]);
  $canViewPtr = $adminAuthorizer->allowsAnyPermission($sidebarUser, [
      'ptr.view', 'ptr.create', 'ptr.update', 'ptr.submit', 'ptr.finalize',
      'ptr.reopen', 'ptr.archive', 'ptr.restore', 'ptr.manage_items', 'ptr.print',
  ]);
  $canViewItr = $adminAuthorizer->allowsAnyPermission($sidebarUser, [
      'itr.view', 'itr.create', 'itr.update', 'itr.submit', 'itr.finalize',
      'itr.reopen', 'itr.archive', 'itr.restore', 'itr.manage_items', 'itr.print',
  ]);
  $canViewWmr = $adminAuthorizer->allowsAnyPermission($sidebarUser, [
      'wmr.view', 'wmr.create', 'wmr.update', 'wmr.submit', 'wmr.approve',
      'wmr.finalize', 'wmr.reopen', 'wmr.archive', 'wmr.restore', 'wmr.manage_items', 'wmr.print',
  ]);
  $showDocumentsMenu = $canViewAir || $canViewRis || $canViewPar || $canViewIcs || $canViewPtr || $canViewItr || $canViewWmr;
  $canViewItems = $adminAuthorizer->allowsAnyPermission($sidebarUser, [
      'items.view', 'items.create', 'items.update', 'items.archive', 'items.restore',
  ]);
  $canViewInventoryItems = $adminAuthorizer->allowsAnyPermission($sidebarUser, [
      'inventory_items.view', 'inventory_items.create', 'inventory_items.update',
      'inventory_items.archive', 'inventory_items.restore', 'inventory_items.manage_files',
      'inventory_items.manage_events', 'inventory_items.import_from_inspection',
  ]);
  $canViewStocks = $adminAuthorizer->allowsAnyPermission($sidebarUser, [
      'stocks.view', 'stocks.adjust', 'stocks.view_ledger',
  ]);
  $showInventoryMenu = $canViewItems || $canViewInventoryItems || $canViewStocks;
  $canViewAssetTypes = $adminAuthorizer->allowsAnyPermission($sidebarUser, ['asset_types.view', 'asset_types.create', 'asset_types.update', 'asset_types.archive', 'asset_types.restore']);
  $canViewAssetCategories = $adminAuthorizer->allowsAnyPermission($sidebarUser, ['asset_categories.view', 'asset_categories.create', 'asset_categories.update', 'asset_categories.archive', 'asset_categories.restore']);
  $canViewDepartments = $adminAuthorizer->allowsAnyPermission($sidebarUser, ['departments.view', 'departments.create', 'departments.update', 'departments.archive', 'departments.restore']);
  $canViewFundClusters = $adminAuthorizer->allowsAnyPermission($sidebarUser, ['fund_clusters.view', 'fund_clusters.create', 'fund_clusters.update', 'fund_clusters.archive', 'fund_clusters.restore']);
  $canViewFundSources = $adminAuthorizer->allowsAnyPermission($sidebarUser, ['fund_sources.view', 'fund_sources.create', 'fund_sources.update', 'fund_sources.archive', 'fund_sources.restore']);
  $canViewAccountablePersons = $adminAuthorizer->allowsAnyPermission($sidebarUser, ['accountable_persons.view', 'accountable_persons.create', 'accountable_persons.update', 'accountable_persons.archive', 'accountable_persons.restore']);
  $showReferenceDataMenu = $canViewAssetTypes || $canViewAssetCategories || $canViewDepartments || $canViewFundClusters || $canViewFundSources || $canViewAccountablePersons;
  $canViewRpci = $adminAuthorizer->allowsAnyPermission($sidebarUser, ['reports.rpci.view', 'stocks.view', 'stocks.adjust']);
  $canViewRpcppe = $adminAuthorizer->allowsAnyPermission($sidebarUser, ['reports.rpcppe.view', 'inventory_items.view', 'inventory_items.update']);
  $canViewRpcsp = $adminAuthorizer->allowsAnyPermission($sidebarUser, ['reports.rpcsp.view', 'inventory_items.view', 'inventory_items.update']);
  $canViewRegspi = $adminAuthorizer->allowsAnyPermission($sidebarUser, ['reports.regspi.view', 'inventory_items.view', 'inventory_items.update']);
  $canViewRspi = $adminAuthorizer->allowsAnyPermission($sidebarUser, ['reports.rspi.view', 'inventory_items.view', 'inventory_items.update']);
  $canViewRrsp = $adminAuthorizer->allowsAnyPermission($sidebarUser, ['reports.rrsp.view', 'inventory_items.view', 'inventory_items.update']);
  $canViewSsmi = $adminAuthorizer->allowsAnyPermission($sidebarUser, ['reports.ssmi.view', 'stocks.view', 'stocks.adjust']);
  $canViewPropertyCards = $adminAuthorizer->allowsAnyPermission($sidebarUser, ['reports.property_cards.view', 'inventory_items.view', 'inventory_items.update']);
  $canViewStickers = $adminAuthorizer->allowsAnyPermission($sidebarUser, ['reports.stickers.view', 'inventory_items.view', 'inventory_items.update']);
  $canViewStockCards = $adminAuthorizer->allowsAnyPermission($sidebarUser, ['reports.stock_cards.view', 'stocks.view', 'stocks.view_ledger']);
  $showReportsMenu = $canViewRpci || $canViewRpcppe || $canViewRpcsp || $canViewRegspi || $canViewRspi || $canViewRrsp || $canViewSsmi || $canViewPropertyCards || $canViewStickers || $canViewStockCards;
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
  </ul>
</li>

@if($canTasksMenu)
  <li class="slide__category">
    <span class="category-name">Tasks</span>
  </li>

  <li class="slide has-sub">
    <a href="javascript:void(0);" class="side-menu__item">
      <i class="bx bx-task side-menu__icon"></i>
      <span class="side-menu__label">Tasks</span>
      <i class="fe fe-chevron-right side-menu__angle"></i>
    </a>

    <ul class="slide-menu child1">
      <li class="slide">
        <a href="{{ route('gso.tasks.my') }}" class="side-menu__item">
          My Tasks
          @if(($gsoSidebarTaskCounts['my'] ?? 0) > 0)
            <span class="text-success text-[0.75em] badge !py-[0.25rem] !px-[0.45rem] rounded-sm bg-success/10 ms-2">
              {{ $gsoSidebarTaskCounts['my'] }}
            </span>
          @endif
        </a>
      </li>

      <li class="slide">
        <a href="{{ route('gso.tasks.available') }}" class="side-menu__item">
          Available to Claim
          @if(($gsoSidebarTaskCounts['claimable'] ?? 0) > 0)
            <span class="text-success text-[0.75em] badge !py-[0.25rem] !px-[0.45rem] rounded-sm bg-success/10 ms-2">
              {{ $gsoSidebarTaskCounts['claimable'] }}
            </span>
          @endif
        </a>
      </li>

      @if($canAllTasks)
        <li class="slide">
          <a href="{{ route('gso.tasks.index', ['scope' => 'all', 'archived' => 'active']) }}" class="side-menu__item">
            All Tasks
          </a>
        </li>
      @endif
    </ul>
  </li>
@endif

@if($showDocumentsMenu)
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
      @if($canViewAir)
        <li class="slide">
          <a href="{{ route('gso.air.index') }}" class="side-menu__item">AIR</a>
        </li>
      @endif
      @if($canViewRis)
        <li class="slide">
          <a href="{{ route('gso.ris.index') }}" class="side-menu__item">RIS</a>
        </li>
      @endif
      @if($canViewPar)
        <li class="slide">
          <a href="{{ route('gso.pars.index') }}" class="side-menu__item">PAR</a>
        </li>
      @endif
      @if($canViewIcs)
        <li class="slide">
          <a href="{{ route('gso.ics.index') }}" class="side-menu__item">ICS</a>
        </li>
      @endif
      @if($canViewPtr)
        <li class="slide">
          <a href="{{ route('gso.ptrs.index') }}" class="side-menu__item">PTR</a>
        </li>
      @endif
      @if($canViewItr)
        <li class="slide">
          <a href="{{ route('gso.itrs.index') }}" class="side-menu__item">ITR</a>
        </li>
      @endif
      @if($canViewWmr)
        <li class="slide">
          <a href="{{ route('gso.wmrs.index') }}" class="side-menu__item">WMR</a>
        </li>
      @endif
    </ul>
  </li>
@endif

@if($showInventoryMenu)
  <li class="slide__category">
    <span class="category-name">Inventory</span>
  </li>

  <li class="slide has-sub">
    <a href="javascript:void(0);" class="side-menu__item">
      <i class="bi bi-box-seam side-menu__icon"></i>
      <span class="side-menu__label">Inventory</span>
      <i class="fe fe-chevron-right side-menu__angle"></i>
    </a>

    <ul class="slide-menu child1">
      @if($canViewItems)
        <li class="slide">
          <a href="{{ route('gso.items.index') }}" class="side-menu__item">Items</a>
        </li>
      @endif
      @if($canViewInventoryItems)
        <li class="slide">
          <a href="{{ route('gso.inventory-items.index') }}" class="side-menu__item">Inventory Items</a>
        </li>
      @endif
      @if($canViewStocks)
        <li class="slide">
          <a href="{{ route('gso.inventory.show', ['page' => 'stocks-ledger']) }}" class="side-menu__item">Stocks / Ledger</a>
        </li>
      @endif
    </ul>
  </li>
@endif

@if($showReferenceDataMenu)
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
      @if($canViewAssetTypes)
        <li class="slide">
          <a href="{{ route('gso.asset-types.index') }}" class="side-menu__item">Asset Types</a>
        </li>
      @endif
      @if($canViewAssetCategories)
        <li class="slide">
          <a href="{{ route('gso.asset-categories.index') }}" class="side-menu__item">Asset Categories</a>
        </li>
      @endif
      @if($canViewDepartments)
        <li class="slide">
          <a href="{{ route('gso.departments.index') }}" class="side-menu__item">Departments</a>
        </li>
      @endif
      @if($canViewFundClusters)
        <li class="slide">
          <a href="{{ route('gso.fund-clusters.index') }}" class="side-menu__item">Fund Clusters</a>
        </li>
      @endif
      @if($canViewFundSources)
        <li class="slide">
          <a href="{{ route('gso.fund-sources.index') }}" class="side-menu__item">Fund Sources</a>
        </li>
      @endif
      @if($canViewAccountablePersons)
        <li class="slide">
          <a href="{{ route('gso.accountable-persons.index') }}" class="side-menu__item">Accountable Persons</a>
        </li>
      @endif
    </ul>
  </li>
@endif

@if($showReportsMenu)
  <li class="slide__category">
    <span class="category-name">Reports</span>
  </li>

  <li class="slide has-sub">
    <a href="javascript:void(0);" class="side-menu__item">
      <i class="bi bi-bar-chart-line side-menu__icon"></i>
      <span class="side-menu__label">Reports</span>
      <i class="fe fe-chevron-right side-menu__angle"></i>
    </a>

    <ul class="slide-menu child1">
      @if($canViewRpci)
        <li class="slide">
          <a href="{{ route('gso.stocks.rpci.print', ['preview' => 1]) }}" class="side-menu__item">RPCI</a>
        </li>
      @endif
      @if($canViewRpcppe)
        <li class="slide">
          <a href="{{ route('gso.reports.rpcppe.print', ['preview' => 1]) }}" class="side-menu__item">RPCPPE</a>
        </li>
      @endif
      @if($canViewRpcsp)
        <li class="slide">
          <a href="{{ route('gso.reports.rpcsp.print', ['preview' => 1]) }}" class="side-menu__item">RPCSP</a>
        </li>
      @endif
      @if($canViewRegspi)
        <li class="slide">
          <a href="{{ route('gso.reports.regspi.print', ['preview' => 1]) }}" class="side-menu__item">RegSPI</a>
        </li>
      @endif
      @if($canViewRspi)
        <li class="slide">
          <a href="{{ route('gso.reports.rspi.print', ['preview' => 1]) }}" class="side-menu__item">RSPI</a>
        </li>
      @endif
      @if($canViewRrsp)
        <li class="slide">
          <a href="{{ route('gso.reports.rrsp.print', ['preview' => 1]) }}" class="side-menu__item">RRSP</a>
        </li>
      @endif
      @if($canViewSsmi)
        <li class="slide">
          <a href="{{ route('gso.stocks.ssmi.print', ['preview' => 1]) }}" class="side-menu__item">SSMI</a>
        </li>
      @endif
      @if($canViewPropertyCards)
        <li class="slide">
          <a href="{{ route('gso.reports.property-cards.print', ['preview' => 1]) }}" class="side-menu__item">Property Cards</a>
        </li>
      @endif
      @if($canViewStickers)
        <li class="slide">
          <a href="{{ route('gso.reports.stickers.print', ['preview' => 1]) }}" class="side-menu__item">Sticker Printing</a>
        </li>
      @endif
      @if($canViewStockCards)
        <li class="slide">
          <a href="{{ route('gso.stocks.index', ['view' => 'stock-cards']) }}" class="side-menu__item">Stock Card</a>
        </li>
      @endif
    </ul>
  </li>
@endif

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
