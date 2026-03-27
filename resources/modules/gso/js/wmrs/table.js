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
      .replace(/\"/g, "&quot;")
      .replace(/'/g, "&#39;");
  }

  function badge(text, tone = "secondary") {
    return `<span class="ti-badge bg-${tone}/10 text-${tone}">${esc(text)}</span>`;
  }

  function statusBadge(v) {
    const s = String(v || "").toLowerCase().trim();
    if (s === "draft") return badge("Draft", "secondary");
    if (s === "submitted") return badge("Submitted", "info");
    if (s === "approved") return badge("Approved", "primary");
    if (s === "disposed") return badge("Disposed", "success");
    if (s === "cancelled" || s === "canceled") return badge("Cancelled", "warning");
    return badge(s || "-", "secondary");
  }

  function fmtDate(v) {
    const s = String(v || "").trim();
    if (!s) return "-";
    return esc(s.slice(0, 10));
  }

  function buildUrlFromTpl(tpl, id) {
    const s = String(tpl || "");
    if (!s) return "";
    return s.replace("__WMR_ID__", encodeURIComponent(String(id || "")));
  }

  onReady(function () {
    const cfg = window.__wmr || {};
    const el = document.getElementById("wmr-table");
    if (!el) return;

    if (window.__wmrTable && typeof window.__wmrTable.destroy === "function") {
      try { window.__wmrTable.destroy(); } catch (e) {}
      window.__wmrTable = null;
    }

    const infoEl = document.getElementById("wmr-info");
    const createBtn = document.getElementById("wmr-create");

    function setInfoText(text) {
      if (!infoEl) return;
      infoEl.textContent = text;
    }

    if (!cfg.ajaxUrl) {
      console.error("[wmr] Missing window.__wmr.ajaxUrl");
      setInfoText("Missing ajaxUrl.");
      return;
    }

    let lastTotal = 0;

    function getFilters() {
      if (typeof window.__wmrGetParams === "function") {
        return window.__wmrGetParams() || {};
      }
      return {
        search: "",
        status: "",
        record_status: "",
        date_from: "",
        date_to: "",
        fund_cluster_id: "",
      };
    }

    function isArchivedView() {
      const f = getFilters();
      return String(f?.record_status || "").toLowerCase().trim() === "archived";
    }

    const table = new Tabulator(el, {
      layout: "fitColumns",
      responsiveLayout: "collapse",
      placeholder: "No WMR found.",
      pagination: true,
      paginationMode: "remote",
      paginationSize: 15,
      paginationSizeSelector: [15, 25, 50, 100],
      ajaxURL: cfg.ajaxUrl,
      ajaxConfig: "GET",
      ajaxLoader: false,
      paginationDataSent: { page: "page", size: "size" },
      ajaxParams: function (url, config, params) {
        return { ...(params || {}), ...getFilters() };
      },
      ajaxResponse: function (url, params, response) {
        lastTotal = Number(response?.total ?? 0);
        return {
          data: Array.isArray(response?.data) ? response.data : [],
          last_page: Number(response?.last_page ?? 1),
        };
      },
      columns: [
        {
          title: "WMR No.",
          field: "wmr_number",
          minWidth: 170,
          formatter: (c) => {
            const row = c.getRow().getData() || {};
            const id = row.id;
            const value = String(c.getValue() || "").trim() || "Draft";
            const editUrl = buildUrlFromTpl(cfg.editUrlTemplate, id);
            if (!id || isArchivedView() || !editUrl) {
              return `<span class="font-semibold">${esc(value)}</span>`;
            }
            return `<a href="${esc(editUrl)}" class="font-semibold text-primary hover:underline">${esc(value)}</a>`;
          },
        },
        { title: "Report Date", field: "report_date", width: 130, formatter: (c) => fmtDate(c.getValue()) },
        {
          title: "Fund Cluster",
          field: "fund_cluster",
          minWidth: 180,
          formatter: (c) => esc(String(c.getValue() || "-").trim() || "-"),
        },
        {
          title: "Place of Storage",
          field: "place_of_storage",
          minWidth: 220,
          formatter: (c) => {
            const v = String(c.getValue() || "").trim();
            return v ? esc(v) : "-";
          },
        },
        {
          title: "Custodian",
          field: "custodian_name",
          minWidth: 180,
          formatter: (c) => {
            const v = String(c.getValue() || "").trim();
            return v ? esc(v) : "-";
          },
        },
        {
          title: "Items",
          field: "items_count",
          width: 90,
          hozAlign: "center",
          formatter: (c) => String(Number(c.getValue() ?? 0) || 0),
        },
        { title: "Status", field: "status", width: 130, formatter: (c) => statusBadge(c.getValue()) },
        {
          title: "Remarks",
          field: "remarks",
          minWidth: 220,
          formatter: (c) => {
            const v = String(c.getValue() || "").trim();
            return v ? esc(v) : "-";
          },
        },
        {
          title: "Actions",
          field: "id",
          width: 170,
          hozAlign: "right",
          headerSort: false,
          formatter: function (cell) {
            const id = cell.getValue();
            if (!id) return "";

            const archived = isArchivedView();
            const editUrl = buildUrlFromTpl(cfg.editUrlTemplate, id);
            const delUrl = buildUrlFromTpl(cfg.deleteUrlTemplate, id);
            const restoreUrl = buildUrlFromTpl(cfg.restoreUrlTemplate, id);
            const canDelete = cfg.canDelete === true;
            const canRestore = cfg.canRestore === true;

            let html = `<div class="hstack flex gap-3 text-[.9375rem] justify-end">`;
            if (!archived) {
              if (cfg.canManage === true && editUrl) {
                html += `
                  <a aria-label="Edit WMR"
                    href="${esc(editUrl)}"
                    class="ti-btn btn-wave ti-btn-sm ti-btn-primary !rounded-full">
                    <i class="ri-pencil-line"></i>
                  </a>
                `;
              }
              if (canDelete) {
                html += `
                  <a aria-label="Archive WMR"
                    href="javascript:void(0);"
                    class="ti-btn btn-wave ti-btn-sm ti-btn-danger !rounded-full"
                    data-action="wmr-delete"
                    data-id="${esc(id)}"
                    data-delete-url="${esc(delUrl)}">
                    <i class="ri-delete-bin-2-line"></i>
                  </a>
                `;
              }
            } else if (canRestore) {
              html += `
                <a aria-label="Restore WMR"
                  href="javascript:void(0);"
                  class="ti-btn btn-wave ti-btn-sm ti-btn-success !rounded-full"
                  data-action="wmr-restore"
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

    window.__wmrTable = table;

    function updateInfo() {
      if (!infoEl) return;
      const page = table.getPage() || 1;
      const pageSize = table.getPageSize ? (table.getPageSize() || 0) : 0;
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
      if (page && page !== 1) table.setPage(1);
      else table.setData();
    }

    window.__wmrReload = reload;

    table.on("dataLoaded", function () {
      el.classList.remove("is-loading");
      updateInfo();
    });

    table.on("pageLoaded", function () {
      updateInfo();
    });

    async function createDraft() {
      if (!cfg.createDraftUrl) return;

      createBtn?.setAttribute("disabled", "disabled");
      try {
        const res = await fetch(cfg.createDraftUrl, {
          method: "POST",
          headers: {
            "X-Requested-With": "XMLHttpRequest",
            "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]')?.content || cfg.csrf || "",
            Accept: "application/json",
          },
        });

        const parsed = typeof window.__wmrParseActionResponse === "function"
          ? await window.__wmrParseActionResponse(res, "Failed to create WMR draft.")
          : { ok: res.ok, message: "Failed to create WMR draft.", data: null };

        if (!parsed.ok) {
          await Swal.fire({ title: "Error", text: parsed.message || "Failed to create WMR draft.", icon: "error" });
          return;
        }

        const editUrl = String(parsed?.data?.edit_url || "").trim();
        if (editUrl) {
          window.location.href = editUrl;
          return;
        }

        await Swal.fire({ title: "Draft created", text: parsed.message || "WMR draft created.", icon: "success", timer: 1200, showConfirmButton: false });
        reload();
      } finally {
        createBtn?.removeAttribute("disabled");
      }
    }

    createBtn?.addEventListener("click", function (e) {
      e.preventDefault();
      createDraft();
    });

    el.addEventListener("click", async function (e) {
      const a = e.target.closest("a[data-action]");
      if (!a) return;
      const action = a.getAttribute("data-action") || "";

      if (action === "wmr-delete") {
        e.preventDefault();
        const deleteUrl = a.getAttribute("data-delete-url") || "";
        if (!deleteUrl) return;

        const confirm = await Swal.fire({
          title: "Archive WMR?",
          text: "This will archive (soft delete) the WMR record.",
          icon: "warning",
          showCancelButton: true,
          confirmButtonText: "Yes, archive",
          cancelButtonText: "Cancel",
          confirmButtonColor: "#d33",
        });
        if (!confirm.isConfirmed) return;

        const res = await fetch(deleteUrl, {
          method: "DELETE",
          headers: {
            "X-Requested-With": "XMLHttpRequest",
            "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]')?.content || cfg.csrf || "",
            Accept: "application/json",
          },
        });

        const parsed = typeof window.__wmrParseActionResponse === "function"
          ? await window.__wmrParseActionResponse(res, "Failed to archive WMR.")
          : { ok: res.ok, message: "Failed to archive WMR." };

        if (!parsed.ok) {
          await Swal.fire({ title: "Error", text: parsed.message || "Failed to archive WMR.", icon: "error" });
          return;
        }

        await Swal.fire({ title: "Archived", text: parsed.message || "WMR archived.", icon: "success", timer: 1200, showConfirmButton: false });
        reload();
        return;
      }

      if (action === "wmr-restore") {
        e.preventDefault();
        const restoreUrl = a.getAttribute("data-restore-url") || "";
        if (!restoreUrl) return;

        const confirm = await Swal.fire({
          title: "Restore WMR?",
          text: "This will restore the archived WMR record.",
          icon: "question",
          showCancelButton: true,
          confirmButtonText: "Yes, restore",
          cancelButtonText: "Cancel",
        });
        if (!confirm.isConfirmed) return;

        const res = await fetch(restoreUrl, {
          method: "PATCH",
          headers: {
            "X-Requested-With": "XMLHttpRequest",
            "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]')?.content || cfg.csrf || "",
            Accept: "application/json",
          },
        });

        const parsed = typeof window.__wmrParseActionResponse === "function"
          ? await window.__wmrParseActionResponse(res, "Failed to restore WMR.")
          : { ok: res.ok, message: "Failed to restore WMR." };

        if (!parsed.ok) {
          await Swal.fire({ title: "Error", text: parsed.message || "Failed to restore WMR.", icon: "error" });
          return;
        }

        await Swal.fire({ title: "Restored", text: parsed.message || "WMR restored.", icon: "success", timer: 1200, showConfirmButton: false });
        reload();
      }
    });

    setInfoText("Loading...");
  });
})();
