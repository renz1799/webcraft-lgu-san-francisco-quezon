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

  function buildUrl(template, id) {
    return String(template || "").replace("__ID__", encodeURIComponent(String(id || "")));
  }

  async function parseErrorResponse(response) {
    const contentType = response.headers.get("content-type") || "";
    const data = contentType.includes("application/json")
      ? await response.json().catch(() => null)
      : null;

    return (
      data?.message ||
      data?.error ||
      (response.status === 403
        ? "You do not have permission to manage AIR records."
        : response.status === 404
        ? "The AIR record could not be found."
        : "The request could not be completed.")
    );
  }

  onReady(function () {
    const tableElement = document.getElementById("gso-air-table");
    if (!tableElement) return;

    const config = window.__gsoAir || {};
    const infoElement = document.getElementById("gso-air-info");
    const searchInput = document.getElementById("gso-air-search");
    const statusSelect = document.getElementById("gso-air-status-filter");
    const departmentSelect = document.getElementById("gso-air-department-filter");
    const fundSelect = document.getElementById("gso-air-fund-filter");
    const supplierInput = document.getElementById("gso-air-supplier-filter");
    const dateFromInput = document.getElementById("gso-air-date-from");
    const dateToInput = document.getElementById("gso-air-date-to");
    const recordStatusSelect = document.getElementById("gso-air-record-status");
    const clearButton = document.getElementById("gso-air-clear");

    let filters = {
      search: "",
      status: "",
      department_id: "",
      fund_source_id: "",
      supplier: "",
      date_from: "",
      date_to: "",
      archived: recordStatusSelect?.value || "active",
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

      setInfo(`Showing ${start}-${end} of ${lastTotal} AIR record(s)`);
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
      placeholder: "No AIR records found.",
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
          title: "PO / AIR",
          field: "label",
          minWidth: 220,
          formatter: (cell) => {
            const row = cell.getRow().getData() || {};
            const poNumber = escapeHtml(row?.po_number || "Draft AIR");
            const airNumber = escapeHtml(row?.air_number || "");
            const continuation = escapeHtml(row?.continuation_label || "Root AIR");

            return airNumber
              ? `${poNumber}<div class="text-xs text-[#8c9097] mt-1">${airNumber} | ${continuation}</div>`
              : `${poNumber}<div class="text-xs text-[#8c9097] mt-1">${continuation}</div>`;
          },
        },
        {
          title: "Supplier",
          field: "supplier_name",
          minWidth: 220,
          formatter: (cell) => escapeHtml(cell.getValue() || "-"),
        },
        {
          title: "Department",
          field: "department_label",
          minWidth: 220,
          formatter: (cell) => escapeHtml(cell.getValue() || "-"),
        },
        {
          title: "Fund Source",
          field: "fund_source_label",
          minWidth: 200,
          formatter: (cell) => escapeHtml(cell.getValue() || "-"),
        },
        {
          title: "Status",
          field: "status_text",
          width: 150,
          formatter: (cell) => {
            const row = cell.getRow().getData() || {};
            const tone =
              row?.status === "submitted"
                ? "bg-primary/10 text-primary"
                : row?.status === "in_progress"
                ? "bg-warning/10 text-warning"
                : row?.status === "inspected"
                ? "bg-success/10 text-success"
                : "bg-light text-defaulttextcolor";

            return `<span class="inline-flex items-center rounded-full px-2 py-1 text-xs font-medium ${tone}">${escapeHtml(cell.getValue() || "Unknown")}</span>`;
          },
        },
        {
          title: "AIR Date",
          field: "air_date_text",
          width: 150,
          formatter: (cell) => escapeHtml(cell.getValue() || "-"),
        },
        {
          title: "",
          field: "id",
          width: 220,
          hozAlign: "right",
          headerSort: false,
          formatter: (cell) => {
            const row = cell.getRow().getData() || {};
            const id = escapeHtml(cell.getValue() || "");
            const editUrl = escapeHtml(buildUrl(config.editUrlTemplate, row?.id));
            const inspectUrl = escapeHtml(buildUrl(config.inspectUrlTemplate, row?.id));
            const printUrl = escapeHtml(buildUrl(config.printUrlTemplate, row?.id));
            const printButton = `<a class="ti-btn ti-btn-sm ti-btn-light !rounded-full" href="${printUrl}" target="_blank" rel="noopener" title="Open print preview">
                    <i class="ri-printer-line"></i>
                  </a>`;
            const inspectButton =
              !row?.is_archived && row?.status && row?.status !== "draft"
                ? `<a class="ti-btn ti-btn-sm ti-btn-primary !rounded-full" href="${inspectUrl}" title="Open inspection workspace">
                    <i class="ri-search-eye-line"></i>
                  </a>`
                : "";

            if (!config.canManage) {
              return `<div class="hstack flex gap-2 justify-end">
                ${printButton}
                <a class="ti-btn ti-btn-sm ti-btn-light !rounded-full" href="${
                  row?.status && row?.status !== "draft" ? inspectUrl : editUrl
                }"><i class="ri-external-link-line"></i></a>
              </div>`;
            }

            if (row?.is_archived) {
              return `
                <div class="hstack flex gap-2 justify-end">
                  ${printButton}
                  <a class="ti-btn ti-btn-sm ti-btn-light !rounded-full" href="${editUrl}">
                    <i class="ri-external-link-line"></i>
                  </a>
                  <button class="ti-btn ti-btn-sm ti-btn-success !rounded-full" type="button" data-action="restore" data-id="${id}">
                    <i class="ri-refresh-line"></i>
                  </button>
                </div>
              `;
            }

            return `
              <div class="hstack flex gap-2 justify-end">
                ${printButton}
                ${inspectButton}
                <a class="ti-btn ti-btn-sm ti-btn-info !rounded-full" href="${editUrl}">
                  <i class="ri-edit-line"></i>
                </a>
                <button class="ti-btn ti-btn-sm ti-btn-danger !rounded-full" type="button" data-action="delete" data-id="${id}">
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

    function applyFilters() {
      filters.search = (searchInput?.value || "").trim();
      filters.status = (statusSelect?.value || "").trim();
      filters.department_id = (departmentSelect?.value || "").trim();
      filters.fund_source_id = (fundSelect?.value || "").trim();
      filters.supplier = (supplierInput?.value || "").trim();
      filters.date_from = (dateFromInput?.value || "").trim();
      filters.date_to = (dateToInput?.value || "").trim();
      filters.archived = (recordStatusSelect?.value || "active").trim();
      reload(table);
    }

    const debouncedApply = debounce(applyFilters, 350);

    searchInput?.addEventListener("input", debouncedApply);
    statusSelect?.addEventListener("change", applyFilters);
    departmentSelect?.addEventListener("change", applyFilters);
    fundSelect?.addEventListener("change", applyFilters);
    supplierInput?.addEventListener("input", debouncedApply);
    dateFromInput?.addEventListener("change", applyFilters);
    dateToInput?.addEventListener("change", applyFilters);
    recordStatusSelect?.addEventListener("change", applyFilters);

    clearButton?.addEventListener("click", () => {
      filters = {
        search: "",
        status: "",
        department_id: "",
        fund_source_id: "",
        supplier: "",
        date_from: "",
        date_to: "",
        archived: "active",
      };

      if (searchInput) searchInput.value = "";
      if (statusSelect) statusSelect.value = "";
      if (departmentSelect) departmentSelect.value = "";
      if (fundSelect) fundSelect.value = "";
      if (supplierInput) supplierInput.value = "";
      if (dateFromInput) dateFromInput.value = "";
      if (dateToInput) dateToInput.value = "";
      if (recordStatusSelect) recordStatusSelect.value = "active";

      reload(table);
    });

    tableElement.addEventListener("click", async (event) => {
      const actionButton = event.target.closest('[data-action="delete"], [data-action="restore"]');
      if (!actionButton) return;

      const isRestore = actionButton.dataset.action === "restore";
      const id = actionButton.dataset.id || "";
      if (!id) return;

      const confirmation = await Swal.fire({
        icon: isRestore ? "question" : "warning",
        title: isRestore ? "Restore AIR?" : "Archive AIR?",
        text: isRestore
          ? "This will restore the AIR record."
          : "This will archive the AIR record.",
        showCancelButton: true,
        confirmButtonText: isRestore ? "Restore" : "Archive",
        cancelButtonText: "Cancel",
      });

      if (!confirmation.isConfirmed) {
        return;
      }

      const response = await fetch(
        buildUrl(isRestore ? config.restoreUrlTemplate : config.deleteUrlTemplate, id),
        {
          method: isRestore ? "PATCH" : "DELETE",
          headers: {
            Accept: "application/json",
            "X-CSRF-TOKEN": config.csrf || "",
          },
        },
      );

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
