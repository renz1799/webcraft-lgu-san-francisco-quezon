(function () {
  "use strict";

  function onReady(fn) {
    if (document.readyState === "loading") {
      document.addEventListener("DOMContentLoaded", fn);
      return;
    }

    fn();
  }

  function escapeHtml(value) {
    return String(value ?? "")
      .replace(/&/g, "&amp;")
      .replace(/</g, "&lt;")
      .replace(/>/g, "&gt;")
      .replace(/"/g, "&quot;")
      .replace(/'/g, "&#39;");
  }

  function getFilters() {
    if (typeof window.__accountablePersonsGetParams === "function") {
      return window.__accountablePersonsGetParams() || {};
    }

    return {
      search: "",
      archived: "active",
      department_id: "",
    };
  }

  onReady(function () {
    const tableElement = document.getElementById("accountable-persons-table");
    if (!tableElement) return;

    const config = window.__accountablePersons || {};
    const infoElement = document.getElementById("accountable-persons-info");

    if (!config.ajaxUrl) {
      if (infoElement) {
        infoElement.textContent = "Missing ajaxUrl.";
      }
      return;
    }

    if (
      window.__accountablePersonsTable &&
      typeof window.__accountablePersonsTable.destroy === "function"
    ) {
      try {
        window.__accountablePersonsTable.destroy();
      } catch (_error) {}
      window.__accountablePersonsTable = null;
    }

    let lastTotal = 0;

    function setInfo(text) {
      if (infoElement) {
        infoElement.textContent = text;
      }
    }

    function updateInfo(table) {
      if (lastTotal <= 0) {
        const rowsCount = table.getDataCount ? table.getDataCount("active") : 0;
        setInfo(rowsCount ? `Showing 1-${rowsCount} record(s)` : "No records found");
        return;
      }

      const page = table.getPage() || 1;
      const size = table.getPageSize ? table.getPageSize() || 15 : 15;
      const start = (page - 1) * size + 1;
      const end = Math.min(start + size - 1, lastTotal);

      if (start > lastTotal) {
        setInfo(`Showing 0 of ${lastTotal} record(s)`);
        return;
      }

      setInfo(`Showing ${start}-${end} of ${lastTotal} record(s)`);
    }

    function hardRefresh(table) {
      if (typeof table.replaceData === "function") {
        table.replaceData();
        return;
      }

      table.setData(config.ajaxUrl, {
        ...getFilters(),
        page: table.getPage() || 1,
        size: table.getPageSize ? table.getPageSize() || 15 : 15,
      });
    }

    function reload(table, { resetPage = true } = {}) {
      tableElement.classList.add("is-loading");
      setInfo("Updating...");

      const page = table.getPage() || 1;

      if (resetPage && page !== 1) {
        table.setPage(1);
        return;
      }

      hardRefresh(table);
    }

    const table = new Tabulator(tableElement, {
      layout: "fitColumns",
      responsiveLayout: "collapse",
      placeholder: "No accountable persons found.",
      pagination: "remote",
      paginationSize: 15,
      paginationSizeSelector: [10, 20, 50, 100],
      ajaxURL: config.ajaxUrl,
      ajaxConfig: "GET",
      ajaxLoader: false,
      paginationDataSent: { page: "page", size: "size" },
      paginationDataReceived: { last_page: "last_page", data: "data", total: "total" },
      ajaxParams: () => ({ ...getFilters() }),
      ajaxResponse: (_url, _params, response) => {
        lastTotal = Number(response?.total ?? 0);
        return response?.data ?? [];
      },
      columns: [
        { title: "Full Name", field: "full_name", minWidth: 240 },
        {
          title: "Designation",
          field: "designation",
          width: 220,
          formatter: (cell) => escapeHtml(cell.getValue() || "-"),
        },
        {
          title: "Office",
          field: "office",
          width: 200,
          formatter: (cell) => escapeHtml(cell.getValue() || "-"),
        },
        {
          title: "Department",
          field: "department_label",
          width: 240,
          formatter: (cell) => escapeHtml(cell.getValue() || "None"),
        },
        { title: "Status", field: "is_active_text", width: 140 },
        { title: "Created", field: "created_at_text", width: 200 },
        {
          title: "",
          field: "id",
          headerSort: false,
          hozAlign: "right",
          formatter: (cell) => {
            if (!config.canManage) {
              return "";
            }

            const row = cell.getRow().getData() || {};
            const id = row?.id;
            if (!id) {
              return "";
            }

            if (row?.is_archived) {
              return `
                <div class="hstack flex gap-2 justify-end">
                  <button class="ti-btn ti-btn-sm ti-btn-success !rounded-full" type="button" data-action="restore-accountable-person" data-id="${escapeHtml(id)}">
                    <i class="ri-refresh-line"></i>
                  </button>
                </div>
              `;
            }

            return `
              <div class="hstack flex gap-2 justify-end">
                <button class="ti-btn ti-btn-sm ti-btn-info !rounded-full" type="button" data-action="edit-accountable-person" data-row='${escapeHtml(JSON.stringify(row))}'>
                  <i class="ri-edit-line"></i>
                </button>
                <button class="ti-btn ti-btn-sm ti-btn-danger !rounded-full" type="button" data-action="delete-accountable-person" data-id="${escapeHtml(id)}">
                  <i class="ri-archive-line"></i>
                </button>
              </div>
            `;
          },
        },
      ],
    });

    window.__accountablePersonsTable = table;
    window.__accountablePersonsReload = (options = {}) => reload(table, options);

    table.on("dataLoaded", function () {
      tableElement.classList.remove("is-loading");
      updateInfo(table);
    });

    table.on("pageLoaded", function () {
      updateInfo(table);
    });

    setInfo("Loading...");
  });
})();
