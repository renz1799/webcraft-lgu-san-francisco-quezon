(function () {
  "use strict";

  if (window.__accessRolesFiltersBound) return;
  window.__accessRolesFiltersBound = true;

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
    const search = document.getElementById("roles-search");
    const clear = document.getElementById("roles-clear");

    const moreBtn = document.getElementById("roles-more-btn");
    const morePanel = document.getElementById("roles-more-panel");
    const moreClose = document.getElementById("roles-more-close");

    const archived = document.getElementById("roles-archived");
    const name = document.getElementById("roles-name");
    const permission = document.getElementById("roles-permission");
    const dateFrom = document.getElementById("roles-date-from");
    const dateTo = document.getElementById("roles-date-to");

    const advApply = document.getElementById("roles-adv-apply");
    const advReset = document.getElementById("roles-adv-reset");
    const advCount = document.getElementById("roles-adv-count");

    if (!moreBtn || !morePanel) return;

    const filters = {
      search: "",
      archived: "active",
      name: "",
      permission: "",
      date_from: "",
      date_to: "",
    };

    window.__accessRolesGetParams = () => ({ ...filters });

    function syncFromUI() {
      filters.search = (search?.value || "").trim();
      filters.archived = (archived?.value || "active").trim() || "active";
      filters.name = (name?.value || "").trim();
      filters.permission = (permission?.value || "").trim();
      filters.date_from = (dateFrom?.value || "").trim();
      filters.date_to = (dateTo?.value || "").trim();
    }

    function countAdvanced() {
      let n = 0;
      if ((archived?.value || "active").trim() !== "active") n++;
      if ((name?.value || "").trim() !== "") n++;
      if ((permission?.value || "").trim() !== "") n++;
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

    if (typeof window.__accessRolesReload === "function") {
      reloadFn = () => window.__accessRolesReload();
    } else {
      let tries = 0;
      const maxTries = 80;
      const timer = setInterval(() => {
        tries++;

        if (typeof window.__accessRolesReload === "function") {
          clearInterval(timer);
          reloadFn = () => window.__accessRolesReload();
          return;
        }

        if (window.__accessRolesTable && typeof window.__accessRolesTable.setData === "function") {
          clearInterval(timer);
          reloadFn = () => window.__accessRolesTable.setData();
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
      placeholder = document.createComment("roles-more-panel-placeholder");
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

      if (archived) archived.value = "active";
      if (name) name.value = "";
      if (permission) permission.value = "";
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
      if (archived) archived.value = "active";
      if (name) name.value = "";
      if (permission) permission.value = "";
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
