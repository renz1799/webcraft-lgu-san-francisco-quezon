import { initPrintWorkspaceController } from "../../../../core/js/print/workspace-controller";

document.addEventListener("DOMContentLoaded", () => {
  initPrintWorkspaceController({
    formSelector: "[data-air-print-form]",
    paperSelectSelector: "[data-air-print-paper-select]",
    defaultsButtonSelector: "[data-air-print-apply-defaults]",
    defaultsAttribute: "data-air-print-paper-defaults",
    settingSelector: '[data-air-print-setting="{key}"]',
    pdfLinkSelector: "[data-air-print-pdf-download]",
    pdfBaseAttribute: "data-air-print-pdf-base",
    fallbackFilename: "air-report.pdf",
    previewMessages: {
      loadingLabel: "Updating Preview...",
      failed: "The AIR preview could not be updated right now.",
    },
    pdfMessages: {
      preparing: "Generating the AIR print file. Please wait.",
      ready: "The AIR PDF download has started.",
      failed: "The AIR PDF could not be generated right now.",
    },
  });
});
