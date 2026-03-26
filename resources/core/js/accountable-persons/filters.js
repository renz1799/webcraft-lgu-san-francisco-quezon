(function () {
  "use strict";

  if (window.__accountablePersonsFiltersBound) return;
  window.__accountablePersonsFiltersBound = true;

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
    const tableElement = document.getElementById("accountable-persons-table");
    if (!tableElement) return;

    const searchInput = document.getElementById("accountable-persons-search");
    const statusSelect = document.getElementById("accountable-persons-status");
    const departmentSelect = document.getElementById("accountable-persons-department-filter");
    const clearButton = document.getElementById("accountable-persons-clear");
    const moreButton = document.getElementById("accountable-persons-more-btn");
    const morePanel = document.getElementById("accountable-persons-more-panel");
    const moreClose = document.getElementById("accountable-persons-more-close");
    const advancedApply = document.getElementById("accountable-persons-adv-apply");
    const advancedReset = document.getElementById("accountable-persons-adv-reset");
    const advancedCount = document.getElementById("accountable-persons-adv-count");

    const filters = {
      search: "",
      archived: statusSelect?.value || "active",
      department_id: departmentSelect?.value || "",
    };

    window.__accountablePersonsGetParams = () => ({ ...filters });

    let reloadFn = function () {};

    function connectReload() {
      if (typeof window.__accountablePersonsReload === "function") {
        reloadFn = () => window.__accountablePersonsReload();
        return true;
      }

      if (
        window.__accountablePersonsTable &&
        typeof window.__accountablePersonsTable.setData === "function"
      ) {
        reloadFn = () => window.__accountablePersonsTable.setData();
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

    function syncFromUi() {
      filters.search = (searchInput?.value || "").trim();
      filters.archived = (statusSelect?.value || "active").trim();
      filters.department_id = (departmentSelect?.value || "").trim();
    }

    function countAdvanced() {
      let count = 0;

      if ((statusSelect?.value || "active").trim() !== "active") count++;
      if ((departmentSelect?.value || "").trim() !== "") count++;

      if (!advancedCount) return;

      if (count > 0) {
        advancedCount.textContent = String(count);
        advancedCount.classList.remove("hidden");
      } else {
        advancedCount.textContent = "0";
        advancedCount.classList.add("hidden");
      }
    }

    const applyFilters = debounce(() => {
      syncFromUi();
      reloadFn();
    });

    searchInput?.addEventListener("input", applyFilters);

    let isOpen = false;
    let justOpenedAt = 0;
    let placeholder = null;
    let portaled = false;

    function portalToBody() {
      if (!morePanel || portaled) return;

      placeholder = document.createComment(
        "accountable-persons-more-panel-placeholder"
      );
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
      left = Math.max(
        margin,
        Math.min(left, window.innerWidth - panelRect.width - margin)
      );

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

        const inside =
          morePanel.contains(event.target) || moreButton.contains(event.target);
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
      syncFromUi();
      countAdvanced();
      reloadFn();
      closePanel();
    });

    advancedReset?.addEventListener("click", (event) => {
      event.preventDefault();

      if (statusSelect) statusSelect.value = "active";
      if (departmentSelect) departmentSelect.value = "";

      syncFromUi();
      countAdvanced();
      reloadFn();

      if (isOpen) positionPanel();
    });

    clearButton?.addEventListener("click", () => {
      if (searchInput) searchInput.value = "";
      if (statusSelect) statusSelect.value = "active";
      if (departmentSelect) departmentSelect.value = "";

      syncFromUi();
      countAdvanced();
      reloadFn();
      closePanel();
    });

    syncFromUi();
    countAdvanced();
  });
})();
