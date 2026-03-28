import { initPrintWorkspaceController } from "../../../../core/js/print/workspace-controller";

document.addEventListener("DOMContentLoaded", () => {
  initPrintWorkspaceController({
    formSelector: "[data-ssmi-print-form]",
    paperSelectSelector: "[data-ssmi-print-paper-select]",
    defaultsButtonSelector: "[data-ssmi-print-apply-defaults]",
    defaultsAttribute: "data-ssmi-print-paper-defaults",
    settingSelector: '[data-ssmi-print-setting="{key}"]',
    pdfLinkSelector: "[data-ssmi-print-pdf-download]",
    pdfBaseAttribute: "data-ssmi-print-pdf-base",
    fallbackFilename: "ssmi-report.pdf",
    previewMessages: {
      loadingLabel: "Updating Preview...",
      failed: "The SSMI preview could not be updated right now.",
    },
    pdfMessages: {
      preparing: "Generating the SSMI print file. Please wait.",
      ready: "The SSMI PDF download has started.",
      failed: "The SSMI PDF could not be generated right now.",
    },
  });
});
