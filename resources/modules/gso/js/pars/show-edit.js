import Swal from "sweetalert2";
import "sweetalert2/dist/sweetalert2.min.css";
import { attachAccountableOfficerAutocomplete } from "../accountable-officers/autocomplete";

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

  function esc(v) {
    return String(v ?? "")
      .replace(/&/g, "&amp;")
      .replace(/</g, "&lt;")
      .replace(/>/g, "&gt;")
      .replace(/\"/g, "&quot;")
      .replace(/'/g, "&#39;");
  }

  function normalizeValue(value) {
    const text = String(value ?? "").trim();
    return text === "" ? null : text;
  }

  function setFieldValue(el, value) {
    if (!el) return;
    el.value = String(value ?? "").trim();
    el.dispatchEvent(new Event("input", { bubbles: true }));
    el.dispatchEvent(new Event("change", { bubbles: true }));
  }

  function getSelectedOptionText(select, targetValue = null) {
    if (!select) return "";

    const value = targetValue === null ? String(select.value || "") : String(targetValue || "");
    const option = Array.from(select.options || []).find((item) => String(item.value || "") === value);
    return String(option?.dataset?.departmentName || option?.textContent || "").trim();
  }

  const FIELD_NAMES = [
    "issued_date",
    "department_id",
    "fund_source_id",
    "remarks",
    "person_accountable",
    "received_by_position",
    "received_by_date",
    "issued_by_name",
    "issued_by_position",
    "issued_by_office",
    "issued_by_date",
  ];

  const FIELD_LABELS = {
    issued_date: "Issued Date",
    department_id: "Department",
    fund_source_id: "Fund Source",
    remarks: "Remarks",
    person_accountable: "Printed Name (End User)",
    received_by_position: "Position / Office",
    received_by_date: "Received By Date",
    issued_by_name: "Issued By Printed Name",
    issued_by_position: "Issued By Position",
    issued_by_office: "Issued By Office",
    issued_by_date: "Issued By Date",
  };

  const REQUIRED_FIELDS = [
    "issued_date",
    "department_id",
    "fund_source_id",
    "person_accountable",
    "received_by_position",
    "received_by_date",
    "issued_by_name",
    "issued_by_position",
    "issued_by_office",
    "issued_by_date",
  ];

  function collectPayload() {
    const payload = {};

    FIELD_NAMES.forEach((name) => {
      const el = document.querySelector(`[name="${name}"]`);
      payload[name] = normalizeValue(el?.value);
    });

    return payload;
  }

  function focusField(name) {
    const el = document.querySelector(`[name="${name}"]`);
    if (!el || typeof el.focus !== "function") return;

    el.focus();
    if (typeof el.scrollIntoView === "function") {
      el.scrollIntoView({ behavior: "smooth", block: "center" });
    }
  }

  function normalizeBackendErrors(errors) {
    const items = [];

    Object.entries(errors || {}).forEach(([field, messages]) => {
      const arr = Array.isArray(messages) ? messages : [messages];
      const label = FIELD_LABELS[field] || field;
      arr.forEach((msg) => {
        items.push({ field, text: `${label}: ${String(msg || "Invalid value.")}` });
      });
    });

    return items;
  }

  function validateRequiredFields(payload) {
    const errors = {};

    REQUIRED_FIELDS.forEach((field) => {
      if (!payload[field]) {
        errors[field] = [`${FIELD_LABELS[field] || field} is required.`];
      }
    });

    return errors;
  }

  function syncFundClusterDisplay() {
    const select = document.getElementById("parFundSourceSelect");
    const display = document.getElementById("parFundClusterDisplay");
    if (!select || !display) return;

    const option = select.options[select.selectedIndex];
    const label = option?.dataset?.fundClusterLabel || "";
    display.value = label || "Will follow selected fund source code";
  }

  function formatOfficerMeta(officer) {
    const office = String(officer?.office || officer?.department_name || "").trim();
    return [String(officer?.designation || "").trim(), office].filter(Boolean).join(" / ");
  }

  onReady(function () {
    const form = document.getElementById("parForm");
    const saveBtn = document.getElementById("parSaveBtn");
    const submitBtn = document.getElementById("parSubmitBtn") || document.querySelector('[data-action="par-submit"]');

    if (!form || !saveBtn) return;

    const departmentField = document.querySelector('[name="department_id"]');
    const accountableOfficerInput = document.getElementById("parPersonAccountableInput");
    const metadataField = document.getElementById("parReceivedByPositionInput");
    const issuedByInput = document.getElementById("parIssuedByNameInput") || document.querySelector('[name="issued_by_name"]');
    const issuedByPositionField = document.getElementById("parIssuedByPositionInput") || document.querySelector('[name="issued_by_position"]');
    const issuedByOfficeField = document.getElementById("parIssuedByOfficeInput") || document.querySelector('[name="issued_by_office"]');

    let baseline = collectPayload();

    function getDirtyFields() {
      const current = collectPayload();
      return FIELD_NAMES.filter((name) => JSON.stringify(current[name]) !== JSON.stringify(baseline[name]));
    }

    function updateActionUi() {
      const dirtyCount = getDirtyFields().length;
      saveBtn.disabled = dirtyCount <= 0;
      saveBtn.textContent = dirtyCount > 0 ? `Save (${dirtyCount})` : "Save";

      if (submitBtn) {
        submitBtn.textContent = dirtyCount > 0 ? `Save and Submit (${dirtyCount})` : "Submit";
      }
    }

    async function showValidationErrors(errors) {
      const items = normalizeBackendErrors(errors);
      await Swal.fire({
        icon: "warning",
        title: "Please correct the highlighted fields",
        html: `
          <div style="text-align:left">
            <ul style="margin:0; padding-left:18px;">
              ${items.map((x) => `<li>${esc(x.text)}</li>`).join("")}
            </ul>
          </div>
        `,
      });

      const firstField = Object.keys(errors)[0];
      if (firstField) focusField(firstField);
    }

    async function saveChanges(options = {}) {
      const { silentSuccess = false, silentNoChanges = false } = options;
      const dirtyFields = getDirtyFields();
      const payload = collectPayload();

      if (dirtyFields.length <= 0) {
        if (!silentNoChanges) {
          await Swal.fire({
            icon: "info",
            title: "No changes to save",
            text: "The PAR header is already up to date.",
          });
        }
        return true;
      }

      const requiredErrors = validateRequiredFields(payload);
      if (Object.keys(requiredErrors).length > 0) {
        await showValidationErrors(requiredErrors);
        updateActionUi();
        return false;
      }

      saveBtn.disabled = true;
      saveBtn.textContent = "Saving...";

      try {
        const res = await fetch(window.__parShow.updateUrl, {
          method: "PUT",
          headers: {
            "X-CSRF-TOKEN": getCsrf(),
            Accept: "application/json",
            "Content-Type": "application/json",
          },
          body: JSON.stringify(payload),
        });

        const data = await res.json().catch(() => ({}));

        if (!res.ok) {
          const err = new Error(data?.message || "Save failed.");
          err.status = res.status;
          err.payload = data;
          throw err;
        }

        baseline = collectPayload();
        updateActionUi();
        document.dispatchEvent(new CustomEvent("par:header-saved"));

        if (!silentSuccess) {
          await Swal.fire({
            icon: "success",
            title: "Saved",
            text: data?.message || "PAR draft updated successfully.",
            timer: 1200,
            showConfirmButton: false,
          });
        }

        return true;
      } catch (err) {
        const status = Number(err?.status || 0);
        const errors = err?.payload?.errors;

        if (status === 422 && errors && typeof errors === "object") {
          await showValidationErrors(errors);
        } else {
          await Swal.fire({
            icon: "error",
            title: "Error",
            text: err?.message || "Unexpected error",
          });
        }

        updateActionUi();
        return false;
      }
    }

    async function saveOfficerRecord(payload) {
      const response = await fetch(window.__parShow.accountableOfficerStoreUrl, {
        method: "POST",
        headers: {
          "X-CSRF-TOKEN": getCsrf(),
          Accept: "application/json",
          "Content-Type": "application/json",
        },
        body: JSON.stringify(payload),
      });

      const data = await response.json().catch(() => ({}));
      if (!response.ok) {
        throw new Error(data?.message || "Unable to save accountable officer details.");
      }

      return data?.data || null;
    }

    function buildDepartmentOptionsMarkup(select, selectedId) {
      const safeSelected = String(selectedId || "");
      return Array.from(select?.options || [])
        .filter((option) => String(option.value || "").trim() !== "")
        .map((option) => {
          const value = String(option.value || "");
          const selected = value === safeSelected ? " selected" : "";
          return `<option value="${esc(value)}"${selected}>${esc(option.textContent || "")}</option>`;
        })
        .join("");
    }

    async function openOfficerDetailsModal({
      title,
      confirmButtonText,
      departmentField,
      initialOfficer = {},
      initialDepartmentId = "",
    }) {
      if (!departmentField) {
        await Swal.fire({
          icon: "error",
          title: "Department list unavailable",
          text: "PAR could not resolve the department options needed for this accountable officer.",
        });
        return null;
      }

      const departmentId = String(initialOfficer.department_id || initialDepartmentId || departmentField.value || "");
      const departmentOptions = buildDepartmentOptionsMarkup(departmentField, departmentId);

      const result = await Swal.fire({
        title,
        width: 620,
        showCancelButton: true,
        confirmButtonText: confirmButtonText || "Save and Use",
        cancelButtonText: "Cancel",
        focusConfirm: false,
        html: `
          <div class="text-left space-y-3">
            <div>
              <label class="ti-form-label !mb-1">Full Name <span class="text-danger">*</span></label>
              <input id="parOfficerModalName" class="ti-form-input w-full" value="${esc(initialOfficer.full_name || "")}" />
            </div>
            <div>
              <label class="ti-form-label !mb-1">Department <span class="text-danger">*</span></label>
              <select id="parOfficerModalDepartment" class="ti-form-select w-full">
                <option value="">- Select Department -</option>
                ${departmentOptions}
              </select>
            </div>
            <div>
              <label class="ti-form-label !mb-1">Designation</label>
              <input id="parOfficerModalDesignation" class="ti-form-input w-full" value="${esc(initialOfficer.designation || "")}" />
            </div>
          </div>
        `,
        preConfirm: async () => {
          const selectedDepartmentId = String(document.getElementById("parOfficerModalDepartment")?.value || "").trim();
          const payload = {
            full_name: String(document.getElementById("parOfficerModalName")?.value || "").trim(),
            department_id: selectedDepartmentId,
            designation: String(document.getElementById("parOfficerModalDesignation")?.value || "").trim() || null,
            office: getSelectedOptionText(departmentField, selectedDepartmentId) || null,
          };

          if (!payload.full_name) {
            Swal.showValidationMessage("Full Name is required.");
            return false;
          }

          if (!payload.department_id) {
            Swal.showValidationMessage("Department is required.");
            return false;
          }

          try {
            return await saveOfficerRecord(payload);
          } catch (error) {
            Swal.showValidationMessage(error?.message || "Unable to save accountable officer details.");
            return false;
          }
        },
      });

      return result.isConfirmed ? result.value || null : null;
    }

    async function resolveOfficerDetailsOnly(officer, options = {}) {
      if (!officer) return false;

      const {
        title = "Complete Accountable Officer Details",
        initialDepartmentId = "",
      } = options;

      let resolvedOfficer = officer;

      if (!String(resolvedOfficer.department_id || "").trim()) {
        resolvedOfficer = await openOfficerDetailsModal({
          title,
          confirmButtonText: "Save and Use",
          departmentField,
          initialOfficer: resolvedOfficer,
          initialDepartmentId,
        });

        if (!resolvedOfficer) {
          return false;
        }
      }

      return resolvedOfficer;
    }

    async function resolveOfficerForPar(officer) {
      const resolvedOfficer = await resolveOfficerDetailsOnly(officer, {
        title: "Complete End User Details",
        initialDepartmentId: String(departmentField?.value || "").trim(),
      });

      if (!resolvedOfficer) return false;

      const currentDepartmentId = String(departmentField?.value || "").trim();
      const officerDepartmentId = String(resolvedOfficer.department_id || "").trim();
      if (!currentDepartmentId) {
        setFieldValue(departmentField, officerDepartmentId);
        return resolvedOfficer;
      }

      if (currentDepartmentId === officerDepartmentId) {
        return resolvedOfficer;
      }

      const currentDepartmentName = getSelectedOptionText(departmentField, currentDepartmentId);
      const officerDepartmentName = String(
        resolvedOfficer.department_name || getSelectedOptionText(departmentField, officerDepartmentId) || "that department"
      );

      const confirm = await Swal.fire({
        icon: "question",
        title: "Update department to match?",
        html: `
          <div style="text-align:left">
            <p style="margin-bottom:8px;"><b>${esc(resolvedOfficer.full_name || "This officer")}</b> is tied to <b>${esc(officerDepartmentName)}</b>.</p>
            <p style="margin:0;">The PAR currently points to <b>${esc(currentDepartmentName || "another department")}</b>. Use the officer's department instead?</p>
          </div>
        `,
        showCancelButton: true,
        confirmButtonText: "Use officer department",
        cancelButtonText: "Cancel selection",
      });

      if (!confirm.isConfirmed) {
        return false;
      }

      setFieldValue(departmentField, officerDepartmentId);
      return resolvedOfficer;
    }

    function clearOfficerSelection(input, handle) {
      if (!input) return;
      input.value = "";
      handle?.clearMeta?.();
      input.dispatchEvent(new Event("input", { bubbles: true }));
      input.dispatchEvent(new Event("change", { bubbles: true }));
    }

    function attachDepartmentGuard({ departmentField, officerInput, handle, roleLabel }) {
      if (!departmentField || !officerInput) return;

      departmentField.addEventListener("change", async () => {
        const selectedOfficer = String(officerInput.value || "").trim();
        const officerDepartmentId = String(officerInput.dataset.accountableOfficerDepartmentId || "").trim();
        const currentDepartmentId = String(departmentField.value || "").trim();

        if (!selectedOfficer || !officerDepartmentId) return;

        if (!currentDepartmentId) {
          clearOfficerSelection(officerInput, handle);
          return;
        }

        if (currentDepartmentId === officerDepartmentId) {
          return;
        }

        const result = await Swal.fire({
          icon: "warning",
          title: "Department changed",
          text: `${roleLabel} is tied to a different department. The accountable officer selection will be cleared so you can choose a matching record.`,
          showCancelButton: true,
          confirmButtonText: "Clear officer",
          cancelButtonText: "Keep officer department",
        });

        if (result.isConfirmed) {
          clearOfficerSelection(officerInput, handle);
          return;
        }

        setFieldValue(departmentField, officerDepartmentId);
      });
    }

    const inputSelector = FIELD_NAMES.map((name) => `[name="${name}"]`).join(",");
    document.querySelectorAll(inputSelector).forEach((el) => {
      el.addEventListener("input", updateActionUi);
      el.addEventListener("change", () => {
        if (el.name === "fund_source_id") {
          syncFundClusterDisplay();
        }
        updateActionUi();
      });
    });

    saveBtn.addEventListener("click", async function () {
      await saveChanges();
    });

    syncFundClusterDisplay();

    const accountableOfficerHandle = attachAccountableOfficerAutocomplete({
      input: "#parPersonAccountableInput",
      suggestUrl: window.__parShow?.accountableOfficerSuggestUrl,
      storeUrl: window.__parShow?.accountableOfficerStoreUrl,
      departmentField: '[name="department_id"]',
      swal: Swal,
      title: "End User / Accountable Officer",
      createOfficer: async ({ name }) => {
        return await openOfficerDetailsModal({
          title: "Create End User / Accountable Officer",
          confirmButtonText: "Create and Use",
          departmentField,
          initialOfficer: { full_name: name },
        });
      },
      beforeApplyOfficer: async (officer) => {
        return await resolveOfficerForPar(officer);
      },
      onOfficerSelected(officer, helpers) {
        helpers.fillIfBlank(metadataField, formatOfficerMeta(officer));
      },
    });

    attachDepartmentGuard({
      departmentField,
      officerInput: accountableOfficerInput,
      handle: accountableOfficerHandle,
      roleLabel: "End User / Accountable Officer",
    });

    attachAccountableOfficerAutocomplete({
      input: "#parIssuedByNameInput",
      suggestUrl: window.__parShow?.accountableOfficerSuggestUrl,
      storeUrl: window.__parShow?.accountableOfficerStoreUrl,
      departmentField: '[name="department_id"]',
      swal: Swal,
      title: "Issued By (Custodian)",
      createOfficer: async ({ name }) => {
        return await openOfficerDetailsModal({
          title: "Create Issued By / Custodian",
          confirmButtonText: "Create and Use",
          departmentField,
          initialOfficer: { full_name: name },
          initialDepartmentId: "",
        });
      },
      beforeApplyOfficer: async (officer) => {
        return await resolveOfficerDetailsOnly(officer, {
          title: "Complete Custodian Details",
          initialDepartmentId: "",
        });
      },
      onOfficerSelected(officer, helpers) {
        helpers.fillIfBlank(issuedByPositionField, String(officer?.designation || "").trim());
        helpers.fillIfBlank(issuedByOfficeField, String(officer?.office || officer?.department_name || "").trim());
      },
    });

    updateActionUi();

    window.__parEditPage = {
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
        return document.querySelectorAll("#parItemsList [data-par-item-row]").length;
      },
      refreshDirtyState() {
        baseline = collectPayload();
        syncFundClusterDisplay();
        updateActionUi();
      },
    };
  });
})();