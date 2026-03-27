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

  function esc(s) {
    return String(s ?? "")
      .replace(/&/g, "&amp;")
      .replace(/</g, "&lt;")
      .replace(/>/g, "&gt;")
      .replace(/"/g, "&quot;")
      .replace(/'/g, "&#39;");
  }

  function badge(text, tone = "secondary") {
    return `<span class="ti-badge bg-${tone}/10 text-${tone}">${esc(text)}</span>`;
  }

  function statusBadge(v) {
    const status = String(v || "").toLowerCase().trim();

    if (status === "draft") return badge("Draft", "secondary");
    if (status === "submitted") return badge("Submitted", "info");
    if (status === "finalized") return badge("Finalized", "success");
    if (status === "cancelled" || status === "canceled") return badge("Cancelled", "warning");

    return badge(status || "-", "secondary");
  }

  function fmtDate(v) {
    const value = String(v || "").trim();
    if (!value) return "-";

    return esc(value.slice(0, 10));
  }

  function buildUrlFromTpl(tpl, id) {
    const value = String(tpl || "");
    if (!value) return "";

    return value.replace("__PAR_ID__", encodeURIComponent(String(id || "")));
  }

  onReady(function () {
    const cfg = window.__pars || {};
    const el = document.getElementById("par-table");
    if (!el) return;

    if (window.__parTable && typeof window.__parTable.destroy === "function") {
      try {
        window.__parTable.destroy();
      } catch (error) {
        // Ignore stale Tabulator teardown errors during page revisits.
      }

      window.__parTable = null;
    }

    const infoEl = document.getElementById("par-info");

    function setInfoText(text) {
      if (!infoEl) return;
      infoEl.textContent = text;
    }

    if (!cfg.ajaxUrl) {
      console.error("[par] Missing window.__pars.ajaxUrl");
      setInfoText("Missing ajaxUrl.");
      return;
    }

    let lastTotal = 0;

    function getFilters() {
      if (typeof window.__parGetParams === "function") {
        return window.__parGetParams() || {};
      }

      return {
        search: "",
        status: "",
        record_status: "",
        date_from: "",
        date_to: "",
        department_id: "",
      };
    }

    function isArchivedView() {
      const filters = getFilters();
      return String(filters?.record_status || "").toLowerCase().trim() === "archived";
    }

    function getCsrf() {
      return document.querySelector('meta[name="csrf-token"]')?.content || cfg.csrf || "";
    }

    const table = new Tabulator(el, {
      layout: "fitColumns",
      responsiveLayout: "collapse",
      placeholder: "No PAR found.",
      pagination: true,
      paginationMode: "remote",
      paginationSize: 15,
      paginationSizeSelector: [15, 25, 50, 100],
      ajaxURL: cfg.ajaxUrl,
      ajaxConfig: "GET",
      ajaxLoader: false,
      paginationDataSent: { page: "page", size: "size" },
      ajaxParams(url, config, params) {
        return { ...(params || {}), ...getFilters() };
      },
      ajaxResponse(url, params, response) {
        lastTotal = Number(response?.total ?? 0);

        return {
          data: Array.isArray(response?.data) ? response.data : [],
          last_page: Number(response?.last_page ?? 1),
        };
      },
      columns: [
        {
          title: "PAR No.",
          field: "par_number",
          minWidth: 190,
          formatter(cell) {
            const value = String(cell.getValue() || "").trim();
            return value ? `<span class="font-semibold">${esc(value)}</span>` : "-";
          },
        },
        {
          title: "Issued",
          field: "issued_date",
          width: 120,
          formatter(cell) {
            return fmtDate(cell.getValue());
          },
        },
        {
          title: "Department",
          field: "department",
          minWidth: 260,
          formatter(cell) {
            const value = String(cell.getValue() || "").trim();
            return value ? esc(value) : "-";
          },
        },
        {
          title: "Officer",
          field: "person_accountable",
          minWidth: 220,
          formatter(cell) {
            const value = String(cell.getValue() || "").trim();
            return value ? esc(value) : "-";
          },
        },
        {
          title: "Items",
          field: "items_count",
          width: 90,
          hozAlign: "center",
          formatter(cell) {
            const count = Number(cell.getValue() ?? 0);
            return Number.isFinite(count) ? String(count) : "0";
          },
        },
        {
          title: "Status",
          field: "status",
          width: 140,
          formatter(cell) {
            return statusBadge(cell.getValue());
          },
        },
        {
          title: "Remarks",
          field: "remarks",
          minWidth: 260,
          formatter(cell) {
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
          formatter(cell) {
            const id = cell.getValue();
            if (!id) return "";

            const archived = isArchivedView();
            const showUrl = buildUrlFromTpl(cfg.showUrlTemplate, id);
            const deleteUrl = buildUrlFromTpl(cfg.deleteUrlTemplate, id);
            const restoreUrl = buildUrlFromTpl(cfg.restoreUrlTemplate, id);
            const canDelete = cfg.canDelete === true;
            const canRestore = cfg.canRestore === true;

            let html = `<div class="hstack flex gap-3 text-[.9375rem] justify-end">`;

            if (showUrl) {
              html += `
                <a aria-label="View PAR"
                  href="${esc(showUrl)}"
                  class="ti-btn btn-wave ti-btn-sm ti-btn-primary !rounded-full"
                  data-action="par-view"
                  data-id="${esc(id)}">
                  <i class="ri-eye-line"></i>
                </a>
              `;
            }

            if (!archived) {
              if (canDelete) {
                html += `
                  <a aria-label="Delete PAR"
                    href="javascript:void(0);"
                    class="ti-btn btn-wave ti-btn-sm ti-btn-danger !rounded-full"
                    data-action="par-delete"
                    data-id="${esc(id)}"
                    data-delete-url="${esc(deleteUrl)}">
                    <i class="ri-delete-bin-2-line"></i>
                  </a>
                `;
              }
            } else if (canRestore) {
              html += `
                <a aria-label="Restore PAR"
                  href="javascript:void(0);"
                  class="ti-btn btn-wave ti-btn-sm ti-btn-success !rounded-full"
                  data-action="par-restore"
                  data-id="${esc(id)}"
                  data-restore-url="${esc(restoreUrl)}">
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

    window.__parTable = table;

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

    function reload() {
      el.classList.add("is-loading");
      setInfoText("Updating...");

      const page = table.getPage();
      if (page && page !== 1) {
        table.setPage(1);
      } else {
        table.setData();
      }
    }

    window.__parReload = reload;

    table.on("dataLoaded", function () {
      el.classList.remove("is-loading");
      updateInfo();
    });

    table.on("pageLoaded", function () {
      updateInfo();
    });

    el.addEventListener("click", async function (event) {
      const actionEl = event.target.closest("a[data-action]");
      if (!actionEl) return;

      const action = actionEl.getAttribute("data-action") || "";
      if (action === "par-view") return;

      if (action === "par-delete") {
        event.preventDefault();
        if (!cfg.canDelete) return;

        const deleteUrl = actionEl.getAttribute("data-delete-url") || "";
        if (!deleteUrl) return;

        const result = await Swal.fire({
          title: "Delete PAR?",
          text: "This will archive (soft delete) the PAR record.",
          icon: "warning",
          showCancelButton: true,
          confirmButtonText: "Yes, delete",
          cancelButtonText: "Cancel",
          confirmButtonColor: "#d33",
        });
        if (!result.isConfirmed) return;

        const response = await fetch(deleteUrl, {
          method: "DELETE",
          headers: {
            "X-Requested-With": "XMLHttpRequest",
            "X-CSRF-TOKEN": getCsrf(),
            Accept: "application/json",
          },
        });

        const contentType = response.headers.get("content-type") || "";
        const data = contentType.includes("application/json") ? await response.json().catch(() => null) : null;

        if (!response.ok) {
          await Swal.fire({
            title: "Error",
            text: data?.message || "Failed to delete PAR.",
            icon: "error",
          });
          return;
        }

        await Swal.fire({
          title: "Deleted",
          text: data?.message || "PAR deleted.",
          icon: "success",
          timer: 1200,
          showConfirmButton: false,
        });
        reload();
        return;
      }

      if (action === "par-restore") {
        event.preventDefault();
        if (!cfg.canRestore) return;

        const restoreUrl = actionEl.getAttribute("data-restore-url") || "";
        if (!restoreUrl) return;

        const result = await Swal.fire({
          title: "Restore PAR?",
          text: "This will restore the archived PAR record.",
          icon: "question",
          showCancelButton: true,
          confirmButtonText: "Yes, restore",
          cancelButtonText: "Cancel",
        });
        if (!result.isConfirmed) return;

        const response = await fetch(restoreUrl, {
          method: "PATCH",
          headers: {
            "X-Requested-With": "XMLHttpRequest",
            "X-CSRF-TOKEN": getCsrf(),
            Accept: "application/json",
          },
        });

        const contentType = response.headers.get("content-type") || "";
        const data = contentType.includes("application/json") ? await response.json().catch(() => null) : null;

        if (!response.ok) {
          await Swal.fire({
            title: "Error",
            text: data?.message || "Failed to restore PAR.",
            icon: "error",
          });
          return;
        }

        await Swal.fire({
          title: "Restored",
          text: data?.message || "PAR restored.",
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
