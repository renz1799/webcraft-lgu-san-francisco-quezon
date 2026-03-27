import { initPrintWorkspaceController } from "../../../../core/js/print/workspace-controller";

document.addEventListener("DOMContentLoaded", () => {
  initPrintWorkspaceController({
    formSelector: "[data-ptr-print-form]",
    paperSelectSelector: "[data-ptr-print-paper-select]",
    defaultsButtonSelector: "[data-ptr-print-apply-defaults]",
    defaultsAttribute: "data-ptr-print-paper-defaults",
    settingSelector: '[data-ptr-print-setting="{key}"]',
    pdfLinkSelector: "[data-ptr-print-pdf-download]",
    pdfBaseAttribute: "data-ptr-print-pdf-base",
    fallbackFilename: "ptr-report.pdf",
    previewMessages: {
      loadingLabel: "Updating Preview...",
      failed: "The PTR preview could not be updated right now.",
    },
    pdfMessages: {
      preparing: "Generating the PTR print file. Please wait.",
      ready: "The PTR PDF download has started.",
      failed: "The PTR PDF could not be generated right now.",
    },
  });
});
