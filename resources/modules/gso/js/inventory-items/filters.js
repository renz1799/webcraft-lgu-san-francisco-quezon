(function () {
  "use strict";

  if (window.__gsoInventoryItemsFiltersBound) return;
  window.__gsoInventoryItemsFiltersBound = true;

  function debounce(fn, wait = 350) {
    let timer = null;

    return (...args) => {
      clearTimeout(timer);
      timer = setTimeout(() => fn(...args), wait);
    };
  }

  function onReady(fn) {
    if (document.readyState === "loading") {
      document.addEventListener("DOMContentLoaded", fn);
      return;
    }

    fn();
  }

  onReady(function () {
    const tableElement = document.getElementById("gso-inventory-items-table");
    if (!tableElement) return;

    const config = window.__gsoInventoryItems || {};
    const searchInput = document.getElementById("gso-inventory-items-search");
    const departmentFilter = document.getElementById("gso-inventory-items-department-filter");
    const classificationFilter = document.getElementById("gso-inventory-items-classification-filter");
    const custodyFilter = document.getElementById("gso-inventory-items-custody-filter");
    const inventoryStatusFilter = document.getElementById("gso-inventory-items-status-filter");
    const recordStatusFilter = document.getElementById("gso-inventory-items-record-status");
    const clearButton = document.getElementById("gso-inventory-items-clear");
    const moreButton = document.getElementById("gso-inventory-items-more-btn");
    const morePanel = document.getElementById("gso-inventory-items-more-panel");
    const moreClose = document.getElementById("gso-inventory-items-more-close");
    const advancedApply = document.getElementById("gso-inventory-items-adv-apply");
    const advancedReset = document.getElementById("gso-inventory-items-adv-reset");
    const advancedCount = document.getElementById("gso-inventory-items-adv-count");
    const filters = {
      search: "",
      department_id: departmentFilter?.value || "",
      classification: classificationFilter?.value || "",
      custody_state: custodyFilter?.value || "",
      inventory_status: inventoryStatusFilter?.value || "",
      archived: recordStatusFilter?.value || "active",
    };

    window.__gsoInventoryItemsGetParams = () => ({ ...filters });

    let reloadFn = function () {};

    function connectReload() {
      if (typeof window.__gsoInventoryItemsReload === "function") {
        reloadFn = () => window.__gsoInventoryItemsReload();
        return true;
      }

      if (
        window.__gsoInventoryItemsTable &&
        typeof window.__gsoInventoryItemsTable.setData === "function"
      ) {
        reloadFn = () => window.__gsoInventoryItemsTable.setData();
        return true;
      }

      return false;
    }

    if (!connectReload()) {
      let tries = 0;
      const maxTries = 80;
      const timer = setInterval(() => {
        tries++;

        if (connectReload() || tries >= maxTries) {
          clearInterval(timer);
        }
      }, 100);
    }

    function syncAdvancedFromUi() {
      filters.department_id = (departmentFilter?.value || "").trim();
      filters.classification = (classificationFilter?.value || "").trim();
      filters.custody_state = (custodyFilter?.value || "").trim();
      filters.inventory_status = (inventoryStatusFilter?.value || "").trim();
      filters.archived = (recordStatusFilter?.value || "active").trim();
    }

    function countAdvanced() {
      let count = 0;

      if ((departmentFilter?.value || "").trim() !== "") count++;
      if ((classificationFilter?.value || "").trim() !== "") count++;
      if ((custodyFilter?.value || "").trim() !== "") count++;
      if ((inventoryStatusFilter?.value || "").trim() !== "") count++;
      if ((recordStatusFilter?.value || "active").trim() !== "active") count++;

      if (!advancedCount) return;

      if (count > 0) {
        advancedCount.textContent = String(count);
        advancedCount.classList.remove("hidden");
      } else {
        advancedCount.textContent = "0";
        advancedCount.classList.add("hidden");
      }
    }

    const applySearch = debounce(() => {
      filters.search = (searchInput?.value || "").trim();
      reloadFn();
    });

    searchInput?.addEventListener("input", applySearch);

    let isOpen = false;
    let justOpenedAt = 0;
    let placeholder = null;
    let portaled = false;

    function portalToBody() {
      if (!morePanel || portaled) return;

      placeholder = document.createComment("gso-inventory-items-more-panel-placeholder");
      morePanel.parentNode.insertBefore(placeholder, morePanel);
      document.body.appendChild(morePanel);
      portaled = true;
    }

    function restoreFromBody() {
      if (!morePanel || !portaled || !placeholder) return;

      placeholder.parentNode.insertBefore(morePanel, placeholder);
      placeholder.parentNode.removeChild(placeholder);

      placeholder = null;
      portaled = false;

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

    function positionPanel() {
      if (!moreButton || !morePanel) return;

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

      const top = buttonRect.bottom + margin;

      morePanel.style.left = `${left}px`;
      morePanel.style.top = `${top}px`;
      morePanel.style.right = "auto";
      morePanel.style.bottom = "auto";
    }

    function openPanel() {
      if (!morePanel) return;

      justOpenedAt = Date.now();
      portalToBody();
      isOpen = true;

      morePanel.classList.remove("hidden");
      morePanel.style.display = "block";

      positionPanel();
    }

    function closePanel() {
      if (!morePanel) return;

      isOpen = false;
      morePanel.classList.add("hidden");
      morePanel.style.display = "none";
      restoreFromBody();
    }

    function togglePanel() {
      if (isOpen) {
        closePanel();
      } else {
        openPanel();
      }
    }

    moreButton?.addEventListener(
      "pointerdown",
      (event) => {
        event.preventDefault();
        event.stopPropagation();
        togglePanel();
      },
      true
    );

    morePanel?.addEventListener(
      "pointerdown",
      (event) => {
        event.stopPropagation();
      },
      true
    );

    moreClose?.addEventListener(
      "pointerdown",
      (event) => {
        event.preventDefault();
        event.stopPropagation();
        closePanel();
      },
      true
    );

    document.addEventListener(
      "pointerdown",
      (event) => {
        if (!isOpen || !morePanel || !moreButton) return;
        if (Date.now() - justOpenedAt < 180) return;

        const inside = morePanel.contains(event.target) || moreButton.contains(event.target);
        if (!inside) closePanel();
      },
      true
    );

    window.addEventListener("resize", () => {
      if (!isOpen) return;
      positionPanel();
    });

    window.addEventListener(
      "scroll",
      () => {
        if (!isOpen) return;
        positionPanel();
      },
      true
    );

    advancedApply?.addEventListener("click", (event) => {
      event.preventDefault();
      syncAdvancedFromUi();
      countAdvanced();
      reloadFn();
      closePanel();
    });

    advancedReset?.addEventListener("click", (event) => {
      event.preventDefault();

      if (departmentFilter) departmentFilter.value = "";
      if (classificationFilter) classificationFilter.value = "";
      if (custodyFilter) custodyFilter.value = "";
      if (inventoryStatusFilter) inventoryStatusFilter.value = "";
      if (recordStatusFilter) recordStatusFilter.value = "active";

      syncAdvancedFromUi();
      countAdvanced();
      reloadFn();

      if (isOpen) positionPanel();
    });

    clearButton?.addEventListener("click", () => {
      if (searchInput) searchInput.value = "";
      if (departmentFilter) departmentFilter.value = "";
      if (classificationFilter) classificationFilter.value = "";
      if (custodyFilter) custodyFilter.value = "";
      if (inventoryStatusFilter) inventoryStatusFilter.value = "";
      if (recordStatusFilter) recordStatusFilter.value = "active";

      filters.search = "";
      syncAdvancedFromUi();
      countAdvanced();
      reloadFn();
      closePanel();
    });

    filters.search = (searchInput?.value || "").trim();
    syncAdvancedFromUi();
    countAdvanced();
  });
})();
