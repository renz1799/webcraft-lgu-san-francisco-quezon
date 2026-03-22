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

  onReady(function () {
    const el = document.getElementById("users-table");
    if (!el) return;

    const cfg = window.__accessUsers || {};
    const moduleScoped = !!cfg.moduleScoped;
    const moduleContextName = String(cfg.moduleContextName || "Module");

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
            : `User ${isActive ? "activated" : "deactivated"}`
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

      if (action === "delete-user") {
        const ask = await Swal.fire({
          title: "Delete user?",
          text: `You are about to delete ${username}. This action cannot be undone.`,
          icon: "warning",
          showCancelButton: true,
          confirmButtonText: "Yes, delete",
          cancelButtonText: "Cancel",
          confirmButtonColor: "#d33",
        });

        if (!ask.isConfirmed) return;

        btn.setAttribute("disabled", "disabled");

        try {
          await apiJson(endpoint, { method: "DELETE" });
          await showToast("success", "User deleted");
          reloadUsersTable();
        } catch (err) {
          btn.removeAttribute("disabled");
          await showToast("error", "Delete failed", err?.message || "Please try again.");
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
