import { initPrintWorkspaceController } from "../../../../core/js/print/workspace-controller";

document.addEventListener("DOMContentLoaded", () => {
  initPrintWorkspaceController({
    formSelector: "[data-par-print-form]",
    paperSelectSelector: "[data-par-print-paper-select]",
    defaultsButtonSelector: "[data-par-print-apply-defaults]",
    defaultsAttribute: "data-par-print-paper-defaults",
    settingSelector: '[data-par-print-setting="{key}"]',
    pdfLinkSelector: "[data-par-print-pdf-download]",
    pdfBaseAttribute: "data-par-print-pdf-base",
    fallbackFilename: "par-report.pdf",
    previewMessages: {
      loadingLabel: "Updating Preview...",
      failed: "The PAR preview could not be updated right now.",
    },
    pdfMessages: {
      preparing: "Generating the PAR print file. Please wait.",
      ready: "The PAR PDF download has started.",
      failed: "The PAR PDF could not be generated right now.",
    },
  });
});
