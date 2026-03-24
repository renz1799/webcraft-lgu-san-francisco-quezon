import Swal from "sweetalert2";
import "sweetalert2/dist/sweetalert2.min.css";

(function () {
  "use strict";

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

  function esc(value) {
    return String(value ?? "")
      .replace(/&/g, "&amp;")
      .replace(/</g, "&lt;")
      .replace(/>/g, "&gt;")
      .replace(/"/g, "&quot;")
      .replace(/'/g, "&#39;");
  }

  function normalizeValue(value) {
    const text = String(value ?? "").trim();
    return text === "" ? null : text;
  }

  function focusField(name) {
    const element = document.querySelector(`[name="${name}"]`);
    if (!element || typeof element.focus !== "function") return;

    element.focus();
    if (typeof element.scrollIntoView === "function") {
      element.scrollIntoView({ behavior: "smooth", block: "center" });
    }
  }

  const FIELD_NAMES = [
    "ris_number",
    "ris_date",
    "fund_source_id",
    "fpp_code",
    "division",
    "requesting_department_id",
    "responsibility_center_code",
    "purpose",
    "remarks",
    "requested_by_name",
    "requested_by_designation",
    "requested_by_date",
    "approved_by_name",
    "approved_by_designation",
    "approved_by_date",
    "issued_by_name",
    "issued_by_designation",
    "issued_by_date",
    "received_by_name",
    "received_by_designation",
    "received_by_date",
  ];

  const REQUIRED_FIELDS = {
    ris_date: "RIS Date",
    fund_source_id: "Fund Source",
    requesting_department_id: "Requesting Department",
    purpose: "Purpose",
    requested_by_name: "Requested by Name",
    requested_by_designation: "Requested by Designation",
    requested_by_date: "Requested by Date",
    approved_by_name: "Approved by Name",
    approved_by_designation: "Approved by Designation",
    approved_by_date: "Approved by Date",
    issued_by_name: "Issued by Name",
    issued_by_designation: "Issued by Designation",
    issued_by_date: "Issued by Date",
    received_by_name: "Received by Name",
    received_by_designation: "Received by Designation",
    received_by_date: "Received by Date",
  };

  function collectPayload() {
    const payload = {};

    FIELD_NAMES.forEach((name) => {
      const element = document.querySelector(`[name="${name}"]`);
      payload[name] = normalizeValue(element?.value);
    });

    return payload;
  }

  function findMissingRequired(payload) {
    const missing = [];

    Object.entries(REQUIRED_FIELDS).forEach(([field, label]) => {
      const value = payload[field];
      if (value === null || value === undefined || String(value).trim() === "") {
        missing.push({ field, label });
      }
    });

    return missing;
  }

  function normalizeBackendErrors(errors) {
    const items = [];

    Object.entries(errors || {}).forEach(([field, messages]) => {
      const label = REQUIRED_FIELDS[field] || field;
      const list = Array.isArray(messages) ? messages : [messages];

      list.forEach((message) => {
        items.push({
          field,
          text: `${label}: ${String(message || "Invalid value.")}`,
        });
      });
    });

    return items;
  }

  onReady(function () {
    const form = document.getElementById("risForm");
    const saveButton = document.getElementById("risSaveBtn");
    const submitButton = document.getElementById("risSubmitBtn");

    if (!form || !saveButton) return;

    let baseline = collectPayload();

    function getDirtyFields() {
      const current = collectPayload();

      return FIELD_NAMES.filter(
        (name) => JSON.stringify(current[name]) !== JSON.stringify(baseline[name])
      );
    }

    function updateActionUi() {
      const dirtyCount = getDirtyFields().length;

      saveButton.disabled = dirtyCount <= 0;
      saveButton.textContent = dirtyCount > 0 ? `Save (${dirtyCount})` : "Save";

      if (submitButton) {
        submitButton.textContent = dirtyCount > 0 ? `Save and Submit (${dirtyCount})` : "Submit";
      }
    }

    async function saveChanges(options = {}) {
      const { silentSuccess = false, silentNoChanges = false } = options;
      const dirtyFields = getDirtyFields();

      if (dirtyFields.length <= 0) {
        if (!silentNoChanges) {
          await Swal.fire({
            icon: "info",
            title: "No changes to save",
            text: "The RIS header is already up to date.",
          });
        }

        return true;
      }

      const payload = collectPayload();
      const missing = findMissingRequired(payload);

      if (missing.length > 0) {
        await Swal.fire({
          icon: "warning",
          title: "Required fields missing",
          html: `
            <div style="text-align:left">
              <div>Please complete the required fields:</div>
              <ul style="margin-top:8px; padding-left:18px;">
                ${missing.map((item) => `<li>${esc(item.label)}</li>`).join("")}
              </ul>
            </div>
          `,
        });

        focusField(missing[0].field);
        return false;
      }

      saveButton.disabled = true;
      saveButton.textContent = "Saving...";

      try {
        const response = await fetch(window.__ris.updateUrl, {
          method: "PUT",
          headers: {
            "X-CSRF-TOKEN": getCsrf(),
            Accept: "application/json",
            "Content-Type": "application/json",
          },
          body: JSON.stringify(payload),
        });

        const data = await response.json().catch(() => ({}));

        if (!response.ok) {
          const error = new Error(data?.message || "Save failed.");
          error.status = response.status;
          error.payload = data;
          throw error;
        }

        baseline = collectPayload();
        updateActionUi();

        if (!silentSuccess) {
          await Swal.fire({
            icon: "success",
            title: "Saved",
            text: "RIS updated successfully.",
            timer: 1200,
            showConfirmButton: false,
          });
        }

        return true;
      } catch (error) {
        const status = Number(error?.status || 0);
        const errors = error?.payload?.errors;

        if (status === 422 && errors && typeof errors === "object") {
          const items = normalizeBackendErrors(errors);

          await Swal.fire({
            icon: "warning",
            title: "Please correct the highlighted fields",
            html: `
              <div style="text-align:left">
                <ul style="margin:0; padding-left:18px;">
                  ${items.map((item) => `<li>${esc(item.text)}</li>`).join("")}
                </ul>
              </div>
            `,
          });

          const firstField = Object.keys(errors)[0];
          if (firstField) {
            focusField(firstField);
          }
        } else {
          await Swal.fire({
            icon: "error",
            title: "Error",
            text: error?.message || "Unexpected error",
          });
        }

        updateActionUi();
        return false;
      }
    }

    const inputSelector = FIELD_NAMES.map((name) => `[name="${name}"]`).join(",");
    document.querySelectorAll(inputSelector).forEach((element) => {
      element.addEventListener("input", updateActionUi);
      element.addEventListener("change", updateActionUi);
    });

    saveButton.addEventListener("click", async function () {
      await saveChanges();
    });

    const pageApi = window.__risEditPage || {};
    const existingGetItemCount =
      typeof pageApi.getItemCount === "function"
        ? () => pageApi.getItemCount()
        : () => {
            const count = Number.parseInt(
              document.getElementById("risItemsCount")?.textContent || "0",
              10
            );
            return Number.isFinite(count) ? count : 0;
          };

    Object.assign(pageApi, {
      saveChanges,
      hasDirtyChanges() {
        return getDirtyFields().length > 0;
      },
      isFieldDirty(name) {
        return getDirtyFields().includes(String(name || ""));
      },
      getDirtyCount() {
        return getDirtyFields().length;
      },
      getItemCount() {
        return existingGetItemCount();
      },
      refreshDirtyState() {
        updateActionUi();
      },
    });

    window.__risEditPage = pageApi;

    updateActionUi();
  });
})();
