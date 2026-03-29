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

  function statusBadge(row) {
    if (row?.is_archived) {
      return '<span class="badge bg-warning/10 text-warning">Archived</span>';
    }

    const status = String(row?.status || "").trim();

    const map = {
      pending: "bg-outline-warning text-warning",
      in_progress: "bg-outline-info text-info",
      done: "bg-outline-success text-success",
      cancelled: "bg-outline-danger text-danger",
    };

    const cls = map[status] || "bg-outline-secondary text-secondary";
    const label = status ? status.replace(/_/g, " ").toUpperCase() : "UNKNOWN";

    return `<span class="badge ${cls}">${esc(label)}</span>`;
  }

  function moduleBadge(row) {
    const code = String(row?.owner_module_code || "").trim();
    const name = String(row?.owner_module_name || "").trim();

    if (!code && !name) {
      return '<span class="text-[#8c9097]">-</span>';
    }

    const badge = code
      ? `<span class="badge bg-primary/10 text-primary">${esc(code)}</span>`
      : "";
    const label = name
      ? `<div class="text-[0.75rem] text-[#8c9097] mt-1">${esc(name)}</div>`
      : "";

    return `${badge}${label}`;
  }

  function routeFromTemplate(template, id, fallback = "") {
    const value = String(template || "").trim();
    const taskId = String(id || "").trim();

    if (value !== "" && taskId !== "") {
      return value.replace("__ID__", encodeURIComponent(taskId));
    }

    return String(fallback || "").trim();
  }

  onReady(function () {
    const cfg = window.__tasks || {};
    const el = document.getElementById("tasks-table");
    if (!el) return;
    const showModuleColumn = cfg.showModuleColumn !== false;

    if (window.__tasksTable && typeof window.__tasksTable.destroy === "function") {
      try {
        window.__tasksTable.destroy();
      } catch (_e) {}
      window.__tasksTable = null;
    }

    const infoEl = document.getElementById("tasks-info");
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
      if (typeof window.__tasksGetParams === "function") {
        return window.__tasksGetParams() || {};
      }

      const searchEl = document.getElementById("tasks-search");
      const archivedEl = document.getElementById("tasks-archived");
      const scopeEl = document.getElementById("tasks-scope");
      const moduleEl = document.getElementById("tasks-module");
      const statusEl = document.getElementById("tasks-status");
      const assignedToEl = document.getElementById("tasks-assigned-to");
      const dateFromEl = document.getElementById("tasks-date-from");
      const dateToEl = document.getElementById("tasks-date-to");

      return {
        search: (searchEl?.value || "").trim(),
        archived: (archivedEl?.value || "active").trim() || "active",
        scope: (scopeEl?.value || "mine").trim() || "mine",
        module_id: (moduleEl?.value || "").trim(),
        status: (statusEl?.value || "").trim(),
        assigned_to: (assignedToEl?.value || "").trim(),
        date_from: (dateFromEl?.value || "").trim(),
        date_to: (dateToEl?.value || "").trim(),
      };
    }

    const columns = [
      {
        title: "Title",
        field: "title",
        minWidth: 280,
        formatter: function (cell) {
          const row = cell.getRow().getData() || {};
          const title = esc(row.title || "-");
          const showUrl = esc(routeFromTemplate(cfg.showUrlTemplate, row.id, row.show_url));

          if (!showUrl) {
            return title;
          }

          return `<a class="text-primary hover:underline font-semibold" href="${showUrl}">${title}</a>`;
        },
      },
      {
        title: "Status",
        field: "status",
        minWidth: 160,
        formatter: function (cell) {
          const row = cell.getRow().getData() || {};
          return statusBadge(row);
        },
      },
      {
        title: "Assigned To",
        field: "assigned_to_name",
        minWidth: 200,
        formatter: function (cell) {
          const value = String(cell.getValue() || "").trim();
          return value !== "" && value !== "-"
            ? esc(value)
            : '<span class="text-[#8c9097]">-</span>';
        },
      },
      {
        title: "Created",
        field: "created_at",
        minWidth: 180,
        formatter: function (cell) {
          const row = cell.getRow().getData() || {};
          return esc(row.created_at_text || cell.getValue() || "-");
        },
      },
      {
        title: "Actions",
        field: "id",
        width: 210,
        hozAlign: "center",
        headerSort: false,
        formatter: function (cell) {
          const row = cell.getRow().getData() || {};
          const title = esc(row.title || "this task");

          const showUrl = esc(routeFromTemplate(cfg.showUrlTemplate, row.id, row.show_url));
          const claimUrl = row.claim_url
            ? esc(routeFromTemplate(cfg.claimUrlTemplate, row.id, row.claim_url))
            : "";
          const archiveUrl = row.archive_url
            ? esc(routeFromTemplate(cfg.archiveUrlTemplate, row.id, row.archive_url))
            : "";
          const restoreUrl = row.restore_url
            ? esc(routeFromTemplate(cfg.restoreUrlTemplate, row.id, row.restore_url))
            : "";

          const buttons = [];

          if (showUrl) {
            buttons.push(`
                <a href="${showUrl}" class="ti-btn btn-wave ti-btn-sm ti-btn-info !rounded-full" title="Open">
                  <i class="ri-eye-line"></i>
                </a>
              `);
          }

          if (row.is_archived) {
            if (restoreUrl) {
              buttons.push(`
                  <button
                    type="button"
                    class="ti-btn btn-wave ti-btn-sm ti-btn-success !rounded-full"
                    data-action="restore-task"
                    data-endpoint="${restoreUrl}"
                    data-title="${title}"
                    title="Restore"
                  >
                    <i class="ri-history-line"></i>
                  </button>
                `);
            }
          } else {
            if (claimUrl) {
              buttons.push(`
                  <button
                    type="button"
                    class="ti-btn btn-wave ti-btn-sm ti-btn-primary !rounded-full"
                    data-action="claim-task"
                    data-endpoint="${claimUrl}"
                    data-title="${title}"
                    title="Claim"
                  >
                    <i class="ri-hand-heart-line"></i>
                  </button>
                `);
            }

            if (archiveUrl) {
              buttons.push(`
                  <button
                    type="button"
                    class="ti-btn btn-wave ti-btn-sm ti-btn-danger !rounded-full"
                    data-action="archive-task"
                    data-endpoint="${archiveUrl}"
                    data-title="${title}"
                    title="Archive"
                  >
                    <i class="ri-delete-bin-line"></i>
                  </button>
                `);
            }
          }

          if (buttons.length === 0) {
            return '<span class="text-xs text-[#8c9097]">N/A</span>';
          }

          return `<div class="hstack flex gap-2 text-[.9375rem] justify-center w-full">${buttons.join("")}</div>`;
        },
      },
    ];

    if (showModuleColumn) {
      columns.unshift({
        title: "Origin",
        field: "owner_module_name",
        minWidth: 180,
        formatter: function (cell) {
          const row = cell.getRow().getData() || {};
          return moduleBadge(row);
        },
      });
    }

    const table = new Tabulator(el, {
      layout: "fitColumns",
      responsiveLayout: "collapse",
      placeholder: "No tasks found.",

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

      initialSort: [{ column: "created_at", dir: "desc" }],

      rowClick: function (e, row) {
        const target = e.target;
        if (
          target.closest("a") ||
          target.closest("button") ||
          target.closest("[data-action]") ||
          target.closest("input") ||
          target.closest("select") ||
          target.closest("textarea")
        ) {
          return;
        }

        const data = row.getData() || {};
        const showUrl = routeFromTemplate(cfg.showUrlTemplate, data.id, data.show_url);
        if (!showUrl) return;

        window.location.href = showUrl;
      },

      columns,
    });

    window.__tasksTable = table;
    function emitSidebarStats() {
      if (typeof window.__tasksUpdateSidebarStats !== "function") {
        return;
      }

      const currentPageRows = typeof table.getDataCount === "function"
        ? table.getDataCount("active")
        : (typeof table.getData === "function" ? table.getData().length : 0);

      window.__tasksUpdateSidebarStats({
        visibleTotal: lastTotal,
        currentPageRows,
        filters: getFilters(),
      });
    }

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

    function hardRefreshCurrentPage() {
      if (typeof table.replaceData === "function") {
        table.replaceData();
        return;
      }

      table.setData(ajaxUrl, {
        ...getFilters(),
        page: table.getPage() || 1,
        size: table.getPageSize ? table.getPageSize() || 15 : 15,
      });
    }

    function reload(options = {}) {
      const resetPage = options.resetPage !== false;

      el.classList.add("is-loading");
      setInfoText("Updating...");

      const page = table.getPage() || 1;

      if (resetPage && page !== 1) {
        table.setPage(1);
        return;
      }

      hardRefreshCurrentPage();
    }

    window.__tasksReload = reload;

    table.on("dataLoaded", function () {
      el.classList.remove("is-loading");
      updateInfo();
      emitSidebarStats();
    });

    table.on("pageLoaded", function () {
      updateInfo();
      emitSidebarStats();
    });

    setInfoText("Loading...");
  });
})();
