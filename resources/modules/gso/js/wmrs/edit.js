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

  function normalizeValue(value) {
    const text = String(value ?? "").trim();
    return text === "" ? null : text;
  }

  const FIELD_NAMES = [
    "fund_cluster_id",
    "place_of_storage",
    "report_date",
    "custodian_name",
    "custodian_designation",
    "custodian_date",
    "approved_by_name",
    "approved_by_designation",
    "approved_by_date",
    "inspection_officer_name",
    "inspection_officer_designation",
    "inspection_officer_date",
    "witness_name",
    "witness_designation",
    "witness_date",
    "remarks",
  ];

  const FIELD_LABELS = {
    fund_cluster_id: "Fund Cluster",
    place_of_storage: "Place of Storage",
    report_date: "Report Date",
    custodian_name: "Custodian Printed Name",
    custodian_designation: "Custodian Designation",
    custodian_date: "Custodian Date",
    approved_by_name: "Approved By Printed Name",
    approved_by_designation: "Approved By Designation",
    approved_by_date: "Approved By Date",
    inspection_officer_name: "Inspection Officer Printed Name",
    inspection_officer_designation: "Inspection Officer Designation",
    inspection_officer_date: "Inspection Officer Date",
    witness_name: "Witness Printed Name",
    witness_designation: "Witness Designation",
    witness_date: "Witness Date",
    remarks: "Remarks",
  };

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

  function escapeHtml(value) {
    return String(value ?? "")
      .replace(/&/g, "&amp;")
      .replace(/</g, "&lt;")
      .replace(/>/g, "&gt;")
      .replace(/\"/g, "&quot;")
      .replace(/'/g, "&#39;");
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

  onReady(function () {
    const form = document.getElementById("wmrForm");
    const saveBtn = document.getElementById("wmrSaveBtn");
    if (!form || !saveBtn || !window.__wmrEdit?.canModify) return;

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
        html: `<div style="text-align:left"><ul style="margin:0; padding-left:18px;">${items.map((x) => `<li>${escapeHtml(x.text)}</li>`).join("")}</ul></div>`,
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
          await Swal.fire({ icon: "info", title: "No changes to save", text: "The WMR header is already up to date." });
        }
        return true;
      }

      saveBtn.disabled = true;
      saveBtn.textContent = "Saving...";

      try {
        const res = await fetch(window.__wmrEdit.updateUrl, {
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

        if (!silentSuccess) {
          await Swal.fire({ icon: "success", title: "Saved", text: data?.message || "WMR draft updated successfully.", timer: 1200, showConfirmButton: false });
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

    const inputSelector = FIELD_NAMES.map((name) => `[name="${name}"]`).join(",");
    document.querySelectorAll(inputSelector).forEach((el) => {
      el.addEventListener("input", updateActionUi);
      el.addEventListener("change", updateActionUi);
    });

    saveBtn.addEventListener("click", async function () {
      await saveChanges();
    });

    attachAccountableOfficerAutocomplete({
      input: "#wmrCustodianNameInput",
      suggestUrl: window.__wmrEdit.accountableOfficerSuggestUrl,
      storeUrl: window.__wmrEdit.accountableOfficerStoreUrl,
      designationField: "#wmrCustodianDesignationInput",
      swal: Swal,
      title: "Custodian",
    });

    attachAccountableOfficerAutocomplete({
      input: "#wmrApprovedByNameInput",
      suggestUrl: window.__wmrEdit.accountableOfficerSuggestUrl,
      storeUrl: window.__wmrEdit.accountableOfficerStoreUrl,
      designationField: "#wmrApprovedByDesignationInput",
      swal: Swal,
      title: "Approved By",
    });

    attachAccountableOfficerAutocomplete({
      input: "#wmrInspectionOfficerNameInput",
      suggestUrl: window.__wmrEdit.accountableOfficerSuggestUrl,
      storeUrl: window.__wmrEdit.accountableOfficerStoreUrl,
      designationField: "#wmrInspectionOfficerDesignationInput",
      swal: Swal,
      title: "Inspection Officer",
    });

    attachAccountableOfficerAutocomplete({
      input: "#wmrWitnessNameInput",
      suggestUrl: window.__wmrEdit.accountableOfficerSuggestUrl,
      storeUrl: window.__wmrEdit.accountableOfficerStoreUrl,
      designationField: "#wmrWitnessDesignationInput",
      swal: Swal,
      title: "Witness",
    });

    baseline = collectPayload();
    updateActionUi();

    window.__wmrEditPage = {
      saveChanges,
      hasDirtyChanges() { return getDirtyFields().length > 0; },
      getDirtyCount() { return getDirtyFields().length; },
      isFieldDirty(name) {
        return getDirtyFields().includes(String(name || ""));
      },
      refreshDirtyState() {
        baseline = collectPayload();
        updateActionUi();
      },
    };
  });
})();

