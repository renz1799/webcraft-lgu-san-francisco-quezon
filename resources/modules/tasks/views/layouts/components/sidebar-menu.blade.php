@php
  $taskUser = auth()->user();
  $taskSidebarCounts = is_array($taskCounts ?? null) ? $taskCounts : [];
  $canTasksMenu = $taskUser && $taskUser->hasAnyRole(['Administrator', 'admin', 'Staff']);
  $canAllTasks = $taskUser
      && ($taskUser->hasAnyRole(['Administrator', 'admin']) || $taskUser->can('view All Tasks'));
@endphp

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
      @if($canAllTasks)
        <li class="slide">
          <a href="{{ route('tasks.index', ['scope' => 'all', 'archived' => 'active']) }}" class="side-menu__item">
            All (Administrator)
          </a>
        </li>
      @endif

      <li class="slide">
        <a href="{{ route('tasks.index', ['scope' => 'mine', 'archived' => 'active']) }}" class="side-menu__item">
          Assigned to Me
          @if(($taskSidebarCounts['my'] ?? 0) > 0)
            <span class="text-success text-[0.75em] badge !py-[0.25rem] !px-[0.45rem] rounded-sm bg-success/10 ms-2">
              {{ $taskSidebarCounts['my'] }}
            </span>
          @endif
        </a>
      </li>

      <li class="slide">
        <a href="{{ route('tasks.index', ['scope' => 'available', 'archived' => 'active']) }}" class="side-menu__item">
          Available to Claim
          @if(($taskSidebarCounts['claimable'] ?? 0) > 0)
            <span class="text-success text-[0.75em] badge !py-[0.25rem] !px-[0.45rem] rounded-sm bg-success/10 ms-2">
              {{ $taskSidebarCounts['claimable'] }}
            </span>
          @endif
        </a>
      </li>
    </ul>
  </li>
@endif
