import { initPrintWorkspaceController } from "../../../../core/js/print/workspace-controller";

document.addEventListener("DOMContentLoaded", () => {
  initPrintWorkspaceController({
    formSelector: "[data-ics-print-form]",
    paperSelectSelector: "[data-ics-print-paper-select]",
    defaultsButtonSelector: "[data-ics-print-apply-defaults]",
    defaultsAttribute: "data-ics-print-paper-defaults",
    settingSelector: '[data-ics-print-setting="{key}"]',
    pdfLinkSelector: "[data-ics-print-pdf-download]",
    pdfBaseAttribute: "data-ics-print-pdf-base",
    fallbackFilename: "ics-report.pdf",
    previewMessages: {
      loadingLabel: "Updating Preview...",
      failed: "The ICS preview could not be updated right now.",
    },
    pdfMessages: {
      preparing: "Generating the ICS print file. Please wait.",
      ready: "The ICS PDF download has started.",
      failed: "The ICS PDF could not be generated right now.",
    },
  });
});
