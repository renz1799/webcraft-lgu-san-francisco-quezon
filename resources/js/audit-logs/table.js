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

  function titleCase(value) {
    return String(value ?? "")
      .replace(/[._]+/g, " ")
      .replace(/\s+/g, " ")
      .trim()
      .replace(/\b\w/g, (match) => match.toUpperCase());
  }

  function formatActionLabel(action) {
    const labels = {
      "user.permissions.synced": "Permissions Updated",
      "user.role.changed": "Role Updated",
      "user.role.assigned_default": "Default Role Assigned",
      "user.status.updated": "Status Updated",
      "user.password.reset": "Temporary Password Generated",
      "user.password.changed": "Password Changed",
      "user.profile.updated": "Profile Updated",
      "user.deleted": "User Archived",
      "user.restored": "User Restored",
      "role.created": "Role Created",
      "role.updated": "Role Updated",
      "role.deleted": "Role Archived",
      "role.restored": "Role Restored",
      "permission.created": "Permission Created",
      "permission.updated": "Permission Updated",
      "permission.deleted": "Permission Archived",
      "permission.restored": "Permission Restored",
    };

    return labels[action] || titleCase(action || "-");
  }

  onReady(function () {
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
        module: "",
        action: "",
        actor_id: "",
        subject_type: "",
        date_from: "",
        date_to: "",
      };
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
          minWidth: 190,
          formatter: function (cell) {
            const row = cell.getRow().getData();
            const text = esc(cell.getValue() || "-");
            const iso = esc(row.created_at_iso || "");
            return `<span class="font-medium" title="${iso}">${text}</span>`;
          },
        },
        {
          title: "User",
          field: "actor_name",
          minWidth: 180,
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
          minWidth: 190,
          formatter: function (cell) {
            return esc(formatActionLabel(cell.getValue() || "-"));
          },
        },
        {
          title: "Message",
          field: "message",
          minWidth: 360,
          widthGrow: 2,
          variableHeight: true,
          formatter: function (cell) {
            const row = cell.getRow().getData();
            const message = esc(cell.getValue() || formatActionLabel(row.action || "-"));
            return `<div class="audit-message-cell" title="${message}">${message}</div>`;
          },
        },
        {
          title: "IP",
          field: "ip",
          minWidth: 130,
          formatter: (cell) => esc(cell.getValue() || "-"),
        },
        {
          title: "Details",
          field: "id",
          width: 150,
          hozAlign: "center",
          headerSort: false,
          formatter: function (cell) {
            const row = cell.getRow().getData();

            return `
              <button type="button"
                class="ti-btn ti-btn-sm ti-btn-light !rounded-md inline-flex items-center gap-1.5"
                data-action="view-log"
                data-message="${esc(row.message ?? "")}" 
                data-action-code="${esc(row.action ?? "")}"
                data-created-at="${esc(row.created_at_text ?? "")}" 
                data-user="${esc(row.actor_name ?? "-")}" 
                data-subject="${esc(row.subject_label ?? "-")}" 
                data-request="${esc(row.request ?? "")}" 
                data-ip="${esc(row.ip ?? "")}" 
                data-old='${esc(JSON.stringify(row.changes_old ?? {}))}'
                data-new='${esc(JSON.stringify(row.changes_new ?? {}))}'
                data-meta='${esc(JSON.stringify(row.meta ?? {}))}'
                data-agent='${esc(String(row.user_agent ?? ""))}'>
                <i class="ri-file-list-3-line"></i>
                <span>View Details</span>
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

    function reload({ resetPage = true } = {}) {
      el.classList.add("is-loading");
      setInfoText("Updating...");

      const page = table.getPage() || 1;

      if (resetPage && page !== 1) {
        table.setPage(1);
        return;
      }

      hardRefreshCurrentPage();
    }

    window.__auditReload = reload;

    table.on("dataLoaded", function () {
      el.classList.remove("is-loading");
      updateInfo();
    });

    table.on("pageLoaded", function () {
      updateInfo();
    });

    setInfoText("Loading...");
  });
})();


