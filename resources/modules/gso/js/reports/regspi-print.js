import { initPrintWorkspaceController } from "../../../../core/js/print/workspace-controller";

document.addEventListener("DOMContentLoaded", () => {
  initPrintWorkspaceController({
    formSelector: "[data-regspi-print-form]",
    paperSelectSelector: "[data-regspi-print-paper-select]",
    defaultsButtonSelector: "[data-regspi-print-apply-defaults]",
    defaultsAttribute: "data-regspi-print-paper-defaults",
    settingSelector: '[data-regspi-print-setting="{key}"]',
    pdfLinkSelector: "[data-regspi-print-pdf-download]",
    pdfBaseAttribute: "data-regspi-print-pdf-base",
    fallbackFilename: "regspi-report.pdf",
    previewMessages: {
      loadingLabel: "Updating Preview...",
      failed: "The RegSPI preview could not be updated right now.",
    },
    pdfMessages: {
      preparing: "Generating the RegSPI print file. Please wait.",
      ready: "The RegSPI PDF download has started.",
      failed: "The RegSPI PDF could not be generated right now.",
    },
  });
});
