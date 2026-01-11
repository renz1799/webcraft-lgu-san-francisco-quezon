@extends('layouts.master')

@section('styles')
  <link rel="stylesheet" href="{{ asset('build/assets/libs/tabulator-tables/css/tabulator.min.css') }}">

  <style>
    .perm-header {
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 12px;
      width: 100%;
    }

    .perm-actions {
      display: flex;
      align-items: center;
      gap: 8px;
    }

    /* Same "full inside the box" look */
    .perm-table-bordered {
      border: 1px solid rgba(0,0,0,.06);
      border-radius: .75rem;
      overflow: hidden;
      background: #fff;
    }

    /* ✅ IMPORTANT: Prevent flex wrapping that causes columns to stack vertically */
    #perm-table .tabulator-row{
      flex-wrap: nowrap !important;
    }

    /* ✅ Remove the flex-cell overrides (these are what usually triggers weird wrapping in some templates) */
    /* (intentionally blank - don't add display:flex to cells here) */

    /* Optional: keep rows looking consistent */
    #perm-table .tabulator-cell{
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
    }

    /* Keep Tabulator border clean inside your bordered wrapper */
    #perm-table .tabulator {
      border: 0;
      border-radius: 0;
    }
  </style>
@endsection

@section('content')

<div class="block justify-between page-header md:flex">
  <div>
    <h3 class="!text-defaulttextcolor dark:!text-defaulttextcolor/70 dark:text-white dark:hover:text-white text-[1.125rem] font-semibold">
      Permissions Management
    </h3>
  </div>
  <ol class="flex items-center whitespace-nowrap min-w-0">
    <li class="text-[0.813rem] ps-[0.5rem]">
      <a class="flex items-center text-primary hover:text-primary dark:text-primary truncate" href="javascript:void(0);">
        Pages
        <i class="ti ti-chevrons-right flex-shrink-0 text-[#8c9097] dark:text-white/50 px-[0.5rem] overflow-visible rtl:rotate-180"></i>
      </a>
    </li>
    <li class="text-[0.813rem] text-defaulttextcolor font-semibold hover:text-primary dark:text-[#8c9097] dark:text-white/50" aria-current="page">
      Permissions
    </li>
  </ol>
</div>

<div class="box">
  <div class="box-header">
    <div class="perm-header">
      <h5 class="box-title">Users</h5>

      <div class="perm-actions">
        <input
          id="perm-search"
          type="text"
          class="form-control w-[320px] !rounded-md"
          placeholder="Search by email or username..."
        />
        <button id="perm-clear" type="button" class="ti-btn ti-btn-light">
          Clear
        </button>
      </div>
    </div>
  </div>

  <div class="box-body">
    <div class="overflow-auto perm-table-bordered">
      <div
        id="perm-table"
        data-endpoint="{{ route('users.permissions.data') }}"
        class="ti-custom-table ti-striped-table ti-custom-table-hover"
      ></div>
    </div>

    <div class="mt-2 flex items-center justify-between text-xs text-[#8c9097]">
      <div id="perm-info">—</div>
    </div>
  </div>
</div>

@push('scripts')
  <script src="{{ asset('build/assets/libs/tabulator-tables/js/tabulator.min.js') }}"></script>
  @vite('resources/js/permissions-tabulator.js')
  @vite('resources/js/permissions.js')
@endpush
@endsection
