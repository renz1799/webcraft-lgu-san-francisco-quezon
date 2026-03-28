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

  const input = form.querySelector('[data-rpci-print-accountable-name="1"]');
  const designationField = form.querySelector(
    '[data-rpci-print-accountable-designation="1"]'
  );
  const hiddenIdField = form.querySelector('[data-rpci-print-accountable-id="1"]');
  const suggestUrl = String(
    form.getAttribute("data-rpci-print-accountable-suggest-url") || ""
  ).trim();

  if (!(input instanceof HTMLInputElement) || !suggestUrl) {
    return;
  }

  if (input.dataset.rpciAutocompleteBound === "1") {
    return;
  }

  input.dataset.rpciAutocompleteBound = "1";

  if (
    hiddenIdField instanceof HTMLInputElement &&
    hiddenIdField.value.trim() !== "" &&
    !input.dataset.rpciSelectedOfficerName
  ) {
    input.dataset.rpciSelectedOfficerName = String(input.value || "").trim();
  }

  const clearSelectionIfNeeded = () => {
    const selectedName = normalizeName(input.dataset.rpciSelectedOfficerName || "");
    const currentName = normalizeName(input.value || "");

    if (selectedName !== "" && selectedName === currentName) {
      return;
    }

    if (hiddenIdField instanceof HTMLInputElement) {
      hiddenIdField.value = "";
    }

    delete input.dataset.rpciSelectedOfficerId;
    delete input.dataset.rpciSelectedOfficerName;
  };

  input.addEventListener("input", clearSelectionIfNeeded);
  input.addEventListener("change", clearSelectionIfNeeded);

  attachAccountableOfficerAutocomplete({
    input,
    suggestUrl,
    designationField,
    title: "Accountable Officer Suggestions",
    emptyHelp: "Type at least 2 characters to search accountable officers.",
    onOfficerSelected: async (officer, helpers) => {
      if (hiddenIdField instanceof HTMLInputElement) {
        hiddenIdField.value = String(officer?.id || "").trim();
      }

      input.dataset.rpciSelectedOfficerId = String(officer?.id || "").trim();
      input.dataset.rpciSelectedOfficerName = String(officer?.full_name || "").trim();
      helpers.setFieldValue(designationField, officer?.designation || "");
    },
  });
}

document.addEventListener("DOMContentLoaded", () => {
  initPrintWorkspaceController({
    formSelector: "[data-rpci-print-form]",
    paperSelectSelector: "[data-rpci-print-paper-select]",
    defaultsButtonSelector: "[data-rpci-print-apply-defaults]",
    defaultsAttribute: "data-rpci-print-paper-defaults",
    settingSelector: '[data-rpci-print-setting="{key}"]',
    pdfLinkSelector: "[data-rpci-print-pdf-download]",
    pdfBaseAttribute: "data-rpci-print-pdf-base",
    fallbackFilename: "rpci-report.pdf",
    previewMessages: {
      loadingLabel: "Updating Preview...",
      failed: "The RPCI preview could not be updated right now.",
    },
    pdfMessages: {
      preparing: "Generating the RPCI print file. Please wait.",
      ready: "The RPCI PDF download has started.",
      failed: "The RPCI PDF could not be generated right now.",
    },
    onInit: ({ form }) => {
      bindAccountableOfficerAutocomplete(form);
    },
  });
});
