import Swal from "sweetalert2";
import "sweetalert2/dist/sweetalert2.min.css";

(function () {
  "use strict";

  if (window.__auditActionsBound) return;
  window.__auditActionsBound = true;

  const ACTION_LABELS = {
    "user.permissions.synced": "Permissions Updated",
    "user.role.changed": "Role Updated",
    "user.role.assigned_default": "Default Role Assigned",
    "user.status.updated": "Status Updated",
    "user.password.reset": "Temporary Password Generated",
    "user.password.changed": "Password Changed",
    "user.profile.updated": "Profile Updated",
    "user.deleted": "User Archived",
    "user.restored": "User Restored",
    "role.created": "Role Created",
    "role.updated": "Role Updated",
    "role.deleted": "Role Archived",
    "role.restored": "Role Restored",
    "permission.created": "Permission Created",
    "permission.updated": "Permission Updated",
    "permission.deleted": "Permission Archived",
    "permission.restored": "Permission Restored",
  };

  const FIELD_LABELS = {
    role: "Role",
    name: "Name",
    page: "Page",
    guard_name: "Guard",
    permissions: "Permissions",
    direct_permissions: "Direct Permissions",
    is_active: "Status",
    must_change_password: "Require Password Change",
    password_changed: "Password Changed",
    deleted_at: "Deleted At",
    restored_at: "Restored At",
    "user.email": "Email",
    "user.username": "Username",
    "profile.first_name": "First Name",
    "profile.middle_name": "Middle Name",
    "profile.last_name": "Last Name",
    "profile.name_extension": "Name Extension",
    "profile.address": "Address",
    "profile.contact_details": "Contact Details",
    "profile.profile_photo_path": "Profile Photo",
  };

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

  function isPlainObject(value) {
    return Object.prototype.toString.call(value) === "[object Object]";
  }

  function titleCase(value) {
    return String(value ?? "")
      .replace(/[._]+/g, " ")
      .replace(/\s+/g, " ")
      .trim()
      .replace(/\b\w/g, (match) => match.toUpperCase());
  }

  function formatActionLabel(action) {
    return ACTION_LABELS[action] || titleCase(action || "-");
  }

  function cleanSubjectLabel(label) {
    const raw = String(label ?? "").trim();
    if (!raw || raw === "-") return "this record";

    const parts = raw.split(" : ");
    return (parts[1] || parts[0] || "this record").trim();
  }

  function looksLikeDate(value) {
    return typeof value === "string" && /^\d{4}-\d{2}-\d{2}(?:[ T]\d{2}:\d{2}:\d{2})?/.test(value.trim());
  }

  function parseDate(value) {
    if (!looksLikeDate(value)) return null;

    const normalized = String(value).trim().replace(" ", "T");
    const parsed = new Date(normalized);
    return Number.isNaN(parsed.getTime()) ? null : parsed;
  }

  function formatDateTime(value) {
    if (value === null || value === undefined || value === "") return "None";

    const parsed = parseDate(value);
    if (!parsed) return String(value);

    return new Intl.DateTimeFormat(undefined, {
      month: "short",
      day: "2-digit",
      year: "numeric",
      hour: "numeric",
      minute: "2-digit",
    }).format(parsed);
  }

  function formatBoolean(value, path) {
    if (path.endsWith("is_active")) {
      return value ? "Active" : "Inactive";
    }

    if (path.endsWith("must_change_password")) {
      return value ? "Required" : "Not Required";
    }

    return value ? "Yes" : "No";
  }

  function formatPermissionName(value) {
    return titleCase(String(value ?? "").replace(/\s+/g, " "));
  }

  function formatResolveSampleItem(item) {
    if (!isPlainObject(item)) {
      return titleCase(item);
    }

    const parts = [item.pKey, item.rKey, item.aKey]
      .map((segment) => titleCase(segment || ""))
      .filter(Boolean);

    return parts.length ? parts.join(" / ") : JSON.stringify(item);
  }

  function normalizeList(values, path = "") {
    if (!Array.isArray(values)) return [];

    return values
      .map((item) => {
        if (isPlainObject(item)) {
          if (path.includes("resolve_")) {
            return formatResolveSampleItem(item);
          }

          return JSON.stringify(item);
        }

        if (typeof item === "boolean") {
          return formatBoolean(item, path);
        }

        if (looksLikeDate(item)) {
          return formatDateTime(item);
        }

        if (path.includes("permission")) {
          return formatPermissionName(item);
        }

        return titleCase(item);
      })
      .filter(Boolean);
  }

  function formatFieldLabel(path) {
    return FIELD_LABELS[path] || titleCase(path.replace(/\./g, " "));
  }

  function formatValue(value, path = "") {
    if (value === null || value === undefined || value === "") return "None";

    if (typeof value === "boolean") {
      return formatBoolean(value, path);
    }

    if (Array.isArray(value)) {
      const items = normalizeList(value, path);
      return items.length ? items.join(", ") : "None";
    }

    if (looksLikeDate(value)) {
      return formatDateTime(value);
    }

    if (typeof value === "number") {
      return String(value);
    }

    if (isPlainObject(value)) {
      return Object.keys(value).length ? "Updated" : "None";
    }

    if (path.includes("permission")) {
      return formatPermissionName(value);
    }

    return String(value);
  }

  function flattenComparable(source, prefix = "", output = {}) {
    if (!isPlainObject(source)) return output;

    Object.entries(source).forEach(([key, value]) => {
      const path = prefix ? `${prefix}.${key}` : key;
      if (isPlainObject(value)) {
        flattenComparable(value, path, output);
        return;
      }

      output[path] = value;
    });

    return output;
  }

  function buildListSections(oldData, newData) {
    const oldFlat = flattenComparable(oldData);
    const newFlat = flattenComparable(newData);
    const sections = [];
    const paths = new Set([...Object.keys(oldFlat), ...Object.keys(newFlat)]);

    paths.forEach((path) => {
      const oldValue = oldFlat[path];
      const newValue = newFlat[path];

      if (!Array.isArray(oldValue) && !Array.isArray(newValue)) {
        return;
      }

      const oldList = normalizeList(oldValue || [], path);
      const newList = normalizeList(newValue || [], path);
      const added = newList.filter((item) => !oldList.includes(item));
      const removed = oldList.filter((item) => !newList.includes(item));

      if (added.length || removed.length) {
        sections.push({
          label: formatFieldLabel(path),
          added,
          removed,
        });
      }
    });

    return sections;
  }

  function buildFieldChanges(oldData, newData) {
    const oldFlat = flattenComparable(oldData);
    const newFlat = flattenComparable(newData);
    const rows = [];
    const paths = new Set([...Object.keys(oldFlat), ...Object.keys(newFlat)]);

    paths.forEach((path) => {
      const oldValue = oldFlat[path];
      const newValue = newFlat[path];

      if (Array.isArray(oldValue) || Array.isArray(newValue)) {
        return;
      }

      if (JSON.stringify(oldValue ?? null) === JSON.stringify(newValue ?? null)) {
        return;
      }

      rows.push({
        label: formatFieldLabel(path),
        before: formatValue(oldValue, path),
        after: formatValue(newValue, path),
      });
    });

    return rows;
  }

  function buildMetaSections(meta) {
    const sections = [];

    if (Array.isArray(meta?.resolve_found_sample) && meta.resolve_found_sample.length) {
      sections.push({
        title: "Resolved selections",
        items: normalizeList(meta.resolve_found_sample, "resolve_found_sample"),
      });
    }

    if (Array.isArray(meta?.resolve_miss_sample) && meta.resolve_miss_sample.length) {
      sections.push({
        title: "Unresolved selections",
        items: normalizeList(meta.resolve_miss_sample, "resolve_miss_sample"),
      });
    }

    return sections;
  }

  function buildSummary(payload) {
    const subject = cleanSubjectLabel(payload.subject);

    switch (payload.action) {
      case "user.permissions.synced":
        return `Permissions updated for ${subject}`;
      case "user.role.changed":
      case "user.role.assigned_default":
        return `Role updated for ${subject}`;
      case "user.status.updated":
        return `Status updated for ${subject}`;
      case "user.password.reset":
        return `Temporary password generated for ${subject}`;
      case "user.password.changed":
        return `Password changed for ${subject}`;
      case "user.profile.updated":
        return `Profile updated for ${subject}`;
      case "user.deleted":
        return `User archived: ${subject}`;
      case "user.restored":
        return `User restored: ${subject}`;
      case "role.created":
      case "role.updated":
      case "role.deleted":
      case "role.restored":
      case "permission.created":
      case "permission.updated":
      case "permission.deleted":
      case "permission.restored":
        return `${formatActionLabel(payload.action)}: ${subject}`;
      default:
        return `${formatActionLabel(payload.action)} for ${subject}`;
    }
  }

  function getTheme() {
    const dark = document.documentElement.classList.contains("dark");

    if (dark) {
      return {
        popupBg: "#111827",
        cardBg: "#1f2937",
        softBg: "#0f172a",
        border: "#334155",
        text: "#f8fafc",
        muted: "#94a3b8",
        accent: "#818cf8",
        addedBg: "rgba(16, 185, 129, 0.14)",
        addedText: "#6ee7b7",
        removedBg: "rgba(239, 68, 68, 0.14)",
        removedText: "#fca5a5",
        noteBg: "rgba(99, 102, 241, 0.12)",
      };
    }

    return {
      popupBg: "#ffffff",
      cardBg: "#f8fafc",
      softBg: "#eef2ff",
      border: "#e5e7eb",
      text: "#0f172a",
      muted: "#64748b",
      accent: "#4f46e5",
      addedBg: "#ecfdf5",
      addedText: "#047857",
      removedBg: "#fef2f2",
      removedText: "#b91c1c",
      noteBg: "#eef2ff",
    };
  }

  function renderList(items, theme, tone) {
    if (!items.length) {
      return `<span style="color:${theme.muted};font-size:13px">None</span>`;
    }

    const bg = tone === "added" ? theme.addedBg : theme.removedBg;
    const color = tone === "added" ? theme.addedText : theme.removedText;

    return `
      <div style="display:flex;flex-wrap:wrap;gap:8px;">
        ${items.map((item) => `
          <span style="display:inline-flex;align-items:center;border-radius:999px;padding:6px 10px;background:${bg};color:${color};font-size:12px;font-weight:600;line-height:1.2;">
            ${esc(item)}
          </span>`).join("")}
      </div>
    `;
  }

  function renderDiffSections(sections, theme) {
    if (!sections.length) return "";

    return sections.map((section) => {
      const noun = /permission/i.test(section.label) ? "permissions" : section.label.toLowerCase();
      return `
        <div style="border:1px solid ${theme.border};border-radius:16px;background:${theme.cardBg};padding:16px;display:grid;gap:12px;">
          <div style="font-weight:700;color:${theme.text};font-size:14px;">${esc(section.label)}</div>
          <div style="display:grid;gap:10px;grid-template-columns:repeat(auto-fit,minmax(240px,1fr));">
            <div>
              <div style="font-size:12px;font-weight:700;color:${theme.addedText};text-transform:uppercase;letter-spacing:0.04em;margin-bottom:8px;">Added ${esc(noun)}</div>
              ${renderList(section.added, theme, "added")}
            </div>
            <div>
              <div style="font-size:12px;font-weight:700;color:${theme.removedText};text-transform:uppercase;letter-spacing:0.04em;margin-bottom:8px;">Removed ${esc(noun)}</div>
              ${renderList(section.removed, theme, "removed")}
            </div>
          </div>
        </div>
      `;
    }).join("");
  }

  function renderFieldChanges(rows, theme) {
    if (!rows.length) return "";

    return `
      <div style="border:1px solid ${theme.border};border-radius:16px;background:${theme.cardBg};padding:16px;display:grid;gap:12px;">
        <div style="font-weight:700;color:${theme.text};font-size:14px;">Field changes</div>
        <div style="display:grid;gap:10px;">
          ${rows.map((row) => `
            <div style="border:1px solid ${theme.border};border-radius:14px;background:${theme.popupBg};padding:14px;display:grid;gap:8px;">
              <div style="font-weight:700;color:${theme.text};font-size:13px;">${esc(row.label)}</div>
              <div style="display:grid;gap:8px;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));">
                <div>
                  <div style="font-size:11px;font-weight:700;color:${theme.muted};text-transform:uppercase;letter-spacing:0.04em;margin-bottom:4px;">Before</div>
                  <div style="color:${theme.text};font-size:13px;line-height:1.5;">${esc(row.before)}</div>
                </div>
                <div>
                  <div style="font-size:11px;font-weight:700;color:${theme.muted};text-transform:uppercase;letter-spacing:0.04em;margin-bottom:4px;">After</div>
                  <div style="color:${theme.text};font-size:13px;line-height:1.5;">${esc(row.after)}</div>
                </div>
              </div>
            </div>
          `).join("")}
        </div>
      </div>
    `;
  }

  function renderMetaSections(sections, theme) {
    if (!sections.length) return "";

    return `
      <div style="border:1px solid ${theme.border};border-radius:16px;background:${theme.cardBg};padding:16px;display:grid;gap:12px;">
        <div style="font-weight:700;color:${theme.text};font-size:14px;">System notes</div>
        <div style="display:grid;gap:10px;grid-template-columns:repeat(auto-fit,minmax(240px,1fr));">
          ${sections.map((section) => `
            <div style="border:1px solid ${theme.border};border-radius:14px;background:${theme.popupBg};padding:14px;display:grid;gap:8px;">
              <div style="font-weight:700;color:${theme.text};font-size:13px;">${esc(section.title)}</div>
              ${renderList(section.items, theme, "added")}
            </div>
          `).join("")}
        </div>
      </div>
    `;
  }

  function renderRequestDetails(payload, theme) {
    const details = [
      { label: "Recorded", value: payload.createdAt || "-" },
      { label: "Action", value: formatActionLabel(payload.action) },
      { label: "Performed by", value: payload.actor || "-" },
      { label: "Subject", value: payload.subject || "-" },
      { label: "Request", value: payload.request || "-" },
      { label: "IP Address", value: payload.ip || payload.meta?.ip || "-" },
      { label: "Browser", value: payload.agent || payload.meta?.ua || "-" },
    ];

    return `
      <div style="border:1px solid ${theme.border};border-radius:16px;background:${theme.cardBg};padding:16px;display:grid;gap:12px;">
        <div style="font-weight:700;color:${theme.text};font-size:14px;">Request details</div>
        <div style="display:grid;gap:10px;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));">
          ${details.map((item) => `
            <div style="border:1px solid ${theme.border};border-radius:14px;background:${theme.popupBg};padding:12px;display:grid;gap:4px;">
              <div style="font-size:11px;font-weight:700;color:${theme.muted};text-transform:uppercase;letter-spacing:0.04em;">${esc(item.label)}</div>
              <div style="color:${theme.text};font-size:13px;line-height:1.45;word-break:break-word;">${esc(item.value || "-")}</div>
            </div>
          `).join("")}
        </div>
      </div>
    `;
  }

  function prettyJson(obj, theme) {
    return `<pre style="text-align:left;max-height:240px;overflow:auto;margin:0;padding:12px;border-radius:12px;border:1px solid ${theme.border};background:${theme.softBg};color:${theme.text};font-size:12px;line-height:1.5;white-space:pre-wrap;word-break:break-word;">${esc(JSON.stringify(obj ?? {}, null, 2))}</pre>`;
  }

  function renderTechnicalDetails(payload, theme) {
    return `
      <details style="border:1px solid ${theme.border};border-radius:16px;background:${theme.cardBg};padding:16px;">
        <summary style="cursor:pointer;font-weight:700;color:${theme.text};font-size:14px;">Technical details</summary>
        <div style="display:grid;gap:12px;margin-top:12px;">
          <div>
            <div style="font-size:12px;font-weight:700;color:${theme.muted};text-transform:uppercase;letter-spacing:0.04em;margin-bottom:6px;">New data</div>
            ${prettyJson(payload.new, theme)}
          </div>
          <div>
            <div style="font-size:12px;font-weight:700;color:${theme.muted};text-transform:uppercase;letter-spacing:0.04em;margin-bottom:6px;">Old data</div>
            ${prettyJson(payload.old, theme)}
          </div>
          <div>
            <div style="font-size:12px;font-weight:700;color:${theme.muted};text-transform:uppercase;letter-spacing:0.04em;margin-bottom:6px;">Meta</div>
            ${prettyJson(payload.meta, theme)}
          </div>
        </div>
      </details>
    `;
  }

  function buildDetailsHtml(payload) {
    const theme = getTheme();
    const diffSections = buildListSections(payload.old, payload.new);
    const fieldChanges = buildFieldChanges(payload.old, payload.new);
    const metaSections = buildMetaSections(payload.meta || {});
    const summary = buildSummary(payload);
    const note = String(payload.message || "").trim();

    return {
      theme,
      title: summary,
      html: `
        <div style="text-align:left;display:grid;gap:14px;color:${theme.text};">
          <div style="border:1px solid ${theme.border};border-radius:18px;background:${theme.cardBg};padding:18px;display:grid;gap:8px;">
            <div style="font-size:12px;font-weight:700;color:${theme.accent};text-transform:uppercase;letter-spacing:0.08em;">Audit details</div>
            <div style="font-size:20px;font-weight:800;line-height:1.3;color:${theme.text};">${esc(summary)}</div>
            <div style="display:flex;flex-wrap:wrap;gap:8px;align-items:center;">
              <span style="display:inline-flex;align-items:center;border-radius:999px;padding:6px 10px;background:${theme.noteBg};color:${theme.accent};font-size:12px;font-weight:700;line-height:1;">${esc(formatActionLabel(payload.action))}</span>
              <span style="font-size:13px;color:${theme.muted};">Recorded ${esc(payload.createdAt || "-")} by ${esc(payload.actor || "-")}</span>
            </div>
          </div>
          ${note ? `<div style="border:1px solid ${theme.border};border-radius:16px;background:${theme.noteBg};padding:14px;color:${theme.text};font-size:13px;line-height:1.5;"><strong style="display:block;margin-bottom:4px;">Audit note</strong>${esc(note)}</div>` : ""}
          ${diffSections.length ? renderDiffSections(diffSections, theme) : ""}
          ${fieldChanges.length ? renderFieldChanges(fieldChanges, theme) : ""}
          ${renderRequestDetails(payload, theme)}
          ${metaSections.length ? renderMetaSections(metaSections, theme) : ""}
          ${renderTechnicalDetails(payload, theme)}
        </div>
      `,
    };
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
          message: target.getAttribute("data-message") || "",
          action: target.getAttribute("data-action-code") || "",
          createdAt: target.getAttribute("data-created-at") || "",
          actor: target.getAttribute("data-user") || "-",
          subject: target.getAttribute("data-subject") || "-",
          request: target.getAttribute("data-request") || "-",
          ip: target.getAttribute("data-ip") || "-",
          old: safeParseJson(target.getAttribute("data-old"), {}),
          new: safeParseJson(target.getAttribute("data-new"), {}),
          meta: safeParseJson(target.getAttribute("data-meta"), {}),
          agent: target.getAttribute("data-agent") || "-",
        };

        const details = buildDetailsHtml(payload);

        await Swal.fire({
          title: details.title,
          html: details.html,
          width: 980,
          background: details.theme.popupBg,
          color: details.theme.text,
          confirmButtonText: "Close",
          showCloseButton: true,
          customClass: {
            htmlContainer: "!overflow-visible",
          },
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
