import Swal from "sweetalert2";
import "sweetalert2/dist/sweetalert2.min.css";

function buildUrlWithForm(baseUrl, form) {
  const url = new URL(baseUrl, window.location.origin);
  const formData = new FormData(form);

  for (const [key, rawValue] of formData.entries()) {
    const value = String(rawValue || "").trim();

    if (value === "") {
      url.searchParams.delete(key);
      continue;
    }

    url.searchParams.set(key, value);
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

  Swal.fire({
    title: "Preparing PDF...",
    text: messages.preparing,
    allowOutsideClick: false,
    allowEscapeKey: false,
    didOpen: () => {
      Swal.showLoading();
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

    const blob = await response.blob();
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
