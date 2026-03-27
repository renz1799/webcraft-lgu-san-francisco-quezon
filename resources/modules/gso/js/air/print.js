import Swal from "sweetalert2";
import "sweetalert2/dist/sweetalert2.min.css";

function buildUrlWithForm(anchor, form) {
  const baseUrl =
    anchor.getAttribute("data-air-print-pdf-base") || anchor.getAttribute("href");

  if (!baseUrl || !form) {
    return baseUrl || "";
  }

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

function parseFilename(response, fallback = "air-report.pdf") {
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

async function downloadPdf(url) {
  if (!url) return;

  Swal.fire({
    title: "Preparing PDF...",
    text: "Generating the AIR print file. Please wait.",
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
      throw new Error("The AIR PDF could not be generated right now.");
    }

    const blob = await response.blob();
    const objectUrl = window.URL.createObjectURL(blob);
    const link = document.createElement("a");
    link.href = objectUrl;
    link.download = parseFilename(response);
    document.body.appendChild(link);
    link.click();
    link.remove();
    window.URL.revokeObjectURL(objectUrl);

    await Swal.fire({
      icon: "success",
      title: "PDF ready",
      text: "The AIR PDF download has started.",
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
          : "The AIR PDF could not be downloaded.",
    });
  }
}

document.addEventListener("DOMContentLoaded", () => {
  const form = document.querySelector("[data-air-print-form]");
  const paperSelect = form?.querySelector("[data-air-print-paper-select]");
  const defaultsButton = form?.querySelector("[data-air-print-apply-defaults]");
  const defaultsMap = (() => {
    const raw = form?.getAttribute("data-air-print-paper-defaults") || "{}";

    try {
      return JSON.parse(raw);
    } catch {
      return {};
    }
  })();

  const applyDefaultsForPaper = (paperCode) => {
    if (!form || !paperCode || !defaultsMap?.[paperCode]) {
      return;
    }

    const defaults = defaultsMap[paperCode];

    Object.entries(defaults).forEach(([key, value]) => {
      const input = form.querySelector(`[data-air-print-setting="${key}"]`);
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

  const pdfLinks = document.querySelectorAll("[data-air-print-pdf-download]");

  pdfLinks.forEach((link) => {
    link.addEventListener("click", async (event) => {
      event.preventDefault();
      await downloadPdf(buildUrlWithForm(link, form));
    });
  });
});
