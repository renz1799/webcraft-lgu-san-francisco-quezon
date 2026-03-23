@if($moduleLinks->count() > 1)
  <li class="slide__category">
    <span class="category-name">Platform Contexts</span>
  </li>

  <li class="slide has-sub">
    <a href="javascript:void(0);" class="side-menu__item">
      <i class="bx bx-grid-alt side-menu__icon"></i>
      <span class="side-menu__label">
        {{ $currentModule?->name ?? 'Select Context' }}
      </span>
      <i class="fe fe-chevron-right side-menu__angle"></i>
    </a>

    <ul class="slide-menu child1">
      <li class="slide">
        <a href="{{ route('modules.index') }}" class="side-menu__item">
          Context Selector
        </a>
      </li>

      @foreach($moduleLinks as $module)
        <li class="slide">
          <a
            href="{{ route('modules.open', ['moduleCode' => strtolower((string) $module->code)]) }}"
            class="side-menu__item"
          >
            {{ $module->name }}
          </a>
        </li>
      @endforeach
    </ul>
  </li>
@endif
