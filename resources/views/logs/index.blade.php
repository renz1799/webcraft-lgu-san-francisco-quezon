@extends('layouts.master')

@section('styles')
    {{-- Tabulator Css --}}
    <link rel="stylesheet" href="{{ asset('build/assets/libs/tabulator-tables/css/tabulator.min.css') }}">

    <style>
        /* Keep header aligned like your template */
        .loginlog-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            width: 100%;
        }

        .loginlog-actions {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        /* ✅ If you want to fully disable Tabulator’s loader overlay */
        .tabulator .tabulator-loader,
        .tabulator .tabulator-loader-msg {
            display: none !important;
        }

        /* Optional: show "busy" state via opacity only (your approach) */
        .tabulator.is-loading {
            opacity: .65;
            pointer-events: none;
        }
    </style>
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
    {{-- ✅ Proper template structure: title + actions inside box-header --}}
    <div class="box-header">
        <div class="loginlog-header">
            <h5 class="box-title">Recent Login Attempts</h5>

            <div class="loginlog-actions">
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
    </div>

    <div class="box-body">
        <div class="overflow-auto table-bordered">
            <div id="login-logs-table" class="ti-custom-table ti-striped-table ti-custom-table-hover"></div>
        </div>
<!-- 
        <div class="mt-2 text-xs text-[#8c9097]">
            Tip: Type to search (debounced). Sorting is server-side.
        </div> Tip part -->

        <div class="mt-2 flex items-center justify-between text-xs text-[#8c9097]">
    <div id="loginlog-info">
        <!-- filled by JS -->
    </div>
</div>

    </div>
</div>

@endsection

@push('scripts')
    <script src="{{ asset('build/assets/libs/tabulator-tables/js/tabulator.min.js') }}"></script>

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

        // info text element
        const infoEl = document.getElementById("loginlog-info");

        // store last known total from server (remote pagination)
        let lastTotal = 0;

        const table = new Tabulator(el, {
          layout: "fitColumns",
          responsiveLayout: "collapse",
          placeholder: "No logs found.",

          pagination: "remote",
          paginationSize: 15,
          paginationSizeSelector: [10, 20, 50, 100],

          ajaxURL: ajaxUrl,
          ajaxConfig: "GET",

          // keep as you want (no tabulator loader)
          ajaxLoader: false,

          paginationDataSent: { page: "page", size: "size" },
          paginationDataReceived: { last_page: "last_page", data: "data", total: "total" },

          ajaxParams: function () {
            return { ...currentFilters };
          },

          // ✅ IMPORTANT for remote pagination:
          // Return the full response so Tabulator can read last_page/total/etc.
        ajaxResponse: function (url, params, response) {
        lastTotal = Number(response?.total ?? 0);
        return response?.data ?? []; // ✅ must be array
        },


          initialSort: [{ column: "created_at", dir: "desc" }],

          columns: [
            {
              title: "Status",
              field: "success",
              headerSort: false,
              formatter: function (cell) {
                const v = cell.getValue();
                const success = (v === true || v === 1 || v === "1" || v === "true" || v === "success");

                if (success) {
                  return `<span class="badge bg-success/15 text-success">Success</span>`;
                }

                const row = cell.getRow().getData();
                const reason = row?.reason ? ` <span class="text-xs text-muted">— ${escapeHtml(row.reason)}</span>` : "";

                return `<span class="badge bg-danger/15 text-danger">Failed</span>${reason}`;
              }
            },
            { title: "User", field: "user" },
            { title: "Email (attempted)", field: "attempted" },
            { title: "IP Address", field: "ip_address" },
            { title: "Device", field: "device" },
            { title: "Address", field: "address" },
            {
              title: "Location",
              field: "location_url",
              headerSort: false,
              formatter: function (cell) {
                const url = cell.getValue();
                if (!url) return "—";
                const safe = escapeAttr(url);
                return `<a href="${safe}" target="_blank" rel="noopener" class="text-primary hover:underline">Map</a>`;
              }
            },
            {
              title: "Date",
              field: "created_at",
            },
          ],
        });

        function escapeHtml(s) {
          return String(s ?? "")
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#39;");
        }

        function escapeAttr(s) {
          return escapeHtml(s);
        }

        function setInfoText(text) {
          if (!infoEl) return;
          infoEl.textContent = text;
        }

        function updateInfo() {
          if (!infoEl) return;

          const page = table.getPage() || 1;
          const pageSize = table.getPageSize ? (table.getPageSize() || 0) : 0;
          const total = lastTotal || 0;

          // total unknown or 0
          if (!total) {
            // show based on currently loaded rows if any
            const rowsCount = table.getDataCount ? table.getDataCount("active") : 0;
            setInfoText(rowsCount ? `Showing 1–${rowsCount} records` : "No records found");
            return;
          }

          const start = (page - 1) * pageSize + 1;
          const end = Math.min(start + pageSize - 1, total);

          // handle edge case where page is out of range after filtering
          if (start > total) {
            setInfoText(`Showing 0 of ${total} records`);
            return;
          }

          setInfoText(`Showing ${start}–${end} of ${total} records`);
        }

        function reload() {
          el.classList.add("is-loading");
          setInfoText("Updating…");

          const page = table.getPage();
          if (page && page !== 1) {
            table.setPage(1);
          } else {
            table.setData();
          }
        }

        table.on("dataLoaded", function () {
          el.classList.remove("is-loading");
          updateInfo();
        });

        table.on("pageLoaded", function () {
          updateInfo();
        });

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
