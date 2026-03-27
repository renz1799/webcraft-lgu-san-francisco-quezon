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
    if (s === "finalized") return badge("Finalized", "success");
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
    return s.replace("__ITR_ID__", encodeURIComponent(String(id || "")));
  }

  onReady(function () {
    const cfg = window.__itr || {};
    const el = document.getElementById("itr-table");
    if (!el) return;

    if (window.__itrTable && typeof window.__itrTable.destroy === "function") {
      try { window.__itrTable.destroy(); } catch (e) {}
      window.__itrTable = null;
    }

    const infoEl = document.getElementById("itr-info");
    const createBtn = document.getElementById("itr-create");

    function setInfoText(text) {
      if (!infoEl) return;
      infoEl.textContent = text;
    }

    if (!cfg.ajaxUrl) {
      console.error("[itr] Missing window.__itr.ajaxUrl");
      setInfoText("Missing ajaxUrl.");
      return;
    }

    let lastTotal = 0;

    function getFilters() {
      if (typeof window.__itrGetParams === "function") {
        return window.__itrGetParams() || {};
      }
      return {
        search: "",
        status: "",
        record_status: "",
        date_from: "",
        date_to: "",
        from_department_id: "",
        to_department_id: "",
        from_fund_source_id: "",
        to_fund_source_id: "",
      };
    }

    function isArchivedView() {
      const f = getFilters();
      return String(f?.record_status || "").toLowerCase().trim() === "archived";
    }

    const table = new Tabulator(el, {
      layout: "fitColumns",
      responsiveLayout: "collapse",
      placeholder: "No ITR found.",
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
          title: "ITR No.",
          field: "itr_number",
          minWidth: 170,
          formatter: (c) => {
            const row = c.getRow().getData() || {};
            const v = String(c.getValue() || "").trim();
            const label = v || "Draft";
            const editUrl = buildUrlFromTpl(cfg.editUrlTemplate, row.id);
            if (!cfg.canManage || isArchivedView() || !editUrl) {
              return `<span class="font-semibold">${esc(label)}</span>`;
            }
            return `<a href="${esc(editUrl)}" class="font-semibold text-primary hover:underline">${esc(label)}</a>`;
          },
        },
        { title: "Transfer Date", field: "transfer_date", width: 130, formatter: (c) => fmtDate(c.getValue()) },
        {
          title: "From",
          field: "from_department",
          minWidth: 220,
          formatter: (c) => {
            const row = c.getRow().getData() || {};
            const dept = String(c.getValue() || "-");
            const officer = String(row.from_accountable_officer || "").trim();
            return esc(dept + (officer ? ` (${officer})` : ""));
          },
        },
        {
          title: "To",
          field: "to_department",
          minWidth: 220,
          formatter: (c) => {
            const row = c.getRow().getData() || {};
            const dept = String(c.getValue() || "-");
            const officer = String(row.to_accountable_officer || "").trim();
            return esc(dept + (officer ? ` (${officer})` : ""));
          },
        },
        {
          title: "Type",
          field: "transfer_type",
          width: 130,
          formatter: (c) => {
            const v = String(c.getValue() || "").trim();
            return v ? esc(v.charAt(0).toUpperCase() + v.slice(1)) : "-";
          },
        },
        {
          title: "Items",
          field: "items_count",
          width: 90,
          hozAlign: "center",
          formatter: (c) => String(Number(c.getValue() ?? 0) || 0),
        },
        { title: "Status", field: "status", width: 140, formatter: (c) => statusBadge(c.getValue()) },
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
          width: 210,
          hozAlign: "right",
          headerSort: false,
          formatter: function (cell) {
            const id = cell.getValue();
            if (!id) return "";

            const archived = isArchivedView();
            const editUrl = buildUrlFromTpl(cfg.editUrlTemplate, id);
            const delUrl = buildUrlFromTpl(cfg.deleteUrlTemplate, id);
            const restoreUrl = buildUrlFromTpl(cfg.restoreUrlTemplate, id);
            const canManage = cfg.canManage === true;
            const canDelete = cfg.canDelete === true;
            const canRestore = cfg.canRestore === true;

            let html = `<div class="hstack flex gap-3 text-[.9375rem] justify-end">`;
            if (!archived) {
              if (canManage && editUrl) {
                html += `
                  <a aria-label="Edit ITR"
                    href="${esc(editUrl)}"
                    class="ti-btn btn-wave ti-btn-sm ti-btn-primary !rounded-full">
                    <i class="ri-pencil-line"></i>
                  </a>
                `;
              }
              if (canDelete) {
                html += `
                  <a aria-label="Archive ITR"
                    href="javascript:void(0);"
                    class="ti-btn btn-wave ti-btn-sm ti-btn-danger !rounded-full"
                    data-action="itr-delete"
                    data-id="${esc(id)}"
                    data-delete-url="${esc(delUrl)}">
                    <i class="ri-delete-bin-2-line"></i>
                  </a>
                `;
              }
            } else if (canRestore) {
              html += `
                <a aria-label="Restore ITR"
                  href="javascript:void(0);"
                  class="ti-btn btn-wave ti-btn-sm ti-btn-success !rounded-full"
                  data-action="itr-restore"
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

    window.__itrTable = table;

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

    window.__itrReload = reload;

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

        const parsed = typeof window.__itrParseActionResponse === "function"
          ? await window.__itrParseActionResponse(res, "Failed to create ITR draft.")
          : { ok: res.ok, message: "Failed to create ITR draft.", data: null };

        if (!parsed.ok) {
          await Swal.fire({ title: "Error", text: parsed.message || "Failed to create ITR draft.", icon: "error" });
          return;
        }

        const editUrl = parsed.data?.edit_url || buildUrlFromTpl(cfg.editUrlTemplate, parsed.data?.itr_id);
        if (editUrl) {
          window.location.href = editUrl;
          return;
        }

        await Swal.fire({ title: "Draft created", text: parsed.message || "ITR draft created.", icon: "success" });
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

      if (action === "itr-delete") {
        e.preventDefault();
        const deleteUrl = a.getAttribute("data-delete-url") || "";
        if (!deleteUrl) return;

        const confirm = await Swal.fire({
          title: "Archive ITR?",
          text: "This will archive (soft delete) the ITR record.",
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

        const parsed = typeof window.__itrParseActionResponse === "function"
          ? await window.__itrParseActionResponse(res, "Failed to archive ITR.")
          : { ok: res.ok, message: "Failed to archive ITR." };

        if (!parsed.ok) {
          await Swal.fire({ title: "Error", text: parsed.message || "Failed to archive ITR.", icon: "error" });
          return;
        }

        await Swal.fire({ title: "Archived", text: parsed.message || "ITR archived.", icon: "success", timer: 1200, showConfirmButton: false });
        reload();
        return;
      }

      if (action === "itr-restore") {
        e.preventDefault();
        const restoreUrl = a.getAttribute("data-restore-url") || "";
        if (!restoreUrl) return;

        const confirm = await Swal.fire({
          title: "Restore ITR?",
          text: "This will restore the archived ITR record.",
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

        const parsed = typeof window.__itrParseActionResponse === "function"
          ? await window.__itrParseActionResponse(res, "Failed to restore ITR.")
          : { ok: res.ok, message: "Failed to restore ITR." };

        if (!parsed.ok) {
          await Swal.fire({ title: "Error", text: parsed.message || "Failed to restore ITR.", icon: "error" });
          return;
        }

        await Swal.fire({ title: "Restored", text: parsed.message || "ITR restored.", icon: "success", timer: 1200, showConfirmButton: false });
        reload();
      }
    });

    setInfoText("Loading...");
  });
})();


