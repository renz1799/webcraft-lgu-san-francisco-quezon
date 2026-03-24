import Swal from "sweetalert2";
import "sweetalert2/dist/sweetalert2.min.css";

(function () {
  "use strict";

  function onReady(fn) {
    if (document.readyState === "loading") {
      document.addEventListener("DOMContentLoaded", fn);
      return;
    }

    fn();
  }

  function getCsrf() {
    return (
      document.querySelector('meta[name="csrf-token"]')?.content ||
      window.__gsoAirInspection?.csrf ||
      ""
    );
  }

  function safeOpen(url) {
    if (!url) return;

    const opened = window.open(url, "_blank", "noopener");
    if (opened) return;

    Swal.fire({
      icon: "info",
      title: "Popup blocked",
      html: `Your browser blocked the new tab.<br>Please click this link:<br><a href="${url}" target="_blank" rel="noopener">Open RIS Editor</a>`,
    });
  }

  async function parseErrorResponse(response) {
    const contentType = response.headers.get("content-type") || "";
    const data = contentType.includes("application/json")
      ? await response.json().catch(() => null)
      : null;

    const message =
      data?.message ||
      (response.status === 401
        ? "Session expired. Please log in again."
        : response.status === 403
        ? "You do not have permission to do this."
        : response.status === 409
        ? "This AIR is not eligible for RIS generation."
        : "Request failed.");

    return { data, message };
  }

  onReady(function () {
    const button = document.getElementById("airGenerateRisBtn");
    if (!button) return;

    button.addEventListener("click", async function (event) {
      event.preventDefault();

      const endpoint = button.dataset.endpoint;
      const mode = String(button.dataset.mode || "generate").toLowerCase();

      if (!endpoint) return;

      if (mode === "view") {
        safeOpen(endpoint);
        return;
      }

      const confirmResult = await Swal.fire({
        title: "Generate RIS?",
        text: "This will create a Requisition and Issue Slip from the consumable items in this AIR.",
        icon: "question",
        showCancelButton: true,
        confirmButtonText: "Yes, Generate",
        cancelButtonText: "Cancel",
        reverseButtons: true,
      });

      if (!confirmResult.isConfirmed) return;

      button.disabled = true;

      Swal.fire({
        title: "Generating RIS...",
        text: "Please wait...",
        allowOutsideClick: false,
        allowEscapeKey: false,
        didOpen: () => Swal.showLoading(),
      });

      try {
        const response = await fetch(endpoint, {
          method: "POST",
          headers: {
            "X-CSRF-TOKEN": getCsrf(),
            Accept: "application/json",
            "X-Requested-With": "XMLHttpRequest",
          },
          credentials: "same-origin",
        });

        if (!response.ok) {
          const { message } = await parseErrorResponse(response);
          Swal.close();

          await Swal.fire({
            icon: "error",
            title: "Failed",
            text: message,
          });

          button.disabled = false;
          return;
        }

        const payload = await response.json().catch(() => null);
        Swal.close();

        await Swal.fire({
          icon: "success",
          title: "RIS Generated",
          text: "Opening RIS editor in a new tab...",
          timer: 1200,
          showConfirmButton: false,
        });

        safeOpen(payload?.redirect_url);
      } catch (error) {
        console.error(error);
        Swal.close();

        await Swal.fire({
          icon: "error",
          title: "Error",
          text: "Something went wrong while generating the RIS.",
        });

        button.disabled = false;
      }
    });
  });
})();
