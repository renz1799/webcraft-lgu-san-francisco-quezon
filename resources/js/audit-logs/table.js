import Swal from "sweetalert2";
import "sweetalert2/dist/sweetalert2.min.css";

(function () {
  "use strict";

  function esc(s) {
    return String(s ?? "")
      .replace(/&/g, "&amp;")
      .replace(/</g, "&lt;")
      .replace(/>/g, "&gt;")
      .replace(/\"/g, "&quot;")
      .replace(/'/g, "&#39;");
  }

  function safeParseJson(input, fallback = {}) {
    if (typeof input !== "string" || input.trim() === "") return fallback;

    try {
      return JSON.parse(input);
    } catch (_e) {
      return fallback;
    }
  }

  function pretty(obj) {
    return `<pre style="text-align:left;max-height:320px;overflow:auto;margin:0">${esc(
      JSON.stringify(obj ?? {}, null, 2)
    )}</pre>`;
  }

  function copyToClipboard(value) {
    const text = String(value ?? "");
    if (!text) return Promise.resolve(false);

    if (navigator.clipboard && navigator.clipboard.writeText) {
      return navigator.clipboard.writeText(text).then(() => true).catch(() => false);
    }

    const ta = document.createElement("textarea");
    ta.value = text;
    ta.style.position = "fixed";
    ta.style.opacity = "0";
    document.body.appendChild(ta);
    ta.select();

    try {
      const ok = document.execCommand("copy");
      document.body.removeChild(ta);
      return Promise.resolve(ok);
    } catch (_e) {
      document.body.removeChild(ta);
      return Promise.resolve(false);
    }
  }

  function showToast(icon, title, text = "") {
    return Swal.fire({
      icon,
      title,
      text,
      timer: icon === "success" ? 1200 : undefined,
      showConfirmButton: icon !== "success",
      position: icon === "success" ? "top-end" : "center",
    });
  }

  document.addEventListener("DOMContentLoaded", function () {
    const cfg = window.__audit || {};
    const el = document.getElementById("audit-table");
    if (!el) return;

    if (window.__auditTable && typeof window.__auditTable.destroy === "function") {
      try {
        window.__auditTable.destroy();
      } catch (_e) {}
      window.__auditTable = null;
    }

    const infoEl = document.getElementById("audit-info");
    const ajaxUrl = cfg.ajaxUrl || "";

    if (!ajaxUrl) {
      if (infoEl) infoEl.textContent = "Missing ajaxUrl.";
      return;
    }

    let lastTotal = 0;

    function setInfoText(text) {
      if (!infoEl) return;
      infoEl.textContent = text;
    }

    function getFilters() {
      if (typeof window.__auditGetParams === "function") {
        return window.__auditGetParams() || {};
      }

      return {
        search: "",
        action: "",
        actor_id: "",
        subject_type: "",
        date_from: "",
        date_to: "",
      };
    }

    function getCsrf() {
      return (
        document.querySelector('meta[name="csrf-token"]')?.content ||
        cfg.csrf ||
        ""
      );
    }

    const table = new Tabulator(el, {
      layout: "fitColumns",
      responsiveLayout: "collapse",
      placeholder: "No activity found.",

      pagination: true,
      paginationMode: "remote",
      paginationSize: 15,
      paginationSizeSelector: [15, 25, 50, 100],

      ajaxURL: ajaxUrl,
      ajaxConfig: "GET",
      ajaxLoader: false,

      paginationDataSent: { page: "page", size: "size" },

      ajaxParams: function () {
        return { ...getFilters() };
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
          title: "When",
          field: "created_at_text",
          minWidth: 170,
          formatter: (cell) => esc(cell.getValue() || "-"),
        },
        {
          title: "User",
          field: "actor_name",
          minWidth: 170,
          formatter: function (cell) {
            const row = cell.getRow().getData();
            const name = esc(row.actor_name || "-");
            const actorId = esc(row.actor_id || "");

            const copyBtn = actorId
              ? `<button type="button"
                    class="ti-btn ti-btn-xs ti-btn-light !rounded-full !p-1 !leading-none"
                    data-action="copy"
                    data-copy="${actorId}"
                    title="Copy UUID">
                    <i class="ri-clipboard-line text-[12px]"></i>
                 </button>`
              : "";

            return `<span class="inline-flex items-center gap-2 w-full"><span class="font-medium">${name}</span>${copyBtn}</span>`;
          },
        },
        {
          title: "Action",
          field: "action",
          minWidth: 180,
          formatter: (cell) => esc(cell.getValue() || "-"),
        },
        {
          title: "Subject",
          field: "subject_label",
          minWidth: 240,
          formatter: function (cell) {
            const row = cell.getRow().getData();
            const label = esc(row.subject_label || "-");
            const subjectId = esc(row.subject_id || "");
            const type = esc(row.subject_type_short || "");
            const deleted = row.subject_is_deleted
              ? ' <span class="text-red-500">(deleted)</span>'
              : "";

            const copyBtn = subjectId
              ? `<button type="button"
                    class="ti-btn ti-btn-xs ti-btn-light !rounded-full !p-1 !leading-none"
                    data-action="copy"
                    data-copy="${subjectId}"
                    title="Copy UUID">
                    <i class="ri-clipboard-line text-[12px]"></i>
                 </button>`
              : "";

            const restoreBtn = row.subject_show_restore && cfg.canRestore
              ? `<button type="button"
                    class="ti-btn ti-btn-xs ti-btn-warning !rounded-full ms-1"
                    data-action="restore-subject"
                    data-type="${type}"
                    data-id="${subjectId}"
                    title="Restore">
                    <i class="ri-history-line"></i>
                 </button>`
              : "";

            return `<span class="inline-flex items-center gap-2 w-full"><span>${label}${deleted}</span>${copyBtn}${restoreBtn}</span>`;
          },
        },
        {
          title: "Request",
          field: "request",
          minWidth: 260,
          formatter: (cell) => esc(cell.getValue() || "-"),
        },
        {
          title: "IP",
          field: "ip",
          minWidth: 120,
          formatter: (cell) => esc(cell.getValue() || "-"),
        },
        {
          title: "Changes",
          field: "id",
          width: 110,
          hozAlign: "center",
          headerSort: false,
          formatter: function (cell) {
            const row = cell.getRow().getData();

            return `
              <button type="button"
                class="ti-btn btn-wave ti-btn-sm ti-btn-info !rounded-full"
                data-action="view-log"
                data-message="${esc(row.message ?? "")}" 
                data-old='${esc(JSON.stringify(row.changes_old ?? {}))}'
                data-new='${esc(JSON.stringify(row.changes_new ?? {}))}'
                data-meta='${esc(JSON.stringify(row.meta ?? {}))}'
                data-agent='${esc(String(row.user_agent ?? ""))}'>
                <i class="ri-eye-line"></i>
              </button>
            `;
          },
        },
      ],
    });

    window.__auditTable = table;

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
      if (page && page !== 1) table.setPage(1);
      else table.setData();
    }

    window.__auditReload = reload;

    table.on("dataLoaded", function () {
      el.classList.remove("is-loading");
      updateInfo();
    });

    table.on("pageLoaded", function () {
      updateInfo();
    });

    el.addEventListener("click", async function (e) {
      const target = e.target.closest("[data-action]");
      if (!target) return;

      const action = target.getAttribute("data-action") || "";

      if (action === "view-log") {
        const payload = {
          message: target.getAttribute("data-message") || "Change details",
          old: safeParseJson(target.getAttribute("data-old"), {}),
          new: safeParseJson(target.getAttribute("data-new"), {}),
          meta: safeParseJson(target.getAttribute("data-meta"), {}),
          agent: target.getAttribute("data-agent") || "-",
        };

        await Swal.fire({
          title: esc(payload.message || "Change details"),
          html: `
            <div style="text-align:left;display:grid;gap:12px">
              <div><h4 style="margin:0 0 6px">New</h4>${pretty(payload.new)}</div>
              <div><h4 style="margin:0 0 6px">Old</h4>${pretty(payload.old)}</div>
              <div><h4 style="margin:0 0 6px">Meta</h4>${pretty(payload.meta)}</div>
              <div><h4 style="margin:0 0 6px">User-Agent</h4>
                <div style="font-size:12px">${esc(payload.agent || "-")}</div>
              </div>
            </div>
          `,
          width: 820,
          confirmButtonText: "Close",
        });
        return;
      }

      if (action === "copy") {
        const value = target.getAttribute("data-copy") || "";
        const ok = await copyToClipboard(value);
        if (ok) await showToast("success", "Copied to clipboard");
        else await showToast("error", "Copy failed");
        return;
      }

      if (action === "restore-subject") {
        e.preventDefault();

        if (!cfg.canRestore) {
          await showToast("error", "Restore not allowed", "You do not have permission to restore this record.");
          return;
        }

        const type = target.getAttribute("data-type") || "";
        const id = target.getAttribute("data-id") || "";
        const endpoint = cfg.restoreEndpoint || "";

        if (!endpoint || !type || !id) {
          await showToast("error", "Restore failed", "Missing endpoint or identifiers.");
          return;
        }

        const ask = await Swal.fire({
          title: "Restore this record?",
          text: "This will un-delete the record.",
          icon: "warning",
          showCancelButton: true,
          confirmButtonText: "Yes, restore",
          cancelButtonText: "Cancel",
          confirmButtonColor: "#16a34a",
        });

        if (!ask.isConfirmed) return;

        target.setAttribute("disabled", "disabled");

        try {
          const response = await fetch(endpoint, {
            method: "POST",
            headers: {
              "Content-Type": "application/json",
              "Accept": "application/json",
              "X-Requested-With": "XMLHttpRequest",
              "X-CSRF-TOKEN": getCsrf(),
            },
            body: JSON.stringify({ type, id }),
          });

          const data = await response.json().catch(() => ({}));

          if (!response.ok || data.ok !== true) {
            throw new Error(data.message || "Restore failed.");
          }

          await showToast("success", "Restored");
          reload();
        } catch (err) {
          target.removeAttribute("disabled");
          await showToast("error", "Restore failed", err?.message || "Please try again.");
        }
      }
    });

    setInfoText("Loading...");
  });
})();
