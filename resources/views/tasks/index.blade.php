@extends('layouts.master')

@section('styles')
  <link rel="stylesheet" href="{{ asset('build/assets/libs/tabulator-tables/css/tabulator.min.css') }}">
  <style>
    .tasks-header{display:flex;align-items:center;justify-content:space-between;gap:12px;width:100%}
    .tasks-actions{display:flex;align-items:center;gap:8px;flex-wrap:wrap;justify-content:flex-end}
    .tabulator .tabulator-loader,.tabulator .tabulator-loader-msg{display:none!important}
    .tabulator.is-loading{opacity:.65;pointer-events:none}
  </style>
@endsection

@section('content')
<div class="block justify-between page-header md:flex">
  <div>
    <h3 class="!text-defaulttextcolor dark:!text-defaulttextcolor/70 dark:text-white text-[1.125rem] font-semibold">
      Tasks
    </h3>
  </div>

  <ol class="flex items-center whitespace-nowrap min-w-0">
    <li class="text-[0.813rem] ps-[0.5rem]">
      <a class="flex items-center text-primary hover:text-primary dark:text-primary truncate" href="javascript:void(0);">
        Dashboard
        <i class="ti ti-chevrons-right flex-shrink-0 text-[#8c9097] dark:text-white/50 px-[0.5rem] overflow-visible rtl:rotate-180"></i>
      </a>
    </li>
    <li class="text-[0.813rem] text-defaulttextcolor font-semibold dark:text-white/50" aria-current="page">
      Tasks
    </li>
  </ol>
</div>

@if(session('success'))
  <div class="mb-4 p-3 rounded bg-success/10 text-success text-sm">
    {{ session('success') }}
  </div>
@endif

<div class="box">
  <div class="box-header">
    <div class="tasks-header">
      <h5 class="box-title">Tasks</h5>

      <div class="tasks-actions">
        <input id="tasks-search" type="text" class="form-control w-[320px] !rounded-md"
               placeholder="Search title..." />
        @php($canAll = auth()->user()?->hasRole('Administrator') || auth()->user()?->can('view All Tasks'))
              @php($scope = request('scope', 'mine'))
              <select id="tasks-scope" class="form-control w-[180px] !rounded-md">
                <option value="mine" {{ $scope === 'mine' ? 'selected' : '' }}>My Tasks</option>
                <option value="available" {{ $scope === 'available' ? 'selected' : '' }}>Claimable Tasks</option>

                @if($canAll)
                  <option value="all" {{ $scope === 'all' ? 'selected' : '' }}>All Tasks</option>
                @endif
              </select>


          <select id="tasks-status" class="form-control w-[180px] !rounded-md">
            <option value="">All Status</option>
            <option value="pending">Pending</option>
            <option value="in_progress">In Progress</option>
            <option value="done">Done</option>
            <option value="cancelled">Cancelled</option>
          </select>

        <button id="tasks-clear" type="button" class="ti-btn ti-btn-light">Clear</button>

        <a href="{{ url('notifications') }}" class="ti-btn ti-btn-light">Notifications</a>
      </div>
    </div>
  </div>
  

  <div class="box-body">
    <div class="overflow-auto table-bordered">
      <div id="tasks-table" class="ti-custom-table ti-striped-table ti-custom-table-hover"></div>
    </div>

    <div class="mt-2 flex items-center justify-between text-xs text-[#8c9097]">
      <div id="tasks-info"></div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
  <script src="{{ asset('build/assets/libs/tabulator-tables/js/tabulator.min.js') }}"></script>

  <script>
    window.__tasks = {
      ajaxUrl: @json(route('tasks.data')),
      csrf: @json(csrf_token()),

      // IMPORTANT: param key is "id"
      showUrlTemplate: @json(route('tasks.show', ['id' => '__ID__'])),
      claimUrlTemplate: @json(route('tasks.claim', ['id' => '__ID__'])),

      // optional
      notificationsUrl: @json(url('notifications')),
    };
  </script>
@endpush

