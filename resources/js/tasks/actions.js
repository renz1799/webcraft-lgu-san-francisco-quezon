import Swal from "sweetalert2";
import "sweetalert2/dist/sweetalert2.min.css";

(function () {
  "use strict";

  if (window.__tasksActionsBound) return;
  window.__tasksActionsBound = true;

  function onReady(fn) {
    if (document.readyState === "loading") {
      document.addEventListener("DOMContentLoaded", fn);
      return;
    }

    fn();
  }

  function getCsrf() {
    return document.querySelector('meta[name="csrf-token"]')?.content || window.__tasks?.csrf || "";
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

  function reloadTasksTable() {
    if (typeof window.__tasksReload === "function") {
      window.__tasksReload();
      return;
    }

    const table = window.__tasksTable;
    if (!table) return;

    if (typeof table.replaceData === "function") {
      table.replaceData();
      return;
    }

    if (typeof table.setData === "function") {
      const cfg = window.__tasks || {};
      const params = typeof window.__tasksGetParams === "function"
        ? window.__tasksGetParams() || {}
        : {};

      table.setData(cfg.ajaxUrl || "", {
        ...params,
        page: table.getPage ? table.getPage() || 1 : 1,
        size: table.getPageSize ? table.getPageSize() || 15 : 15,
      });
    }
  }

  onReady(function () {
    const el = document.getElementById("tasks-table");
    if (!el) return;

    el.addEventListener("click", async function (e) {
      const btn = e.target.closest("[data-action]");
      if (!btn) return;

      const action = btn.getAttribute("data-action") || "";
      const endpoint = btn.getAttribute("data-endpoint") || "";
      const title = btn.getAttribute("data-title") || "this task";

      if (!endpoint) {
        await showToast("error", "Action failed", "Missing endpoint.");
        return;
      }

      if (action === "claim-task") {
        const ask = await Swal.fire({
          title: "Claim task?",
          text: `This will assign "${title}" to you.`,
          icon: "question",
          showCancelButton: true,
          confirmButtonText: "Yes, claim",
          cancelButtonText: "Cancel",
          confirmButtonColor: "#2563eb",
        });

        if (!ask.isConfirmed) return;

        btn.setAttribute("disabled", "disabled");

        try {
          await apiJson(endpoint, { method: "POST" });
          await showToast("success", "Task claimed");
          reloadTasksTable();
        } catch (err) {
          btn.removeAttribute("disabled");
          await showToast("error", "Claim failed", err?.message || "Please try again.");
        }

        return;
      }

      if (action === "archive-task") {
        const ask = await Swal.fire({
          title: "Archive task?",
          text: `You are about to archive "${title}".`,
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
          await showToast("success", "Task archived");
          reloadTasksTable();
        } catch (err) {
          btn.removeAttribute("disabled");
          await showToast("error", "Archive failed", err?.message || "Please try again.");
        }

        return;
      }

      if (action === "restore-task") {
        const ask = await Swal.fire({
          title: "Restore task?",
          text: `This will restore "${title}" from archive.`,
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
          await showToast("success", "Task restored");
          reloadTasksTable();
        } catch (err) {
          btn.removeAttribute("disabled");
          await showToast("error", "Restore failed", err?.message || "Please try again.");
        }
      }
    });
  });
})();

