import Swal from "sweetalert2";
import "sweetalert2/dist/sweetalert2.min.css";

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

async function downloadPdf(anchor) {
  const url = anchor.getAttribute("href");
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
  const pdfLinks = document.querySelectorAll("[data-air-print-pdf-download]");

  pdfLinks.forEach((link) => {
    link.addEventListener("click", async (event) => {
      event.preventDefault();
      await downloadPdf(link);
    });
  });
});
