import { initPrintWorkspaceController } from "../../../../core/js/print/workspace-controller";
import { attachAccountableOfficerAutocomplete } from "../accountable-officers/autocomplete.js";

function normalizeName(value) {
  return String(value ?? "")
    .trim()
    .replace(/\s+/g, " ")
    .toLowerCase();
}

function bindAccountableOfficerAutocomplete(form) {
  if (!(form instanceof HTMLFormElement)) {
    return;
  }

  const input = form.querySelector('[data-rpcppe-print-accountable-name="1"]');
  const designationField = form.querySelector(
    '[data-rpcppe-print-accountable-designation="1"]'
  );
  const filterSelect = form.querySelector(
    '[data-rpcppe-print-accountable-filter="1"]'
  );
  const suggestUrl = String(
    form.getAttribute("data-rpcppe-print-accountable-suggest-url") || ""
  ).trim();

  if (!(input instanceof HTMLInputElement) || !suggestUrl) {
    return;
  }

  if (input.dataset.rpcppeAutocompleteBound === "1") {
    return;
  }

  input.dataset.rpcppeAutocompleteBound = "1";

  const syncFromSelectedOption = () => {
    if (!(filterSelect instanceof HTMLSelectElement)) {
      return;
    }

    const option = filterSelect.selectedOptions?.[0];
    if (!(option instanceof HTMLOptionElement) || option.value.trim() === "") {
      return;
    }

    const fullName = String(option.dataset.officerName || "").trim();
    const designation = String(option.dataset.officerDesignation || "").trim();

    if (fullName !== "") {
      input.value = fullName;
      input.dataset.rpcppeSelectedOfficerId = option.value.trim();
      input.dataset.rpcppeSelectedOfficerName = fullName;
    }

    if (designationField instanceof HTMLInputElement && designation !== "") {
      designationField.value = designation;
      designationField.dispatchEvent(new Event("input", { bubbles: true }));
      designationField.dispatchEvent(new Event("change", { bubbles: true }));
    }
  };

  if (
    filterSelect instanceof HTMLSelectElement &&
    filterSelect.value.trim() !== "" &&
    !input.dataset.rpcppeSelectedOfficerName
  ) {
    syncFromSelectedOption();
  }

  const clearSelectionIfNeeded = () => {
    const selectedName = normalizeName(
      input.dataset.rpcppeSelectedOfficerName || ""
    );
    const currentName = normalizeName(input.value || "");

    if (selectedName !== "" && selectedName === currentName) {
      return;
    }

    if (
      filterSelect instanceof HTMLSelectElement &&
      filterSelect.value.trim() ===
        String(input.dataset.rpcppeSelectedOfficerId || "").trim()
    ) {
      filterSelect.value = "";
      filterSelect.dispatchEvent(new Event("change", { bubbles: true }));
    }

    delete input.dataset.rpcppeSelectedOfficerId;
    delete input.dataset.rpcppeSelectedOfficerName;
  };

  input.addEventListener("input", clearSelectionIfNeeded);
  input.addEventListener("change", clearSelectionIfNeeded);

  if (filterSelect instanceof HTMLSelectElement) {
    filterSelect.addEventListener("change", () => {
      if (filterSelect.value.trim() === "") {
        delete input.dataset.rpcppeSelectedOfficerId;
        delete input.dataset.rpcppeSelectedOfficerName;
        return;
      }

      syncFromSelectedOption();
    });
  }

  attachAccountableOfficerAutocomplete({
    input,
    suggestUrl,
    designationField,
    title: "Accountable Officer Suggestions",
    emptyHelp: "Type at least 2 characters to search accountable officers.",
    onOfficerSelected: async (officer, helpers) => {
      if (filterSelect instanceof HTMLSelectElement) {
        filterSelect.value = String(officer?.id || "").trim();
        filterSelect.dispatchEvent(new Event("change", { bubbles: true }));
      }

      input.dataset.rpcppeSelectedOfficerId = String(officer?.id || "").trim();
      input.dataset.rpcppeSelectedOfficerName = String(
        officer?.full_name || ""
      ).trim();
      helpers.setFieldValue(designationField, officer?.designation || "");
    },
  });
}

document.addEventListener("DOMContentLoaded", () => {
  initPrintWorkspaceController({
    formSelector: "[data-rpcppe-print-form]",
    paperSelectSelector: "[data-rpcppe-print-paper-select]",
    defaultsButtonSelector: "[data-rpcppe-print-apply-defaults]",
    defaultsAttribute: "data-rpcppe-print-paper-defaults",
    settingSelector: '[data-rpcppe-print-setting="{key}"]',
    pdfLinkSelector: "[data-rpcppe-print-pdf-download]",
    pdfBaseAttribute: "data-rpcppe-print-pdf-base",
    fallbackFilename: "rpcppe-report.pdf",
    previewMessages: {
      loadingLabel: "Updating Preview...",
      failed: "The RPCPPE preview could not be updated right now.",
    },
    pdfMessages: {
      preparing: "Generating the RPCPPE print file. Please wait.",
      ready: "The RPCPPE PDF download has started.",
      failed: "The RPCPPE PDF could not be generated right now.",
    },
    onInit: ({ form }) => {
      bindAccountableOfficerAutocomplete(form);
    },
  });
});
