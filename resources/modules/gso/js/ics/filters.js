(function () {
  "use strict";

  if (window.__icsFiltersBound) return;
  window.__icsFiltersBound = true;

  function debounce(fn, wait = 350) {
    let t = null;
    return (...args) => {
      clearTimeout(t);
      t = setTimeout(() => fn.apply(null, args), wait);
    };
  }

  function onReady(fn) {
    if (document.readyState === "loading") document.addEventListener("DOMContentLoaded", fn);
    else fn();
  }

  onReady(function () {
    const search = document.getElementById("ics-search");
    const clear = document.getElementById("ics-clear");
    const workflowStatusSel = document.getElementById("ics-status");
    const moreBtn = document.getElementById("ics-more-btn");
    const morePanel = document.getElementById("ics-more-panel");
    const moreClose = document.getElementById("ics-more-close");
    const dateFrom = document.getElementById("ics-date-from");
    const dateTo = document.getElementById("ics-date-to");
    const departmentSel = document.getElementById("ics-department");
    const fundSourceSel = document.getElementById("ics-fund-source");
    const recordStatusSel = document.getElementById("ics-record-status");
    const advApply = document.getElementById("ics-adv-apply");
    const advReset = document.getElementById("ics-adv-reset");
    const advCount = document.getElementById("ics-adv-count");

    if (!moreBtn || !morePanel) return;

    const filters = {
      search: "",
      status: "",
      record_status: "",
      date_from: "",
      date_to: "",
      department_id: "",
      fund_source_id: "",
    };

    window.__icsGetParams = () => ({ ...filters });

    function syncFromUI() {
      filters.search = (search?.value || "").trim();
      filters.status = (workflowStatusSel?.value || "").trim();
      filters.date_from = (dateFrom?.value || "").trim();
      filters.date_to = (dateTo?.value || "").trim();
      filters.department_id = (departmentSel?.value || "").trim();
      filters.fund_source_id = (fundSourceSel?.value || "").trim();
      filters.record_status = (recordStatusSel?.value || "").trim();
    }

    function countAdvanced() {
      let n = 0;
      if ((workflowStatusSel?.value || "").trim() !== "") n++;
      if ((dateFrom?.value || "").trim() !== "") n++;
      if ((dateTo?.value || "").trim() !== "") n++;
      if ((departmentSel?.value || "").trim() !== "") n++;
      if ((fundSourceSel?.value || "").trim() !== "") n++;
      if ((recordStatusSel?.value || "").trim() !== "") n++;

      if (!advCount) return;
      if (n > 0) {
        advCount.textContent = String(n);
        advCount.classList.remove("hidden");
      } else {
        advCount.textContent = "0";
        advCount.classList.add("hidden");
      }
    }

    let reloadFn = function () {};
    if (typeof window.__icsReload === "function") {
      reloadFn = () => window.__icsReload();
    } else {
      let tries = 0;
      const maxTries = 80;
      const timer = setInterval(() => {
        tries++;
        if (typeof window.__icsReload === "function") {
          clearInterval(timer);
          reloadFn = () => window.__icsReload();
          return;
        }
        if (window.__icsTable && typeof window.__icsTable.setData === "function") {
          clearInterval(timer);
          reloadFn = () => window.__icsTable.setData();
          return;
        }
        if (tries >= maxTries) clearInterval(timer);
      }, 100);
    }

    const applyPrimary = debounce(() => {
      syncFromUI();
      countAdvanced();
      reloadFn();
    }, 350);

    search?.addEventListener("input", applyPrimary);

    let isOpen = false;
    let justOpenedAt = Date.now();
    let placeholder = null;
    let portaled = false;

    function portalToBody() {
      if (portaled) return;
      placeholder = document.createComment("ics-more-panel-placeholder");
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
      const btnRect = moreBtn.getBoundingClientRect();
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
      let left = btnRect.right - panelRect.width;
      left = Math.max(margin, Math.min(left, window.innerWidth - panelRect.width - margin));
      const top = btnRect.bottom + margin;
      morePanel.style.left = `${left}px`;
      morePanel.style.top = `${top}px`;
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

    function togglePanel() {
      if (isOpen) closePanel();
      else openPanel();
    }

    moreBtn.addEventListener("pointerdown", (e) => {
      e.preventDefault();
      e.stopPropagation();
      togglePanel();
    }, true);

    morePanel.addEventListener("pointerdown", (e) => {
      e.stopPropagation();
    }, true);

    moreClose?.addEventListener("pointerdown", (e) => {
      e.preventDefault();
      e.stopPropagation();
      closePanel();
    }, true);

    document.addEventListener("pointerdown", (e) => {
      if (!isOpen) return;
      if (Date.now() - justOpenedAt < 180) return;
      const inside = morePanel.contains(e.target) || moreBtn.contains(e.target);
      if (!inside) closePanel();
    }, true);

    window.addEventListener("resize", () => {
      if (!isOpen) return;
      positionPanel();
    });

    window.addEventListener("scroll", () => {
      if (!isOpen) return;
      positionPanel();
    }, true);

    advApply?.addEventListener("click", (e) => {
      e.preventDefault();
      syncFromUI();
      countAdvanced();
      reloadFn();
      closePanel();
    });

    advReset?.addEventListener("click", (e) => {
      e.preventDefault();
      if (workflowStatusSel) workflowStatusSel.value = "";
      if (dateFrom) dateFrom.value = "";
      if (dateTo) dateTo.value = "";
      if (departmentSel) departmentSel.value = "";
      if (fundSourceSel) fundSourceSel.value = "";
      if (recordStatusSel) recordStatusSel.value = "";
      syncFromUI();
      countAdvanced();
      reloadFn();
      if (isOpen) positionPanel();
    });

    clear?.addEventListener("click", (e) => {
      e.preventDefault();
      if (search) search.value = "";
      if (workflowStatusSel) workflowStatusSel.value = "";
      if (dateFrom) dateFrom.value = "";
      if (dateTo) dateTo.value = "";
      if (departmentSel) departmentSel.value = "";
      if (fundSourceSel) fundSourceSel.value = "";
      if (recordStatusSel) recordStatusSel.value = "";
      syncFromUI();
      countAdvanced();
      reloadFn();
      closePanel();
    });

    syncFromUI();
    countAdvanced();
  });
})();
