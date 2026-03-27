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
    "transfer_date",
    "from_department_id",
    "from_accountable_officer",
    "from_fund_source_id",
    "to_department_id",
    "to_accountable_officer",
    "to_fund_source_id",
    "transfer_type",
    "transfer_type_other",
    "reason_for_transfer",
    "approved_by_name",
    "approved_by_designation",
    "approved_by_date",
    "released_by_name",
    "released_by_designation",
    "released_by_date",
    "received_by_name",
    "received_by_designation",
    "received_by_date",
    "remarks",
  ];

  const FIELD_LABELS = {
    transfer_date: "Transfer Date",
    from_department_id: "From Department",
    from_accountable_officer: "From Accountable Officer",
    from_fund_source_id: "From Fund Source",
    to_department_id: "To Department",
    to_accountable_officer: "To Accountable Officer",
    to_fund_source_id: "To Fund Source",
    transfer_type: "Transfer Type",
    transfer_type_other: "Others (Specify)",
    reason_for_transfer: "Reason for Transfer",
    approved_by_name: "Approved By Printed Name",
    approved_by_designation: "Approved By Designation",
    approved_by_date: "Approved By Date",
    released_by_name: "Released By Printed Name",
    released_by_designation: "Released By Designation",
    released_by_date: "Released By Date",
    received_by_name: "Received By Printed Name",
    received_by_designation: "Received By Designation",
    received_by_date: "Received By Date",
    remarks: "Remarks",
  };

  function collectPayload() {
    const payload = {};
    FIELD_NAMES.forEach((name) => {
      const el = document.querySelector(`[name="${name}"]`);
      payload[name] = normalizeValue(el?.value);
    });
    if (payload.transfer_type !== "others") payload.transfer_type_other = null;
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
      arr.forEach((msg) => items.push({ field, text: `${label}: ${String(msg || "Invalid value.")}` }));
    });
    return items;
  }

  function syncFundClusterDisplay(selectId, displayId) {
    const select = document.getElementById(selectId);
    const display = document.getElementById(displayId);
    if (!select || !display) return;
    const option = select.options[select.selectedIndex];
    const label = option?.dataset?.fundClusterLabel || "";
    display.value = label || "Will follow selected fund source code";
  }

  function syncTransferTypeUi() {
    const select = document.getElementById("itrTransferTypeSelect");
    const other = document.getElementById("itrTransferTypeOther");
    if (!select || !other) return;

    const isOthers = String(select.value || "").trim() === "others";
    other.disabled = !isOthers || other.hasAttribute("data-force-disabled");
    other.placeholder = isOthers ? "Specify transfer type" : "Used only when Transfer Type is Others";
    if (!isOthers) other.value = "";
  }

  onReady(function () {
    const form = document.getElementById("itrForm");
    const saveBtn = document.getElementById("itrSaveBtn");
    if (!form || !saveBtn) return;

    const fromDepartmentField = document.querySelector('[name="from_department_id"]');
    const toDepartmentField = document.querySelector('[name="to_department_id"]');
    const fromOfficerInput = document.getElementById("itrFromAccountableOfficerInput");
    const toOfficerInput = document.getElementById("itrToAccountableOfficerInput");
    const approvedByDesignationField = document.getElementById("itrApprovedByDesignationInput") || document.querySelector('[name="approved_by_designation"]');
    const releasedByDesignationField = document.getElementById("itrReleasedByDesignationInput") || document.querySelector('[name="released_by_designation"]');
    const receivedByDesignationField = document.getElementById("itrReceivedByDesignationInput") || document.querySelector('[name="received_by_designation"]');

    let baseline = collectPayload();

    function getDirtyFields() {
      const current = collectPayload();
      return FIELD_NAMES.filter((name) => JSON.stringify(current[name]) !== JSON.stringify(baseline[name]));
    }

    function updateActionUi() {
      const dirtyCount = getDirtyFields().length;
      saveBtn.disabled = dirtyCount <= 0;
      saveBtn.textContent = dirtyCount > 0 ? `Save (${dirtyCount})` : "Save";
    }

    async function showValidationErrors(errors) {
      const items = normalizeBackendErrors(errors);
      await Swal.fire({
        icon: "warning",
        title: "Please correct the highlighted fields",
        html: `<div style="text-align:left"><ul style="margin:0; padding-left:18px;">${items.map((x) => `<li>${esc(x.text)}</li>`).join("")}</ul></div>`,
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
          await Swal.fire({ icon: "info", title: "No changes to save", text: "The ITR header is already up to date." });
        }
        return true;
      }

      saveBtn.disabled = true;
      saveBtn.textContent = "Saving...";

      try {
        const res = await fetch(window.__itrEdit.updateUrl, {
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
        syncFundClusterDisplay("itrFromFundSourceSelect", "itrFromFundClusterDisplay");
        syncFundClusterDisplay("itrToFundSourceSelect", "itrToFundClusterDisplay");
        syncTransferTypeUi();
        updateActionUi();

        if (!silentSuccess) {
          await Swal.fire({ icon: "success", title: "Saved", text: data?.message || "ITR draft updated successfully.", timer: 1200, showConfirmButton: false });
        }
        return true;
      } catch (err) {
        const status = Number(err?.status || 0);
        const errors = err?.payload?.errors;
        if (status === 422 && errors && typeof errors === "object") {
          await showValidationErrors(errors);
        } else {
          await Swal.fire({ icon: "error", title: "Error", text: err?.message || "Unexpected error" });
        }
        updateActionUi();
        return false;
      }
    }

    async function saveOfficerRecord(payload) {
      const response = await fetch(window.__itrEdit.accountableOfficerStoreUrl, {
        method: "POST",
        headers: {
          "X-CSRF-TOKEN": getCsrf(),
          Accept: "application/json",
          "Content-Type": "application/json",
        },
        body: JSON.stringify(payload),
      });

      const data = await response.json().catch(() => ({}));
      if (!response.ok) throw new Error(data?.message || "Unable to save accountable officer details.");
      return data?.data || null;
    }

    function buildDepartmentOptionsMarkup(select, selectedId) {
      const safeSelected = String(selectedId || "");
      return Array.from(select?.options || [])
        .filter((option) => String(option.value || "").trim() !== "")
        .map((option) => {
          const value = String(option.value || "");
          const selected = value === safeSelected ? ' selected' : "";
          return `<option value="${esc(value)}"${selected}>${esc(option.textContent || "")}</option>`;
        })
        .join("");
    }

    async function openOfficerDetailsModal({ title, confirmButtonText, departmentField, initialOfficer = {}, initialDepartmentId = "" }) {
      if (!departmentField) {
        await Swal.fire({ icon: "error", title: "Department list unavailable", text: "ITR could not resolve the department options needed for this accountable officer." });
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
              <input id="itrOfficerModalName" class="ti-form-input w-full" value="${esc(initialOfficer.full_name || "")}" />
            </div>
            <div>
              <label class="ti-form-label !mb-1">Department <span class="text-danger">*</span></label>
              <select id="itrOfficerModalDepartment" class="ti-form-select w-full">
                <option value="">- Select Department -</option>
                ${departmentOptions}
              </select>
            </div>
            <div>
              <label class="ti-form-label !mb-1">Designation</label>
              <input id="itrOfficerModalDesignation" class="ti-form-input w-full" value="${esc(initialOfficer.designation || "")}" />
            </div>
          </div>
        `,
        preConfirm: async () => {
          const selectedDepartmentId = String(document.getElementById("itrOfficerModalDepartment")?.value || "").trim();
          const payload = {
            full_name: String(document.getElementById("itrOfficerModalName")?.value || "").trim(),
            department_id: selectedDepartmentId,
            designation: String(document.getElementById("itrOfficerModalDesignation")?.value || "").trim() || null,
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
      const { title = "Complete Accountable Officer Details", departmentField, initialDepartmentId = "" } = options;
      let resolvedOfficer = officer;
      if (!String(resolvedOfficer.department_id || "").trim()) {
        resolvedOfficer = await openOfficerDetailsModal({ title, confirmButtonText: "Save and Use", departmentField, initialOfficer: resolvedOfficer, initialDepartmentId });
        if (!resolvedOfficer) return false;
      }
      return resolvedOfficer;
    }

    async function resolveOfficerForDepartment(officer, departmentField, roleLabel) {
      const resolvedOfficer = await resolveOfficerDetailsOnly(officer, {
        title: `Complete ${roleLabel} Details`,
        departmentField,
        initialDepartmentId: String(departmentField?.value || "").trim(),
      });
      if (!resolvedOfficer) return false;

      const currentDepartmentId = String(departmentField?.value || "").trim();
      const officerDepartmentId = String(resolvedOfficer.department_id || "").trim();
      if (!currentDepartmentId) {
        setFieldValue(departmentField, officerDepartmentId);
        return resolvedOfficer;
      }
      if (currentDepartmentId === officerDepartmentId) return resolvedOfficer;

      const currentDepartmentName = getSelectedOptionText(departmentField, currentDepartmentId);
      const officerDepartmentName = String(
        resolvedOfficer.department_name
          || resolvedOfficer.department_label
          || getSelectedOptionText(departmentField, officerDepartmentId)
          || "that department"
      );

      const confirm = await Swal.fire({
        icon: "question",
        title: "Update department to match?",
        html: `<div style="text-align:left"><p style="margin-bottom:8px;"><b>${esc(resolvedOfficer.full_name || "This officer")}</b> is tied to <b>${esc(officerDepartmentName)}</b>.</p><p style="margin:0;">The ITR currently points to <b>${esc(currentDepartmentName || "another department")}</b>. Use the officer's department instead?</p></div>`,
        showCancelButton: true,
        confirmButtonText: "Use officer department",
        cancelButtonText: "Cancel selection",
      });

      if (!confirm.isConfirmed) return false;
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
        if (currentDepartmentId === officerDepartmentId) return;

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
        if (el.name === "from_fund_source_id") syncFundClusterDisplay("itrFromFundSourceSelect", "itrFromFundClusterDisplay");
        if (el.name === "to_fund_source_id") syncFundClusterDisplay("itrToFundSourceSelect", "itrToFundClusterDisplay");
        if (el.name === "transfer_type") syncTransferTypeUi();
        updateActionUi();
      });
    });

    saveBtn.addEventListener("click", async function () { await saveChanges(); });

    syncFundClusterDisplay("itrFromFundSourceSelect", "itrFromFundClusterDisplay");
    syncFundClusterDisplay("itrToFundSourceSelect", "itrToFundClusterDisplay");
    syncTransferTypeUi();

    const fromOfficerHandle = attachAccountableOfficerAutocomplete({
      input: "#itrFromAccountableOfficerInput",
      suggestUrl: window.__itrEdit?.accountableOfficerSuggestUrl,
      storeUrl: window.__itrEdit?.accountableOfficerStoreUrl,
      departmentField: '[name="from_department_id"]',
      swal: Swal,
      title: "From Accountable Officer",
      createOfficer: async ({ name }) => openOfficerDetailsModal({ title: "Create Source Accountable Officer", confirmButtonText: "Create and Use", departmentField: fromDepartmentField, initialOfficer: { full_name: name } }),
      beforeApplyOfficer: async (officer) => resolveOfficerForDepartment(officer, fromDepartmentField, "Source Accountable Officer"),
    });

    const toOfficerHandle = attachAccountableOfficerAutocomplete({
      input: "#itrToAccountableOfficerInput",
      suggestUrl: window.__itrEdit?.accountableOfficerSuggestUrl,
      storeUrl: window.__itrEdit?.accountableOfficerStoreUrl,
      departmentField: '[name="to_department_id"]',
      swal: Swal,
      title: "To Accountable Officer",
      createOfficer: async ({ name }) => openOfficerDetailsModal({ title: "Create Destination Accountable Officer", confirmButtonText: "Create and Use", departmentField: toDepartmentField, initialOfficer: { full_name: name } }),
      beforeApplyOfficer: async (officer) => resolveOfficerForDepartment(officer, toDepartmentField, "Destination Accountable Officer"),
    });

    attachDepartmentGuard({ departmentField: fromDepartmentField, officerInput: fromOfficerInput, handle: fromOfficerHandle, roleLabel: "From Accountable Officer" });
    attachDepartmentGuard({ departmentField: toDepartmentField, officerInput: toOfficerInput, handle: toOfficerHandle, roleLabel: "To Accountable Officer" });

    attachAccountableOfficerAutocomplete({
      input: "#itrApprovedByNameInput",
      suggestUrl: window.__itrEdit?.accountableOfficerSuggestUrl,
      storeUrl: window.__itrEdit?.accountableOfficerStoreUrl,
      departmentField: '[name="from_department_id"]',
      designationField: approvedByDesignationField,
      swal: Swal,
      title: "Approved By",
      createOfficer: async ({ name }) => openOfficerDetailsModal({ title: "Create Approving Officer", confirmButtonText: "Create and Use", departmentField: fromDepartmentField, initialOfficer: { full_name: name }, initialDepartmentId: String(fromDepartmentField?.value || "").trim() }),
      beforeApplyOfficer: async (officer) => resolveOfficerDetailsOnly(officer, { title: "Complete Approving Officer Details", departmentField: fromDepartmentField, initialDepartmentId: String(fromDepartmentField?.value || "").trim() }),
    });

    attachAccountableOfficerAutocomplete({
      input: "#itrReleasedByNameInput",
      suggestUrl: window.__itrEdit?.accountableOfficerSuggestUrl,
      storeUrl: window.__itrEdit?.accountableOfficerStoreUrl,
      departmentField: '[name="from_department_id"]',
      designationField: releasedByDesignationField,
      swal: Swal,
      title: "Released / Issued By",
      createOfficer: async ({ name }) => openOfficerDetailsModal({ title: "Create Released / Issued By Officer", confirmButtonText: "Create and Use", departmentField: fromDepartmentField, initialOfficer: { full_name: name }, initialDepartmentId: String(fromDepartmentField?.value || "").trim() }),
      beforeApplyOfficer: async (officer) => resolveOfficerDetailsOnly(officer, { title: "Complete Released / Issued By Details", departmentField: fromDepartmentField, initialDepartmentId: String(fromDepartmentField?.value || "").trim() }),
    });

    attachAccountableOfficerAutocomplete({
      input: "#itrReceivedByNameInput",
      suggestUrl: window.__itrEdit?.accountableOfficerSuggestUrl,
      storeUrl: window.__itrEdit?.accountableOfficerStoreUrl,
      departmentField: '[name="to_department_id"]',
      designationField: receivedByDesignationField,
      swal: Swal,
      title: "Received By",
      createOfficer: async ({ name }) => openOfficerDetailsModal({ title: "Create Receiving Officer", confirmButtonText: "Create and Use", departmentField: toDepartmentField, initialOfficer: { full_name: name }, initialDepartmentId: String(toDepartmentField?.value || "").trim() }),
      beforeApplyOfficer: async (officer) => resolveOfficerDetailsOnly(officer, { title: "Complete Receiving Officer Details", departmentField: toDepartmentField, initialDepartmentId: String(toDepartmentField?.value || "").trim() }),
    });

    baseline = collectPayload();
    updateActionUi();

    window.__itrEditPage = {
      saveChanges,
      hasDirtyChanges() { return getDirtyFields().length > 0; },
      isFieldDirty(name) { return getDirtyFields().includes(String(name || "")); },
      getDirtyCount() { return getDirtyFields().length; },
      refreshDirtyState() {
        baseline = collectPayload();
        syncFundClusterDisplay("itrFromFundSourceSelect", "itrFromFundClusterDisplay");
        syncFundClusterDisplay("itrToFundSourceSelect", "itrToFundClusterDisplay");
        syncTransferTypeUi();
        updateActionUi();
      },
    };
  });
})();


