(function () {
  "use strict";

  function qs(form) {
    const fd = new FormData(form);
    return {
      action: (fd.get("action") || "").toString().trim(),
      actor_id: (fd.get("actor_id") || "").toString().trim(),
      date_from: (fd.get("date_from") || "").toString().trim(),
      date_to: (fd.get("date_to") || "").toString().trim(),
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

  document.addEventListener("DOMContentLoaded", function () {
    const el = document.getElementById("activity-table");
    if (!el) return;

    const infoEl = document.getElementById("activity-info");
    const filterForm = document.querySelector("form[method='GET']");
    const ajaxUrl = el.dataset.endpoint || document.querySelector("meta[name='audit-data-url']")?.content;

    const endpoint = ajaxUrl || (window.AUDIT_LOGS_DATA_URL || "");
    const restoreEndpoint = el.dataset.restoreEndpoint || "";

    if (!endpoint) {
      console.error("Missing audit logs data endpoint.");
      return;
    }

    let filters = filterForm ? qs(filterForm) : {};
    let lastTotal = 0;

    const table = new Tabulator(el, {
      layout: "fitColumns",
      responsiveLayout: "collapse",
      placeholder: "No activity yet.",

      pagination: "remote",
      paginationSize: 20,
      paginationSizeSelector: [10, 20, 50, 100],

      ajaxURL: endpoint,
      ajaxConfig: "GET",
      ajaxLoader: false,

      paginationDataSent: { page: "page", size: "size" },

      ajaxParams: function () {
        return { ...filters };
      },

      ajaxResponse: function (url, params, response) {
        lastTotal = Number(response?.total ?? 0);
        return response?.data ?? [];
      },

      columns: [
        { title: "When", field: "created_at_text", widthGrow: 1 },

        {
          title: "User",
          field: "actor_name",
          widthGrow: 1,
          formatter: function (cell) {
            const row = cell.getRow().getData();
            const name = escapeHtml(row.actor_name || "—");
            const id = escapeHtml(row.actor_id || "");

            const copyBtn = id
              ? `<button type="button"
                    class="ti-btn ti-btn-xs ti-btn-light !rounded-full !p-1 !leading-none"
                    data-action="copy"
                    data-copy="${id}"
                    title="Copy UUID">
                    <i class="ri-clipboard-line text-[12px]"></i>
                 </button>`
              : "";

            return `<span class="inline-flex items-center gap-2 w-full">
                      <span class="font-medium">${name}</span>
                      ${copyBtn}
                    </span>`;
          },
        },

        { title: "Action", field: "action", widthGrow: 1 },

        {
          title: "Subject",
          field: "subject_label",
          widthGrow: 2,
          formatter: function (cell) {
            const row = cell.getRow().getData();
            const label = escapeHtml(row.subject_label || "—");
            const sid = escapeHtml(row.subject_id || "");
            const deleted = row.subject_is_deleted ? ` <span class="text-red-500">(deleted)</span>` : "";

            const copyBtn = sid
              ? `<button type="button"
                    class="ti-btn ti-btn-xs ti-btn-light !rounded-full !p-1 !leading-none"
                    data-action="copy"
                    data-copy="${sid}"
                    title="Copy UUID">
                    <i class="ri-clipboard-line text-[12px]"></i>
                 </button>`
              : "";

            const restoreBtn = row.subject_show_restore
              ? `<button type="button"
                    class="ti-btn ti-btn-xs ti-btn-warning !rounded-full ms-1"
                    data-action="restore-subject"
                    data-endpoint="${escapeHtml(restoreEndpoint)}"
                    data-type="${escapeHtml(row.subject_type_short || "")}"
                    data-id="${sid}"
                    title="Restore">
                    <i class="ri-history-line"></i>
                 </button>`
              : "";

            return `<span class="inline-flex items-center gap-2 w-full">
                      <span>${label}${deleted}</span>
                      ${copyBtn}
                      ${restoreBtn}
                    </span>`;
          },
        },

        { title: "Request", field: "request", widthGrow: 2 },
        { title: "IP", field: "ip", widthGrow: 1 },

        {
          title: "Changes",
          headerSort: false,
          width: 110,
          formatter: function (cell) {
            const row = cell.getRow().getData();
            return `
              <button type="button"
                class="ti-btn btn-wave ti-btn-sm ti-btn-info !rounded-full"
                data-action="view-log"
                data-message="${escapeHtml(row.message ?? "")}"
                data-old='${escapeHtml(JSON.stringify(row.changes_old ?? {}))}'
                data-new='${escapeHtml(JSON.stringify(row.changes_new ?? {}))}'
                data-meta='${escapeHtml(JSON.stringify(row.meta ?? {}))}'
                data-agent='${escapeHtml(JSON.stringify(row.user_agent ?? ""))}'
              >
                <i class="ri-eye-line"></i>
              </button>
            `;
          },
        },
      ],
    });

    function updateInfo() {
      if (!infoEl) return;

      const page = table.getPage() || 1;
      const size = table.getPageSize ? (table.getPageSize() || 20) : 20;
      const total = lastTotal || 0;

      if (!total) {
        const count = table.getDataCount ? table.getDataCount("active") : 0;
        infoEl.textContent = count ? `Showing 1–${count} records` : "No activity yet.";
        return;
      }

      const start = (page - 1) * size + 1;
      const end = Math.min(start + size - 1, total);

      infoEl.textContent = `Showing ${start}–${end} of ${total} records`;
    }

    table.on("dataLoaded", updateInfo);
    table.on("pageLoaded", updateInfo);

    if (filterForm) {
      filterForm.addEventListener("submit", function (e) {
        e.preventDefault();
        filters = qs(filterForm);
        table.setPage(1);
        table.setData();
      });
    }
  });
})();
