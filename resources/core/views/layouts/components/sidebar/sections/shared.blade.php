@php
  $sharedUser = auth()->user();
  $sharedAuthorizer = app(\App\Core\Support\AdminContextAuthorizer::class);
  $taskSidebarCounts = is_array($taskCounts ?? null) ? $taskCounts : [];
  $canTasksMenu = $sharedAuthorizer->allowsAnyPermission($sharedUser, [
      'tasks.view',
      'tasks.view_all',
      'tasks.claim',
      'tasks.comment',
      'tasks.update_status',
      'tasks.reassign',
  ]);
  $canAllTasks = $sharedAuthorizer->allowsPermission($sharedUser, 'tasks.view_all');
@endphp

@if($canTasksMenu)
  <li class="slide__category">
    <span class="category-name">Shared Workspace</span>
  </li>

  <li class="slide has-sub">
    <a href="javascript:void(0);" class="side-menu__item">
      <i class="bx bx-task side-menu__icon"></i>
      <span class="side-menu__label">Tasks</span>
      <i class="fe fe-chevron-right side-menu__angle"></i>
    </a>

    <ul class="slide-menu child1">
      <li class="slide">
        <a href="{{ route('tasks.my') }}" class="side-menu__item">
          My Tasks
          @if(($taskSidebarCounts['my'] ?? 0) > 0)
            <span class="text-success text-[0.75em] badge !py-[0.25rem] !px-[0.45rem] rounded-sm bg-success/10 ms-2">
              {{ $taskSidebarCounts['my'] }}
            </span>
          @endif
        </a>
      </li>

      <li class="slide">
        <a href="{{ route('tasks.available') }}" class="side-menu__item">
          Available to Claim
          @if(($taskSidebarCounts['claimable'] ?? 0) > 0)
            <span class="text-success text-[0.75em] badge !py-[0.25rem] !px-[0.45rem] rounded-sm bg-success/10 ms-2">
              {{ $taskSidebarCounts['claimable'] }}
            </span>
          @endif
        </a>
      </li>

      @if($canAllTasks)
        <li class="slide">
          <a href="{{ route('tasks.index', ['scope' => 'all', 'archived' => 'active']) }}" class="side-menu__item">
            All Tasks
          </a>
        </li>
      @endif
    </ul>
  </li>
@endif
