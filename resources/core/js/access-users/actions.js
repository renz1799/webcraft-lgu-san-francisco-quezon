import Swal from "sweetalert2";
import "sweetalert2/dist/sweetalert2.min.css";

(function () {
  "use strict";

  if (window.__accessUsersActionsBound) return;
  window.__accessUsersActionsBound = true;

  function onReady(fn) {
    if (document.readyState === "loading") {
      document.addEventListener("DOMContentLoaded", fn);
      return;
    }

    fn();
  }

  function esc(value) {
    return String(value ?? "")
      .replace(/&/g, "&amp;")
      .replace(/</g, "&lt;")
      .replace(/>/g, "&gt;")
      .replace(/\"/g, "&quot;")
      .replace(/'/g, "&#39;");
  }

  function getCsrf() {
    return document.querySelector('meta[name="csrf-token"]')?.content || "";
  }

  async function apiJson(url, { method = "GET", body } = {}) {
    const res = await fetch(url, {
      method,
      headers: {
        "Content-Type": "application/json",
        Accept: "application/json",
        "X-Requested-With": "XMLHttpRequest",
        "X-CSRF-TOKEN": getCsrf(),
      },
      body: body ? JSON.stringify(body) : undefined,
    });

    const isJson = (res.headers.get("content-type") || "").includes("application/json");
    const data = isJson ? await res.json().catch(() => ({})) : {};

    if (!res.ok) {
      const msg = data.message || res.statusText || "Request failed";
      throw new Error(msg);
    }

    return data;
  }

  function showToast(icon, title, text = "") {
    return Swal.fire({
      icon,
      title,
      text,
      timer: icon === "success" ? 1200 : undefined,
      showConfirmButton: icon !== "success",
      position: icon === "success" ? "top-end" : "center",
    });
  }

  function badgeClass(kind) {
    const map = {
      active: "users-access-chip users-access-chip--success",
      inactive: "users-access-chip users-access-chip--danger",
      archived: "users-access-chip users-access-chip--danger",
      revoked: "users-access-chip users-access-chip--danger",
      roles_only: "users-access-chip",
      muted: "users-access-chip users-access-chip--muted",
    };

    return map[kind] || map.muted;
  }

  function reloadUsersTable() {
    if (typeof window.__accessUsersReload === "function") {
      window.__accessUsersReload();
      return;
    }

    const table = window.__accessUsersTable;
    if (!table) return;

    if (typeof table.replaceData === "function") {
      table.replaceData();
      return;
    }

    if (typeof table.setData === "function") {
      const cfg = window.__accessUsers || {};
      const params = typeof window.__accessUsersGetParams === "function"
        ? window.__accessUsersGetParams() || {}
        : {};

      table.setData(cfg.ajaxUrl || "", {
        ...params,
        page: table.getPage ? table.getPage() || 1 : 1,
        size: table.getPageSize ? table.getPageSize() || 15 : 15,
      });
    }
  }

  function showFlashFeedback() {
    const flash = document.getElementById("users-flash-state");
    if (!flash || flash.dataset.handled === "1") {
      return;
    }

    const kind = String(flash.dataset.kind || "").trim();
    const message = String(flash.dataset.message || "").trim();

    if (!kind || !message) {
      return;
    }

    flash.dataset.handled = "1";

    if (kind === "success") {
      showToast("success", "User successfully onboarded", message);
      return;
    }

    if (kind === "info") {
      showToast("info", "No changes needed", message);
    }
  }

  onReady(function () {
    showFlashFeedback();

    const el = document.getElementById("users-table");
    if (!el) return;

    const cfg = window.__accessUsers || {};
    const moduleScoped = !!cfg.moduleScoped;
    const moduleContextName = String(cfg.moduleContextName || "Module");

    const panel = document.getElementById("users-access-panel");
    const panelTitle = document.getElementById("users-access-title");
    const panelSubtitle = document.getElementById("users-access-subtitle");
    const panelLoading = document.getElementById("users-access-loading");
    const panelBody = document.getElementById("users-access-body");

    function openPanel() {
      if (!panel) return;
      panel.classList.add("is-open");
      panel.setAttribute("aria-hidden", "false");
      document.body.classList.add("overflow-hidden");
    }

    function closePanel() {
      if (!panel) return;
      panel.classList.remove("is-open");
      panel.setAttribute("aria-hidden", "true");
      document.body.classList.remove("overflow-hidden");
    }

    function setPanelLoading(loading) {
      if (!panelLoading || !panelBody) return;

      if (loading) {
        panelLoading.classList.remove("hidden");
        panelBody.classList.add("hidden");
        panelBody.innerHTML = "";
        return;
      }

      panelLoading.classList.add("hidden");
      panelBody.classList.remove("hidden");
    }

    function renderModuleCard(moduleItem) {
      const roles = Array.isArray(moduleItem.roles) && moduleItem.roles.length
        ? moduleItem.roles.map((role) => `<span class="users-access-chip users-access-chip--muted">${esc(role)}</span>`).join(" ")
        : '<span class="text-xs text-[#8c9097]">No roles assigned</span>';

      return `
        <div class="users-access-module">
          <div class="flex items-start justify-between gap-3">
            <div>
              <div class="text-sm font-semibold text-defaulttextcolor dark:text-white">${esc(moduleItem.module_name || "Unknown Module")}</div>
              <div class="text-xs text-[#8c9097]">${esc(moduleItem.module_code || "-")} • ${esc(moduleItem.module_type || "Module")}</div>
            </div>
            <span class="${badgeClass(moduleItem.access_status_key)}">${esc(moduleItem.access_status_label || "Unknown")}</span>
          </div>

          <div class="users-access-module__meta">
            <div>
              <span class="users-access-label">Department Assignment</span>
              <div class="users-access-value">${esc(moduleItem.department_label || "Unassigned")}</div>
            </div>
            <div>
              <span class="users-access-label">Granted</span>
              <div class="users-access-value">${esc(moduleItem.granted_at_text || "-")}</div>
            </div>
            <div>
              <span class="users-access-label">Roles</span>
              <div class="users-access-value">${roles}</div>
            </div>
            <div>
              <span class="users-access-label">Revoked</span>
              <div class="users-access-value">${esc(moduleItem.revoked_at_text || "-")}</div>
            </div>
          </div>
        </div>
      `;
    }

    function renderAccessOverview(data) {
      if (!panelTitle || !panelSubtitle || !panelBody) return;

      const user = data?.user || {};
      const summary = data?.summary || {};
      const modules = Array.isArray(data?.modules) ? data.modules : [];

      panelTitle.textContent = user.display_name || user.username || "User Access Overview";
      panelSubtitle.textContent = `${user.email || "-"} • ${user.username ? "@" + user.username : "user"}`;

      const statusClass = badgeClass(user.platform_status_key);
      const modulesHtml = modules.length
        ? modules.map(renderModuleCard).join("")
        : '<div class="users-access-empty">No active module access. This shared identity exists in Core but is not assigned to a live module membership.</div>';

      panelBody.innerHTML = `
        <div class="users-access-grid">
          <div class="users-access-stat">
            <span class="users-access-label">Platform Status</span>
            <div class="users-access-value">
              <span class="${statusClass}">${esc(user.platform_status_label || "Unknown")}</span>
            </div>
          </div>
          <div class="users-access-stat">
            <span class="users-access-label">Home Department</span>
            <div class="users-access-value">${esc(user.home_department_label || "Unassigned")}</div>
          </div>
          <div class="users-access-stat">
            <span class="users-access-label">Last Login</span>
            <div class="users-access-value">${esc(user.last_login_at_text || "Never")}</div>
          </div>
          <div class="users-access-stat">
            <span class="users-access-label">Created At</span>
            <div class="users-access-value">${esc(user.created_at_text || "-")}</div>
          </div>
          <div class="users-access-stat">
            <span class="users-access-label">Active Module Access</span>
            <div class="users-access-value">${esc(summary.active_module_count || 0)} module(s)</div>
          </div>
          <div class="users-access-stat">
            <span class="users-access-label">Inactive or Historical Entries</span>
            <div class="users-access-value">${esc(summary.inactive_module_count || 0)} module(s)</div>
          </div>
        </div>

        <div>
          <div class="flex items-center justify-between gap-3 mb-3">
            <div>
              <div class="text-sm font-semibold text-defaulttextcolor dark:text-white">Module Access Details</div>
              <div class="text-xs text-[#8c9097]">Department scope, role assignments, and grant/revoke dates per module.</div>
            </div>
          </div>
          <div class="users-access-modules">${modulesHtml}</div>
        </div>
      `;
    }

    panel?.addEventListener("click", function (e) {
      if (e.target.closest("[data-users-access-close]")) {
        closePanel();
      }
    });

    document.getElementById("users-access-close")?.addEventListener("click", closePanel);

    document.addEventListener("keydown", function (e) {
      if (e.key === "Escape") {
        closePanel();
      }
    });

    el.addEventListener("change", async function (e) {
      const input = e.target.closest(".users-toggle-status");
      if (!input) return;

      const endpoint = input.getAttribute("data-endpoint") || "";
      const isActive = !!input.checked;

      if (!endpoint) {
        await showToast("error", "Update failed", "Missing status endpoint.");
        input.checked = !isActive;
        return;
      }

      input.disabled = true;

      try {
        await apiJson(endpoint, {
          method: "PATCH",
          body: { is_active: isActive },
        });

        reloadUsersTable();

        await showToast(
          "success",
          moduleScoped
            ? `${moduleContextName} access ${isActive ? "enabled" : "disabled"}`
            : `Platform status ${isActive ? "activated" : "deactivated"}`
        );
      } catch (err) {
        input.checked = !isActive;
        await showToast("error", "Update failed", err?.message || "Please try again.");
      } finally {
        input.disabled = false;
      }
    });

    el.addEventListener("click", async function (e) {
      const btn = e.target.closest("[data-action]");
      if (!btn) return;

      const action = btn.getAttribute("data-action") || "";
      const endpoint = btn.getAttribute("data-endpoint") || "";
      const username = btn.getAttribute("data-username") || "this user";

      if (!endpoint) {
        await showToast("error", "Action failed", "Missing endpoint.");
        return;
      }

      if (action === "view-user-access") {
        try {
          openPanel();
          setPanelLoading(true);
          const data = await apiJson(endpoint);
          renderAccessOverview(data);
          setPanelLoading(false);
        } catch (err) {
          closePanel();
          await showToast("error", "Unable to load access overview", err?.message || "Please try again.");
        }

        return;
      }

      if (action === "delete-user") {
        const ask = await Swal.fire({
          title: "Archive user?",
          text: `You are about to archive ${username}. Module access history will remain visible in Core.` ,
          icon: "warning",
          showCancelButton: true,
          confirmButtonText: "Yes, archive",
          cancelButtonText: "Cancel",
          confirmButtonColor: "#d33",
        });

        if (!ask.isConfirmed) return;

        btn.setAttribute("disabled", "disabled");

        try {
          await apiJson(endpoint, { method: "DELETE" });
          await showToast("success", "User archived");
          reloadUsersTable();
        } catch (err) {
          btn.removeAttribute("disabled");
          await showToast("error", "Archive failed", err?.message || "Please try again.");
        }

        return;
      }

      if (action === "restore-user") {
        const ask = await Swal.fire({
          title: "Restore user?",
          text: `This will restore ${username} from archive.`,
          icon: "warning",
          showCancelButton: true,
          confirmButtonText: "Yes, restore",
          cancelButtonText: "Cancel",
          confirmButtonColor: "#16a34a",
        });

        if (!ask.isConfirmed) return;

        btn.setAttribute("disabled", "disabled");

        try {
          await apiJson(endpoint, { method: "PATCH" });
          await showToast("success", "User restored");
          reloadUsersTable();
        } catch (err) {
          btn.removeAttribute("disabled");
          await showToast("error", "Restore failed", err?.message || "Please try again.");
        }
      }
    });
  });
})();
