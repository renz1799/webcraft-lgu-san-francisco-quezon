(function () {
  "use strict";

  if (window.__gsoItemsFiltersBound) return;
  window.__gsoItemsFiltersBound = true;

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
    const tableElement = document.getElementById("gso-items-table");
    if (!tableElement) return;

    const searchInput = document.getElementById("gso-items-search");
    const statusSelect = document.getElementById("gso-items-status");
    const assetFilterSelect = document.getElementById("gso-items-asset-filter");
    const trackingFilterSelect = document.getElementById("gso-items-tracking-filter");
    const serialFilterSelect = document.getElementById("gso-items-serial-filter");
    const semiFilterSelect = document.getElementById("gso-items-semi-filter");
    const clearButton = document.getElementById("gso-items-clear");
    const moreButton = document.getElementById("gso-items-more-btn");
    const morePanel = document.getElementById("gso-items-more-panel");
    const moreClose = document.getElementById("gso-items-more-close");
    const advancedApply = document.getElementById("gso-items-adv-apply");
    const advancedReset = document.getElementById("gso-items-adv-reset");
    const advancedCount = document.getElementById("gso-items-adv-count");

    const filters = {
      search: "",
      archived: statusSelect?.value || "active",
      asset_id: assetFilterSelect?.value || "",
      tracking_type: trackingFilterSelect?.value || "",
      requires_serial: serialFilterSelect?.value || "",
      is_semi_expendable: semiFilterSelect?.value || "",
    };

    window.__gsoItemsGetParams = () => ({ ...filters });

    let reloadFn = function () {};

    function connectReload() {
      if (typeof window.__gsoItemsReload === "function") {
        reloadFn = () => window.__gsoItemsReload();
        return true;
      }

      if (window.__gsoItemsTable && typeof window.__gsoItemsTable.setData === "function") {
        reloadFn = () => window.__gsoItemsTable.setData();
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
      filters.archived = (statusSelect?.value || "active").trim();
      filters.asset_id = (assetFilterSelect?.value || "").trim();
      filters.tracking_type = (trackingFilterSelect?.value || "").trim();
      filters.requires_serial = (serialFilterSelect?.value || "").trim();
      filters.is_semi_expendable = (semiFilterSelect?.value || "").trim();
    }

    function countAdvanced() {
      let count = 0;

      if ((statusSelect?.value || "active").trim() !== "active") count++;
      if ((assetFilterSelect?.value || "").trim() !== "") count++;
      if ((trackingFilterSelect?.value || "").trim() !== "") count++;
      if ((serialFilterSelect?.value || "").trim() !== "") count++;
      if ((semiFilterSelect?.value || "").trim() !== "") count++;

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

      placeholder = document.createComment("gso-items-more-panel-placeholder");
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

      if (statusSelect) statusSelect.value = "active";
      if (assetFilterSelect) assetFilterSelect.value = "";
      if (trackingFilterSelect) trackingFilterSelect.value = "";
      if (serialFilterSelect) serialFilterSelect.value = "";
      if (semiFilterSelect) semiFilterSelect.value = "";

      syncAdvancedFromUi();
      countAdvanced();
      reloadFn();

      if (isOpen) positionPanel();
    });

    clearButton?.addEventListener("click", () => {
      if (searchInput) searchInput.value = "";
      if (statusSelect) statusSelect.value = "active";
      if (assetFilterSelect) assetFilterSelect.value = "";
      if (trackingFilterSelect) trackingFilterSelect.value = "";
      if (serialFilterSelect) serialFilterSelect.value = "";
      if (semiFilterSelect) semiFilterSelect.value = "";

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
