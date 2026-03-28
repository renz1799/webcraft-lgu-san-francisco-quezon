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

  const input = form.querySelector('[data-rpcsp-print-accountable-name="1"]');
  const designationField = form.querySelector(
    '[data-rpcsp-print-accountable-designation="1"]'
  );
  const filterSelect = form.querySelector(
    '[data-rpcsp-print-accountable-filter="1"]'
  );
  const suggestUrl = String(
    form.getAttribute("data-rpcsp-print-accountable-suggest-url") || ""
  ).trim();

  if (!(input instanceof HTMLInputElement) || !suggestUrl) {
    return;
  }

  if (input.dataset.rpcspAutocompleteBound === "1") {
    return;
  }

  input.dataset.rpcspAutocompleteBound = "1";

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
      input.dataset.rpcspSelectedOfficerId = option.value.trim();
      input.dataset.rpcspSelectedOfficerName = fullName;
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
    !input.dataset.rpcspSelectedOfficerName
  ) {
    syncFromSelectedOption();
  }

  const clearSelectionIfNeeded = () => {
    const selectedName = normalizeName(
      input.dataset.rpcspSelectedOfficerName || ""
    );
    const currentName = normalizeName(input.value || "");

    if (selectedName !== "" && selectedName === currentName) {
      return;
    }

    if (
      filterSelect instanceof HTMLSelectElement &&
      filterSelect.value.trim() ===
        String(input.dataset.rpcspSelectedOfficerId || "").trim()
    ) {
      filterSelect.value = "";
      filterSelect.dispatchEvent(new Event("change", { bubbles: true }));
    }

    delete input.dataset.rpcspSelectedOfficerId;
    delete input.dataset.rpcspSelectedOfficerName;
  };

  input.addEventListener("input", clearSelectionIfNeeded);
  input.addEventListener("change", clearSelectionIfNeeded);

  if (filterSelect instanceof HTMLSelectElement) {
    filterSelect.addEventListener("change", () => {
      if (filterSelect.value.trim() === "") {
        delete input.dataset.rpcspSelectedOfficerId;
        delete input.dataset.rpcspSelectedOfficerName;
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

      input.dataset.rpcspSelectedOfficerId = String(officer?.id || "").trim();
      input.dataset.rpcspSelectedOfficerName = String(
        officer?.full_name || ""
      ).trim();
      helpers.setFieldValue(designationField, officer?.designation || "");
    },
  });
}

document.addEventListener("DOMContentLoaded", () => {
  initPrintWorkspaceController({
    formSelector: "[data-rpcsp-print-form]",
    paperSelectSelector: "[data-rpcsp-print-paper-select]",
    defaultsButtonSelector: "[data-rpcsp-print-apply-defaults]",
    defaultsAttribute: "data-rpcsp-print-paper-defaults",
    settingSelector: '[data-rpcsp-print-setting="{key}"]',
    pdfLinkSelector: "[data-rpcsp-print-pdf-download]",
    pdfBaseAttribute: "data-rpcsp-print-pdf-base",
    fallbackFilename: "rpcsp-report.pdf",
    previewMessages: {
      loadingLabel: "Updating Preview...",
      failed: "The RPCSP preview could not be updated right now.",
    },
    pdfMessages: {
      preparing: "Generating the RPCSP print file. Please wait.",
      ready: "The RPCSP PDF download has started.",
      failed: "The RPCSP PDF could not be generated right now.",
    },
    onInit: ({ form }) => {
      bindAccountableOfficerAutocomplete(form);
    },
  });
});
