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
    let timer = null;

    return (...args) => {
      clearTimeout(timer);
      timer = setTimeout(() => fn(...args), wait);
    };
  }

  function escapeHtml(value) {
    return String(value ?? "")
      .replace(/&/g, "&amp;")
      .replace(/</g, "&lt;")
      .replace(/>/g, "&gt;")
      .replace(/"/g, "&quot;")
      .replace(/'/g, "&#39;");
  }

  function buildItemUrl(template, itemId, params = {}) {
    const url = String(template || "").replace("__ITEM__", encodeURIComponent(String(itemId || "")));
    const search = new URLSearchParams();

    Object.entries(params).forEach(([key, value]) => {
      if (value === null || value === undefined || value === "") {
        return;
      }

      search.set(key, String(value));
    });

    const query = search.toString();
    return query ? `${url}?${query}` : url;
  }

  async function parseErrorResponse(response, fallbackMessage) {
    const contentType = response.headers.get("content-type") || "";
    const data = contentType.includes("application/json")
      ? await response.json().catch(() => null)
      : null;

    if (data?.errors) {
      const firstKey = Object.keys(data.errors)[0];
      if (firstKey && Array.isArray(data.errors[firstKey]) && data.errors[firstKey][0]) {
        return String(data.errors[firstKey][0]);
      }
    }

    return data?.message || data?.error || fallbackMessage;
  }

  function parseFunds(raw) {
    if (!raw) {
      return [];
    }

    try {
      const parsed = JSON.parse(raw);
      return Array.isArray(parsed) ? parsed : [];
    } catch {
      return [];
    }
  }

  function fundBadges(funds) {
    if (!Array.isArray(funds) || funds.length === 0) {
      return '<span class="text-xs text-[#8c9097]">No stock rows yet</span>';
    }

    return funds
      .map((fund) => {
        const code = escapeHtml(fund?.code || "NA");
        const qty = Number(fund?.on_hand || 0);
        const title = escapeHtml(fund?.label || code);

        return `<span title="${title}" class="inline-flex items-center rounded-full px-2 py-1 text-xs font-medium bg-primary/10 text-primary mr-1 mb-1">${code}: ${qty}</span>`;
      })
      .join("");
  }

  function defaultFundSourceId(funds, filters) {
    if (filters.fund_source_id) {
      return filters.fund_source_id;
    }

    if (Array.isArray(funds) && funds.length === 1) {
      return String(funds[0]?.id || "");
    }

    return "";
  }

  async function openStockCardDialog(config, itemId, funds, filters) {
    if (!Array.isArray(funds) || funds.length === 0) {
      await Swal.fire({
        icon: "warning",
        title: "No Stock Row",
        text: "This item does not have a stock row yet. Create one through a manual adjustment first.",
      });
      return;
    }

    const currentDefault = defaultFundSourceId(funds, filters);
    const options = ['<option value="">Auto Select</option>']
      .concat(
        funds.map((fund) => {
          const selected = currentDefault === String(fund?.id || "") ? "selected" : "";
          return `<option value="${escapeHtml(fund?.id || "")}" ${selected}>${escapeHtml(fund?.label || "Fund Source")} (On hand: ${Number(fund?.on_hand || 0)})</option>`;
        })
      )
      .join("");

    const result = await Swal.fire({
      title: "Open Stock Card",
      html: `
        <div class="text-left space-y-3">
          <div>
            <label class="block text-sm mb-1">Fund Source</label>
            <select id="gso-stock-card-fund-source" class="swal2-input" style="width:100%;">${options}</select>
          </div>
          <div>
            <label class="block text-sm mb-1">As of Date (optional)</label>
            <input id="gso-stock-card-as-of" type="date" class="swal2-input" style="width:100%;" value="">
          </div>
        </div>
      `,
      focusConfirm: false,
      showCancelButton: true,
      confirmButtonText: "Open Preview",
      preConfirm: () => ({
        fund_source_id: document.getElementById("gso-stock-card-fund-source")?.value || "",
        as_of: document.getElementById("gso-stock-card-as-of")?.value || "",
      }),
    });

    if (!result.isConfirmed) {
      return;
    }

    window.open(
      buildItemUrl(config.cardUrlTemplate, itemId, {
        preview: 1,
        fund_source_id: result.value?.fund_source_id || "",
        as_of: result.value?.as_of || "",
      }),
      "_blank",
      "noopener"
    );
  }

  async function openAdjustDialog(config, itemId, funds, filters, reload) {
    const globalFunds = Array.isArray(config.fundSources) ? config.fundSources : [];
    const currentDefault = defaultFundSourceId(funds, filters);
    const options = ['<option value="">Unassigned / Auto</option>']
      .concat(globalFunds.map((fund) => {
        const selected = currentDefault === String(fund?.id || "") ? "selected" : "";
        return `<option value="${escapeHtml(fund?.id || "")}" ${selected}>${escapeHtml(fund?.label || "Fund Source")}</option>`;
      }))
      .join("");

    const result = await Swal.fire({
      title: "Adjust Stock",
      html: `
        <div class="text-left space-y-3">
          <div>
            <label class="block text-sm mb-1">Fund Source</label>
            <select id="gso-stock-adjust-fund-source" class="swal2-input" style="width:100%;">${options}</select>
          </div>
          <div>
            <label class="block text-sm mb-1">Adjustment Type</label>
            <select id="gso-stock-adjust-type" class="swal2-input" style="width:100%;">
              <option value="increase">Increase</option>
              <option value="decrease">Decrease</option>
              <option value="set">Set Quantity</option>
            </select>
          </div>
          <div>
            <label class="block text-sm mb-1">Quantity</label>
            <input id="gso-stock-adjust-qty" type="number" min="1" class="swal2-input" style="width:100%;" placeholder="Enter quantity">
          </div>
          <div>
            <label class="block text-sm mb-1">Remarks (optional)</label>
            <input id="gso-stock-adjust-remarks" type="text" class="swal2-input" style="width:100%;" placeholder="Reason or note">
          </div>
        </div>
      `,
      focusConfirm: false,
      showCancelButton: true,
      confirmButtonText: "Submit Adjustment",
      preConfirm: () => {
        const qty = Number(document.getElementById("gso-stock-adjust-qty")?.value || 0);
        if (!Number.isFinite(qty) || qty <= 0) {
          Swal.showValidationMessage("Enter a quantity greater than zero.");
          return false;
        }

        return {
          fund_source_id: document.getElementById("gso-stock-adjust-fund-source")?.value || "",
          type: document.getElementById("gso-stock-adjust-type")?.value || "",
          qty,
          remarks: document.getElementById("gso-stock-adjust-remarks")?.value || "",
        };
      },
    });

    if (!result.isConfirmed) {
      return;
    }

    Swal.fire({
      title: "Saving...",
      allowOutsideClick: false,
      allowEscapeKey: false,
      didOpen: () => Swal.showLoading(),
    });

    const response = await fetch(config.adjustUrl, {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
        "X-CSRF-TOKEN": config.csrf || "",
        "X-Requested-With": "XMLHttpRequest",
      },
      body: JSON.stringify({
        item_id: itemId,
        fund_source_id: result.value?.fund_source_id || null,
        type: result.value?.type || "increase",
        qty: result.value?.qty || 0,
        remarks: result.value?.remarks || "",
      }),
    });

    if (!response.ok) {
      const message = await parseErrorResponse(response, "The stock adjustment could not be completed.");
      await Swal.fire({
        icon: "error",
        title: "Cannot Adjust Stock",
        text: message,
      });
      return;
    }

    const payload = await response.json().catch(() => ({}));
    await Swal.fire({
      icon: "success",
      title: "Stock Updated",
      text: payload?.message || "The stock balance was updated successfully.",
      timer: 1400,
      showConfirmButton: false,
    });

    reload();
  }

  function numberFormatter(value) {
    return escapeHtml(Number(value || 0).toLocaleString());
  }

  onReady(function () {
    const tableElement = document.getElementById("gso-stocks-table");
    if (!tableElement) return;

    const config = window.__gsoStocks || {};
    const pageMode = String(config.pageMode || "stocks").trim();
    const isStockCardMode = pageMode === "stock-cards";
    const infoElement = document.getElementById("gso-stocks-info");
    const searchInput = document.getElementById("gso-stocks-search");
    const archivedSelect = document.getElementById("gso-stocks-archived-filter");
    const fundFilter = document.getElementById("gso-stocks-fund-filter");
    const dateFromInput = document.getElementById("gso-stocks-date-from");
    const dateToInput = document.getElementById("gso-stocks-date-to");
    const onHandMinInput = document.getElementById("gso-stocks-onhand-min");
    const onHandMaxInput = document.getElementById("gso-stocks-onhand-max");
    const clearButton = document.getElementById("gso-stocks-clear");
    const moreButton = document.getElementById("gso-stocks-more-btn");
    const morePanel = document.getElementById("gso-stocks-more-panel");
    const moreCloseButton = document.getElementById("gso-stocks-more-close");
    const advancedApplyButton = document.getElementById("gso-stocks-adv-apply");
    const advancedResetButton = document.getElementById("gso-stocks-adv-reset");
    const advancedCountBadge = document.getElementById("gso-stocks-adv-count");

    let filters = {
      search: "",
      fund_source_id: "",
      date_from: "",
      date_to: "",
      onhand_min: "",
      onhand_max: "",
      archived: archivedSelect?.value || "active",
    };
    let lastTotal = 0;
    let panelOpen = false;
    let panelOpenedAt = 0;
    let panelPlaceholder = null;
    let panelPortaled = false;

    function setInfo(text) {
      if (infoElement) {
        infoElement.textContent = text;
      }
    }

    function updateInfo(table) {
      if (lastTotal <= 0) {
        setInfo("No records found");
        return;
      }

      const page = table.getPage() || 1;
      const size = table.getPageSize ? table.getPageSize() || 15 : 15;
      const start = (page - 1) * size + 1;
      const end = Math.min(start + size - 1, lastTotal);

      setInfo(
        isStockCardMode
          ? `Showing ${start}-${end} of ${lastTotal} stock-card source item(s)`
          : `Showing ${start}-${end} of ${lastTotal} consumable item(s)`
      );
    }

    function countAdvancedFilters() {
      let count = 0;

      if ((archivedSelect?.value || "active").trim() !== "active") count++;
      if ((fundFilter?.value || "").trim() !== "") count++;
      if ((dateFromInput?.value || "").trim() !== "") count++;
      if ((dateToInput?.value || "").trim() !== "") count++;
      if ((onHandMinInput?.value || "").trim() !== "") count++;
      if ((onHandMaxInput?.value || "").trim() !== "") count++;

      if (advancedCountBadge) {
        advancedCountBadge.textContent = String(count);
        advancedCountBadge.classList.toggle("hidden", count === 0);
      }

      return count;
    }

    function syncFiltersFromUi() {
      filters.search = (searchInput?.value || "").trim();
      filters.archived = (archivedSelect?.value || "active").trim();
      filters.fund_source_id = (fundFilter?.value || "").trim();
      filters.date_from = (dateFromInput?.value || "").trim();
      filters.date_to = (dateToInput?.value || "").trim();
      filters.onhand_min = (onHandMinInput?.value || "").trim();
      filters.onhand_max = (onHandMaxInput?.value || "").trim();
    }

    function restorePanel() {
      if (!panelPortaled || !panelPlaceholder || !morePanel) {
        return;
      }

      panelPlaceholder.parentNode.insertBefore(morePanel, panelPlaceholder);
      panelPlaceholder.parentNode.removeChild(panelPlaceholder);
      panelPlaceholder = null;
      panelPortaled = false;

      morePanel.style.position = "";
      morePanel.style.top = "";
      morePanel.style.left = "";
      morePanel.style.right = "";
      morePanel.style.bottom = "";
      morePanel.style.zIndex = "";
      morePanel.style.transform = "";
      morePanel.style.opacity = "";
      morePanel.style.visibility = "";
      morePanel.style.pointerEvents = "";
      morePanel.style.display = "";
    }

    function portalPanel() {
      if (!morePanel || panelPortaled) {
        return;
      }

      panelPlaceholder = document.createComment("gso-stocks-more-panel-placeholder");
      morePanel.parentNode.insertBefore(panelPlaceholder, morePanel);
      document.body.appendChild(morePanel);
      panelPortaled = true;
    }

    function positionPanel() {
      if (!moreButton || !morePanel) {
        return;
      }

      const buttonRect = moreButton.getBoundingClientRect();
      const margin = 8;

      morePanel.classList.remove("hidden");
      morePanel.style.display = "block";
      morePanel.style.visibility = "visible";
      morePanel.style.opacity = "1";
      morePanel.style.pointerEvents = "auto";
      morePanel.style.transform = "none";
      morePanel.style.zIndex = "999999";
      morePanel.style.position = "fixed";

      const panelRect = morePanel.getBoundingClientRect();
      let left = buttonRect.right - panelRect.width;
      left = Math.max(margin, Math.min(left, window.innerWidth - panelRect.width - margin));

      morePanel.style.left = `${left}px`;
      morePanel.style.top = `${buttonRect.bottom + margin}px`;
      morePanel.style.right = "auto";
      morePanel.style.bottom = "auto";
    }

    function closePanel() {
      if (!morePanel) {
        return;
      }

      panelOpen = false;
      morePanel.classList.add("hidden");
      morePanel.style.display = "none";
      restorePanel();
    }

    function openPanel() {
      if (!morePanel) {
        return;
      }

      panelOpen = true;
      panelOpenedAt = Date.now();
      portalPanel();
      positionPanel();
    }

    function togglePanel() {
      if (!morePanel) {
        return;
      }

      if (panelOpen) {
        closePanel();
      } else {
        openPanel();
      }
    }

    function reload(table) {
      if ((table.getPage?.() || 1) !== 1) {
        table.setPage(1);
        return;
      }

      table.setData();
    }

    const table = new Tabulator(tableElement, {
      layout: "fitColumns",
      responsiveLayout: "collapse",
      placeholder: "No stock rows found.",
      pagination: "remote",
      paginationSize: 15,
      paginationSizeSelector: [10, 20, 50, 100],
      ajaxURL: config.ajaxUrl,
      ajaxConfig: "GET",
      paginationDataSent: { page: "page", size: "size" },
      paginationDataReceived: {
        last_page: "last_page",
        data: "data",
        total: "total",
      },
      ajaxParams: () => ({ ...filters }),
      ajaxResponse: (_, __, response) => {
        lastTotal = Number(response?.total ?? 0);
        return response?.data ?? [];
      },
      columns: [
        {
          title: "Item",
          field: "item_name",
          minWidth: 240,
          formatter: (cell) => {
            const row = cell.getRow().getData() || {};
            const name = escapeHtml(row?.item_name || "Consumable Item");
            const description = escapeHtml(row?.description || "");

            return description
              ? `${name}<div class="text-xs text-[#8c9097] mt-1">${description}</div>`
              : name;
          },
        },
        {
          title: "Stock No.",
          field: "stock_number",
          width: 160,
          formatter: (cell) => escapeHtml(cell.getValue() || "-"),
        },
        {
          title: "Unit",
          field: "unit",
          width: 120,
          formatter: (cell) => escapeHtml(cell.getValue() || "-"),
        },
        {
          title: "Fund Balances",
          field: "funds",
          minWidth: 250,
          headerSort: false,
          formatter: (cell) => fundBadges(cell.getValue()),
        },
        {
          title: "On Hand",
          field: "on_hand",
          width: 120,
          hozAlign: "right",
          formatter: (cell) => numberFormatter(cell.getValue()),
        },
        {
          title: "Last Movement",
          field: "last_movement_at",
          width: 190,
          formatter: (cell) => escapeHtml(cell.getValue() || "-"),
        },
        {
          title: "",
          field: "item_id",
          headerSort: false,
          hozAlign: "right",
          width: 180,
          formatter: (cell) => {
            const row = cell.getRow().getData() || {};
            const itemId = escapeHtml(cell.getValue() || "");
            const funds = escapeHtml(JSON.stringify(row?.funds || []));
            const printDisabled = row?.has_stock_rows ? "" : "disabled";

            if (isStockCardMode) {
              return `
                <div class="hstack flex gap-2 justify-end">
                  <button
                    class="ti-btn ti-btn-sm ti-btn-info !rounded-full"
                    type="button"
                    data-action="stock-card"
                    data-item-id="${itemId}"
                    data-funds="${funds}"
                    ${printDisabled}
                  >
                    <i class="ri-printer-line"></i>
                  </button>
                </div>
              `;
            }

            return `
              <div class="hstack flex gap-2 justify-end">
                <button
                  class="ti-btn ti-btn-sm ti-btn-info !rounded-full"
                  type="button"
                  data-action="stock-card"
                  data-item-id="${itemId}"
                  data-funds="${funds}"
                  ${printDisabled}
                >
                  <i class="ri-printer-line"></i>
                </button>
                <a class="ti-btn ti-btn-sm ti-btn-light !rounded-full" href="${escapeHtml(buildItemUrl(config.ledgerUrlTemplate, row?.item_id))}">
                  <i class="ri-file-list-3-line"></i>
                </a>
                ${
                  config.canManage
                    ? `<button
                        class="ti-btn ti-btn-sm ti-btn-primary !rounded-full"
                        type="button"
                        data-action="adjust-stock"
                        data-item-id="${itemId}"
                        data-funds="${funds}"
                      >
                        <i class="ri-edit-line"></i>
                      </button>`
                    : ""
                }
              </div>
            `;
          },
        },
      ],
    });

    table.on("dataLoaded", () => updateInfo(table));
    table.on("pageLoaded", () => updateInfo(table));

    function applyFilters() {
      syncFiltersFromUi();
      countAdvancedFilters();
      reload(table);
    }

    const debouncedApply = debounce(applyFilters, 350);

    searchInput?.addEventListener("input", debouncedApply);
    archivedSelect?.addEventListener("change", applyFilters);
    moreButton?.addEventListener("click", (event) => {
      event.preventDefault();
      event.stopPropagation();
      togglePanel();
    });
    moreCloseButton?.addEventListener("click", (event) => {
      event.preventDefault();
      closePanel();
    });
    advancedApplyButton?.addEventListener("click", (event) => {
      event.preventDefault();
      applyFilters();
      closePanel();
    });
    advancedResetButton?.addEventListener("click", (event) => {
      event.preventDefault();

      if (archivedSelect) archivedSelect.value = "active";
      if (fundFilter) fundFilter.value = "";
      if (dateFromInput) dateFromInput.value = "";
      if (dateToInput) dateToInput.value = "";
      if (onHandMinInput) onHandMinInput.value = "";
      if (onHandMaxInput) onHandMaxInput.value = "";

      countAdvancedFilters();
    });

    clearButton?.addEventListener("click", () => {
      filters = {
        search: "",
        fund_source_id: "",
        date_from: "",
        date_to: "",
        onhand_min: "",
        onhand_max: "",
        archived: "active",
      };

      if (searchInput) searchInput.value = "";
      if (archivedSelect) archivedSelect.value = "active";
      if (fundFilter) fundFilter.value = "";
      if (dateFromInput) dateFromInput.value = "";
      if (dateToInput) dateToInput.value = "";
      if (onHandMinInput) onHandMinInput.value = "";
      if (onHandMaxInput) onHandMaxInput.value = "";

      countAdvancedFilters();
      closePanel();
      reload(table);
    });

    document.addEventListener("click", (event) => {
      if (!panelOpen || !morePanel || !moreButton) {
        return;
      }

      const target = event.target;

      if (morePanel.contains(target) || moreButton.contains(target)) {
        return;
      }

      if (Date.now() - panelOpenedAt < 50) {
        return;
      }

      closePanel();
    });

    window.addEventListener("resize", () => {
      if (panelOpen) {
        positionPanel();
      }
    });

    window.addEventListener("scroll", () => {
      if (panelOpen) {
        positionPanel();
      }
    }, true);

    countAdvancedFilters();

    tableElement.addEventListener("click", async (event) => {
      const button = event.target.closest("button[data-action]");
      if (!button) return;

      const action = button.dataset.action || "";
      const itemId = button.dataset.itemId || "";
      const funds = parseFunds(button.dataset.funds || "[]");

      if (!itemId) {
        return;
      }

      if (action === "stock-card") {
        await openStockCardDialog(config, itemId, funds, filters);
        return;
      }

      if (action === "adjust-stock" && config.canManage) {
        await openAdjustDialog(config, itemId, funds, filters, () => reload(table));
      }
    });
  });
})();
