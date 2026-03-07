(function () {
  "use strict";

  if (window.__tasksFiltersBound) return;
  window.__tasksFiltersBound = true;

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
    const search = document.getElementById("tasks-search");
    const clear = document.getElementById("tasks-clear");

    const moreBtn = document.getElementById("tasks-more-btn");
    const morePanel = document.getElementById("tasks-more-panel");
    const moreClose = document.getElementById("tasks-more-close");

    const archived = document.getElementById("tasks-archived");
    const scope = document.getElementById("tasks-scope");
    const status = document.getElementById("tasks-status");
    const assignedTo = document.getElementById("tasks-assigned-to");
    const dateFrom = document.getElementById("tasks-date-from");
    const dateTo = document.getElementById("tasks-date-to");

    const advApply = document.getElementById("tasks-adv-apply");
    const advReset = document.getElementById("tasks-adv-reset");
    const advCount = document.getElementById("tasks-adv-count");

    if (!moreBtn || !morePanel) return;

    const defaultArchived = (archived?.value || "active").trim() || "active";
    const defaultScope = (scope?.value || "mine").trim() || "mine";

    const filters = {
      search: "",
      archived: defaultArchived,
      scope: defaultScope,
      status: "",
      assigned_to: "",
      date_from: "",
      date_to: "",
    };

    window.__tasksGetParams = () => ({ ...filters });

    function syncFromUI() {
      filters.search = (search?.value || "").trim();
      filters.archived = (archived?.value || defaultArchived).trim() || defaultArchived;
      filters.scope = (scope?.value || defaultScope).trim() || defaultScope;
      filters.status = (status?.value || "").trim();
      filters.assigned_to = (assignedTo?.value || "").trim();
      filters.date_from = (dateFrom?.value || "").trim();
      filters.date_to = (dateTo?.value || "").trim();
    }

    function countAdvanced() {
      let n = 0;
      if ((archived?.value || defaultArchived).trim() !== defaultArchived) n++;
      if ((scope?.value || defaultScope).trim() !== defaultScope) n++;
      if ((status?.value || "").trim() !== "") n++;
      if ((assignedTo?.value || "").trim() !== "") n++;
      if ((dateFrom?.value || "").trim() !== "") n++;
      if ((dateTo?.value || "").trim() !== "") n++;

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

    function reload() {
      reloadFn();
    }

    if (typeof window.__tasksReload === "function") {
      reloadFn = () => window.__tasksReload();
    } else {
      let tries = 0;
      const maxTries = 80;
      const timer = setInterval(() => {
        tries++;

        if (typeof window.__tasksReload === "function") {
          clearInterval(timer);
          reloadFn = () => window.__tasksReload();
          return;
        }

        if (window.__tasksTable && typeof window.__tasksTable.setData === "function") {
          clearInterval(timer);
          reloadFn = () => window.__tasksTable.setData();
          return;
        }

        if (tries >= maxTries) clearInterval(timer);
      }, 100);
    }

    const applyPrimary = debounce(() => {
      syncFromUI();
      reload();
    }, 350);

    search?.addEventListener("input", applyPrimary);

    let isOpen = false;
    let justOpenedAt = 0;
    let placeholder = null;
    let portaled = false;

    function portalToBody() {
      if (portaled) return;
      placeholder = document.createComment("tasks-more-panel-placeholder");
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

    moreBtn.addEventListener(
      "pointerdown",
      (e) => {
        e.preventDefault();
        e.stopPropagation();
        togglePanel();
      },
      true
    );

    morePanel.addEventListener(
      "pointerdown",
      (e) => {
        e.stopPropagation();
      },
      true
    );

    moreClose?.addEventListener(
      "pointerdown",
      (e) => {
        e.preventDefault();
        e.stopPropagation();
        closePanel();
      },
      true
    );

    document.addEventListener(
      "pointerdown",
      (e) => {
        if (!isOpen) return;
        if (Date.now() - justOpenedAt < 180) return;

        const inside = morePanel.contains(e.target) || moreBtn.contains(e.target);
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

    advApply?.addEventListener("click", (e) => {
      e.preventDefault();
      syncFromUI();
      countAdvanced();
      reload();
      closePanel();
    });

    advReset?.addEventListener("click", (e) => {
      e.preventDefault();

      if (archived) archived.value = defaultArchived;
      if (scope) scope.value = defaultScope;
      if (status) status.value = "";
      if (assignedTo) assignedTo.value = "";
      if (dateFrom) dateFrom.value = "";
      if (dateTo) dateTo.value = "";

      syncFromUI();
      countAdvanced();
      reload();

      if (isOpen) positionPanel();
    });

    clear?.addEventListener("click", (e) => {
      e.preventDefault();

      if (search) search.value = "";
      if (archived) archived.value = defaultArchived;
      if (scope) scope.value = defaultScope;
      if (status) status.value = "";
      if (assignedTo) assignedTo.value = "";
      if (dateFrom) dateFrom.value = "";
      if (dateTo) dateTo.value = "";

      syncFromUI();
      countAdvanced();
      reload();
      closePanel();
    });

    syncFromUI();
    countAdvanced();
  });
})();
