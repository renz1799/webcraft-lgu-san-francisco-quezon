import { initPrintWorkspaceController } from "../../../../core/js/print/workspace-controller";

document.addEventListener("DOMContentLoaded", () => {
  initPrintWorkspaceController({
    formSelector: "[data-wmr-print-form]",
    paperSelectSelector: "[data-wmr-print-paper-select]",
    defaultsButtonSelector: "[data-wmr-print-apply-defaults]",
    defaultsAttribute: "data-wmr-print-paper-defaults",
    settingSelector: '[data-wmr-print-setting="{key}"]',
    pdfLinkSelector: "[data-wmr-print-pdf-download]",
    pdfBaseAttribute: "data-wmr-print-pdf-base",
    fallbackFilename: "wmr-report.pdf",
    previewMessages: {
      loadingLabel: "Updating Preview...",
      failed: "The WMR preview could not be updated right now.",
    },
    pdfMessages: {
      preparing: "Generating the WMR print file. Please wait.",
      ready: "The WMR PDF download has started.",
      failed: "The WMR PDF could not be generated right now.",
    },
  });
});
