import { initPrintWorkspaceController } from "../../../../core/js/print/workspace-controller";

document.addEventListener("DOMContentLoaded", () => {
  initPrintWorkspaceController({
    formSelector: "[data-ris-print-form]",
    paperSelectSelector: "[data-ris-print-paper-select]",
    defaultsButtonSelector: "[data-ris-print-apply-defaults]",
    defaultsAttribute: "data-ris-print-paper-defaults",
    settingSelector: '[data-ris-print-setting="{key}"]',
    pdfLinkSelector: "[data-ris-print-pdf-download]",
    pdfBaseAttribute: "data-ris-print-pdf-base",
    fallbackFilename: "ris-report.pdf",
    previewMessages: {
      loadingLabel: "Updating Preview...",
      failed: "The RIS preview could not be updated right now.",
    },
    pdfMessages: {
      preparing: "Generating the RIS print file. Please wait.",
      ready: "The RIS PDF download has started.",
      failed: "The RIS PDF could not be generated right now.",
    },
  });
});
