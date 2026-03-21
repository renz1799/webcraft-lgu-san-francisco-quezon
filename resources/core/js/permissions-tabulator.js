(function () {
  "use strict";

  function debounce(fn, wait = 350) {
    let t = null;
    return function (...args) {
      clearTimeout(t);
      t = setTimeout(() => fn.apply(this, args), wait);
    };
  }

  function escapeHtml(s) {
    return String(s ?? "")
      .replace(/&/g, "&amp;")
      .replace(/</g, "&lt;")
      .replace(/>/g, "&gt;")
      .replace(/"/g, "&quot;")
      .replace(/'/g, "&#39;");
  }

  document.addEventListener("DOMContentLoaded", function () {
    const el = document.getElementById("perm-table");
    if (!el) return;

    const endpoint = el.dataset.endpoint || "";
    if (!endpoint) {
      console.error("Missing users permissions data endpoint.");
      return;
    }

    const infoEl = document.getElementById("perm-info");
    const searchInput = document.getElementById("perm-search");
    const clearBtn = document.getElementById("perm-clear");

    let filters = { q: "" };
    let lastTotal = 0;

    const table = new Tabulator(el, {
      layout: "fitColumns",
      responsiveLayout: "collapse",
      placeholder: "No users found.",

      pagination: "remote",
      paginationSize: 20,
      paginationSizeSelector: [10, 20, 50, 100],

      ajaxURL: endpoint,
      ajaxConfig: "GET",
      ajaxLoader: false,

      paginationDataSent: { page: "page", size: "size" },

      ajaxParams: function () {
        return { ...filters };
      },

      ajaxResponse: function (url, params, response) {
        lastTotal = Number(response?.total ?? 0);
        return response?.data ?? [];
      },

      columns: [
        { title: "User Name", field: "username", widthGrow: 1 },
        { title: "Email", field: "email", widthGrow: 2 },
        { title: "Role", field: "role", widthGrow: 1 },
        { title: "Created", field: "created", widthGrow: 1 },

        {
          title: "Status",
          field: "is_active",
          headerSort: false,
          cssClass: "status-cell",
          width: 120,
          formatter: function (cell) {
            const row = cell.getRow().getData();
            const checked = row.is_active ? "checked" : "";
            const url = escapeHtml(row.status_url || "");

            return `
              <input type="checkbox"
                class="ti-switch shrink-0 !w-[35px] !h-[21px] before:size-4 toggle-status"
                data-endpoint="${url}"
                ${checked}>
            `;
          },
        },

        {
          title: "Actions",
          headerSort: false,
          cssClass: "actions-cell",
          width: 140,
          formatter: function (cell) {
            const row = cell.getRow().getData();
            const editUrl = escapeHtml(row.edit_url || "");
            const delUrl = escapeHtml(row.delete_url || "");
            const userId = escapeHtml(row.id || "");

            return `
              <div class="hstack flex gap-3 text-[.9375rem] justify-center w-full">
                <a href="${editUrl}"
                   class="ti-btn btn-wave ti-btn-sm ti-btn-info !rounded-full">
                  <i class="ri-edit-line"></i>
                </a>

                <a href="javascript:void(0);"
                   class="ti-btn btn-wave ti-btn-sm ti-btn-danger !rounded-full"
                   data-action="delete-user"
                   data-user-id="${userId}"
                   data-endpoint="${delUrl}">
                  <i class="ri-delete-bin-line"></i>
                </a>
              </div>
            `;
          },
        },
      ],
    });

    function setInfo(text) {
      if (!infoEl) return;
      infoEl.textContent = text;
    }

    function updateInfo() {
      if (!infoEl) return;

      const page = table.getPage() || 1;
      const size = table.getPageSize ? (table.getPageSize() || 20) : 20;
      const total = lastTotal || 0;

      if (!total) {
        const count = table.getDataCount ? table.getDataCount("active") : 0;
        setInfo(count ? `Showing 1–${count} users` : "No users found.");
        return;
      }

      const start = (page - 1) * size + 1;
      const end = Math.min(start + size - 1, total);

      setInfo(`Showing ${start}–${end} of ${total} users`);
    }

    function reload() {
      setInfo("Updating…");
      const p = table.getPage();
      if (p && p !== 1) table.setPage(1);
      else table.setData();
    }

    table.on("dataLoaded", updateInfo);
    table.on("pageLoaded", updateInfo);

    const applySearch = debounce(function () {
      filters.q = (searchInput?.value || "").trim();
      reload();
    }, 350);

    searchInput?.addEventListener("input", applySearch);

    clearBtn?.addEventListener("click", function (e) {
      e.preventDefault();
      if (searchInput) searchInput.value = "";
      filters.q = "";
      reload();
    });
  });
})();
