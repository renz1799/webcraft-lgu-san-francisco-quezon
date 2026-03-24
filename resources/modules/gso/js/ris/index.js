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

  function esc(value) {
    return String(value ?? "")
      .replace(/&/g, "&amp;")
      .replace(/</g, "&lt;")
      .replace(/>/g, "&gt;")
      .replace(/"/g, "&quot;")
      .replace(/'/g, "&#39;");
  }

  function badge(text, tone = "secondary") {
    return `<span class="ti-badge bg-${tone}/10 text-${tone}">${esc(text)}</span>`;
  }

  function statusBadge(value) {
    const status = String(value || "").toLowerCase().trim();

    if (status === "draft") return badge("Draft", "secondary");
    if (status === "submitted") return badge("Submitted", "info");
    if (status === "issued") return badge("Issued", "success");
    if (status === "rejected") return badge("Rejected", "danger");

    return badge(status || "-", "secondary");
  }

  function formatDate(value) {
    const text = String(value || "").trim();
    return text ? esc(text.slice(0, 10)) : "-";
  }

  function buildUrlFromTemplate(template, id) {
    return String(template || "").replace("__RIS_ID__", encodeURIComponent(String(id || "")));
  }

  onReady(function () {
    const config = window.__ris || {};
    const tableElement = document.getElementById("ris-table");
    if (!tableElement) return;

    const infoElement = document.getElementById("ris-info");
    let lastTotal = 0;

    function setInfoText(text) {
      if (infoElement) {
        infoElement.textContent = text;
      }
    }

    function getFilters() {
      if (typeof window.__risGetParams === "function") {
        return window.__risGetParams() || {};
      }

      return {
        search: "",
        status: "",
        record_status: "",
        date_from: "",
        date_to: "",
        fund: "",
      };
    }

    function isArchivedView() {
      return String(getFilters().record_status || "").toLowerCase().trim() === "archived";
    }

    function getCsrf() {
      return document.querySelector('meta[name="csrf-token"]')?.content || config.csrf || "";
    }

    const table = new Tabulator(tableElement, {
      layout: "fitColumns",
      responsiveLayout: "collapse",
      placeholder: "No RIS found.",
      pagination: true,
      paginationMode: "remote",
      paginationSize: 15,
      paginationSizeSelector: [15, 25, 50, 100],
      ajaxURL: config.ajaxUrl,
      ajaxConfig: "GET",
      ajaxLoader: false,
      paginationDataSent: { page: "page", size: "size" },
      ajaxParams: function (_url, _config, params) {
        return { ...(params || {}), ...getFilters() };
      },
      ajaxResponse: function (_url, _params, response) {
        lastTotal = Number(response?.total ?? 0);

        return {
          data: Array.isArray(response?.data) ? response.data : [],
          last_page: Number(response?.last_page ?? 1),
        };
      },
      columns: [
        {
          title: "RIS No.",
          field: "ris_number",
          minWidth: 180,
          formatter: (cell) => {
            const value = String(cell.getValue() || "").trim();
            return value ? `<span class="font-semibold">${esc(value)}</span>` : "-";
          },
        },
        {
          title: "Date",
          field: "ris_date",
          width: 120,
          formatter: (cell) => formatDate(cell.getValue()),
        },
        {
          title: "Fund",
          field: "fund",
          minWidth: 180,
          formatter: (cell) => {
            const value = String(cell.getValue() || "").trim();
            return value ? esc(value) : "-";
          },
        },
        {
          title: "Status",
          field: "status",
          width: 140,
          formatter: (cell) => statusBadge(cell.getValue()),
        },
        {
          title: "Purpose",
          field: "purpose",
          minWidth: 320,
          formatter: (cell) => {
            const value = String(cell.getValue() || "").trim();
            return value ? esc(value) : "-";
          },
        },
        {
          title: "Actions",
          field: "id",
          width: 160,
          hozAlign: "right",
          headerSort: false,
          formatter: function (cell) {
            const id = cell.getValue();
            if (!id) return "";

            const editUrl = buildUrlFromTemplate(config.editUrlTemplate, id);
            const deleteUrl = buildUrlFromTemplate(config.deleteUrlTemplate, id);
            const restoreUrl = buildUrlFromTemplate(config.restoreUrlTemplate, id);
            const archived = isArchivedView();

            let html = `<div class="hstack flex gap-3 text-[.9375rem] justify-end">`;

            if (config.canEdit !== false && editUrl) {
              html += `
                <a
                  aria-label="Edit RIS"
                  href="${esc(editUrl)}"
                  class="ti-btn btn-wave ti-btn-sm ti-btn-primary !rounded-full"
                  data-action="ris-edit"
                  data-id="${esc(id)}"
                >
                  <i class="ri-edit-2-line"></i>
                </a>
              `;
            }

            if (!archived && config.canDelete === true) {
              html += `
                <a
                  aria-label="Delete RIS"
                  href="javascript:void(0);"
                  class="ti-btn btn-wave ti-btn-sm ti-btn-danger !rounded-full"
                  data-action="ris-delete"
                  data-id="${esc(id)}"
                  data-delete-url="${esc(deleteUrl)}"
                >
                  <i class="ri-delete-bin-2-line"></i>
                </a>
              `;
            }

            if (archived && config.canRestore === true) {
              html += `
                <a
                  aria-label="Restore RIS"
                  href="javascript:void(0);"
                  class="ti-btn btn-wave ti-btn-sm ti-btn-success !rounded-full"
                  data-action="ris-restore"
                  data-id="${esc(id)}"
                  data-restore-url="${esc(restoreUrl)}"
                >
                  <i class="ri-arrow-go-back-line"></i>
                </a>
              `;
            }

            html += `</div>`;
            return html;
          },
        },
      ],
    });

    function updateInfo() {
      const page = table.getPage() || 1;
      const pageSize = table.getPageSize ? (table.getPageSize() || 0) : 0;

      if (!lastTotal) {
        const rowsCount = table.getDataCount ? table.getDataCount("active") : 0;
        setInfoText(rowsCount ? `Showing 1-${rowsCount} records` : "No records found");
        return;
      }

      const start = (page - 1) * pageSize + 1;
      const end = Math.min(start + pageSize - 1, lastTotal);

      if (start > lastTotal) {
        setInfoText(`Showing 0 of ${lastTotal} records`);
        return;
      }

      setInfoText(`Showing ${start}-${end} of ${lastTotal} record(s).`);
    }

    function reload() {
      tableElement.classList.add("is-loading");
      setInfoText("Updating...");

      const page = table.getPage();
      if (page && page !== 1) {
        table.setPage(1);
        return;
      }

      table.setData();
    }

    window.__risReload = reload;

    table.on("dataLoaded", function () {
      tableElement.classList.remove("is-loading");
      updateInfo();
    });

    table.on("pageLoaded", function () {
      updateInfo();
    });

    tableElement.addEventListener("click", async function (event) {
      const actionLink = event.target.closest("a[data-action]");
      if (!actionLink) return;

      const action = actionLink.getAttribute("data-action") || "";
      if (action === "ris-edit") return;

      const csrf = getCsrf();

      if (action === "ris-delete") {
        event.preventDefault();

        const result = await Swal.fire({
          title: "Delete RIS?",
          text: "This will archive the RIS record.",
          icon: "warning",
          showCancelButton: true,
          confirmButtonText: "Yes, delete",
          cancelButtonText: "Cancel",
          confirmButtonColor: "#d33",
        });

        if (!result.isConfirmed) return;

        const response = await fetch(actionLink.getAttribute("data-delete-url") || "", {
          method: "DELETE",
          headers: {
            "X-Requested-With": "XMLHttpRequest",
            "X-CSRF-TOKEN": csrf,
            Accept: "application/json",
          },
        });

        const contentType = response.headers.get("content-type") || "";
        const payload = contentType.includes("application/json")
          ? await response.json().catch(() => null)
          : null;

        if (!response.ok) {
          await Swal.fire({
            title: "Error",
            text: payload?.message || "Failed to delete RIS.",
            icon: "error",
          });
          return;
        }

        await Swal.fire({
          title: "Deleted",
          text: payload?.message || "RIS deleted.",
          icon: "success",
          timer: 1200,
          showConfirmButton: false,
        });

        reload();
      }

      if (action === "ris-restore") {
        event.preventDefault();

        const result = await Swal.fire({
          title: "Restore RIS?",
          text: "This will restore the archived RIS record.",
          icon: "question",
          showCancelButton: true,
          confirmButtonText: "Yes, restore",
          cancelButtonText: "Cancel",
        });

        if (!result.isConfirmed) return;

        const response = await fetch(actionLink.getAttribute("data-restore-url") || "", {
          method: "PATCH",
          headers: {
            "X-Requested-With": "XMLHttpRequest",
            "X-CSRF-TOKEN": csrf,
            Accept: "application/json",
          },
        });

        const contentType = response.headers.get("content-type") || "";
        const payload = contentType.includes("application/json")
          ? await response.json().catch(() => null)
          : null;

        if (!response.ok) {
          await Swal.fire({
            title: "Error",
            text: payload?.message || "Failed to restore RIS.",
            icon: "error",
          });
          return;
        }

        await Swal.fire({
          title: "Restored",
          text: payload?.message || "RIS restored.",
          icon: "success",
          timer: 1200,
          showConfirmButton: false,
        });

        reload();
      }
    });

    setInfoText("Loading...");
  });
})();
