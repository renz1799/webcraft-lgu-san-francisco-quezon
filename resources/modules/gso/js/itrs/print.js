import { initPrintWorkspaceController } from "../../../../core/js/print/workspace-controller";

document.addEventListener("DOMContentLoaded", () => {
  initPrintWorkspaceController({
    formSelector: "[data-itr-print-form]",
    paperSelectSelector: "[data-itr-print-paper-select]",
    defaultsButtonSelector: "[data-itr-print-apply-defaults]",
    defaultsAttribute: "data-itr-print-paper-defaults",
    settingSelector: '[data-itr-print-setting="{key}"]',
    pdfLinkSelector: "[data-itr-print-pdf-download]",
    pdfBaseAttribute: "data-itr-print-pdf-base",
    fallbackFilename: "itr-report.pdf",
    previewMessages: {
      loadingLabel: "Updating Preview...",
      failed: "The ITR preview could not be updated right now.",
    },
    pdfMessages: {
      preparing: "Generating the ITR print file. Please wait.",
      ready: "The ITR PDF download has started.",
      failed: "The ITR PDF could not be generated right now.",
    },
  });
});
