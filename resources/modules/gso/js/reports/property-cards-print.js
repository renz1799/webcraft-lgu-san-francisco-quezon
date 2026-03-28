import { initPrintWorkspaceController } from "../../../../core/js/print/workspace-controller";

document.addEventListener("DOMContentLoaded", () => {
  initPrintWorkspaceController({
    formSelector: "[data-property-cards-print-form]",
    paperSelectSelector: "[data-property-cards-print-paper-select]",
    defaultsButtonSelector: "[data-property-cards-print-apply-defaults]",
    defaultsAttribute: "data-property-cards-print-paper-defaults",
    settingSelector: '[data-property-cards-print-setting="{key}"]',
    pdfLinkSelector: "[data-property-cards-print-pdf-download]",
    pdfBaseAttribute: "data-property-cards-print-pdf-base",
    fallbackFilename: "property-cards.pdf",
    previewMessages: {
      loadingLabel: "Updating Preview...",
      failed: "The Property Cards preview could not be updated right now.",
    },
    pdfMessages: {
      preparing: "Generating the Property Cards PDF. Please wait.",
      ready: "The Property Cards PDF download has started.",
      failed: "The Property Cards PDF could not be generated right now.",
    },
  });
});
