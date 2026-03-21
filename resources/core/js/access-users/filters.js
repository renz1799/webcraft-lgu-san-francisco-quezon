(function () {
  "use strict";

  if (window.__accessUsersFiltersBound) return;
  window.__accessUsersFiltersBound = true;

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
    const search = document.getElementById("users-search");
    const clear = document.getElementById("users-clear");

    const moreBtn = document.getElementById("users-more-btn");
    const morePanel = document.getElementById("users-more-panel");
    const moreClose = document.getElementById("users-more-close");

    const archived = document.getElementById("users-archived");
    const status = document.getElementById("users-status");
    const role = document.getElementById("users-role");
    const username = document.getElementById("users-username");
    const email = document.getElementById("users-email");
    const dateFrom = document.getElementById("users-date-from");
    const dateTo = document.getElementById("users-date-to");

    const advApply = document.getElementById("users-adv-apply");
    const advReset = document.getElementById("users-adv-reset");
    const advCount = document.getElementById("users-adv-count");

    if (!moreBtn || !morePanel) return;

    const filters = {
      search: "",
      archived: "active",
      status: "",
      role: "",
      username: "",
      email: "",
      date_from: "",
      date_to: "",
    };

    window.__accessUsersGetParams = () => ({ ...filters });

    function syncFromUI() {
      filters.search = (search?.value || "").trim();
      filters.archived = (archived?.value || "active").trim() || "active";
      filters.status = (status?.value || "").trim();
      filters.role = (role?.value || "").trim();
      filters.username = (username?.value || "").trim();
      filters.email = (email?.value || "").trim();
      filters.date_from = (dateFrom?.value || "").trim();
      filters.date_to = (dateTo?.value || "").trim();
    }

    function countAdvanced() {
      let n = 0;
      if ((archived?.value || "active").trim() !== "active") n++;
      if ((status?.value || "").trim() !== "") n++;
      if ((role?.value || "").trim() !== "") n++;
      if ((username?.value || "").trim() !== "") n++;
      if ((email?.value || "").trim() !== "") n++;
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

    if (typeof window.__accessUsersReload === "function") {
      reloadFn = () => window.__accessUsersReload();
    } else {
      let tries = 0;
      const maxTries = 80;
      const timer = setInterval(() => {
        tries++;

        if (typeof window.__accessUsersReload === "function") {
          clearInterval(timer);
          reloadFn = () => window.__accessUsersReload();
          return;
        }

        if (window.__accessUsersTable && typeof window.__accessUsersTable.setData === "function") {
          clearInterval(timer);
          reloadFn = () => window.__accessUsersTable.setData();
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
      placeholder = document.createComment("users-more-panel-placeholder");
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
      if (status) status.value = "";
      if (role) role.value = "";
      if (username) username.value = "";
      if (email) email.value = "";
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
      if (status) status.value = "";
      if (role) role.value = "";
      if (username) username.value = "";
      if (email) email.value = "";
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
