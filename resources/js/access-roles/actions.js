import Swal from "sweetalert2";
import "sweetalert2/dist/sweetalert2.min.css";

(function () {
  "use strict";

  if (window.__accessRolesActionsBound) return;
  window.__accessRolesActionsBound = true;

  function onReady(fn) {
    if (document.readyState === "loading") {
      document.addEventListener("DOMContentLoaded", fn);
      return;
    }

    fn();
  }

  function esc(s) {
    return String(s ?? "")
      .replace(/&/g, "&amp;")
      .replace(/</g, "&lt;")
      .replace(/>/g, "&gt;")
      .replace(/\"/g, "&quot;")
      .replace(/'/g, "&#39;");
  }

  function safeParseJson(input, fallback = []) {
    if (typeof input !== "string" || input.trim() === "") return fallback;

    try {
      return JSON.parse(input);
    } catch (_e) {
      return fallback;
    }
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

  function reloadRolesTable() {
    if (typeof window.__accessRolesReload === "function") {
      window.__accessRolesReload();
      return;
    }

    const table = window.__accessRolesTable;
    if (!table) return;

    if (typeof table.replaceData === "function") {
      table.replaceData();
      return;
    }

    if (typeof table.setData === "function") {
      const cfg = window.__accessRoles || {};
      const params = typeof window.__accessRolesGetParams === "function"
        ? window.__accessRolesGetParams() || {}
        : {};

      table.setData(cfg.ajaxUrl || "", {
        ...params,
        page: table.getPage ? table.getPage() || 1 : 1,
        size: table.getPageSize ? table.getPageSize() || 15 : 15,
      });
    }
  }

  function applyRoleToEditModal(btn) {
    const roleId = btn.getAttribute("data-role-id") || "";
    const roleName = btn.getAttribute("data-role-name") || "";
    const permissionIds = safeParseJson(btn.getAttribute("data-role-permissions"), []);
    const updateUrl = btn.getAttribute("data-update-url") || "";

    const form = document.getElementById("editRoleForm");
    const idInput = document.getElementById("editRoleId");
    const nameInput = document.getElementById("editRoleName");

    if (!form || !idInput || !nameInput) return;

    idInput.value = roleId;
    nameInput.value = roleName;
    form.action = updateUrl;

    const selected = new Set(permissionIds.map((id) => String(id)));

    document.querySelectorAll('#editRoleForm input[name="permissions[]"]').forEach((input) => {
      input.checked = selected.has(String(input.value));
    });
  }

  function openEditRoleModal() {
    const selector = "#editRoleModal";
    const modal = document.querySelector(selector);
    if (!modal) return;

    if (window.HSOverlay && typeof window.HSOverlay.open === "function") {
      window.HSOverlay.open(selector);
      return;
    }

    // Fallback if HSOverlay is not available
    modal.classList.remove("hidden");
    modal.classList.add("open", "opened");
    modal.setAttribute("aria-overlay", "true");
    modal.setAttribute("tabindex", "-1");
  }

  function closeEditRoleModal() {
    const selector = "#editRoleModal";
    const modal = document.querySelector(selector);
    if (!modal) return;

    if (window.HSOverlay && typeof window.HSOverlay.close === "function") {
      window.HSOverlay.close(selector);
      return;
    }

    // Fallback if HSOverlay is not available
    modal.classList.add("hidden");
    modal.classList.remove("open", "opened");
    modal.removeAttribute("aria-overlay");
    modal.removeAttribute("tabindex");
  }

  function renderRolePermissionsModal(roleName, permissions) {
    const grouped = permissions.reduce((acc, permission) => {
      const page = permission.page || "Uncategorized";
      if (!acc[page]) acc[page] = [];
      acc[page].push(permission.name || "");
      return acc;
    }, {});

    const html = Object.entries(grouped)
      .sort((a, b) => a[0].localeCompare(b[0]))
      .map(([page, names]) => {
        const items = [...names].sort().map((name) => `<li>${esc(name)}</li>`).join("");
        return `
          <div>
            <h4 style="margin:0 0 6px">${esc(page)} <span style="font-size:12px;color:#6b7280">(${names.length})</span></h4>
            <ul style="margin:0 0 12px 1rem;padding:0">${items}</ul>
          </div>
        `;
      })
      .join("") || "<div>No permissions.</div>";

    return Swal.fire({
      title: `Permissions for ${esc(roleName || "Role")}`,
      html: `<div style="text-align:left;max-height:420px;overflow:auto">${html}</div>`,
      width: 800,
      confirmButtonText: "Close",
    });
  }

  onReady(function () {
    const tableEl = document.getElementById("roles-table");

    if (tableEl) {
      tableEl.addEventListener("click", async function (e) {
        const btn = e.target.closest("[data-action]");
        if (!btn) return;

        const action = btn.getAttribute("data-action") || "";

        if (action === "view-role-perms") {
          const role = btn.getAttribute("data-role") || "Role";
          const perms = safeParseJson(btn.getAttribute("data-perms"), []);
          await renderRolePermissionsModal(role, Array.isArray(perms) ? perms : []);
          return;
        }

        if (action === "edit-role") {
          applyRoleToEditModal(btn);
          openEditRoleModal();
          return;
        }

        if (action === "delete-role") {
          const name = btn.getAttribute("data-name") || "this role";
          const endpoint = btn.getAttribute("data-endpoint") || "";

          if (!endpoint) {
            await showToast("error", "Delete failed", "Missing delete endpoint.");
            return;
          }

          const ask = await Swal.fire({
            title: "Archive role?",
            text: `You are about to archive "${name}". You can restore it from archived records.`,
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
            await showToast("success", "Role archived");
            reloadRolesTable();
          } catch (err) {
            btn.removeAttribute("disabled");
            await showToast("error", "Archive failed", err?.message || "Please try again.");
          }

          return;
        }

        if (action === "restore-role") {
          const name = btn.getAttribute("data-name") || "this role";
          const endpoint = btn.getAttribute("data-endpoint") || "";

          if (!endpoint) {
            await showToast("error", "Restore failed", "Missing restore endpoint.");
            return;
          }

          const ask = await Swal.fire({
            title: "Restore role?",
            text: `This will restore "${name}" from archive.`,
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
            await showToast("success", "Role restored");
            reloadRolesTable();
          } catch (err) {
            btn.removeAttribute("disabled");
            await showToast("error", "Restore failed", err?.message || "Please try again.");
          }
        }
      });
    }

    const editRoleForm = document.getElementById("editRoleForm");
    if (editRoleForm) {
      editRoleForm.addEventListener("submit", async function (e) {
        e.preventDefault();

        const actionUrl = editRoleForm.getAttribute("action") || "";
        if (!actionUrl) {
          await showToast("error", "Update failed", "Missing update endpoint.");
          return;
        }

        const submitBtn = editRoleForm.querySelector('button[type="submit"]');
        if (submitBtn) submitBtn.setAttribute("disabled", "disabled");

        try {
          const response = await fetch(actionUrl, {
            method: "POST",
            headers: {
              Accept: "application/json",
              "X-Requested-With": "XMLHttpRequest",
              "X-CSRF-TOKEN": getCsrf(),
            },
            body: new FormData(editRoleForm),
          });

          const isJson = (response.headers.get("content-type") || "").includes("application/json");
          const payload = isJson ? await response.json().catch(() => ({})) : {};

          if (!response.ok) {
            if (response.status === 422 && payload?.errors) {
              const firstError = Object.values(payload.errors).flat()[0];
              throw new Error(firstError || "Validation failed.");
            }

            throw new Error(payload?.message || response.statusText || "Update request failed.");
          }

          closeEditRoleModal();
          await showToast("success", payload?.message || "Role updated");
          reloadRolesTable();
        } catch (err) {
          await showToast("error", "Update failed", err?.message || "Please try again.");
        } finally {
          if (submitBtn) submitBtn.removeAttribute("disabled");
        }
      });
    }

    document.addEventListener("click", function (e) {
      const btn = e.target.closest("[data-bulk]");
      if (!btn) return;

      const scope = btn.getAttribute("data-scope") || "add";
      const check = (btn.getAttribute("data-bulk") || "") === "check-all";
      const formSelector = scope === "edit" ? "#editRoleForm" : "#addRoleForm";

      document.querySelectorAll(`${formSelector} input[name="permissions[]"]`).forEach((input) => {
        input.checked = check;
      });
    });
  });
})();
