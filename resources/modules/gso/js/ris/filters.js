(function () {
  "use strict";

  if (window.__risFiltersBound) return;
  window.__risFiltersBound = true;

  function debounce(fn, wait = 350) {
    let timeout = null;
    return (...args) => {
      clearTimeout(timeout);
      timeout = setTimeout(() => fn.apply(null, args), wait);
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
    const search = document.getElementById("ris-search");
    const clear = document.getElementById("ris-clear");
    const workflowStatus = document.getElementById("ris-status");
    const moreButton = document.getElementById("ris-more-btn");
    const morePanel = document.getElementById("ris-more-panel");
    const moreClose = document.getElementById("ris-more-close");
    const dateFrom = document.getElementById("ris-date-from");
    const dateTo = document.getElementById("ris-date-to");
    const fund = document.getElementById("ris-fund");
    const recordStatus = document.getElementById("ris-record-status");
    const advancedApply = document.getElementById("ris-adv-apply");
    const advancedReset = document.getElementById("ris-adv-reset");
    const advancedCount = document.getElementById("ris-adv-count");

    if (!moreButton || !morePanel) return;

    const filters = {
      search: "",
      status: "",
      record_status: "",
      date_from: "",
      date_to: "",
      fund: "",
    };

    window.__risGetParams = () => ({ ...filters });

    function syncFromUi() {
      filters.search = (search?.value || "").trim();
      filters.status = (workflowStatus?.value || "").trim();
      filters.date_from = (dateFrom?.value || "").trim();
      filters.date_to = (dateTo?.value || "").trim();
      filters.fund = (fund?.value || "").trim();
      filters.record_status = (recordStatus?.value || "").trim();
    }

    function countAdvanced() {
      let count = 0;
      if ((dateFrom?.value || "").trim()) count++;
      if ((dateTo?.value || "").trim()) count++;
      if ((fund?.value || "").trim()) count++;
      if ((recordStatus?.value || "").trim()) count++;

      if (!advancedCount) return;

      if (count > 0) {
        advancedCount.textContent = String(count);
        advancedCount.classList.remove("hidden");
      } else {
        advancedCount.textContent = "0";
        advancedCount.classList.add("hidden");
      }
    }

    function reload() {
      if (typeof window.__risReload === "function") {
        window.__risReload();
      }
    }

    const applyPrimary = debounce(() => {
      syncFromUi();
      reload();
    }, 350);

    search?.addEventListener("input", applyPrimary);
    workflowStatus?.addEventListener("change", applyPrimary);

    let isOpen = false;
    let justOpenedAt = 0;
    let placeholder = null;
    let portaled = false;

    function portalToBody() {
      if (portaled) return;
      placeholder = document.createComment("ris-more-panel-placeholder");
      morePanel.parentNode.insertBefore(placeholder, morePanel);
      document.body.appendChild(morePanel);
      portaled = true;
    }

    function restoreFromBody() {
      if (!portaled || !placeholder) return;

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

    function openPanel() {
      justOpenedAt = Date.now();
      portalToBody();
      isOpen = true;
      morePanel.classList.remove("hidden");
      morePanel.style.display = "block";
      positionPanel();
    }

    function closePanel() {
      isOpen = false;
      morePanel.classList.add("hidden");
      morePanel.style.display = "none";
      restoreFromBody();
    }

    moreButton.addEventListener(
      "pointerdown",
      (event) => {
        event.preventDefault();
        event.stopPropagation();
        if (isOpen) {
          closePanel();
          return;
        }
        openPanel();
      },
      true
    );

    morePanel.addEventListener(
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
        if (!isOpen) return;
        if (Date.now() - justOpenedAt < 180) return;

        const inside = morePanel.contains(event.target) || moreButton.contains(event.target);
        if (!inside) {
          closePanel();
        }
      },
      true
    );

    window.addEventListener("resize", () => {
      if (isOpen) positionPanel();
    });

    window.addEventListener(
      "scroll",
      () => {
        if (isOpen) positionPanel();
      },
      true
    );

    advancedApply?.addEventListener("click", (event) => {
      event.preventDefault();
      syncFromUi();
      countAdvanced();
      reload();
      closePanel();
    });

    advancedReset?.addEventListener("click", (event) => {
      event.preventDefault();

      if (dateFrom) dateFrom.value = "";
      if (dateTo) dateTo.value = "";
      if (fund) fund.value = "";
      if (recordStatus) recordStatus.value = "";

      syncFromUi();
      countAdvanced();
      reload();

      if (isOpen) positionPanel();
    });

    clear?.addEventListener("click", (event) => {
      event.preventDefault();

      if (search) search.value = "";
      if (workflowStatus) workflowStatus.value = "";
      if (dateFrom) dateFrom.value = "";
      if (dateTo) dateTo.value = "";
      if (fund) fund.value = "";
      if (recordStatus) recordStatus.value = "";

      syncFromUi();
      countAdvanced();
      reload();
      closePanel();
    });

    syncFromUi();
    countAdvanced();
  });
})();
