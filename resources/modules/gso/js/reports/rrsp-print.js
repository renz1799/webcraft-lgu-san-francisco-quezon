import { initPrintWorkspaceController } from "../../../../core/js/print/workspace-controller";

document.addEventListener("DOMContentLoaded", () => {
  initPrintWorkspaceController({
    formSelector: "[data-rrsp-print-form]",
    paperSelectSelector: "[data-rrsp-print-paper-select]",
    defaultsButtonSelector: "[data-rrsp-print-apply-defaults]",
    defaultsAttribute: "data-rrsp-print-paper-defaults",
    settingSelector: '[data-rrsp-print-setting="{key}"]',
    pdfLinkSelector: "[data-rrsp-print-pdf-download]",
    pdfBaseAttribute: "data-rrsp-print-pdf-base",
    fallbackFilename: "rrsp-report.pdf",
    previewMessages: {
      loadingLabel: "Updating Preview...",
      failed: "The RRSP preview could not be updated right now.",
    },
    pdfMessages: {
      preparing: "Generating the RRSP print file. Please wait.",
      ready: "The RRSP PDF download has started.",
      failed: "The RRSP PDF could not be generated right now.",
    },
  });
});
