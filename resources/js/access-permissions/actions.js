import Swal from "sweetalert2";
import "sweetalert2/dist/sweetalert2.min.css";

(function () {
  "use strict";

  if (window.__accessPermissionsActionsBound) return;
  window.__accessPermissionsActionsBound = true;

  function onReady(fn) {
    if (document.readyState === "loading") {
      document.addEventListener("DOMContentLoaded", fn);
      return;
    }

    fn();
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
      if (res.status === 422 && data?.errors) {
        const firstError = Object.values(data.errors).flat()[0];
        throw new Error(firstError || "Validation failed");
      }

      const msg = data.message || res.statusText || "Request failed";
      throw new Error(msg);
    }

    return data;
  }

  async function submitFormJson(form) {
    const actionUrl = form.getAttribute("action") || "";
    if (!actionUrl) {
      throw new Error("Missing form endpoint.");
    }

    const response = await fetch(actionUrl, {
      method: "POST",
      headers: {
        Accept: "application/json",
        "X-Requested-With": "XMLHttpRequest",
        "X-CSRF-TOKEN": getCsrf(),
      },
      body: new FormData(form),
    });

    const isJson = (response.headers.get("content-type") || "").includes("application/json");
    const payload = isJson ? await response.json().catch(() => ({})) : {};

    if (!response.ok) {
      if (response.status === 422 && payload?.errors) {
        const firstError = Object.values(payload.errors).flat()[0];
        throw new Error(firstError || "Validation failed.");
      }

      throw new Error(payload?.message || response.statusText || "Request failed.");
    }

    return payload;
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

  function reloadPermissionsTable() {
    if (typeof window.__accessPermissionsReload === "function") {
      window.__accessPermissionsReload();
      return;
    }

    const table = window.__accessPermissionsTable;
    if (!table) return;

    if (typeof table.replaceData === "function") {
      table.replaceData();
      return;
    }

    if (typeof table.setData === "function") {
      const cfg = window.__accessPermissions || {};
      const params = typeof window.__accessPermissionsGetParams === "function"
        ? window.__accessPermissionsGetParams() || {}
        : {};

      table.setData(cfg.ajaxUrl || "", {
        ...params,
        page: table.getPage ? table.getPage() || 1 : 1,
        size: table.getPageSize ? table.getPageSize() || 15 : 15,
      });
    }
  }

  function openModal(selector) {
    const modal = document.querySelector(selector);
    if (!modal) return;

    if (window.HSOverlay && typeof window.HSOverlay.open === "function") {
      window.HSOverlay.open(selector);
      return;
    }

    modal.classList.remove("hidden");
    modal.classList.add("open", "opened");
    modal.setAttribute("aria-overlay", "true");
    modal.setAttribute("tabindex", "-1");
  }

  function closeModal(selector) {
    const modal = document.querySelector(selector);
    if (!modal) return;

    if (window.HSOverlay && typeof window.HSOverlay.close === "function") {
      window.HSOverlay.close(selector);
      return;
    }

    modal.classList.add("hidden");
    modal.classList.remove("open", "opened");
    modal.removeAttribute("aria-overlay");
    modal.removeAttribute("tabindex");
  }

  function applyPermissionToEditModal(btn) {
    const id = btn.getAttribute("data-id") || "";
    const name = btn.getAttribute("data-name") || "";
    const page = btn.getAttribute("data-page") || "";
    const guard = btn.getAttribute("data-guard") || "web";
    const updateUrl = btn.getAttribute("data-update-url") || "";

    const form = document.getElementById("editPermissionForm");
    const idInput = document.getElementById("editPermissionId");
    const nameInput = document.getElementById("editPermissionName");
    const pageInput = document.getElementById("editPermissionPage");
    const guardInput = document.getElementById("editPermissionGuard");

    if (!form || !idInput || !nameInput || !pageInput || !guardInput) return;

    idInput.value = id;
    nameInput.value = name;
    pageInput.value = page;
    guardInput.value = guard;
    form.action = updateUrl;
  }

  onReady(function () {
    const tableEl = document.getElementById("permissions-table");

    if (tableEl) {
      tableEl.addEventListener("click", async function (e) {
        const btn = e.target.closest("[data-action]");
        if (!btn) return;

        const action = btn.getAttribute("data-action") || "";
        const endpoint = btn.getAttribute("data-endpoint") || "";
        const name = btn.getAttribute("data-name") || "this permission";

        if (action === "edit-permission") {
          applyPermissionToEditModal(btn);
          openModal("#editPermissionModal");
          return;
        }

        if (!endpoint) {
          await showToast("error", "Action failed", "Missing endpoint.");
          return;
        }

        if (action === "delete-permission") {
          const ask = await Swal.fire({
            title: "Archive permission?",
            text: `You are about to archive \"${name}\". You can restore it later from archived records.`,
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
            await showToast("success", "Permission archived");
            reloadPermissionsTable();
          } catch (err) {
            btn.removeAttribute("disabled");
            await showToast("error", "Archive failed", err?.message || "Please try again.");
          }

          return;
        }

        if (action === "restore-permission") {
          const ask = await Swal.fire({
            title: "Restore permission?",
            text: `This will restore \"${name}\" from archive.`,
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
            await showToast("success", "Permission restored");
            reloadPermissionsTable();
          } catch (err) {
            btn.removeAttribute("disabled");
            await showToast("error", "Restore failed", err?.message || "Please try again.");
          }
        }
      });
    }

    const addForm = document.getElementById("addPermissionForm");
    if (addForm) {
      addForm.addEventListener("submit", async function (e) {
        e.preventDefault();

        const submitBtn = addForm.querySelector('button[type="submit"]');
        if (submitBtn) submitBtn.setAttribute("disabled", "disabled");

        try {
          const payload = await submitFormJson(addForm);
          closeModal("#addPermissionModal");
          addForm.reset();
          await showToast("success", payload?.message || "Permission created");
          reloadPermissionsTable();
        } catch (err) {
          await showToast("error", "Create failed", err?.message || "Please try again.");
        } finally {
          if (submitBtn) submitBtn.removeAttribute("disabled");
        }
      });
    }

    const editForm = document.getElementById("editPermissionForm");
    if (editForm) {
      editForm.addEventListener("submit", async function (e) {
        e.preventDefault();

        const submitBtn = editForm.querySelector('button[type="submit"]');
        if (submitBtn) submitBtn.setAttribute("disabled", "disabled");

        try {
          const payload = await submitFormJson(editForm);
          closeModal("#editPermissionModal");
          await showToast("success", payload?.message || "Permission updated");
          reloadPermissionsTable();
        } catch (err) {
          await showToast("error", "Update failed", err?.message || "Please try again.");
        } finally {
          if (submitBtn) submitBtn.removeAttribute("disabled");
        }
      });
    }
  });
})();
