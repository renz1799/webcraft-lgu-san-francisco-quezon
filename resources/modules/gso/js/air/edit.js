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

  function qs(id) {
    return document.getElementById(id);
  }

  function clearErrors() {
    const formError = qs("gsoAirFormError");
    if (formError) {
      formError.classList.add("hidden");
      formError.textContent = "";
    }

    [
      "gsoAirPoNumberErr",
      "gsoAirPoDateErr",
      "gsoAirAirNumberErr",
      "gsoAirAirDateErr",
      "gsoAirInvoiceNumberErr",
      "gsoAirInvoiceDateErr",
      "gsoAirSupplierNameErr",
      "gsoAirDepartmentIdErr",
      "gsoAirFundSourceIdErr",
      "gsoAirInspectedByNameErr",
      "gsoAirAcceptedByNameErr",
      "gsoAirRemarksErr",
    ].forEach((id) => {
      const element = qs(id);
      if (!element) return;
      element.classList.add("hidden");
      element.textContent = "";
    });
  }

  function showFormError(message) {
    const formError = qs("gsoAirFormError");
    if (!formError) return;
    formError.textContent = message || "Something went wrong.";
    formError.classList.remove("hidden");
  }

  function applyValidationErrors(errors) {
    const map = {
      po_number: "gsoAirPoNumberErr",
      po_date: "gsoAirPoDateErr",
      air_number: "gsoAirAirNumberErr",
      air_date: "gsoAirAirDateErr",
      invoice_number: "gsoAirInvoiceNumberErr",
      invoice_date: "gsoAirInvoiceDateErr",
      supplier_name: "gsoAirSupplierNameErr",
      requesting_department_id: "gsoAirDepartmentIdErr",
      fund_source_id: "gsoAirFundSourceIdErr",
      inspected_by_name: "gsoAirInspectedByNameErr",
      accepted_by_name: "gsoAirAcceptedByNameErr",
      remarks: "gsoAirRemarksErr",
      status: "gsoAirFormError",
      air: "gsoAirFormError",
    };

    Object.entries(map).forEach(([field, elementId]) => {
      const message = errors?.[field]?.[0];
      const element = qs(elementId);
      if (!message || !element) return;

      if (elementId === "gsoAirFormError") {
        showFormError(message);
        return;
      }

      element.textContent = String(message);
      element.classList.remove("hidden");
    });
  }

  async function parseResponse(response, fallbackMessage) {
    const contentType = response.headers.get("content-type") || "";
    const data = contentType.includes("application/json")
      ? await response.json().catch(() => null)
      : null;

    return {
      ok: response.ok,
      status: response.status,
      data,
      message:
        data?.message ||
        data?.error ||
        fallbackMessage ||
        "The request could not be completed.",
    };
  }

  function payload() {
    return {
      po_number: (qs("gsoAirPoNumber")?.value || "").trim(),
      po_date: (qs("gsoAirPoDate")?.value || "").trim(),
      air_number: (qs("gsoAirAirNumber")?.value || "").trim(),
      air_date: (qs("gsoAirAirDate")?.value || "").trim(),
      invoice_number: (qs("gsoAirInvoiceNumber")?.value || "").trim(),
      invoice_date: (qs("gsoAirInvoiceDate")?.value || "").trim(),
      supplier_name: (qs("gsoAirSupplierName")?.value || "").trim(),
      requesting_department_id: (qs("gsoAirDepartmentId")?.value || "").trim(),
      fund_source_id: (qs("gsoAirFundSourceId")?.value || "").trim(),
      inspected_by_name: (qs("gsoAirInspectedByName")?.value || "").trim(),
      accepted_by_name: (qs("gsoAirAcceptedByName")?.value || "").trim(),
      remarks: (qs("gsoAirRemarks")?.value || "").trim(),
    };
  }

  async function sendJson(url, method, body, fallbackMessage, handleValidation = true) {
    const config = window.__gsoAirEdit || {};
    const response = await fetch(url, {
      method,
      headers: {
        Accept: "application/json",
        "Content-Type": "application/json",
        "X-CSRF-TOKEN": config.csrf || "",
      },
      body: body ? JSON.stringify(body) : null,
    });

    const parsed = await parseResponse(response, fallbackMessage);

    if (handleValidation && parsed.status === 422) {
      clearErrors();
      applyValidationErrors(parsed.data?.errors || {});
      showFormError(parsed.message);
      return null;
    }

    if (!parsed.ok) {
      throw new Error(parsed.message);
    }

    return parsed.data || {};
  }

  async function saveDirtyAirItems() {
    const manager = window.__gsoAirItems;

    if (!manager?.saveDirtyRows) {
      return true;
    }

    return manager.saveDirtyRows({ showSuccess: false });
  }

  async function reloadAirFiles() {
    const manager = window.__gsoAirFiles;

    if (!manager?.reload) {
      return;
    }

    try {
      await manager.reload();
    } catch (error) {
      showFormError(
        error instanceof Error
          ? error.message
          : "The AIR header saved, but the document panel could not be refreshed."
      );
    }
  }

  onReady(function () {
    const page = qs("gso-air-edit-page");
    if (!page) return;

    const config = window.__gsoAirEdit || {};
    const saveButton = qs("gsoAirSaveBtn");
    const submitButton = qs("gsoAirSubmitBtn");
    const archiveButton = qs("gsoAirArchiveBtn");
    const restoreButton = qs("gsoAirRestoreBtn");
    const forceDeleteButton = qs("gsoAirForceDeleteBtn");

    if (saveButton) {
      saveButton.addEventListener("click", async () => {
        clearErrors();

        const confirmation = await Swal.fire({
          icon: "question",
          title: "Save draft?",
          showCancelButton: true,
          confirmButtonText: "Save",
          cancelButtonText: "Cancel",
        });

        if (!confirmation.isConfirmed) return;

        try {
          await sendJson(config.updateUrl, "PUT", payload(), "The AIR draft could not be saved.");
          const itemsSaved = await saveDirtyAirItems();
          await reloadAirFiles();
          if (!itemsSaved) {
            showFormError("The AIR header was saved, but some AIR item row changes still need attention.");
            return;
          }
          await Swal.fire({
            icon: "success",
            title: "Draft Saved",
            timer: 900,
            showConfirmButton: false,
          });
        } catch (error) {
          showFormError(error instanceof Error ? error.message : "Save failed.");
        }
      });
    }

    if (submitButton) {
      submitButton.addEventListener("click", async () => {
        clearErrors();

        const confirmation = await Swal.fire({
          icon: "question",
          title: "Submit AIR?",
          text: "This will move the AIR out of draft status.",
          showCancelButton: true,
          confirmButtonText: "Submit",
          cancelButtonText: "Cancel",
        });

        if (!confirmation.isConfirmed) return;

        try {
          await sendJson(config.updateUrl, "PUT", payload(), "The AIR draft could not be saved.");
          const itemsSaved = await saveDirtyAirItems();
          if (!itemsSaved) {
            showFormError("Save the AIR item row changes first before submitting this draft.");
            return;
          }
          await sendJson(config.submitUrl, "PUT", {}, "The AIR could not be submitted.");
          await Swal.fire({
            icon: "success",
            title: "AIR Submitted",
            timer: 1200,
            showConfirmButton: false,
          });
          window.location.href = config.editUrl || window.location.href;
        } catch (error) {
          showFormError(error instanceof Error ? error.message : "Submit failed.");
        }
      });
    }

    if (archiveButton) {
      archiveButton.addEventListener("click", async () => {
        const confirmation = await Swal.fire({
          icon: "warning",
          title: "Archive AIR?",
          showCancelButton: true,
          confirmButtonText: "Archive",
          cancelButtonText: "Cancel",
        });

        if (!confirmation.isConfirmed) return;

        try {
          await sendJson(config.deleteUrl, "DELETE", null, "The AIR could not be archived.", false);
          await Swal.fire({
            icon: "success",
            title: "Archived",
            timer: 900,
            showConfirmButton: false,
          });
          window.location.href = config.indexUrl || "/";
        } catch (error) {
          showFormError(error instanceof Error ? error.message : "Archive failed.");
        }
      });
    }

    if (restoreButton) {
      restoreButton.addEventListener("click", async () => {
        const confirmation = await Swal.fire({
          icon: "question",
          title: "Restore AIR?",
          showCancelButton: true,
          confirmButtonText: "Restore",
          cancelButtonText: "Cancel",
        });

        if (!confirmation.isConfirmed) return;

        try {
          await sendJson(config.restoreUrl, "PATCH", null, "The AIR could not be restored.", false);
          await Swal.fire({
            icon: "success",
            title: "Restored",
            timer: 900,
            showConfirmButton: false,
          });
          window.location.href = config.editUrl || window.location.href;
        } catch (error) {
          showFormError(error instanceof Error ? error.message : "Restore failed.");
        }
      });
    }

    if (forceDeleteButton) {
      forceDeleteButton.addEventListener("click", async () => {
        const confirmation = await Swal.fire({
          icon: "warning",
          title: "Force delete AIR?",
          text: "This permanently removes the AIR record.",
          showCancelButton: true,
          confirmButtonText: "Delete Permanently",
          cancelButtonText: "Cancel",
        });

        if (!confirmation.isConfirmed) return;

        try {
          await sendJson(config.forceDeleteUrl, "DELETE", null, "The AIR could not be permanently deleted.", false);
          await Swal.fire({
            icon: "success",
            title: "Deleted",
            timer: 900,
            showConfirmButton: false,
          });
          window.location.href = config.indexUrl || "/";
        } catch (error) {
          showFormError(error instanceof Error ? error.message : "Delete failed.");
        }
      });
    }
  });
})();
