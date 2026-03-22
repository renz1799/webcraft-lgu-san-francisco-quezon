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
      window.__gsoItems?.csrf ||
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
        ? "You do not have permission to manage items."
        : response.status === 419
        ? "Your security token expired. Refresh the page and try again."
        : response.status === 404
        ? "The item could not be found."
        : "The request could not be completed.")
    );
  }

  function trackingBadge(row) {
    const label = escapeHtml(row?.tracking_type_text || "-");
    const value = row?.tracking_type;
    const tone =
      value === "consumable"
        ? "bg-warning/10 text-warning"
        : "bg-primary/10 text-primary";

    return `<span class="inline-flex items-center rounded-full px-2 py-1 text-xs font-medium ${tone}">${label}</span>`;
  }

  function boolBadge(value, trueLabel, falseLabel) {
    const enabled = Boolean(value);
    const tone = enabled
      ? "bg-success/10 text-success"
      : "bg-light text-defaulttextcolor";
    const label = enabled ? trueLabel : falseLabel;

    return `<span class="inline-flex items-center rounded-full px-2 py-1 text-xs font-medium ${tone}">${escapeHtml(label)}</span>`;
  }

  onReady(function () {
    const tableElement = document.getElementById("gso-items-table");
    if (!tableElement) return;

    const config = window.__gsoItems || {};
    const infoElement = document.getElementById("gso-items-info");
    const searchInput = document.getElementById("gso-items-search");
    const statusSelect = document.getElementById("gso-items-status");
    const assetFilterSelect = document.getElementById("gso-items-asset-filter");
    const trackingFilterSelect = document.getElementById("gso-items-tracking-filter");
    const serialFilterSelect = document.getElementById("gso-items-serial-filter");
    const semiFilterSelect = document.getElementById("gso-items-semi-filter");
    const clearButton = document.getElementById("gso-items-clear");

    let filters = {
      search: "",
      archived: statusSelect?.value || "active",
      asset_id: assetFilterSelect?.value || "",
      tracking_type: trackingFilterSelect?.value || "",
      requires_serial: serialFilterSelect?.value || "",
      is_semi_expendable: semiFilterSelect?.value || "",
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
      placeholder: "No items found.",
      pagination: "remote",
      paginationSize: 15,
      paginationSizeSelector: [10, 20, 50, 100],
      ajaxURL: config.ajaxUrl,
      ajaxConfig: "GET",
      paginationDataSent: { page: "page", size: "size" },
      paginationDataReceived: {
        last_page: "last_page",
        data: "data",
        total: "total",
      },
      ajaxParams: () => ({ ...filters }),
      ajaxResponse: (_, __, response) => {
        lastTotal = Number(response?.total ?? 0);
        return response?.data ?? [];
      },
      columns: [
        {
          title: "Asset Category",
          field: "asset_label",
          minWidth: 220,
          formatter: (cell) => escapeHtml(cell.getValue() || "Unknown Asset Category"),
        },
        {
          title: "Item",
          field: "item_name",
          minWidth: 240,
          formatter: (cell) => {
            const row = cell.getRow().getData() || {};
            const name = escapeHtml(row?.item_name || "Unnamed Item");
            const identification = escapeHtml(row?.item_identification || "");

            if (!identification) {
              return name;
            }

            return `${name}<div class="text-xs text-[#8c9097] mt-1">${identification}</div>`;
          },
        },
        {
          title: "Base Unit",
          field: "base_unit",
          width: 140,
          formatter: (cell) => escapeHtml(cell.getValue() || "-"),
        },
        {
          title: "Tracking",
          field: "tracking_type_text",
          width: 150,
          formatter: (cell) => trackingBadge(cell.getRow().getData() || {}),
        },
        {
          title: "Serial",
          field: "requires_serial",
          width: 140,
          formatter: (cell) => boolBadge(cell.getValue(), "Required", "Not Required"),
        },
        {
          title: "Semi-Expendable",
          field: "is_semi_expendable",
          width: 170,
          formatter: (cell) => boolBadge(cell.getValue(), "Yes", "No"),
        },
        {
          title: "Created",
          field: "created_at_text",
          width: 190,
          formatter: (cell) => escapeHtml(cell.getValue() || "-"),
        },
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
                <button class="ti-btn ti-btn-sm ti-btn-info !rounded-full" type="button" data-action="edit-item" data-id="${escapeHtml(id)}">
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

    window.__gsoItemsReload = () => reload(table);

    const applyFilters = debounce(() => {
      filters.search = (searchInput?.value || "").trim();
      filters.archived = (statusSelect?.value || "active").trim();
      filters.asset_id = (assetFilterSelect?.value || "").trim();
      filters.tracking_type = (trackingFilterSelect?.value || "").trim();
      filters.requires_serial = (serialFilterSelect?.value || "").trim();
      filters.is_semi_expendable = (semiFilterSelect?.value || "").trim();
      reload(table);
    });

    searchInput?.addEventListener("input", applyFilters);
    statusSelect?.addEventListener("change", applyFilters);
    assetFilterSelect?.addEventListener("change", applyFilters);
    trackingFilterSelect?.addEventListener("change", applyFilters);
    serialFilterSelect?.addEventListener("change", applyFilters);
    semiFilterSelect?.addEventListener("change", applyFilters);
    clearButton?.addEventListener("click", () => {
      if (searchInput) searchInput.value = "";
      if (statusSelect) statusSelect.value = "active";
      if (assetFilterSelect) assetFilterSelect.value = "";
      if (trackingFilterSelect) trackingFilterSelect.value = "";
      if (serialFilterSelect) serialFilterSelect.value = "";
      if (semiFilterSelect) semiFilterSelect.value = "";

      filters = {
        search: "",
        archived: "active",
        asset_id: "",
        tracking_type: "",
        requires_serial: "",
        is_semi_expendable: "",
      };
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
        title: isRestore ? "Restore item?" : "Archive item?",
        text: isRestore
          ? "This will restore the item."
          : "This will archive the item.",
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
