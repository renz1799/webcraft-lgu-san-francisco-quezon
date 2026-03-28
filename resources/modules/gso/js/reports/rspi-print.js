import { initPrintWorkspaceController } from "../../../../core/js/print/workspace-controller";

document.addEventListener("DOMContentLoaded", () => {
  initPrintWorkspaceController({
    formSelector: "[data-rspi-print-form]",
    paperSelectSelector: "[data-rspi-print-paper-select]",
    defaultsButtonSelector: "[data-rspi-print-apply-defaults]",
    defaultsAttribute: "data-rspi-print-paper-defaults",
    settingSelector: '[data-rspi-print-setting="{key}"]',
    pdfLinkSelector: "[data-rspi-print-pdf-download]",
    pdfBaseAttribute: "data-rspi-print-pdf-base",
    fallbackFilename: "rspi-report.pdf",
    previewMessages: {
      loadingLabel: "Updating Preview...",
      failed: "The RSPI preview could not be updated right now.",
    },
    pdfMessages: {
      preparing: "Generating the RSPI print file. Please wait.",
      ready: "The RSPI PDF download has started.",
      failed: "The RSPI PDF could not be generated right now.",
    },
  });
});
