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
    const cfg = window.__accessPermissions || {};
    const el = document.getElementById("permissions-table");
    if (!el) return;

    if (window.__accessPermissionsTable && typeof window.__accessPermissionsTable.destroy === "function") {
      try {
        window.__accessPermissionsTable.destroy();
      } catch (_e) {}
      window.__accessPermissionsTable = null;
    }

    const infoEl = document.getElementById("permissions-info");
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
      if (typeof window.__accessPermissionsGetParams === "function") {
        return window.__accessPermissionsGetParams() || {};
      }

      return {
        search: "",
        archived: "active",
        module: "",
        guard_name: "",
        role: "",
        date_from: "",
        date_to: "",
      };
    }

    const table = new Tabulator(el, {
      layout: "fitColumns",
      responsiveLayout: "collapse",
      placeholder: "No permissions found.",

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

      initialSort: [{ column: "page", dir: "asc" }, { column: "name", dir: "asc" }],

      columns: [
        {
          title: "Permission",
          field: "name",
          minWidth: 230,
          formatter: function (cell) {
            const row = cell.getRow().getData();
            const name = esc(cell.getValue() || "-");
            const archived = row.is_archived
              ? ' <span class="badge bg-warning/10 text-warning align-middle">Archived</span>'
              : "";

            return `${name}${archived}`;
          },
        },
        {
          title: "Page / Module",
          field: "page",
          minWidth: 180,
          formatter: (cell) => esc(cell.getValue() || "Uncategorized"),
        },
        {
          title: "Guard",
          field: "guard_name",
          width: 110,
          hozAlign: "center",
          formatter: function (cell) {
            const value = esc(cell.getValue() || "web");
            return `<span class="badge bg-primary/10 text-primary">${value}</span>`;
          },
        },
        {
          title: "Roles",
          field: "roles_count",
          minWidth: 230,
          formatter: function (cell) {
            const row = cell.getRow().getData();
            const count = Number(row.roles_count || 0);

            if (count <= 0) {
              return '<span class="text-[#8c9097]">Unused</span>';
            }

            const preview = esc(row.roles_preview || "");
            const more = Number(row.roles_more_count || 0);
            const moreText = more > 0 ? `<span class="text-[#8c9097]"> +${more} more</span>` : "";

            return `<span>${preview}${moreText}</span>`;
          },
        },
        {
          title: "Created",
          field: "created_at",
          minWidth: 190,
          formatter: function (cell) {
            const row = cell.getRow().getData();
            return esc(row.created_at_text || cell.getValue() || "-");
          },
        },
        {
          title: "State",
          field: "is_archived",
          width: 120,
          hozAlign: "center",
          headerSort: false,
          formatter: function (cell) {
            return cell.getValue()
              ? '<span class="badge bg-warning/10 text-warning">Archived</span>'
              : '<span class="badge bg-success/10 text-success">Active</span>';
          },
        },
        {
          title: "Actions",
          field: "id",
          width: 180,
          hozAlign: "center",
          headerSort: false,
          formatter: function (cell) {
            const row = cell.getRow().getData();
            const name = esc(row.name || "this permission");

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
                    data-action="restore-permission"
                    data-endpoint="${restoreUrl}"
                    data-name="${name}"
                    title="Restore"
                  >
                    <i class="ri-history-line"></i>
                  </button>
                </div>
              `;
            }

            const updateUrl = esc(row.update_url || "");
            const deleteUrl = esc(row.delete_url || "");
            const page = esc(row.page || "");
            const guardName = esc(row.guard_name || "web");
            const id = esc(row.id || "");

            return `
              <div class="hstack flex gap-3 text-[.9375rem] justify-center w-full">
                <button
                  type="button"
                  class="ti-btn btn-wave ti-btn-sm ti-btn-info !rounded-full"
                  data-action="edit-permission"
                  data-id="${id}"
                  data-name="${name}"
                  data-page="${page}"
                  data-guard="${guardName}"
                  data-update-url="${updateUrl}"
                  title="Edit"
                >
                  <i class="ri-edit-line"></i>
                </button>

                <button
                  type="button"
                  class="ti-btn btn-wave ti-btn-sm ti-btn-danger !rounded-full"
                  data-action="delete-permission"
                  data-endpoint="${deleteUrl}"
                  data-name="${name}"
                  title="Archive"
                >
                  <i class="ri-delete-bin-line"></i>
                </button>
              </div>
            `;
          },
        },
      ],
    });

    window.__accessPermissionsTable = table;

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

    window.__accessPermissionsReload = reload;

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
