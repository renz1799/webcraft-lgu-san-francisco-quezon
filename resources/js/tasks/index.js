// resources/js/tasks/index.js
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
    const d = new Date(String(dateStr).replace(" ", "T"));
    if (Number.isNaN(d.getTime())) return String(dateStr);
    return d.toLocaleDateString(undefined, {
      year: "numeric",
      month: "short",
      day: "2-digit",
    });
  }

  // "2d ago" / "3h ago" / "just now" / fallback date
  function timeAgo(dateStr) {
    if (!dateStr) return "";
    const d = new Date(String(dateStr).replace(" ", "T"));
    if (Number.isNaN(d.getTime())) return String(dateStr);

    const now = new Date();
    const diffMs = now.getTime() - d.getTime();
    const future = diffMs < 0;
    const abs = Math.abs(diffMs);

    const sec = Math.floor(abs / 1000);
    const min = Math.floor(sec / 60);
    const hr = Math.floor(min / 60);
    const day = Math.floor(hr / 24);

    if (sec < 20) return future ? "in a few seconds" : "just now";
    if (min < 60) return future ? `in ${min}m` : `${min}m ago`;
    if (hr < 24) return future ? `in ${hr}h` : `${hr}h ago`;
    if (day < 14) return future ? `in ${day}d` : `${day}d ago`;

    return fmtDate(dateStr);
  }

  function upper(s) {
    return String(s ?? "").toUpperCase();
  }

  function buildUrlTemplate(tpl, id) {
    if (!tpl || !id) return "";
    return tpl.replace("__ID__", encodeURIComponent(String(id)));
  }

  function statusBadgeHtml(statusRaw) {
    const s = String(statusRaw ?? "").trim();

    let cls = "bg-outline-secondary";
    let label = upper(s);

    switch (s) {
      case "pending":
        cls = "bg-outline-warning";
        label = "PENDING";
        break;
      case "in_progress":
        cls = "bg-outline-info";
        label = "IN PROGRESS";
        break;
      case "done":
        cls = "bg-outline-success";
        label = "DONE";
        break;
      case "cancelled":
        cls = "bg-outline-danger";
        label = "CANCELLED";
        break;
      default:
        cls = "bg-outline-secondary";
        label = upper(s || "UNKNOWN");
        break;
    }

    return `<span class="badge ${cls}">${escapeHtml(label)}</span>`;
  }

  onReady(function () {
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
      scope: (scopeSel?.value || "mine").trim(), // mine | available | all
      status: (statusSel?.value || "").trim(), // '' = all
    };

    let lastTotal = 0;
    let tableRef = null;

    function setInfo(text) {
      if (!infoEl) return;
      infoEl.textContent = text;
    }

    function hasActiveFilters() {
      return (
        (filters.q || "").trim() !== "" ||
        (filters.status || "").trim() !== "" ||
        ((filters.scope || "mine").trim() !== "mine")
      );
    }

    function buildEmptyStateHtml() {
      const scope = (filters.scope || "mine").trim();
      const status = (filters.status || "").trim();
      const q = (filters.q || "").trim();

      const parts = [];
      if (scope !== "mine") parts.push(`Scope: <b>${escapeHtml(scope)}</b>`);
      if (status !== "") parts.push(`Status: <b>${escapeHtml(status)}</b>`);
      if (q !== "") parts.push(`Search: <b>${escapeHtml(q)}</b>`);

      const subtitle =
        parts.length > 0
          ? `No tasks match your filters. ${parts.join(" • ")}`
          : "No tasks found yet.";

    const clearBtn = hasActiveFilters()
      ? `<div class="mt-2">
          <button type="button"
            class="ti-btn ti-btn-light ti-btn-sm !py-1 !px-2 !text-[0.70rem] !leading-tight"
            data-action="empty-clear">
            Clear filters
          </button>
        </div>`
      : "";


      return `
        <div class="p-4 text-center">
          <div class="text-sm font-semibold text-defaulttextcolor dark:text-white/80">Nothing to show</div>
          <div class="text-xs text-[#8c9097] mt-1">${subtitle}</div>
          ${clearBtn}
        </div>
      `;
    }

    function refreshPlaceholder() {
      if (!tableRef) return;

      // ✅ Tabulator-supported method (fixes your setOptions error)
      if (typeof tableRef.setPlaceholder === "function") {
        tableRef.setPlaceholder(buildEmptyStateHtml());
      }
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
      if (count) setInfo(`Showing ${count} record(s)`);
      else setInfo(hasActiveFilters() ? "No records match your current filters" : "No records found");
    }

    function reload(table) {
      const page = table.getPage ? table.getPage() : 1;
      if (page && page !== 1) table.setPage(1);
      else table.setData();
    }

    function safeShowHideAssignedCol() {
      if (!tableRef) return;

      const scope = (filters.scope || "mine").trim();
      const shouldShow = scope === "all";

      // Avoid noisy "No matching column found" logs by only calling when methods exist
      // and catching any internal issues.
      try {
        if (shouldShow) {
          if (typeof tableRef.showColumn === "function") tableRef.showColumn("assigned_to_name");
        } else {
          if (typeof tableRef.hideColumn === "function") tableRef.hideColumn("assigned_to_name");
        }
      } catch (e) {
        // do nothing (don’t break UX because of column toggling)
      }
    }

    tableRef = new Tabulator(el, {
      layout: "fitColumns",
      responsiveLayout: "collapse",
      placeholder: buildEmptyStateHtml(),

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
        refreshPlaceholder();
        return res?.data ?? [];
      },

      initialSort: [{ column: "created_at", dir: "desc" }],

      // ✅ Row click open (with safeguards)
      rowClick: (e, row) => {
        const t = e.target;

        if (
          t.closest("a") ||
          t.closest("button") ||
          t.closest('[data-action]') ||
          t.closest("input") ||
          t.closest("select") ||
          t.closest("textarea")
        ) {
          return;
        }

        const data = row.getData() || {};
        const id = data?.id;
        const url = buildUrlTemplate(cfg.showUrlTemplate, id);
        if (!url) return;

        window.location.href = url;
      },

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

        {
          title: "Status",
          field: "status",
          width: 160,
          formatter: (cell) => statusBadgeHtml(cell.getValue()),
        },

        {
          title: "Assigned To",
          field: "assigned_to_name",
          minWidth: 180,
          visible: (filters.scope || "mine").trim() === "all",
          formatter: (cell) => {
            const v = String(cell.getValue() ?? "").trim();
            return v && v !== "—"
              ? escapeHtml(v)
              : `<span class="text-[#8c9097]">—</span>`;
          },
        },

        // ✅ Created: show time context + tooltip exact date
        {
          title: "Created",
          field: "created_at",
          width: 170,
          tooltip: (e, cell) => {
            const v = cell.getValue();
            return v ? `Created: ${fmtDate(v)}` : "";
          },
          formatter: (cell) => {
            const v = cell.getValue();
            if (!v) return `<span class="text-[#8c9097]">—</span>`;

            const rel = timeAgo(v);
            const exact = fmtDate(v);

            return `<span>${escapeHtml(rel)}</span>`;

          },
        },

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

    tableRef.on("dataLoaded", () => updateInfo(tableRef));
    tableRef.on("pageLoaded", () => updateInfo(tableRef));

    // Initial column visibility + placeholder
    safeShowHideAssignedCol();
    refreshPlaceholder();

    const applyFilters = debounce(() => {
      filters.q = (search?.value || "").trim();
      filters.scope = (scopeSel?.value || "mine").trim();
      filters.status = (statusSel?.value || "").trim();

      safeShowHideAssignedCol();
      refreshPlaceholder();
      reload(tableRef);
    }, 350);

    window.__tasksReload = () => reload(tableRef);

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

      safeShowHideAssignedCol();
      refreshPlaceholder();
      reload(tableRef);
    });

    // Empty-state "Clear filters" button
    document.addEventListener("click", (e) => {
      const btn = e.target.closest('[data-action="empty-clear"]');
      if (!btn) return;
      clear?.click();
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

      await swalSuccess("Claimed", "");

      if (scopeSel) scopeSel.value = "mine";
      filters.scope = "mine";

      safeShowHideAssignedCol();
      refreshPlaceholder();
      reload(tableRef);
    });
  });
})();

