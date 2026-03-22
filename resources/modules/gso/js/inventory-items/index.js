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
      window.__gsoInventoryItems?.csrf ||
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
        ? "You do not have permission to manage inventory items."
        : response.status === 419
        ? "Your security token expired. Refresh the page and try again."
        : response.status === 404
        ? "The inventory item could not be found."
        : "The request could not be completed.")
    );
  }

  function pill(text, tone) {
    return `<span class="inline-flex items-center rounded-full px-2 py-1 text-xs font-medium bg-${tone}/10 text-${tone}">${escapeHtml(
      text || "-"
    )}</span>`;
  }

  onReady(function () {
    const tableElement = document.getElementById("gso-inventory-items-table");
    if (!tableElement) return;

    const config = window.__gsoInventoryItems || {};
    const infoElement = document.getElementById("gso-inventory-items-info");
    const searchInput = document.getElementById("gso-inventory-items-search");
    const departmentFilter = document.getElementById("gso-inventory-items-department-filter");
    const classificationFilter = document.getElementById("gso-inventory-items-classification-filter");
    const custodyFilter = document.getElementById("gso-inventory-items-custody-filter");
    const inventoryStatusFilter = document.getElementById("gso-inventory-items-status-filter");
    const recordStatusFilter = document.getElementById("gso-inventory-items-record-status");
    const clearButton = document.getElementById("gso-inventory-items-clear");
    const batchPrintButton = document.getElementById("gso-inventory-items-batch-print");

    let filters = {
      search: "",
      department_id: departmentFilter?.value || "",
      classification: classificationFilter?.value || "",
      custody_state: custodyFilter?.value || "",
      inventory_status: inventoryStatusFilter?.value || "",
      archived: recordStatusFilter?.value || "active",
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
      placeholder: "No inventory items found.",
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
          title: "Property No.",
          field: "property_number",
          width: 180,
          formatter: (cell) => escapeHtml(cell.getValue() || "-"),
        },
        {
          title: "Item",
          field: "item_label",
          minWidth: 260,
          formatter: (cell) => {
            const row = cell.getRow().getData() || {};
            const itemLabel = escapeHtml(cell.getValue() || "Inventory Item");
            const poNumber = escapeHtml(row?.po_number || "");
            const serialNumber = escapeHtml(row?.serial_number || "");
            const extra = [poNumber ? `PO: ${poNumber}` : "", serialNumber ? `SN: ${serialNumber}` : ""]
              .filter(Boolean)
              .join(" | ");

            return extra
              ? `${itemLabel}<div class="text-xs text-[#8c9097] mt-1">${extra}</div>`
              : itemLabel;
          },
        },
        {
          title: "Department",
          field: "department_label",
          minWidth: 200,
          formatter: (cell) => escapeHtml(cell.getValue() || "None"),
        },
        {
          title: "Class",
          field: "classification_text",
          width: 120,
          formatter: (cell) =>
            pill(cell.getValue(), cell.getValue() === "ICS" ? "info" : "primary"),
        },
        {
          title: "Custody",
          field: "custody_state_text",
          width: 130,
          formatter: (cell) => pill(cell.getValue(), "secondary"),
        },
        {
          title: "Status",
          field: "status_text",
          width: 150,
          formatter: (cell) => {
            const value = String(cell.getRow().getData()?.status || "");
            const tone =
              value === "serviceable"
                ? "success"
                : value === "unserviceable" || value === "disposed" || value === "lost"
                ? "danger"
                : value === "for_repair" || value === "under_repair"
                ? "warning"
                : "secondary";

            return pill(cell.getValue(), tone);
          },
        },
        {
          title: "Condition",
          field: "condition_text",
          width: 140,
          formatter: (cell) => escapeHtml(cell.getValue() || "-"),
        },
        {
          title: "Acq. Cost",
          field: "acquisition_cost_text",
          width: 140,
          hozAlign: "right",
          formatter: (cell) => escapeHtml(cell.getValue() || "-"),
        },
        {
          title: "Accountable Officer",
          field: "accountable_officer_label",
          minWidth: 190,
          formatter: (cell) => escapeHtml(cell.getValue() || "None"),
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
            const row = cell.getRow().getData() || {};
            const id = row?.id;
            if (!id) {
              return "";
            }

            const filesButton = `
              <button class="ti-btn ti-btn-sm ti-btn-primary !rounded-full" type="button" data-action="inventory-item-files" data-id="${escapeHtml(
                id
              )}" title="Inventory Files">
                <i class="ri-folder-image-line"></i>
                <span class="ml-1">${escapeHtml(row?.file_count ?? 0)}</span>
              </button>
            `;

            const eventsButton = `
              <button class="ti-btn ti-btn-sm ti-btn-warning !rounded-full" type="button" data-action="inventory-item-events" data-id="${escapeHtml(
                id
              )}" title="Inventory Events">
                <i class="ri-history-line"></i>
                <span class="ml-1">${escapeHtml(row?.event_count ?? 0)}</span>
              </button>
            `;

            const publicAssetButton = row?.public_asset_url
              ? `
                <a class="ti-btn ti-btn-sm ti-btn-success !rounded-full" href="${escapeHtml(
                  row.public_asset_url
                )}" target="_blank" rel="noopener" title="Public Asset Page">
                  <i class="ri-global-line"></i>
                </a>
              `
              : "";

            const propertyCardButton = row?.property_card_print_url
              ? `
                <a class="ti-btn ti-btn-sm ti-btn-secondary !rounded-full" href="${escapeHtml(
                  row.property_card_print_url
                )}" target="_blank" rel="noopener" title="Property Card">
                  <i class="ri-file-print-line"></i>
                </a>
              `
              : "";

            if (row?.is_archived) {
              if (!config.canManage) {
                return `<div class="hstack flex gap-2 justify-end">${publicAssetButton}${propertyCardButton}${filesButton}${eventsButton}</div>`;
              }

              return `
                <div class="hstack flex gap-2 justify-end">
                  ${publicAssetButton}
                  ${propertyCardButton}
                  ${filesButton}
                  ${eventsButton}
                  <button class="ti-btn ti-btn-sm ti-btn-success !rounded-full" type="button" data-action="restore" data-id="${escapeHtml(
                    id
                  )}">
                    <i class="ri-refresh-line"></i>
                  </button>
                </div>
              `;
            }

            if (!config.canManage) {
              return `<div class="hstack flex gap-2 justify-end">${publicAssetButton}${propertyCardButton}${filesButton}${eventsButton}</div>`;
            }

            return `
              <div class="hstack flex gap-2 justify-end">
                ${publicAssetButton}
                ${propertyCardButton}
                ${filesButton}
                ${eventsButton}
                <button class="ti-btn ti-btn-sm ti-btn-info !rounded-full" type="button" data-action="edit-inventory-item" data-id="${escapeHtml(
                  id
                )}">
                  <i class="ri-edit-line"></i>
                </button>
                <button class="ti-btn ti-btn-sm ti-btn-danger !rounded-full" type="button" data-action="delete" data-id="${escapeHtml(
                  id
                )}">
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

    window.__gsoInventoryItemsReload = () => reload(table);

    const applyFilters = debounce(() => {
      filters.search = (searchInput?.value || "").trim();
      filters.department_id = (departmentFilter?.value || "").trim();
      filters.classification = (classificationFilter?.value || "").trim();
      filters.custody_state = (custodyFilter?.value || "").trim();
      filters.inventory_status = (inventoryStatusFilter?.value || "").trim();
      filters.archived = (recordStatusFilter?.value || "active").trim();
      reload(table);
    });

    searchInput?.addEventListener("input", applyFilters);
    departmentFilter?.addEventListener("change", applyFilters);
    classificationFilter?.addEventListener("change", applyFilters);
    custodyFilter?.addEventListener("change", applyFilters);
    inventoryStatusFilter?.addEventListener("change", applyFilters);
    recordStatusFilter?.addEventListener("change", applyFilters);
    clearButton?.addEventListener("click", () => {
      if (searchInput) searchInput.value = "";
      if (departmentFilter) departmentFilter.value = "";
      if (classificationFilter) classificationFilter.value = "";
      if (custodyFilter) custodyFilter.value = "";
      if (inventoryStatusFilter) inventoryStatusFilter.value = "";
      if (recordStatusFilter) recordStatusFilter.value = "active";

      filters = {
        search: "",
        department_id: "",
        classification: "",
        custody_state: "",
        inventory_status: "",
        archived: "active",
      };
      reload(table);
    });

    batchPrintButton?.addEventListener("click", () => {
      if (!config.batchPropertyCardsUrl) return;

      const params = new URLSearchParams();
      Object.entries(filters).forEach(([key, value]) => {
        if (value !== null && value !== undefined && String(value).trim() !== "") {
          params.set(key, String(value));
        }
      });

      params.set("page", String(table.getPage?.() || 1));
      params.set("size", String(table.getPageSize?.() || 15));
      params.set("preview", "1");

      window.open(`${config.batchPropertyCardsUrl}?${params.toString()}`, "_blank", "noopener");
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
        title: isRestore ? "Restore inventory item?" : "Archive inventory item?",
        text: isRestore
          ? "This will restore the inventory item."
          : "This will archive the inventory item.",
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
