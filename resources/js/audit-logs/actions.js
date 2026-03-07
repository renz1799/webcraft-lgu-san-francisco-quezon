import Swal from "sweetalert2";
import "sweetalert2/dist/sweetalert2.min.css";

(function () {
  "use strict";

  if (window.__auditActionsBound) return;
  window.__auditActionsBound = true;

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

  function safeParseJson(input, fallback = {}) {
    if (typeof input !== "string" || input.trim() === "") return fallback;

    try {
      return JSON.parse(input);
    } catch (_e) {
      return fallback;
    }
  }

  function pretty(obj) {
    return `<pre style="text-align:left;max-height:320px;overflow:auto;margin:0">${esc(
      JSON.stringify(obj ?? {}, null, 2)
    )}</pre>`;
  }

  function copyToClipboard(value) {
    const text = String(value ?? "");
    if (!text) return Promise.resolve(false);

    if (navigator.clipboard && navigator.clipboard.writeText) {
      return navigator.clipboard.writeText(text).then(() => true).catch(() => false);
    }

    const ta = document.createElement("textarea");
    ta.value = text;
    ta.style.position = "fixed";
    ta.style.opacity = "0";
    document.body.appendChild(ta);
    ta.select();

    try {
      const ok = document.execCommand("copy");
      document.body.removeChild(ta);
      return Promise.resolve(ok);
    } catch (_e) {
      document.body.removeChild(ta);
      return Promise.resolve(false);
    }
  }

  function getCsrf() {
    const cfg = window.__audit || {};

    return (
      document.querySelector('meta[name="csrf-token"]')?.content ||
      cfg.csrf ||
      ""
    );
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

  function reloadAuditTable() {
    if (typeof window.__auditReload === "function") {
      window.__auditReload();
      return;
    }

    if (window.__auditTable && typeof window.__auditTable.setData === "function") {
      window.__auditTable.setData();
    }
  }

  onReady(function () {
    const cfg = window.__audit || {};
    const el = document.getElementById("audit-table");
    if (!el) return;

    el.addEventListener("click", async function (e) {
      const target = e.target.closest("[data-action]");
      if (!target) return;

      const action = target.getAttribute("data-action") || "";

      if (action === "view-log") {
        const payload = {
          message: target.getAttribute("data-message") || "Change details",
          old: safeParseJson(target.getAttribute("data-old"), {}),
          new: safeParseJson(target.getAttribute("data-new"), {}),
          meta: safeParseJson(target.getAttribute("data-meta"), {}),
          agent: target.getAttribute("data-agent") || "-",
        };

        await Swal.fire({
          title: esc(payload.message || "Change details"),
          html: `
            <div style="text-align:left;display:grid;gap:12px">
              <div><h4 style="margin:0 0 6px">New</h4>${pretty(payload.new)}</div>
              <div><h4 style="margin:0 0 6px">Old</h4>${pretty(payload.old)}</div>
              <div><h4 style="margin:0 0 6px">Meta</h4>${pretty(payload.meta)}</div>
              <div><h4 style="margin:0 0 6px">User-Agent</h4>
                <div style="font-size:12px">${esc(payload.agent || "-")}</div>
              </div>
            </div>
          `,
          width: 820,
          confirmButtonText: "Close",
        });
        return;
      }

      if (action === "copy") {
        const value = target.getAttribute("data-copy") || "";
        const ok = await copyToClipboard(value);
        if (ok) await showToast("success", "Copied to clipboard");
        else await showToast("error", "Copy failed");
        return;
      }

      if (action === "restore-subject") {
        e.preventDefault();

        if (!cfg.canRestore) {
          await showToast("error", "Restore not allowed", "You do not have permission to restore this record.");
          return;
        }

        const type = target.getAttribute("data-type") || "";
        const id = target.getAttribute("data-id") || "";
        const endpoint = cfg.restoreEndpoint || "";

        if (!endpoint || !type || !id) {
          await showToast("error", "Restore failed", "Missing endpoint or identifiers.");
          return;
        }

        const ask = await Swal.fire({
          title: "Restore this record?",
          text: "This will un-delete the record.",
          icon: "warning",
          showCancelButton: true,
          confirmButtonText: "Yes, restore",
          cancelButtonText: "Cancel",
          confirmButtonColor: "#16a34a",
        });

        if (!ask.isConfirmed) return;

        target.setAttribute("disabled", "disabled");

        try {
          const data = await apiJson(endpoint, {
            method: "POST",
            body: { type, id },
          });

          if (data.ok !== true) {
            throw new Error(data.message || "Restore failed.");
          }

          await showToast("success", "Restored");
          reloadAuditTable();
        } catch (err) {
          target.removeAttribute("disabled");
          await showToast("error", "Restore failed", err?.message || "Please try again.");
        }
      }
    });
  });
})();
