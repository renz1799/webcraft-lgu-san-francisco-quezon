import { initPrintWorkspaceController } from "../../../../core/js/print/workspace-controller";
import Swal from "sweetalert2";

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

function getCsrfToken() {
  return document.querySelector('meta[name="csrf-token"]')?.getAttribute("content") || "";
}

function buildStickerJobBody(form) {
  const params = new URLSearchParams();

  for (const [key, rawValue] of new FormData(form).entries()) {
    const value = String(rawValue || "").trim();

    if (value === "") {
      continue;
    }

    params.append(key, value);
  }

  return params;
}

function updateStickerJobDialog(payload) {
  const container = Swal.getHtmlContainer();

  if (!container) {
    return;
  }

  const stageNode = container.querySelector("[data-sticker-job-stage]");
  const percentNode = container.querySelector("[data-sticker-job-percent]");
  const detailNode = container.querySelector("[data-sticker-job-detail]");
  const barNode = container.querySelector("[data-sticker-job-bar]");

  if (stageNode) {
    stageNode.textContent = payload.stage || "Preparing sticker PDF...";
  }

  if (percentNode) {
    percentNode.textContent = `${Math.max(0, Math.min(100, Math.round(payload.progress_percent || 0)))}%`;
  }

  if (detailNode) {
    detailNode.textContent = payload.detail || "";
  }

  if (barNode) {
    barNode.style.width = `${Math.max(0, Math.min(100, Math.round(payload.progress_percent || 0)))}%`;
  }
}

function buildStickerJobDetail(statusPayload) {
  const totalPages = Number(statusPayload.total_pages || 0);
  const completedPages = Number(statusPayload.completed_pages || 0);

  if (statusPayload.status === "queued") {
    return "Waiting for the sticker PDF worker to start...";
  }

  if (statusPayload.status === "running" && totalPages > 0 && completedPages < totalPages) {
    return `Prepared ${completedPages} of ${totalPages} page(s).`;
  }

  if (statusPayload.status === "running") {
    return "Finalizing the sticker PDF document...";
  }

  if (statusPayload.status === "completed") {
    return "The sticker PDF is ready. Starting the download...";
  }

  if (statusPayload.status === "failed") {
    return statusPayload.error_message || "The sticker PDF job failed.";
  }

  return "Preparing the sticker PDF...";
}

function openStickerJobDialog() {
  return Swal.fire({
    title: "Preparing PDF...",
    html: `
      <div class="text-start">
        <div class="flex items-center justify-between gap-4 text-sm font-medium text-defaulttextcolor/80">
          <span data-sticker-job-stage>Queued for PDF generation.</span>
          <span data-sticker-job-percent>0%</span>
        </div>
        <div class="mt-3 h-2 overflow-hidden rounded-full bg-light">
          <div data-sticker-job-bar class="h-full rounded-full bg-primary transition-all duration-300" style="width: 0%;"></div>
        </div>
        <p data-sticker-job-detail class="mt-3 text-xs text-muted">Waiting for the sticker PDF worker to start...</p>
      </div>
    `,
    allowOutsideClick: false,
    allowEscapeKey: false,
    didOpen: () => {
      Swal.showLoading();
    },
  });
}

function startStickerPreparationSimulation() {
  let progressPercent = 6;

  updateStickerJobDialog({
    stage: "Preparing sticker pages...",
    progress_percent: progressPercent,
    detail: "Generating the sticker PDF in this local session...",
  });

  const timer = window.setInterval(() => {
    if (progressPercent >= 88) {
      return;
    }

    progressPercent = Math.min(
      progressPercent + (progressPercent < 40 ? 6 : progressPercent < 70 ? 3 : 1),
      88,
    );

    updateStickerJobDialog({
      stage: "Preparing sticker pages...",
      progress_percent: progressPercent,
      detail: "Generating the sticker PDF in this local session...",
    });
  }, 450);

  return () => {
    window.clearInterval(timer);
  };
}

function triggerStickerPdfDownload(downloadUrl) {
  const link = document.createElement("a");
  link.href = downloadUrl;
  link.style.display = "none";
  document.body.appendChild(link);
  link.click();
  link.remove();
}

function delay(ms) {
  return new Promise((resolve) => {
    window.setTimeout(resolve, ms);
  });
}

async function pollStickerPdfJob(statusUrl) {
  const startedAt = Date.now();

  while (true) {
    const response = await fetch(statusUrl, {
      method: "GET",
      headers: {
        "X-Requested-With": "XMLHttpRequest",
        Accept: "application/json",
      },
      credentials: "same-origin",
    });

    if (!response.ok) {
      throw new Error("The sticker PDF status could not be checked right now.");
    }

    const payload = await response.json();
    const detail = buildStickerJobDetail(payload);

    updateStickerJobDialog({
      stage: payload.stage,
      progress_percent: payload.progress_percent,
      detail,
    });

    if (payload.status === "completed") {
      return payload;
    }

    if (payload.status === "failed") {
      throw new Error(payload.error_message || "The sticker PDF job failed.");
    }

    if (payload.status === "queued" && Date.now() - startedAt >= 30000) {
      throw new Error(
        "The sticker PDF job is still queued. Please try again in a moment.",
      );
    }

    await delay(1200);
  }
}

async function handleStickerPdfJobStart(button) {
  const form = document.querySelector("[data-stickers-print-form]");
  const startUrl = button.getAttribute("data-stickers-print-job-start-url") || "";

  if (!form || !startUrl || button.disabled) {
    return;
  }

  openStickerJobDialog();
  const stopPreparationSimulation = startStickerPreparationSimulation();

  try {
    const response = await fetch(startUrl, {
      method: "POST",
      headers: {
        "X-CSRF-TOKEN": getCsrfToken(),
        "X-Requested-With": "XMLHttpRequest",
        Accept: "application/json",
        "Content-Type": "application/x-www-form-urlencoded; charset=UTF-8",
      },
      credentials: "same-origin",
      body: buildStickerJobBody(form).toString(),
    });

    const payload = await response.json().catch(() => ({}));
    stopPreparationSimulation();

    if (!response.ok) {
      throw new Error(
        payload.error_message || payload.message || "The sticker PDF job could not be started.",
      );
    }

    if (payload.status === "failed") {
      throw new Error(payload.error_message || "The sticker PDF job failed.");
    }

    if (payload.status === "completed" && payload.download_url) {
      updateStickerJobDialog({
        stage: payload.stage || "Sticker PDF is ready.",
        progress_percent: 100,
        detail: "The sticker PDF is ready. Starting the download...",
      });

      triggerStickerPdfDownload(payload.download_url);

      await Swal.fire({
        icon: "success",
        title: "PDF ready",
        text: "The sticker PDF is ready.",
        timer: 1400,
        showConfirmButton: false,
      });

      return;
    }

    updateStickerJobDialog({
      stage: payload.stage,
      progress_percent: payload.progress_percent,
      detail: buildStickerJobDetail(payload),
    });

    const completedPayload = await pollStickerPdfJob(payload.status_url);

    updateStickerJobDialog({
      stage: completedPayload.stage,
      progress_percent: 100,
      detail: "The sticker PDF is ready. Starting the download...",
    });

    triggerStickerPdfDownload(completedPayload.download_url);

    await Swal.fire({
      icon: "success",
      title: "PDF ready",
      text: "The sticker PDF is ready.",
      timer: 1400,
      showConfirmButton: false,
    });
  } catch (error) {
    stopPreparationSimulation();
    await Swal.fire({
      icon: "error",
      title: "Download failed",
      text: error instanceof Error ? error.message : "The sticker PDF could not be prepared right now.",
    });
  }
}

function bindStickerPdfJobButton() {
  const button = document.querySelector("[data-stickers-print-job-start]");

  if (!button || button.dataset.stickerPdfJobBound === "1") {
    return;
  }

  button.dataset.stickerPdfJobBound = "1";
  button.addEventListener("click", async () => {
    await handleStickerPdfJobStart(button);
  });
}

document.addEventListener("DOMContentLoaded", () => {
  initPrintWorkspaceController({
    formSelector: "[data-stickers-print-form]",
    paperSelectSelector: "[data-stickers-print-paper-select]",
    defaultsButtonSelector: "[data-stickers-print-apply-defaults]",
    defaultsAttribute: "data-stickers-print-paper-defaults",
    settingSelector: '[data-stickers-print-setting="{key}"]',
    pdfLinkSelector: "[data-stickers-print-pdf-download-disabled]",
    pdfBaseAttribute: "data-stickers-print-pdf-base-disabled",
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
      bindStickerPdfJobButton();
    },
  });
});
