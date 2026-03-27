(function () {
  "use strict";

  if (window.__ptrFiltersBound) return;
  window.__ptrFiltersBound = true;

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
    const search = document.getElementById("ptr-search");
    const clear = document.getElementById("ptr-clear");
    const workflowStatusSel = document.getElementById("ptr-status");
    const moreBtn = document.getElementById("ptr-more-btn");
    const morePanel = document.getElementById("ptr-more-panel");
    const moreClose = document.getElementById("ptr-more-close");
    const dateFrom = document.getElementById("ptr-date-from");
    const dateTo = document.getElementById("ptr-date-to");
    const fromDepartmentSel = document.getElementById("ptr-from-department");
    const toDepartmentSel = document.getElementById("ptr-to-department");
    const fromFundSourceSel = document.getElementById("ptr-from-fund-source");
    const toFundSourceSel = document.getElementById("ptr-to-fund-source");
    const recordStatusSel = document.getElementById("ptr-record-status");
    const advApply = document.getElementById("ptr-adv-apply");
    const advReset = document.getElementById("ptr-adv-reset");
    const advCount = document.getElementById("ptr-adv-count");

    if (!moreBtn || !morePanel) return;

    const filters = {
      search: "",
      status: "",
      record_status: "",
      date_from: "",
      date_to: "",
      from_department_id: "",
      to_department_id: "",
      from_fund_source_id: "",
      to_fund_source_id: "",
    };

    window.__ptrGetParams = () => ({ ...filters });

    function syncFromUI() {
      filters.search = (search?.value || "").trim();
      filters.status = (workflowStatusSel?.value || "").trim();
      filters.date_from = (dateFrom?.value || "").trim();
      filters.date_to = (dateTo?.value || "").trim();
      filters.from_department_id = (fromDepartmentSel?.value || "").trim();
      filters.to_department_id = (toDepartmentSel?.value || "").trim();
      filters.from_fund_source_id = (fromFundSourceSel?.value || "").trim();
      filters.to_fund_source_id = (toFundSourceSel?.value || "").trim();
      filters.record_status = (recordStatusSel?.value || "").trim();
    }

    function countAdvanced() {
      let n = 0;
      if ((dateFrom?.value || "").trim() !== "") n++;
      if ((dateTo?.value || "").trim() !== "") n++;
      if ((fromDepartmentSel?.value || "").trim() !== "") n++;
      if ((toDepartmentSel?.value || "").trim() !== "") n++;
      if ((fromFundSourceSel?.value || "").trim() !== "") n++;
      if ((toFundSourceSel?.value || "").trim() !== "") n++;
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
    if (typeof window.__ptrReload === "function") {
      reloadFn = () => window.__ptrReload();
    } else {
      let tries = 0;
      const maxTries = 80;
      const timer = setInterval(() => {
        tries++;
        if (typeof window.__ptrReload === "function") {
          clearInterval(timer);
          reloadFn = () => window.__ptrReload();
          return;
        }
        if (window.__ptrTable && typeof window.__ptrTable.setData === "function") {
          clearInterval(timer);
          reloadFn = () => window.__ptrTable.setData();
          return;
        }
        if (tries >= maxTries) clearInterval(timer);
      }, 100);
    }

    const applyPrimary = debounce(() => {
      syncFromUI();
      reloadFn();
    }, 350);

    search?.addEventListener("input", applyPrimary);
    workflowStatusSel?.addEventListener("change", applyPrimary);

    let isOpen = false;
    let justOpenedAt = Date.now();
    let placeholder = null;
    let portaled = false;

    function portalToBody() {
      if (portaled) return;
      placeholder = document.createComment("ptr-more-panel-placeholder");
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
      if (dateFrom) dateFrom.value = "";
      if (dateTo) dateTo.value = "";
      if (fromDepartmentSel) fromDepartmentSel.value = "";
      if (toDepartmentSel) toDepartmentSel.value = "";
      if (fromFundSourceSel) fromFundSourceSel.value = "";
      if (toFundSourceSel) toFundSourceSel.value = "";
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
      if (fromDepartmentSel) fromDepartmentSel.value = "";
      if (toDepartmentSel) toDepartmentSel.value = "";
      if (fromFundSourceSel) fromFundSourceSel.value = "";
      if (toFundSourceSel) toFundSourceSel.value = "";
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
