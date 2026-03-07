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

  function safeAttr(s) {
    return esc(s);
  }

  onReady(function () {
    const cfg = window.__loginLogs || {};
    const el = document.getElementById("login-table");
    if (!el) return;

    if (window.__loginTable && typeof window.__loginTable.destroy === "function") {
      try {
        window.__loginTable.destroy();
      } catch (_e) {}
      window.__loginTable = null;
    }

    const infoEl = document.getElementById("login-info");
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
      if (typeof window.__loginGetParams === "function") {
        return window.__loginGetParams() || {};
      }

      return {
        search: "",
        status: "",
        user: "",
        email: "",
        ip_address: "",
        device: "",
        date_from: "",
        date_to: "",
      };
    }

    const table = new Tabulator(el, {
      layout: "fitColumns",
      responsiveLayout: "collapse",
      placeholder: "No login logs found.",

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

      columns: [
        {
          title: "Status",
          field: "success",
          width: 170,
          headerSort: false,
          formatter: function (cell) {
            const v = cell.getValue();
            const ok = v === true || v === 1 || v === "1" || v === "true";

            if (ok) {
              return '<span class="badge bg-success/15 text-success">Success</span>';
            }

            const row = cell.getRow().getData();
            const reason = row?.reason
              ? ` <span class="text-xs text-muted">- ${esc(row.reason)}</span>`
              : "";

            return `<span class="badge bg-danger/15 text-danger">Failed</span>${reason}`;
          },
        },
        {
          title: "User",
          field: "user",
          minWidth: 160,
          formatter: (cell) => esc(cell.getValue() || "-"),
        },
        {
          title: "Email (attempted)",
          field: "attempted",
          minWidth: 220,
          formatter: (cell) => esc(cell.getValue() || "-"),
        },
        {
          title: "IP Address",
          field: "ip_address",
          minWidth: 140,
          formatter: (cell) => esc(cell.getValue() || "-"),
        },
        {
          title: "Device",
          field: "device",
          minWidth: 220,
          formatter: (cell) => esc(cell.getValue() || "-"),
        },
        {
          title: "Address",
          field: "address",
          minWidth: 200,
          formatter: (cell) => esc(cell.getValue() || "-"),
        },
        {
          title: "Location",
          field: "location_url",
          width: 110,
          headerSort: false,
          formatter: function (cell) {
            const url = cell.getValue();
            if (!url) return "-";
            const safe = safeAttr(url);
            return `<a href="${safe}" target="_blank" rel="noopener" class="text-primary hover:underline">Map</a>`;
          },
        },
        {
          title: "Date",
          field: "created_at",
          minWidth: 180,
          formatter: function (cell) {
            const row = cell.getRow().getData();
            return esc(row.created_at_text || cell.getValue() || "-");
          },
        },
      ],
    });

    window.__loginTable = table;

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

    window.__loginReload = reload;

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
