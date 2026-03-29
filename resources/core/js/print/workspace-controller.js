import Swal from "sweetalert2";
import "sweetalert2/dist/sweetalert2.min.css";

function buildUrlWithForm(baseUrl, form) {
  const url = new URL(baseUrl, window.location.origin);
  const formData = new FormData(form);
  const fieldNames = new Set(
    Array.from(form.elements || [])
      .map((element) => element?.name)
      .filter((name) => typeof name === "string" && name.trim() !== ""),
  );

  fieldNames.forEach((name) => {
    url.searchParams.delete(name);
  });

  for (const [key, rawValue] of formData.entries()) {
    const value = String(rawValue || "").trim();

    if (value === "") {
      continue;
    }

    url.searchParams.append(key, value);
  }

  return url.toString();
}

function parseDefaultsMap(form, defaultsAttribute) {
  const raw = form?.getAttribute(defaultsAttribute) || "{}";

  try {
    return JSON.parse(raw);
  } catch {
    return {};
  }
}

function parseFilename(response, fallback) {
  const disposition = response.headers.get("content-disposition") || "";
  const utfMatch = disposition.match(/filename\*=UTF-8''([^;]+)/i);
  if (utfMatch?.[1]) {
    return decodeURIComponent(utfMatch[1]);
  }

  const basicMatch = disposition.match(/filename="?([^"]+)"?/i);
  if (basicMatch?.[1]) {
    return basicMatch[1];
  }

  return fallback;
}

function clampPercent(value) {
  return Math.max(0, Math.min(100, Math.round(value)));
}

function formatBytes(bytes) {
  if (!Number.isFinite(bytes) || bytes <= 0) {
    return "0 B";
  }

  const units = ["B", "KB", "MB", "GB"];
  let value = bytes;
  let index = 0;

  while (value >= 1024 && index < units.length - 1) {
    value /= 1024;
    index += 1;
  }

  const decimals = value >= 10 || index === 0 ? 0 : 1;

  return `${value.toFixed(decimals)} ${units[index]}`;
}

function updatePdfProgressDialog(stage, percent, detail) {
  const container = Swal.getHtmlContainer();

  if (!container) {
    return;
  }

  const stageNode = container.querySelector("[data-pdf-progress-stage]");
  const percentNode = container.querySelector("[data-pdf-progress-percent]");
  const detailNode = container.querySelector("[data-pdf-progress-detail]");
  const barNode = container.querySelector("[data-pdf-progress-bar]");

  if (stageNode) {
    stageNode.textContent = stage;
  }

  if (percentNode) {
    percentNode.textContent = `${clampPercent(percent)}%`;
  }

  if (detailNode) {
    detailNode.textContent = detail;
  }

  if (barNode) {
    barNode.style.width = `${clampPercent(percent)}%`;
  }
}

function startEstimatedPdfProgress(messages) {
  let percent = 8;

  updatePdfProgressDialog(
    "Generating PDF...",
    percent,
    messages.preparing,
  );

  const intervalId = window.setInterval(() => {
    if (percent >= 82) {
      return;
    }

    if (percent < 35) {
      percent += 6;
    } else if (percent < 60) {
      percent += 4;
    } else {
      percent += 2;
    }

    updatePdfProgressDialog(
      "Generating PDF...",
      percent,
      "Rendering the PDF layout and preparing the download...",
    );
  }, 320);

  return {
    current() {
      return percent;
    },
    stop() {
      window.clearInterval(intervalId);
    },
  };
}

async function responseToBlobWithProgress(response, onProgress) {
  if (!response.body || typeof response.body.getReader !== "function") {
    const blob = await response.blob();

    onProgress(blob.size, blob.size || 0);

    return blob;
  }

  const total = Number.parseInt(response.headers.get("content-length") || "", 10);
  const reader = response.body.getReader();
  const chunks = [];
  let received = 0;

  while (true) {
    const { done, value } = await reader.read();

    if (done) {
      break;
    }

    if (!value) {
      continue;
    }

    chunks.push(value);
    received += value.byteLength;
    onProgress(received, Number.isFinite(total) ? total : 0);
  }

  return new Blob(chunks, {
    type: response.headers.get("content-type") || "application/pdf",
  });
}

function rangeNodes(startNode, endNode) {
  const nodes = [];

  for (let node = startNode?.nextSibling; node && node !== endNode; node = node.nextSibling) {
    nodes.push(node);
  }

  return nodes;
}

function replaceManagedHeadStyles(nextDocument) {
  const currentStart = document.head.querySelector("meta[data-print-workspace-styles-start]");
  const currentEnd = document.head.querySelector("meta[data-print-workspace-styles-end]");
  const nextStart = nextDocument.head.querySelector("meta[data-print-workspace-styles-start]");
  const nextEnd = nextDocument.head.querySelector("meta[data-print-workspace-styles-end]");

  if (!currentStart || !currentEnd || !nextStart || !nextEnd) {
    return;
  }

  rangeNodes(currentStart, currentEnd).forEach((node) => node.remove());

  const fragment = document.createDocumentFragment();
  rangeNodes(nextStart, nextEnd).forEach((node) => {
    fragment.appendChild(document.importNode(node, true));
  });

  currentEnd.parentNode?.insertBefore(fragment, currentEnd);

  if (nextDocument.title) {
    document.title = nextDocument.title;
  }
}

function setBusyState(workspace, submitButton, loadingLabel) {
  const originalButtonHtml = submitButton?.innerHTML ?? null;

  workspace?.classList.add("is-preview-loading");

  if (submitButton) {
    submitButton.disabled = true;
    submitButton.setAttribute("aria-busy", "true");
    submitButton.innerHTML = loadingLabel;
  }

  return () => {
    workspace?.classList.remove("is-preview-loading");

    if (submitButton) {
      submitButton.disabled = false;
      submitButton.removeAttribute("aria-busy");

      if (originalButtonHtml !== null) {
        submitButton.innerHTML = originalButtonHtml;
      }
    }
  };
}

async function downloadPdf(url, messages, fallbackFilename) {
  if (!url) {
    return;
  }

  const estimatedProgress = startEstimatedPdfProgress(messages);

  Swal.fire({
    title: "Preparing PDF...",
    html: `
      <div class="text-start">
        <div class="flex items-center justify-between gap-4 text-sm font-medium text-defaulttextcolor/80">
          <span data-pdf-progress-stage>Generating PDF...</span>
          <span data-pdf-progress-percent>0%</span>
        </div>
        <div class="mt-3 h-2 overflow-hidden rounded-full bg-light">
          <div data-pdf-progress-bar class="h-full rounded-full bg-primary transition-all duration-300" style="width: 0%;"></div>
        </div>
        <p data-pdf-progress-detail class="mt-3 text-xs text-muted">${messages.preparing}</p>
      </div>
    `,
    allowOutsideClick: false,
    allowEscapeKey: false,
    didOpen: () => {
      Swal.showLoading();
      updatePdfProgressDialog(
        "Generating PDF...",
        estimatedProgress.current(),
        messages.preparing,
      );
    },
  });

  try {
    const response = await fetch(url, {
      method: "GET",
      headers: {
        "X-Requested-With": "XMLHttpRequest",
      },
      credentials: "same-origin",
    });

    if (!response.ok) {
      throw new Error(messages.failed);
    }

    estimatedProgress.stop();
    updatePdfProgressDialog(
      "Downloading PDF...",
      estimatedProgress.current(),
      "The PDF is ready. Downloading it to your browser now...",
    );

    const downloadStartPercent = Math.max(estimatedProgress.current(), 82);
    const blob = await responseToBlobWithProgress(response, (received, total) => {
      if (total > 0) {
        const ratio = Math.min(received / total, 1);
        const percent = downloadStartPercent + ((100 - downloadStartPercent) * ratio);

        updatePdfProgressDialog(
          "Downloading PDF...",
          percent,
          `${formatBytes(received)} of ${formatBytes(total)} downloaded`,
        );

        return;
      }

      updatePdfProgressDialog(
        "Downloading PDF...",
        96,
        `${formatBytes(received)} downloaded`,
      );
    });

    updatePdfProgressDialog(
      "Finishing download...",
      100,
      `${formatBytes(blob.size)} ready`,
    );

    const objectUrl = window.URL.createObjectURL(blob);
    const link = document.createElement("a");
    link.href = objectUrl;
    link.download = parseFilename(response, fallbackFilename);
    document.body.appendChild(link);
    link.click();
    link.remove();
    window.URL.revokeObjectURL(objectUrl);

    await Swal.fire({
      icon: "success",
      title: "PDF ready",
      text: messages.ready,
      timer: 1400,
      showConfirmButton: false,
    });
  } catch (error) {
    estimatedProgress.stop();

    await Swal.fire({
      icon: "error",
      title: "Download failed",
      text: error instanceof Error ? error.message : messages.failed,
    });
  }
}

async function refreshPreview(form, config) {
  const workspace = form.closest("[data-print-workspace]");
  const submitButton = form.querySelector('button[type="submit"]');
  const currentPreview = workspace?.querySelector("[data-print-workspace-preview]");
  const currentSidebarScroller =
    workspace?.querySelector(".core-print-sidebar__form") ||
    workspace?.querySelector("[data-print-workspace-sidebar]");
  const previewScrollTop = currentPreview?.scrollTop ?? 0;
  const previewScrollLeft = currentPreview?.scrollLeft ?? 0;
  const sidebarScrollTop = currentSidebarScroller?.scrollTop ?? 0;
  const restoreState = setBusyState(
    workspace,
    submitButton,
    config.previewMessages.loadingLabel,
  );

  try {
    const url = buildUrlWithForm(form.getAttribute("action") || window.location.href, form);
    const response = await fetch(url, {
      method: "GET",
      headers: {
        "X-Requested-With": "XMLHttpRequest",
      },
      credentials: "same-origin",
    });

    if (!response.ok) {
      throw new Error(config.previewMessages.failed);
    }

    const html = await response.text();
    const nextDocument = new DOMParser().parseFromString(html, "text/html");
    const nextWorkspace = nextDocument.querySelector("[data-print-workspace]");
    const currentWorkspace = document.querySelector("[data-print-workspace]");

    if (!nextWorkspace || !currentWorkspace) {
      throw new Error(config.previewMessages.failed);
    }

    replaceManagedHeadStyles(nextDocument);
    currentWorkspace.replaceWith(nextWorkspace);
    window.history.replaceState({}, "", url);

    const nextPreview = nextWorkspace.querySelector("[data-print-workspace-preview]");
    const nextSidebarScroller =
      nextWorkspace.querySelector(".core-print-sidebar__form") ||
      nextWorkspace.querySelector("[data-print-workspace-sidebar]");

    if (nextPreview) {
      nextPreview.scrollTop = previewScrollTop;
      nextPreview.scrollLeft = previewScrollLeft;
    }

    if (nextSidebarScroller) {
      nextSidebarScroller.scrollTop = sidebarScrollTop;
    }

    if (window.matchMedia("(max-width: 1180px)").matches) {
      nextPreview?.scrollIntoView({ behavior: "smooth", block: "start" });
    }

    initPrintWorkspaceController(config);
  } catch (error) {
    restoreState();

    await Swal.fire({
      icon: "error",
      title: "Preview failed",
      text: error instanceof Error ? error.message : config.previewMessages.failed,
    });
  }
}

export function initPrintWorkspaceController(config) {
  const form = document.querySelector(config.formSelector);

  if (!form || form.dataset.printWorkspaceBound === "1") {
    return;
  }

  form.dataset.printWorkspaceBound = "1";

  const paperSelect = form.querySelector(config.paperSelectSelector);
  const defaultsButton = form.querySelector(config.defaultsButtonSelector);
  const defaultsMap = parseDefaultsMap(form, config.defaultsAttribute);

  const applyDefaultsForPaper = (paperCode) => {
    if (!paperCode || !defaultsMap?.[paperCode]) {
      return;
    }

    Object.entries(defaultsMap[paperCode]).forEach(([key, value]) => {
      const input = form.querySelector(
        config.settingSelector.replace("{key}", key),
      );

      if (!input || value === null || value === undefined) {
        return;
      }

      input.value = String(value);
    });
  };

  paperSelect?.addEventListener("change", (event) => {
    applyDefaultsForPaper(event.target.value);
  });

  defaultsButton?.addEventListener("click", () => {
    applyDefaultsForPaper(paperSelect?.value);
  });

  form.addEventListener("submit", async (event) => {
    event.preventDefault();
    await refreshPreview(form, config);
  });

  const pdfLinks = document.querySelectorAll(config.pdfLinkSelector);

  pdfLinks.forEach((link) => {
    link.addEventListener("click", async (event) => {
      event.preventDefault();

      const baseUrl =
        link.getAttribute(config.pdfBaseAttribute) || link.getAttribute("href") || "";

      await downloadPdf(
        buildUrlWithForm(baseUrl, form),
        config.pdfMessages,
        config.fallbackFilename,
      );
    });
  });

  if (typeof config.onInit === "function") {
    config.onInit({ form, paperSelect, defaultsButton });
  }
}
