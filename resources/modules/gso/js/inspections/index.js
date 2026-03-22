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
      window.__gsoInspections?.csrf ||
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
        ? "You do not have permission to manage inspections."
        : response.status === 419
        ? "Your security token expired. Refresh the page and try again."
        : response.status === 404
        ? "The inspection could not be found."
        : "The request could not be completed.")
    );
  }

  function pill(text, tone) {
    return `<span class="inline-flex items-center rounded-full px-2 py-1 text-xs font-medium bg-${tone}/10 text-${tone}">${escapeHtml(
      text || "-"
    )}</span>`;
  }

  onReady(function () {
    const tableElement = document.getElementById("gso-inspections-table");
    if (!tableElement) return;

    const config = window.__gsoInspections || {};
    const infoElement = document.getElementById("gso-inspections-info");
    const searchInput = document.getElementById("gso-inspections-search");
    const statusFilter = document.getElementById("gso-inspections-status-filter");
    const departmentFilter = document.getElementById("gso-inspections-department-filter");
    const recordStatusFilter = document.getElementById("gso-inspections-record-status");
    const clearButton = document.getElementById("gso-inspections-clear");

    let filters = {
      search: "",
      status: statusFilter?.value || "",
      department_id: departmentFilter?.value || "",
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
      placeholder: "No inspections found.",
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
          title: "Status",
          field: "status_text",
          width: 140,
          formatter: (cell) => {
            const value = String(cell.getRow().getData()?.status || "");
            const tone =
              value === "approved"
                ? "success"
                : value === "submitted" || value === "pending"
                ? "warning"
                : value === "returned"
                ? "danger"
                : "secondary";

            return pill(cell.getValue(), tone);
          },
        },
        {
          title: "Item",
          field: "item_label",
          minWidth: 230,
          formatter: (cell) => {
            const row = cell.getRow().getData() || {};
            const itemLabel = escapeHtml(cell.getValue() || "Inspection");
            const observed = escapeHtml(row?.observed_description || "");

            return observed
              ? `${itemLabel}<div class="text-xs text-[#8c9097] mt-1">${observed}</div>`
              : itemLabel;
          },
        },
        {
          title: "PO / DV",
          field: "po_number",
          width: 160,
          formatter: (cell) => {
            const row = cell.getRow().getData() || {};
            const poNumber = escapeHtml(cell.getValue() || "-");
            const dvNumber = escapeHtml(row?.dv_number || "");

            return dvNumber
              ? `${poNumber}<div class="text-xs text-[#8c9097] mt-1">DV: ${dvNumber}</div>`
              : poNumber;
          },
        },
        {
          title: "Office / Department",
          field: "department_label",
          minWidth: 210,
          formatter: (cell) => escapeHtml(cell.getValue() || "None"),
        },
        {
          title: "Accountable",
          field: "accountable_officer",
          minWidth: 180,
          formatter: (cell) => escapeHtml(cell.getValue() || "None"),
        },
        {
          title: "Qty",
          field: "quantity",
          width: 90,
          hozAlign: "right",
          formatter: (cell) => escapeHtml(cell.getValue() || "0"),
        },
        {
          title: "Condition",
          field: "condition_text",
          width: 140,
          formatter: (cell) => escapeHtml(cell.getValue() || "-"),
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

            const photoButton = `
              <button class="ti-btn ti-btn-sm ti-btn-primary !rounded-full" type="button" data-action="inspection-photos" data-id="${escapeHtml(
                id
              )}" title="Inspection Photos">
                <i class="ri-image-line"></i>
                <span class="ml-1">${escapeHtml(row?.photo_count ?? 0)}</span>
              </button>
            `;

            if (row?.is_archived) {
              if (!config.canManage) {
                return `<div class="hstack flex gap-2 justify-end">${photoButton}</div>`;
              }

              return `
                <div class="hstack flex gap-2 justify-end">
                  ${photoButton}
                  <button class="ti-btn ti-btn-sm ti-btn-success !rounded-full" type="button" data-action="restore" data-id="${escapeHtml(
                    id
                  )}">
                    <i class="ri-refresh-line"></i>
                  </button>
                </div>
              `;
            }

            if (!config.canManage) {
              return `<div class="hstack flex gap-2 justify-end">${photoButton}</div>`;
            }

            return `
              <div class="hstack flex gap-2 justify-end">
                ${photoButton}
                <button class="ti-btn ti-btn-sm ti-btn-info !rounded-full" type="button" data-action="edit-inspection" data-id="${escapeHtml(
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

    window.__gsoInspectionsReload = () => reload(table);

    const applyFilters = debounce(() => {
      filters.search = (searchInput?.value || "").trim();
      filters.status = (statusFilter?.value || "").trim();
      filters.department_id = (departmentFilter?.value || "").trim();
      filters.archived = (recordStatusFilter?.value || "active").trim();
      reload(table);
    });

    searchInput?.addEventListener("input", applyFilters);
    statusFilter?.addEventListener("change", applyFilters);
    departmentFilter?.addEventListener("change", applyFilters);
    recordStatusFilter?.addEventListener("change", applyFilters);
    clearButton?.addEventListener("click", () => {
      if (searchInput) searchInput.value = "";
      if (statusFilter) statusFilter.value = "";
      if (departmentFilter) departmentFilter.value = "";
      if (recordStatusFilter) recordStatusFilter.value = "active";

      filters = {
        search: "",
        status: "",
        department_id: "",
        archived: "active",
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
        title: isRestore ? "Restore inspection?" : "Archive inspection?",
        text: isRestore
          ? "This will restore the inspection record."
          : "This will archive the inspection record.",
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
