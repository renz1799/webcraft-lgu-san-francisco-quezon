// resources/js/tasks/index.js
import Swal from "sweetalert2";
import "sweetalert2/dist/sweetalert2.min.css";

(function () {
  "use strict";

  function debounce(fn, wait = 350) {
    let t = null;
    return (...args) => {
      clearTimeout(t);
      t = setTimeout(() => fn.apply(this, args), wait);
    };
  }

  function escapeHtml(s) {
    return String(s ?? "")
      .replace(/&/g, "&amp;")
      .replace(/</g, "&lt;")
      .replace(/>/g, "&gt;")
      .replace(/"/g, "&quot;")
      .replace(/'/g, "&#39;");
  }

  function getCsrf() {
    return (
      document.querySelector('meta[name="csrf-token"]')?.content ||
      window.__tasks?.csrf ||
      ""
    );
  }

  async function parseErrorResponse(res) {
    const ct = res.headers.get("content-type") || "";
    let data = null;
    let text = "";

    if (ct.includes("application/json")) {
      data = await res.json().catch(() => null);
    } else {
      text = await res.text().catch(() => "");
    }

    const message =
      data?.message ||
      data?.error ||
      (res.status === 401
        ? "Your session has expired. Please log in again."
        : res.status === 403
        ? "You don’t have permission to do that."
        : res.status === 419
        ? "Security token expired. Please refresh the page and try again."
        : res.status === 404
        ? "Record not found. It may have been deleted already."
        : "Request failed.");

    return { message, data, text };
  }

  async function swalError(title, msg) {
    if (typeof Swal === "undefined") return;
    await Swal.fire({
      icon: "error",
      title: title || "Failed",
      text: msg || "Request failed.",
    });
  }

  async function swalSuccess(title, msg) {
    if (typeof Swal === "undefined") return;
    await Swal.fire({
      icon: "success",
      title: title || "Saved",
      text: msg || "",
      timer: 900,
      showConfirmButton: false,
    });
  }

  function fmtDate(dateStr) {
    if (!dateStr) return "";
    // Expecting ISO-ish from Laravel (e.g., 2026-01-26 10:00:00)
    const d = new Date(String(dateStr).replace(" ", "T"));
    if (Number.isNaN(d.getTime())) return String(dateStr);
    return d.toLocaleDateString(undefined, { year: "numeric", month: "short", day: "2-digit" });
  }

  function upper(s) {
    return String(s ?? "").toUpperCase();
  }

  function buildUrlTemplate(tpl, id) {
    if (!tpl || !id) return "";
    return tpl.replace("__ID__", encodeURIComponent(String(id)));
  }

  document.addEventListener("DOMContentLoaded", function () {
    const el = document.getElementById("tasks-table");
    if (!el) return;

    const cfg = window.__tasks || {};
    const infoEl = document.getElementById("tasks-info");

    const search = document.getElementById("tasks-search");
    const clear = document.getElementById("tasks-clear");
    const scopeSel = document.getElementById("tasks-scope");
    const statusSel = document.getElementById("tasks-status");

    if (!cfg.ajaxUrl || !cfg.showUrlTemplate) {
      console.error("[Tasks] Missing window.__tasks config", cfg);
      return;
    }

    let filters = {
      q: "",
      scope: (scopeSel?.value || "mine").trim(), // mine | available
      status: (statusSel?.value || "").trim(),   // '' = all
    };

    let lastTotal = 0;

    function setInfo(text) {
      if (!infoEl) return;
      infoEl.textContent = text;
    }

    function updateInfo(table) {
      const total = Number(lastTotal || 0);

      if (total > 0) {
        const page = table.getPage() || 1;
        const size = table.getPageSize ? (table.getPageSize() || 15) : 15;

        const start = (page - 1) * size + 1;
        const end = Math.min(start + size - 1, total);

        setInfo(`Showing ${start}–${end} of ${total} record(s)`);
        return;
      }

      const count = table.getDataCount ? table.getDataCount("active") : 0;
      setInfo(count ? `Showing ${count} record(s)` : "No records found");
    }

    function reload(table) {
      const page = table.getPage ? table.getPage() : 1;
      if (page && page !== 1) table.setPage(1);
      else table.setData();
    }

    const table = new Tabulator(el, {
      layout: "fitColumns",
      responsiveLayout: "collapse",
      placeholder: "No tasks found.",

      pagination: "remote",
      paginationSize: 15,
      paginationSizeSelector: [10, 20, 50, 100],

      ajaxURL: cfg.ajaxUrl,
      ajaxConfig: "GET",

      paginationDataSent: { page: "page", size: "size" },
      paginationDataReceived: { last_page: "last_page", data: "data", total: "total" },

      ajaxParams: () => ({ ...filters }),

      ajaxResponse: (_, __, res) => {
        lastTotal = Number(res?.total ?? res?.meta?.total ?? 0);
        return res?.data ?? [];
      },

      initialSort: [{ column: "created_at", dir: "desc" }],

      columns: [
        {
          title: "Title",
          field: "title",
          minWidth: 260,
          formatter: (cell) => {
            const row = cell.getRow().getData() || {};
            const id = row?.id;
            const title = row?.title || "";
            const showUrl = buildUrlTemplate(cfg.showUrlTemplate, id);

            if (!id || !showUrl) return escapeHtml(title);

            return `
              <a class="text-primary hover:underline font-semibold"
                 href="${escapeHtml(showUrl)}">
                 ${escapeHtml(title)}
              </a>
            `;
          },
        },
        { title: "Status", field: "status", width: 140, formatter: (cell) => escapeHtml(upper(cell.getValue())) },
        {
          title: "Due",
          field: "due_at",
          width: 160,
          formatter: (cell) => {
            const v = cell.getValue();
            return v ? escapeHtml(fmtDate(v)) : `<span class="text-[#8c9097]">—</span>`;
          },
        },
        { title: "Created", field: "created_at", width: 180 },

        {
          title: "",
          field: "id",
          headerSort: false,
          hozAlign: "right",
          formatter: (cell) => {
            const row = cell.getRow().getData() || {};
            const id = row?.id;
            if (!id) return "";

            const scope = (filters.scope || "mine").trim();

            // Buttons:
            // - "Open" always available
            // - "Claim" only when scope=available
            const showUrl = buildUrlTemplate(cfg.showUrlTemplate, id);

            const openBtn = `
              <a class="ti-btn ti-btn-sm ti-btn-info !rounded-full"
                 href="${escapeHtml(showUrl)}"
                 aria-label="Open Task">
                <i class="ri-external-link-line"></i>
              </a>
            `;

            if (scope !== "available") {
              return `<div class="hstack flex gap-2 justify-end">${openBtn}</div>`;
            }

            // IMPORTANT: claimUrlTemplate must be generated with ['id' => '__ID__'] in Blade
            const claimUrl = buildUrlTemplate(cfg.claimUrlTemplate, id);

            return `
              <div class="hstack flex gap-2 justify-end">
                ${openBtn}

                <a class="ti-btn ti-btn-sm ti-btn-primary !rounded-full"
                   href="javascript:void(0);"
                   data-action="claim"
                   data-id="${escapeHtml(id)}"
                   aria-label="Claim Task"
                   title="Claim">
                  <i class="ri-hand-heart-line"></i>
                </a>
              </div>
            `;
          },
        },
      ],
    });

    table.on("dataLoaded", () => updateInfo(table));
    table.on("pageLoaded", () => updateInfo(table));

    const applyFilters = debounce(() => {
      filters.q = (search?.value || "").trim();
      filters.scope = (scopeSel?.value || "mine").trim();
      filters.status = (statusSel?.value || "").trim();
      reload(table);
    }, 350);

    window.__tasksReload = () => reload(table);

    search?.addEventListener("input", applyFilters);
    scopeSel?.addEventListener("change", applyFilters);
    statusSel?.addEventListener("change", applyFilters);

    clear?.addEventListener("click", (e) => {
      e.preventDefault();
      if (search) search.value = "";
      if (scopeSel) scopeSel.value = "mine";
      if (statusSel) statusSel.value = "";
      filters.q = "";
      filters.scope = "mine";
      filters.status = "";
      reload(table);
    });

    // Claim action
    document.addEventListener("click", async (e) => {
      const claimBtn = e.target.closest('[data-action="claim"]');
      if (!claimBtn) return;

      if (typeof Swal === "undefined") {
        console.error("[Tasks] Swal is not available. Make sure SweetAlert2 is loaded.");
        return;
      }

      const id = claimBtn?.dataset?.id;
      if (!id) return;

      if (!cfg.claimUrlTemplate) {
        console.error("[Tasks] Missing claimUrlTemplate in window.__tasks config", cfg);
        return;
      }

      const confirm = await Swal.fire({
        icon: "question",
        title: "Claim this task?",
        text: "This will assign the task to you.",
        showCancelButton: true,
        confirmButtonText: "Claim",
        cancelButtonText: "Cancel",
        confirmButtonColor: "#2563eb",
      });

      if (!confirm.isConfirmed) return;

      Swal.fire({
        title: "Claiming...",
        allowOutsideClick: false,
        didOpen: () => Swal.showLoading(),
      });

      const endpoint = buildUrlTemplate(cfg.claimUrlTemplate, id);

      const res = await fetch(endpoint, {
        method: "POST",
        headers: {
          "X-CSRF-TOKEN": getCsrf(),
          Accept: "application/json",
        },
      });

      if (!res.ok) {
        const { message, data, text } = await parseErrorResponse(res);

        console.error("[Tasks] Claim failed", {
          status: res.status,
          endpoint,
          data,
          text,
        });

        await swalError("Claim failed", message);
        return;
      }

      // If your claim endpoint returns redirect HTML sometimes, we still treat it as ok.
      await swalSuccess("Claimed", "");

      // After claim: switch to "mine" so user sees it immediately (optional)
      if (scopeSel) scopeSel.value = "mine";
      filters.scope = "mine";

      reload(table);
    });
  });
})();
