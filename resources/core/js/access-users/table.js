(function () {
  "use strict";

  function onReady(fn) {
    if (document.readyState === "loading") {
      document.addEventListener("DOMContentLoaded", fn);
      return;
    }

    fn();
  }

  function esc(s) {
    return String(s ?? "")
      .replace(/&/g, "&amp;")
      .replace(/</g, "&lt;")
      .replace(/>/g, "&gt;")
      .replace(/\"/g, "&quot;")
      .replace(/'/g, "&#39;");
  }

  onReady(function () {
    const cfg = window.__accessUsers || {};
    const el = document.getElementById("users-table");
    if (!el) return;

    if (window.__accessUsersTable && typeof window.__accessUsersTable.destroy === "function") {
      try {
        window.__accessUsersTable.destroy();
      } catch (_e) {}
      window.__accessUsersTable = null;
    }

    const infoEl = document.getElementById("users-info");
    const ajaxUrl = cfg.ajaxUrl || "";

    if (!ajaxUrl) {
      if (infoEl) infoEl.textContent = "Missing ajaxUrl.";
      return;
    }

    let lastTotal = 0;

    function setInfoText(text) {
      if (!infoEl) return;
      infoEl.textContent = text;
    }

    function getFilters() {
      if (typeof window.__accessUsersGetParams === "function") {
        return window.__accessUsersGetParams() || {};
      }

      return {
        search: "",
        archived: "active",
        status: "",
        role: "",
        username: "",
        email: "",
        date_from: "",
        date_to: "",
      };
    }

    const table = new Tabulator(el, {
      layout: "fitColumns",
      responsiveLayout: "collapse",
      placeholder: "No users found.",

      pagination: true,
      paginationMode: "remote",
      paginationSize: 15,
      paginationSizeSelector: [15, 25, 50, 100],

      ajaxURL: ajaxUrl,
      ajaxConfig: "GET",
      ajaxLoader: false,

      paginationDataSent: { page: "page", size: "size" },

      ajaxParams: function () {
        return { ...getFilters() };
      },

      ajaxResponse: function (_url, _params, response) {
        lastTotal = Number(response?.total ?? 0);

        return {
          data: Array.isArray(response?.data) ? response.data : [],
          last_page: Number(response?.last_page ?? 1),
        };
      },

      initialSort: [{ column: "created_at", dir: "desc" }],

      columns: [
        {
          title: "User Name",
          field: "username",
          minWidth: 160,
          formatter: (cell) => esc(cell.getValue() || "-"),
        },
        {
          title: "Email",
          field: "email",
          minWidth: 220,
          formatter: (cell) => esc(cell.getValue() || "-"),
        },
        {
          title: "Role",
          field: "role",
          minWidth: 170,
          formatter: (cell) => esc(cell.getValue() || "No Role Assigned"),
        },
        {
          title: "Created",
          field: "created_at",
          minWidth: 180,
          formatter: function (cell) {
            const row = cell.getRow().getData();
            return esc(row.created_at_text || cell.getValue() || "-");
          },
        },
        {
          title: "Status",
          field: "is_active",
          width: 130,
          hozAlign: "center",
          headerSort: false,
          formatter: function (cell) {
            const row = cell.getRow().getData();
            if (row.is_archived) {
              return '<span class="badge bg-warning/10 text-warning">Archived</span>';
            }

            const checked = row.is_active ? "checked" : "";
            const endpoint = esc(row.status_url || "");

            if (!endpoint) {
              return row.is_active
                ? '<span class="badge bg-success/10 text-success">Active</span>'
                : '<span class="badge bg-danger/10 text-danger">Inactive</span>';
            }

            return `
              <input
                type="checkbox"
                class="ti-switch shrink-0 !w-[35px] !h-[21px] before:size-4 users-toggle-status"
                data-endpoint="${endpoint}"
                ${checked}
              >
            `;
          },
        },
        {
          title: "Actions",
          field: "id",
          width: 170,
          hozAlign: "center",
          headerSort: false,
          formatter: function (cell) {
            const row = cell.getRow().getData();
            const username = esc(row.username || "this user");

            if (row.is_archived) {
              const restoreUrl = esc(row.restore_url || "");

              if (!restoreUrl) {
                return '<span class="text-xs text-[#8c9097]">N/A</span>';
              }

              return `
                <div class="hstack flex gap-3 text-[.9375rem] justify-center w-full">
                  <button
                    type="button"
                    class="ti-btn btn-wave ti-btn-sm ti-btn-success !rounded-full"
                    data-action="restore-user"
                    data-endpoint="${restoreUrl}"
                    data-username="${username}"
                    title="Restore"
                  >
                    <i class="ri-history-line"></i>
                  </button>
                </div>
              `;
            }

            const editUrl = esc(row.edit_url || "");
            const deleteUrl = esc(row.delete_url || "");
            const buttons = [];

            if (editUrl) {
              buttons.push(`
                <a href="${editUrl}" class="ti-btn btn-wave ti-btn-sm ti-btn-info !rounded-full" title="Edit">
                  <i class="ri-edit-line"></i>
                </a>
              `);
            }

            if (deleteUrl) {
              buttons.push(`
                <button
                  type="button"
                  class="ti-btn btn-wave ti-btn-sm ti-btn-danger !rounded-full"
                  data-action="delete-user"
                  data-endpoint="${deleteUrl}"
                  data-username="${username}"
                  title="Delete"
                >
                  <i class="ri-delete-bin-line"></i>
                </button>
              `);
            }

            if (!buttons.length) {
              return '<span class="text-xs text-[#8c9097]">View only</span>';
            }

            return `
              <div class="hstack flex gap-3 text-[.9375rem] justify-center w-full">
                ${buttons.join("")}
              </div>
            `;
          },
        },
      ],
    });

    window.__accessUsersTable = table;

    function updateInfo() {
      if (!infoEl) return;

      const page = table.getPage() || 1;
      const pageSize = table.getPageSize ? table.getPageSize() || 0 : 0;
      const total = lastTotal || 0;

      if (!total) {
        const rowsCount = table.getDataCount ? table.getDataCount("active") : 0;
        setInfoText(rowsCount ? `Showing 1-${rowsCount} records` : "No records found");
        return;
      }

      const start = (page - 1) * pageSize + 1;
      const end = Math.min(start + pageSize - 1, total);

      if (start > total) {
        setInfoText(`Showing 0 of ${total} records`);
        return;
      }

      setInfoText(`Showing ${start}-${end} of ${total} record(s).`);
    }

    function hardRefreshCurrentPage() {
      if (typeof table.replaceData === "function") {
        table.replaceData();
        return;
      }

      table.setData(ajaxUrl, {
        ...getFilters(),
        page: table.getPage() || 1,
        size: table.getPageSize ? table.getPageSize() || 15 : 15,
      });
    }

    function reload({ resetPage = true } = {}) {
      el.classList.add("is-loading");
      setInfoText("Updating...");

      const page = table.getPage() || 1;

      if (resetPage && page !== 1) {
        table.setPage(1);
        return;
      }

      hardRefreshCurrentPage();
    }

    window.__accessUsersReload = reload;

    table.on("dataLoaded", function () {
      el.classList.remove("is-loading");
      updateInfo();
    });

    table.on("pageLoaded", function () {
      updateInfo();
    });

    setInfoText("Loading...");
  });
})();
