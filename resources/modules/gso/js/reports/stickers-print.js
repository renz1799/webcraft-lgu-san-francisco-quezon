import { initPrintWorkspaceController } from "../../../../core/js/print/workspace-controller";

function syncStickerSearchInput(select) {
  const instance = window.HSSelect?.getInstance?.(select, true)?.element;
  const placeholder = select.getAttribute("data-stickers-search-placeholder") || "";

  if (instance?.mode !== "tags" || !instance.tagsInput) {
    return;
  }

  instance.tagsInput.placeholder = placeholder;
  instance.tagsInput.style.minWidth = "12rem";

  if (instance.tagsInputHelper) {
    instance.tagsInputHelper.textContent = instance.tagsInput.value || placeholder;

    if (typeof instance.calculateInputWidth === "function") {
      instance.calculateInputWidth();
    }
  }
}

function queueStickerSearchSync(select) {
  window.requestAnimationFrame(() => {
    window.requestAnimationFrame(() => {
      syncStickerSearchInput(select);
    });
  });
}

function keepStickerSearchAvailable() {
  document.querySelectorAll("[data-stickers-inventory-select]").forEach((select) => {
    if (select.dataset.stickerSearchBound !== "1") {
      select.dataset.stickerSearchBound = "1";

      select.addEventListener("change", () => {
        queueStickerSearchSync(select);
      });
    }

    queueStickerSearchSync(select);
  });
}

function initStickerInventorySelect() {
  if (window.HSSelect?.autoInit) {
    window.HSSelect.autoInit();
    keepStickerSearchAvailable();
    return;
  }

  if (window.HSStaticMethods?.autoInit) {
    window.HSStaticMethods.autoInit();
  }

  keepStickerSearchAvailable();
}

document.addEventListener("DOMContentLoaded", () => {
  initPrintWorkspaceController({
    formSelector: "[data-stickers-print-form]",
    paperSelectSelector: "[data-stickers-print-paper-select]",
    defaultsButtonSelector: "[data-stickers-print-apply-defaults]",
    defaultsAttribute: "data-stickers-print-paper-defaults",
    settingSelector: '[data-stickers-print-setting="{key}"]',
    pdfLinkSelector: "[data-stickers-print-pdf-download]",
    pdfBaseAttribute: "data-stickers-print-pdf-base",
    fallbackFilename: "stickers.pdf",
    previewMessages: {
      loadingLabel: "Updating Preview...",
      failed: "The sticker preview could not be updated right now.",
    },
    pdfMessages: {
      preparing: "Preparing the sticker sheet. Please wait.",
      ready: "The sticker sheet is ready.",
      failed: "The sticker sheet could not be prepared right now.",
    },
    onInit: () => {
      initStickerInventorySelect();
    },
  });
});
