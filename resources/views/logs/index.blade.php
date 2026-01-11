@extends('layouts.master')

@section('styles')
    {{-- Tabulator Css (already in your template reference) --}}
    <link rel="stylesheet" href="{{ asset('build/assets/libs/tabulator-tables/css/tabulator.min.css') }}">
@endsection

@section('content')

<!-- Page Header -->
<div class="block justify-between page-header md:flex">
    <div>
        <h3 class="!text-defaulttextcolor dark:!text-defaulttextcolor/70 dark:text-white dark:hover:text-white text-[1.125rem] font-semibold">
            Login Logs
        </h3>
    </div>
    <ol class="flex items-center whitespace-nowrap min-w-0">
        <li class="text-[0.813rem] ps-[0.5rem]">
            <a class="flex items-center text-primary hover:text-primary dark:text-primary truncate" href="javascript:void(0);">
                Dashboard
                <i class="ti ti-chevrons-right flex-shrink-0 text-[#8c9097] dark:text-white/50 px-[0.5rem] overflow-visible rtl:rotate-180"></i>
            </a>
        </li>
        <li class="text-[0.813rem] text-defaulttextcolor font-semibold hover:text-primary dark:text-[#8c9097] dark:text-white/50" aria-current="page">
            Login Logs
        </li>
    </ol>
</div>
<!-- /Page Header -->

<div class="box">
    <div class="flex items-center justify-between mb-4">
    <h5 class="box-title">Recent Login Attempts</h5>

    <div class="flex items-center gap-2">
        <input
        id="loginlog-search"
        type="text"
        class="form-control w-[320px] !rounded-md"
        placeholder="Search user/email/ip/device..."
        />
        <button id="loginlog-clear" type="button" class="ti-btn ti-btn-light">
        Clear
        </button>
    </div>
    </div>

    <div class="box-body">
        <div class="overflow-auto table-bordered">
            <div id="login-logs-table" class="ti-custom-table ti-striped-table ti-custom-table-hover"></div>
        </div>

        <div class="mt-2 text-xs text-[#8c9097]">
            Tip: Type to search (debounced). Sorting is server-side.
        </div>
    </div>
</div>

@push('scripts')
<script src="{{ asset('build/assets/libs/tabulator-tables/js/tabulator.min.js') }}"></script>

@push('scripts')
<script>
(function () {
  "use strict";

  function debounce(fn, wait = 350) {
    let t = null;
    return function (...args) {
      clearTimeout(t);
      t = setTimeout(() => fn.apply(this, args), wait);
    };
  }

  document.addEventListener("DOMContentLoaded", function () {
    const el = document.getElementById("login-logs-table");
    if (!el) return;

    const ajaxUrl = @json(route('logs.data'));

    let currentFilters = { q: "" };

    const table = new Tabulator(el, {
      layout: "fitColumns",
      responsiveLayout: "collapse",
      placeholder: "No logs found.",

      pagination: "remote",
      paginationSize: 20,
      paginationSizeSelector: [10, 20, 50, 100],

      ajaxURL: ajaxUrl,
      ajaxConfig: "GET",

      paginationDataSent: { page: "page", size: "size" },
      paginationDataReceived: { last_page: "last_page", data: "data", total: "total" },

      ajaxParams: function () {
        return { ...currentFilters };
      },

      ajaxResponse: function (url, params, response) {
        return response?.data ?? [];
      },

      initialSort: [{ column: "created_at", dir: "desc" }],

      columns: [
        { title: "Status", field: "success" },
        { title: "User", field: "user" },
        { title: "Email (attempted)", field: "attempted" },
        { title: "IP Address", field: "ip_address" },
        { title: "Device", field: "device" },
        { title: "Address", field: "address" },
        { title: "Location", field: "location_url" },
        { title: "Date", field: "created_at_human" },
      ],
    });

    // ✅ force reload even when already on page 1
    function reload() {
      const page = table.getPage();
      if (page && page !== 1) {
        table.setPage(1); // triggers ajax
      } else {
        table.setData();  // forces ajax even if still on page 1
      }
    }

    const searchInput = document.getElementById("loginlog-search");
    const clearBtn = document.getElementById("loginlog-clear");

    const applySearch = debounce(function () {
      currentFilters.q = (searchInput?.value || "").trim();
      reload();
    }, 350);

    searchInput?.addEventListener("input", applySearch);

    clearBtn?.addEventListener("click", function (e) {
      e.preventDefault();
      if (searchInput) searchInput.value = "";
      currentFilters.q = "";
      reload();
    });
  });
})();
</script>
@endpush

@endpush

@endsection
