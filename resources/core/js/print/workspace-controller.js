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

async function extractErrorMessage(response, fallback) {
  const contentType = (response.headers.get("content-type") || "").toLowerCase();

  try {
    if (contentType.includes("application/json")) {
      const payload = await response.json();
      const validationMessage = extractJsonValidationMessage(payload);

      if (validationMessage !== "") {
        return validationMessage;
      }

      const message = typeof payload?.message === "string" ? payload.message.trim() : "";

      if (message !== "") {
        return message;
      }
    }

    const text = (await response.text()).trim();

    if (text !== "" && !text.startsWith("<!DOCTYPE html") && !text.startsWith("<html")) {
      return text;
    }
  } catch {
    // Fall back to the caller-provided message when the response body is not readable.
  }

  return fallback;
}

function extractJsonValidationMessage(payload) {
  if (!payload || typeof payload !== "object") {
    return "";
  }

  const errors = payload.errors;

  if (!errors || typeof errors !== "object") {
    return "";
  }

  for (const key of Object.keys(errors)) {
    const entries = errors[key];

    if (Array.isArray(entries) && typeof entries[0] === "string" && entries[0].trim() !== "") {
      return entries[0].trim();
    }
  }

  return "";
}

function csrfToken() {
  return document
    .querySelector('meta[name="csrf-token"]')
    ?.getAttribute("content")
    ?.trim();
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
      throw new Error(await extractErrorMessage(response, messages.failed));
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

function buildUploadFormData(form, file) {
  const payload = new FormData();
  const formData = new FormData(form);

  for (const [key, rawValue] of formData.entries()) {
    if (rawValue instanceof File) {
      continue;
    }

    const value = String(rawValue || "").trim();

    if (value === "") {
      continue;
    }

    payload.append(key, value);
  }

  payload.append("signed_pdf", file, file.name);

  return payload;
}

function escapeHtml(value) {
  return String(value ?? "")
    .replaceAll("&", "&amp;")
    .replaceAll("<", "&lt;")
    .replaceAll(">", "&gt;")
    .replaceAll('"', "&quot;")
    .replaceAll("'", "&#039;");
}

function buildArchiveViewerUrl(url) {
  if (!url) {
    return "";
  }

  const [base, hash = ""] = String(url).split("#", 2);
  const params = new URLSearchParams(hash);

  if (!params.has("toolbar")) {
    params.set("toolbar", "0");
  }

  if (!params.has("navpanes")) {
    params.set("navpanes", "0");
  }

  if (!params.has("scrollbar")) {
    params.set("scrollbar", "0");
  }

  if (!params.has("zoom")) {
    params.set("zoom", "page-width");
  }

  return `${base}#${params.toString()}`;
}

function renderArchiveStatus(archive) {
  const fileName = escapeHtml(archive?.file_name || "Signed document.pdf");
  const folderPath = escapeHtml(archive?.folder_path || "the configured document folder");
  const createdTime = String(archive?.created_time || "").trim();

  return `
    Signed PDF available as <span class="font-medium">${fileName}</span> under
    <span class="font-medium">${folderPath}</span>.
    ${createdTime !== "" ? `<span class="block mt-1 text-muted">Uploaded ${escapeHtml(createdTime)}</span>` : ""}
  `;
}

function updateArchiveControls(triggerButton, archive) {
  const controls = triggerButton.closest("[data-print-archive-controls]");

  if (!controls) {
    return;
  }

  controls.dataset.printArchiveState = "uploaded";

  const viewButton = controls.querySelector("[data-print-archive-view]");
  const uploadButton = controls.querySelector("[data-print-archive-upload]");
  const statusNode = controls.querySelector("[data-print-archive-status]");

  if (viewButton) {
    viewButton.classList.remove("hidden");
    viewButton.disabled = false;
  }

  if (uploadButton) {
    uploadButton.classList.remove("ti-btn-outline-success");
    uploadButton.classList.add("ti-btn-outline-warning", "mt-2");
    uploadButton.innerHTML = `
      <i class="ri-upload-cloud-2-line label-ti-btn-icon me-2"></i>
      Replace Signed PDF
    `;
  }

  if (statusNode) {
    statusNode.classList.remove("text-muted");
    statusNode.classList.add("text-success");
    statusNode.innerHTML = renderArchiveStatus(archive);
  }
}

async function promptForSignedPdf(button) {
  const documentType = button.dataset.printArchiveDocumentType || "Document";
  const documentNumber = button.dataset.printArchiveDocumentNumber || "DOCUMENT";
  const result = await Swal.fire({
    title: `Upload signed ${documentType} PDF`,
    html: `
      <div class="text-start">
        <p class="text-sm text-muted mb-3">
          Select the scanned signed PDF. It will be stored as
          <strong>${escapeHtml(documentNumber)}.pdf</strong>.
        </p>
        <input
          id="swal-signed-pdf-input"
          type="file"
          accept="application/pdf,.pdf"
          class="swal2-file"
        >
      </div>
    `,
    showCancelButton: true,
    confirmButtonText: "Upload Signed PDF",
    focusConfirm: false,
    preConfirm: () => {
      const input = document.getElementById("swal-signed-pdf-input");
      const file = input?.files?.[0];

      if (!file) {
        Swal.showValidationMessage("Select the scanned signed PDF to upload.");
        return false;
      }

      const fileName = String(file.name || "").toLowerCase();
      const isPdf = file.type === "application/pdf" || fileName.endsWith(".pdf");

      if (!isPdf) {
        Swal.showValidationMessage("Only PDF files can be uploaded as signed documents.");
        return false;
      }

      if (Number(file.size || 0) > 10 * 1024 * 1024) {
        Swal.showValidationMessage("The signed PDF must be 10 MB or smaller.");
        return false;
      }

      return file;
    },
  });

  return result.isConfirmed ? result.value : null;
}

async function uploadSignedPdf(url, form, file, button) {
  const documentType = button.dataset.printArchiveDocumentType || "document";
  const token = csrfToken();

  Swal.fire({
    title: `Uploading signed ${documentType} PDF...`,
    text: "Please wait while the scanned file is stored in Google Drive.",
    allowOutsideClick: false,
    allowEscapeKey: false,
    didOpen: () => Swal.showLoading(),
  });

  try {
    const response = await fetch(url, {
      method: "POST",
      headers: {
        Accept: "application/json",
        "X-Requested-With": "XMLHttpRequest",
        ...(token ? { "X-CSRF-TOKEN": token } : {}),
      },
      body: buildUploadFormData(form, file),
      credentials: "same-origin",
    });

    if (!response.ok) {
      throw new Error(
        await extractErrorMessage(
          response,
          `The signed ${documentType} PDF could not be uploaded right now.`,
        ),
      );
    }

    const payload = await response.json();
    const archive = payload?.archive || {};
    const replaced = Boolean(archive?.replaced_existing);
    updateArchiveControls(button, archive);

    await Swal.fire({
      icon: "success",
      title: "Signed PDF uploaded",
      html: `
        <div class="text-start text-sm">
          <div><strong>${escapeHtml(archive.file_name || file.name)}</strong> was stored under <strong>${escapeHtml(archive.folder_path || "the configured document folder")}</strong>.</div>
          ${replaced ? '<div class="mt-2">An older signed PDF with the same document number was replaced.</div>' : ""}
        </div>
      `,
    });
  } catch (error) {
    await Swal.fire({
      icon: "error",
      title: "Upload failed",
      text:
        error instanceof Error
          ? error.message
          : `The signed ${documentType} PDF could not be uploaded right now.`,
    });
  }
}

async function downloadSignedArchivePdf(viewUrl, documentType, documentNumber) {
  if (!viewUrl) {
    return;
  }

  Swal.fire({
    title: `Downloading signed ${documentType} PDF...`,
    text: "Please wait while the archived scan is prepared.",
    allowOutsideClick: false,
    allowEscapeKey: false,
    didOpen: () => Swal.showLoading(),
  });

  try {
    const response = await fetch(viewUrl, {
      method: "GET",
      headers: {
        "X-Requested-With": "XMLHttpRequest",
      },
      credentials: "same-origin",
    });

    if (!response.ok) {
      throw new Error(
        await extractErrorMessage(
          response,
          `The signed ${documentType} PDF could not be downloaded right now.`,
        ),
      );
    }

    const blob = await response.blob();
    const objectUrl = window.URL.createObjectURL(blob);
    const link = document.createElement("a");
    link.href = objectUrl;
    link.download = parseFilename(response, `${documentNumber}.pdf`);
    document.body.appendChild(link);
    link.click();
    link.remove();
    window.URL.revokeObjectURL(objectUrl);

    await Swal.fire({
      icon: "success",
      title: "Download ready",
      text: `${documentNumber}.pdf has been sent to your browser.`,
      timer: 1400,
      showConfirmButton: false,
    });
  } catch (error) {
    await Swal.fire({
      icon: "error",
      title: "Download failed",
      text:
        error instanceof Error
          ? error.message
          : `The signed ${documentType} PDF could not be downloaded right now.`,
    });
  }
}

async function previewSignedPdf(button, form) {
  const viewUrl = button.dataset.printArchiveViewUrl || "";
  const documentType = button.dataset.printArchiveDocumentType || "document";
  const documentNumber = button.dataset.printArchiveDocumentNumber || "DOCUMENT";
  const controls = button.closest("[data-print-archive-controls]");
  const uploadButton = controls?.querySelector("[data-print-archive-upload]");

  if (!viewUrl) {
    return;
  }

  const result = await Swal.fire({
    title: `${documentType} signed PDF`,
    width: "min(92vw, 1100px)",
    html: `
      <div class="text-start">
        <div class="mb-3 text-sm text-muted">
          Previewing <strong>${escapeHtml(documentNumber)}.pdf</strong>.
        </div>
        <div class="rounded border border-defaultborder overflow-hidden bg-bodybg" style="height: min(72vh, 900px);">
          <iframe
            src="${escapeHtml(buildArchiveViewerUrl(viewUrl))}"
            title="${escapeHtml(documentType)} signed PDF preview"
            style="width: 100%; height: 100%; border: 0; background: #2b2b2b;"
          ></iframe>
        </div>
        <div class="mt-3 text-xs text-muted">
          Need to correct the scan? Replace it here and the file in Google Drive will stay named
          <strong>${escapeHtml(documentNumber)}.pdf</strong>.
        </div>
        <div class="mt-3 flex flex-wrap gap-2">
          <button
            type="button"
            class="ti-btn btn-wave ti-btn-outline-primary"
            data-print-archive-download="1"
          >
            <i class="ri-download-2-line me-2"></i>
            Download PDF
          </button>
        </div>
      </div>
    `,
    showCancelButton: true,
    cancelButtonText: "Close",
    showConfirmButton: Boolean(uploadButton),
    confirmButtonText: "Replace PDF",
    focusConfirm: false,
    didOpen: () => {
      const downloadButton = Swal.getHtmlContainer()?.querySelector(
        "[data-print-archive-download]",
      );

      downloadButton?.addEventListener("click", async () => {
        await downloadSignedArchivePdf(viewUrl, documentType, documentNumber);
      });
    },
  });

  if (!result.isConfirmed || !uploadButton) {
    return;
  }

  const file = await promptForSignedPdf(uploadButton);

  if (!file) {
    return;
  }

  await uploadSignedPdf(
    uploadButton.getAttribute("data-print-archive-url") || "",
    form,
    file,
    uploadButton,
  );
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
      throw new Error(
        await extractErrorMessage(response, config.previewMessages.failed),
      );
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

  const archiveButtons = document.querySelectorAll(
    config.archiveButtonSelector || "[data-print-archive-upload]",
  );

  archiveButtons.forEach((button) => {
    button.addEventListener("click", async (event) => {
      event.preventDefault();

      if (button.disabled) {
        return;
      }

      const archiveUrl = button.getAttribute("data-print-archive-url") || "";

      if (!archiveUrl) {
        return;
      }

      const file = await promptForSignedPdf(button);

      if (!file) {
        return;
      }

      await uploadSignedPdf(archiveUrl, form, file, button);
    });
  });

  const archiveViewButtons = document.querySelectorAll("[data-print-archive-view]");

  archiveViewButtons.forEach((button) => {
    button.addEventListener("click", async (event) => {
      event.preventDefault();

      if (button.disabled || button.classList.contains("hidden")) {
        return;
      }

      await previewSignedPdf(button, form);
    });
  });

  if (typeof config.onInit === "function") {
    config.onInit({ form, paperSelect, defaultsButton });
  }
}
