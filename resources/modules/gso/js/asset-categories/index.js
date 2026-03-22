import Swal from "sweetalert2";
import "sweetalert2/dist/sweetalert2.min.css";

(function () {
  "use strict";

  function onReady(fn) {
    if (document.readyState === "loading") {
      document.addEventListener("DOMContentLoaded", fn);
      return;
    }

    fn();
  }

  function debounce(fn, wait = 350) {
    let timer = null;

    return (...args) => {
      clearTimeout(timer);
      timer = setTimeout(() => fn(...args), wait);
    };
  }

  function escapeHtml(value) {
    return String(value ?? "")
      .replace(/&/g, "&amp;")
      .replace(/</g, "&lt;")
      .replace(/>/g, "&gt;")
      .replace(/"/g, "&quot;")
      .replace(/'/g, "&#39;");
  }

  function getCsrf() {
    return (
      document.querySelector('meta[name="csrf-token"]')?.content ||
      window.__gsoAssetCategories?.csrf ||
      ""
    );
  }

  async function parseErrorResponse(response) {
    const contentType = response.headers.get("content-type") || "";
    const data = contentType.includes("application/json")
      ? await response.json().catch(() => null)
      : null;

    return (
      data?.message ||
      data?.error ||
      (response.status === 401
        ? "Your session has expired. Please sign in again."
        : response.status === 403
        ? "You do not have permission to manage asset categories."
        : response.status === 419
        ? "Your security token expired. Refresh the page and try again."
        : response.status === 404
        ? "The asset category could not be found."
        : "The request could not be completed.")
    );
  }

  onReady(function () {
    const tableElement = document.getElementById("asset-categories-table");
    if (!tableElement) return;

    const config = window.__gsoAssetCategories || {};
    const infoElement = document.getElementById("asset-categories-info");
    const searchInput = document.getElementById("asset-categories-search");
    const statusSelect = document.getElementById("asset-categories-status");
    const typeFilterSelect = document.getElementById("asset-categories-type-filter");
    const clearButton = document.getElementById("asset-categories-clear");

    let filters = {
      search: "",
      archived: statusSelect?.value || "active",
      asset_type_id: typeFilterSelect?.value || "",
    };
    let lastTotal = 0;

    function setInfo(text) {
      if (infoElement) {
        infoElement.textContent = text;
      }
    }

    function updateInfo(table) {
      if (lastTotal <= 0) {
        setInfo("No records found");
        return;
      }

      const page = table.getPage() || 1;
      const size = table.getPageSize ? table.getPageSize() || 15 : 15;
      const start = (page - 1) * size + 1;
      const end = Math.min(start + size - 1, lastTotal);

      setInfo(`Showing ${start}-${end} of ${lastTotal} record(s)`);
    }

    function reload(table) {
      if ((table.getPage?.() || 1) !== 1) {
        table.setPage(1);
        return;
      }

      table.setData();
    }

    const table = new Tabulator(tableElement, {
      layout: "fitColumns",
      responsiveLayout: "collapse",
      placeholder: "No asset categories found.",
      pagination: "remote",
      paginationSize: 15,
      paginationSizeSelector: [10, 20, 50, 100],
      ajaxURL: config.ajaxUrl,
      ajaxConfig: "GET",
      paginationDataSent: { page: "page", size: "size" },
      paginationDataReceived: { last_page: "last_page", data: "data", total: "total" },
      ajaxParams: () => ({ ...filters }),
      ajaxResponse: (_, __, response) => {
        lastTotal = Number(response?.total ?? 0);
        return response?.data ?? [];
      },
      columns: [
        {
          title: "Type",
          field: "type_code",
          width: 260,
          formatter: (cell) => {
            const row = cell.getRow().getData() || {};
            const type = row?.type || null;
            if (!type) {
              return "-";
            }

            return `${escapeHtml(type.type_code)} - ${escapeHtml(type.type_name)}`;
          },
        },
        { title: "Code", field: "asset_code", width: 160 },
        { title: "Name", field: "asset_name", minWidth: 260 },
        {
          title: "Account Group",
          field: "account_group",
          width: 190,
          formatter: (cell) => escapeHtml(cell.getValue() || "-"),
        },
        { title: "Created", field: "created_at_text", width: 200 },
        {
          title: "",
          field: "id",
          headerSort: false,
          hozAlign: "right",
          formatter: (cell) => {
            if (!config.canManage) {
              return "";
            }

            const row = cell.getRow().getData() || {};
            const id = row?.id;
            if (!id) {
              return "";
            }

            if (row?.is_archived) {
              return `
                <div class="hstack flex gap-2 justify-end">
                  <button class="ti-btn ti-btn-sm ti-btn-success !rounded-full" type="button" data-action="restore" data-id="${escapeHtml(id)}">
                    <i class="ri-refresh-line"></i>
                  </button>
                </div>
              `;
            }

            return `
              <div class="hstack flex gap-2 justify-end">
                <button class="ti-btn ti-btn-sm ti-btn-info !rounded-full" type="button" data-action="edit-asset-category" data-row='${escapeHtml(JSON.stringify(row))}'>
                  <i class="ri-edit-line"></i>
                </button>
                <button class="ti-btn ti-btn-sm ti-btn-danger !rounded-full" type="button" data-action="delete" data-id="${escapeHtml(id)}">
                  <i class="ri-archive-line"></i>
                </button>
              </div>
            `;
          },
        },
      ],
    });

    table.on("dataLoaded", () => updateInfo(table));
    table.on("pageLoaded", () => updateInfo(table));

    window.__gsoAssetCategoriesReload = () => reload(table);

    const applyFilters = debounce(() => {
      filters.search = (searchInput?.value || "").trim();
      filters.archived = (statusSelect?.value || "active").trim();
      filters.asset_type_id = (typeFilterSelect?.value || "").trim();
      reload(table);
    });

    searchInput?.addEventListener("input", applyFilters);
    statusSelect?.addEventListener("change", applyFilters);
    typeFilterSelect?.addEventListener("change", applyFilters);
    clearButton?.addEventListener("click", () => {
      if (searchInput) searchInput.value = "";
      if (statusSelect) statusSelect.value = "active";
      if (typeFilterSelect) typeFilterSelect.value = "";
      filters = { search: "", archived: "active", asset_type_id: "" };
      reload(table);
    });

    document.addEventListener("click", async (event) => {
      const actionButton = event.target.closest('[data-action="delete"], [data-action="restore"]');
      if (!actionButton) return;

      const isRestore = actionButton.dataset.action === "restore";
      const id = actionButton.dataset.id;
      const template = isRestore ? config.restoreUrlTemplate : config.deleteUrlTemplate;
      if (!id || !template) return;

      const confirmation = await Swal.fire({
        icon: isRestore ? "question" : "warning",
        title: isRestore ? "Restore asset category?" : "Archive asset category?",
        text: isRestore
          ? "This will restore the asset category."
          : "This will archive the asset category.",
        showCancelButton: true,
        confirmButtonText: isRestore ? "Restore" : "Archive",
        cancelButtonText: "Cancel",
      });

      if (!confirmation.isConfirmed) return;

      const response = await fetch(template.replace("__ID__", encodeURIComponent(id)), {
        method: isRestore ? "PATCH" : "DELETE",
        headers: {
          "X-CSRF-TOKEN": getCsrf(),
          Accept: "application/json",
        },
      });

      if (!response.ok) {
        await Swal.fire({
          icon: "error",
          title: isRestore ? "Restore failed" : "Archive failed",
          text: await parseErrorResponse(response),
        });
        return;
      }

      await Swal.fire({
        icon: "success",
        title: isRestore ? "Restored" : "Archived",
        timer: 900,
        showConfirmButton: false,
      });

      reload(table);
    });
  });
})();
