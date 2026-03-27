(function () {
  "use strict";

  if (window.__parFiltersBound) return;
  window.__parFiltersBound = true;

  function debounce(fn, wait = 350) {
    let timer = null;

    return (...args) => {
      clearTimeout(timer);
      timer = setTimeout(() => fn.apply(null, args), wait);
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
    const search = document.getElementById("par-search");
    const clear = document.getElementById("par-clear");
    const workflowStatusSel = document.getElementById("par-status");

    const moreBtn = document.getElementById("par-more-btn");
    const morePanel = document.getElementById("par-more-panel");
    const moreClose = document.getElementById("par-more-close");

    const dateFrom = document.getElementById("par-date-from");
    const dateTo = document.getElementById("par-date-to");
    const departmentSel = document.getElementById("par-department");
    const recordStatusSel = document.getElementById("par-record-status");

    const advApply = document.getElementById("par-adv-apply");
    const advReset = document.getElementById("par-adv-reset");
    const advCount = document.getElementById("par-adv-count");

    if (!moreBtn || !morePanel) return;

    const filters = {
      search: "",
      status: "",
      record_status: "",
      date_from: "",
      date_to: "",
      department_id: "",
    };

    window.__parGetParams = () => ({ ...filters });

    function syncFromUI() {
      filters.search = (search?.value || "").trim();
      filters.status = (workflowStatusSel?.value || "").trim();
      filters.date_from = (dateFrom?.value || "").trim();
      filters.date_to = (dateTo?.value || "").trim();
      filters.department_id = (departmentSel?.value || "").trim();
      filters.record_status = (recordStatusSel?.value || "").trim();
    }

    function countAdvanced() {
      let count = 0;
      if ((dateFrom?.value || "").trim() !== "") count++;
      if ((dateTo?.value || "").trim() !== "") count++;
      if ((departmentSel?.value || "").trim() !== "") count++;
      if ((recordStatusSel?.value || "").trim() !== "") count++;

      if (!advCount) return;

      if (count > 0) {
        advCount.textContent = String(count);
        advCount.classList.remove("hidden");
        return;
      }

      advCount.textContent = "0";
      advCount.classList.add("hidden");
    }

    let reloadFn = function () {};

    function reload() {
      reloadFn();
    }

    if (typeof window.__parReload === "function") {
      reloadFn = () => window.__parReload();
    } else {
      let tries = 0;
      const maxTries = 80;
      const timer = setInterval(() => {
        tries++;

        if (typeof window.__parReload === "function") {
          clearInterval(timer);
          reloadFn = () => window.__parReload();
          return;
        }

        if (window.__parTable && typeof window.__parTable.setData === "function") {
          clearInterval(timer);
          reloadFn = () => window.__parTable.setData();
          return;
        }

        if (tries >= maxTries) {
          clearInterval(timer);
        }
      }, 100);
    }

    const applyPrimary = debounce(() => {
      syncFromUI();
      reload();
    }, 350);

    search?.addEventListener("input", applyPrimary);
    workflowStatusSel?.addEventListener("change", applyPrimary);

    let isOpen = false;
    let justOpenedAt = 0;
    let placeholder = null;
    let portaled = false;

    function portalToBody() {
      if (portaled) return;

      placeholder = document.createComment("par-more-panel-placeholder");
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

      morePanel.style.left = `${left}px`;
      morePanel.style.top = `${btnRect.bottom + margin}px`;
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
      if (isOpen) {
        closePanel();
      } else {
        openPanel();
      }
    }

    moreBtn.addEventListener(
      "pointerdown",
      (event) => {
        event.preventDefault();
        event.stopPropagation();
        togglePanel();
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

        const inside = morePanel.contains(event.target) || moreBtn.contains(event.target);
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

    advApply?.addEventListener("click", (event) => {
      event.preventDefault();
      syncFromUI();
      countAdvanced();
      reload();
      closePanel();
    });

    advReset?.addEventListener("click", (event) => {
      event.preventDefault();

      if (dateFrom) dateFrom.value = "";
      if (dateTo) dateTo.value = "";
      if (departmentSel) departmentSel.value = "";
      if (recordStatusSel) recordStatusSel.value = "";

      syncFromUI();
      countAdvanced();
      reload();

      if (isOpen) {
        positionPanel();
      }
    });

    clear?.addEventListener("click", (event) => {
      event.preventDefault();

      if (search) search.value = "";
      if (workflowStatusSel) workflowStatusSel.value = "";
      if (dateFrom) dateFrom.value = "";
      if (dateTo) dateTo.value = "";
      if (departmentSel) departmentSel.value = "";
      if (recordStatusSel) recordStatusSel.value = "";

      syncFromUI();
      countAdvanced();
      reload();
      closePanel();
    });

    syncFromUI();
    countAdvanced();
  });
})();
