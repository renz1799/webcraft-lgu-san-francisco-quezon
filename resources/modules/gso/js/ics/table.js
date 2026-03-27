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
    return s.replace("__ICS_ID__", encodeURIComponent(String(id || "")));
  }

  onReady(function () {
    const cfg = window.__ics || {};
    const el = document.getElementById("ics-table");
    if (!el) return;

    if (window.__icsTable && typeof window.__icsTable.destroy === "function") {
      try { window.__icsTable.destroy(); } catch (e) {}
      window.__icsTable = null;
    }

    const infoEl = document.getElementById("ics-info");
    function setInfoText(text) {
      if (!infoEl) return;
      infoEl.textContent = text;
    }

    if (!cfg.ajaxUrl) {
      console.error("[ics] Missing window.__ics.ajaxUrl");
      setInfoText("Missing ajaxUrl.");
      return;
    }

    let lastTotal = 0;

    function getFilters() {
      if (typeof window.__icsGetParams === "function") {
        return window.__icsGetParams() || {};
      }
      return { search: "", status: "", record_status: "", date_from: "", date_to: "", department_id: "", fund_source_id: "" };
    }

    function isArchivedView() {
      const f = getFilters();
      return String(f?.record_status || "").toLowerCase().trim() === "archived";
    }

    const table = new Tabulator(el, {
      layout: "fitColumns",
      responsiveLayout: "collapse",
      placeholder: "No ICS found.",
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
          title: "ICS No.",
          field: "ics_number",
          minWidth: 180,
          formatter: (c) => {
            const row = c.getRow().getData() || {};
            const v = String(c.getValue() || "").trim();
            const label = v || "Draft";
            if (!cfg.canEdit || isArchivedView()) {
              return `<span class="font-semibold">${esc(label)}</span>`;
            }
            const editUrl = buildUrlFromTpl(cfg.editUrlTemplate, row.id);
            return editUrl
              ? `<a href="${esc(editUrl)}" class="font-semibold text-primary hover:underline">${esc(label)}</a>`
              : `<span class="font-semibold">${esc(label)}</span>`;
          },
        },
        { title: "Issued", field: "issued_date", width: 120, formatter: (c) => fmtDate(c.getValue()) },
        {
          title: "Department",
          field: "department",
          minWidth: 240,
          formatter: (c) => esc(String(c.getValue() || "-") || "-"),
        },
        {
          title: "Fund Source",
          field: "fund_source",
          minWidth: 220,
          formatter: (c) => esc(String(c.getValue() || "-") || "-"),
        },
        {
          title: "Received By",
          field: "received_by_name",
          minWidth: 220,
          formatter: (c) => {
            const row = c.getRow().getData() || {};
            const name = String(c.getValue() || "").trim();
            const office = String(row.received_by_office || "").trim();
            if (!name && !office) return "-";
            return esc(name + (office ? ` (${office})` : ""));
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
          minWidth: 240,
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
            const canEdit = cfg.canEdit === true;
            const canDelete = cfg.canDelete === true;
            const canRestore = cfg.canRestore === true;

            let html = `<div class="hstack flex gap-3 text-[.9375rem] justify-end">`;
            if (!archived && canEdit && editUrl) {
              html += `
                <a aria-label="Open ICS"
                  href="${esc(editUrl)}"
                  class="ti-btn btn-wave ti-btn-sm ti-btn-primary !rounded-full">
                  <i class="ri-pencil-line"></i>
                </a>
              `;
            }

            if (!archived) {
              if (canDelete) {
                html += `
                  <a aria-label="Archive ICS"
                    href="javascript:void(0);"
                    class="ti-btn btn-wave ti-btn-sm ti-btn-danger !rounded-full"
                    data-action="ics-delete"
                    data-id="${esc(id)}"
                    data-delete-url="${esc(delUrl)}">
                    <i class="ri-delete-bin-2-line"></i>
                  </a>
                `;
              }
            } else if (canRestore) {
              html += `
                <a aria-label="Restore ICS"
                  href="javascript:void(0);"
                  class="ti-btn btn-wave ti-btn-sm ti-btn-success !rounded-full"
                  data-action="ics-restore"
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

    window.__icsTable = table;

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

    window.__icsReload = reload;

    table.on("dataLoaded", function () {
      el.classList.remove("is-loading");
      updateInfo();
    });

    table.on("pageLoaded", function () {
      updateInfo();
    });

    el.addEventListener("click", async function (e) {
      const a = e.target.closest("a[data-action]");
      if (!a) return;

      const action = a.getAttribute("data-action") || "";

      if (action === "ics-delete") {
        e.preventDefault();
        const deleteUrl = a.getAttribute("data-delete-url") || "";
        if (!deleteUrl) return;

        const res = await Swal.fire({
          title: "Archive ICS?",
          text: "This will archive (soft delete) the ICS record.",
          icon: "warning",
          showCancelButton: true,
          confirmButtonText: "Yes, archive",
          cancelButtonText: "Cancel",
          confirmButtonColor: "#d33",
        });
        if (!res.isConfirmed) return;

        const r = await fetch(deleteUrl, {
          method: "DELETE",
          headers: {
            "X-Requested-With": "XMLHttpRequest",
            "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]')?.content || cfg.csrf || "",
            Accept: "application/json",
          },
        });

        const ct = r.headers.get("content-type") || "";
        const data = ct.includes("application/json") ? await r.json().catch(() => null) : null;
        if (!r.ok) {
          await Swal.fire({ title: "Error", text: data?.message || "Failed to archive ICS.", icon: "error" });
          return;
        }

        await Swal.fire({ title: "Archived", text: data?.message || "ICS archived.", icon: "success", timer: 1200, showConfirmButton: false });
        reload();
        return;
      }

      if (action === "ics-restore") {
        e.preventDefault();
        const restoreUrl = a.getAttribute("data-restore-url") || "";
        if (!restoreUrl) return;

        const res = await Swal.fire({
          title: "Restore ICS?",
          text: "This will restore the archived ICS record.",
          icon: "question",
          showCancelButton: true,
          confirmButtonText: "Yes, restore",
          cancelButtonText: "Cancel",
        });
        if (!res.isConfirmed) return;

        const r = await fetch(restoreUrl, {
          method: "PATCH",
          headers: {
            "X-Requested-With": "XMLHttpRequest",
            "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]')?.content || cfg.csrf || "",
            Accept: "application/json",
          },
        });

        const ct = r.headers.get("content-type") || "";
        const data = ct.includes("application/json") ? await r.json().catch(() => null) : null;
        if (!r.ok) {
          await Swal.fire({ title: "Error", text: data?.message || "Failed to restore ICS.", icon: "error" });
          return;
        }

        await Swal.fire({ title: "Restored", text: data?.message || "ICS restored.", icon: "success", timer: 1200, showConfirmButton: false });
        reload();
      }
    });

    setInfoText("Loading...");
  });
})();