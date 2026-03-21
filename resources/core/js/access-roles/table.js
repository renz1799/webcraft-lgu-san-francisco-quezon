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
    const cfg = window.__accessRoles || {};
    const el = document.getElementById("roles-table");
    if (!el) return;

    if (window.__accessRolesTable && typeof window.__accessRolesTable.destroy === "function") {
      try {
        window.__accessRolesTable.destroy();
      } catch (_e) {}
      window.__accessRolesTable = null;
    }

    const infoEl = document.getElementById("roles-info");
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
      if (typeof window.__accessRolesGetParams === "function") {
        return window.__accessRolesGetParams() || {};
      }

      return {
        search: "",
        archived: "active",
        name: "",
        permission: "",
        date_from: "",
        date_to: "",
      };
    }

    const table = new Tabulator(el, {
      layout: "fitColumns",
      responsiveLayout: "collapse",
      placeholder: "No roles found.",

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
          title: "Role Name",
          field: "name",
          minWidth: 180,
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
          title: "Permissions",
          field: "permissions_count",
          minWidth: 320,
          formatter: function (cell) {
            const row = cell.getRow().getData();
            const roleName = esc(row.name || "Role");
            const permsJson = esc(JSON.stringify(row.permissions || []));
            const count = Number(row.permissions_count || 0);
            const preview = esc(row.permissions_preview || "No permissions assigned");
            const moreCount = Number(row.permissions_more_count || 0);
            const moreText = moreCount > 0 ? `<span class="text-[#8c9097]"> +${moreCount} more</span>` : "";

            return `
              <span class="inline-flex items-center gap-2 w-full">
                <span>${count > 0 ? preview : "No permissions assigned"}${moreText}</span>
                <button
                  type="button"
                  class="ti-btn ti-btn-xs ti-btn-info !rounded-full"
                  data-action="view-role-perms"
                  data-role="${roleName}"
                  data-perms='${permsJson}'
                  title="View permissions"
                >
                  <i class="ri-eye-line"></i>
                </button>
              </span>
            `;
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
          width: 130,
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
          width: 170,
          hozAlign: "center",
          headerSort: false,
          formatter: function (cell) {
            const row = cell.getRow().getData();
            const roleName = esc(row.name || "");

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
                    data-action="restore-role"
                    data-endpoint="${restoreUrl}"
                    data-name="${roleName}"
                    title="Restore"
                  >
                    <i class="ri-history-line"></i>
                  </button>
                </div>
              `;
            }

            const permissionIds = esc(JSON.stringify(row.permission_ids || []));
            const updateUrl = esc(row.update_url || "");
            const deleteUrl = esc(row.delete_url || "");

            return `
              <div class="hstack flex gap-3 text-[.9375rem] justify-center w-full">
                <button
                  type="button"
                  class="ti-btn btn-wave ti-btn-sm ti-btn-info !rounded-full"
                  data-action="edit-role"
                  data-hs-overlay="#editRoleModal"
                  data-role-id="${esc(row.id || "")}" 
                  data-role-name="${roleName}"
                  data-role-permissions='${permissionIds}'
                  data-update-url="${updateUrl}"
                  title="Edit"
                >
                  <i class="ri-edit-line"></i>
                </button>

                <button
                  type="button"
                  class="ti-btn btn-wave ti-btn-sm ti-btn-danger !rounded-full"
                  data-action="delete-role"
                  data-endpoint="${deleteUrl}"
                  data-name="${roleName}"
                  title="Delete"
                >
                  <i class="ri-delete-bin-line"></i>
                </button>
              </div>
            `;
          },
        },
      ],
    });

    window.__accessRolesTable = table;

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

    window.__accessRolesReload = reload;

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

