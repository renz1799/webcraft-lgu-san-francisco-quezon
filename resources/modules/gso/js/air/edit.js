import Swal from "sweetalert2";
import "sweetalert2/dist/sweetalert2.min.css";
import { attachAccountableOfficerAutocomplete } from "../accountable-officers/autocomplete.js";

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

  function getForm() {
    return qs("gsoAirEditForm");
  }

  function attachErrorStyleOnce() {
    if (document.getElementById("gsoAirFieldErrorStyle")) {
      return;
    }

    const style = document.createElement("style");
    style.id = "gsoAirFieldErrorStyle";
    style.textContent = `
      .gso-air-field-error {
        border-color: #ef4444 !important;
        box-shadow: 0 0 0 1px rgba(239, 68, 68, 0.35) !important;
      }
    `;
    document.head.appendChild(style);
  }

  function formPayload(form) {
    const formData = new FormData(form);
    const payload = {};

    for (const [key, value] of formData.entries()) {
      if (key === "_token") continue;
      payload[key] = typeof value === "string" ? value.trim() : value;
    }

    payload.invoice_number = (qs("gsoAirInvoiceNumber")?.value || "").trim();
    payload.invoice_date = (qs("gsoAirInvoiceDate")?.value || "").trim();

    return payload;
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

    document
      .querySelectorAll(".gso-air-field-error")
      .forEach((node) => node.classList.remove("gso-air-field-error"));
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

    const fieldNodes = {
      po_number: qs("gsoAirPoNumber"),
      po_date: qs("gsoAirPoDate"),
      air_number: qs("gsoAirAirNumber"),
      air_date: qs("gsoAirAirDate"),
      invoice_number: qs("gsoAirInvoiceNumber"),
      invoice_date: qs("gsoAirInvoiceDate"),
      supplier_name: qs("gsoAirSupplierName"),
      requesting_department_id: qs("gsoAirDepartmentId"),
      fund_source_id: qs("gsoAirFundSourceId"),
      inspected_by_name: qs("gsoAirInspectedByName"),
      accepted_by_name: qs("gsoAirAcceptedByName"),
      remarks: qs("gsoAirRemarks"),
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
      fieldNodes[field]?.classList.add("gso-air-field-error");
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

  function ensureToolbarState() {
    const existing = window.__gsoAirEditUi;
    if (existing?.__initialized) {
      return existing;
    }

    const state = {
      __initialized: true,
      headerCount: 0,
      itemCount: Math.max(0, Number(window.__gsoAirPendingItemDirtyCount || 0) || 0),
      isSaving: false,
      isSubmitting: false,
      getTotalCount() {
        return (this.headerCount || 0) + (this.itemCount || 0);
      },
      hasChanges() {
        return this.getTotalCount() > 0;
      },
      setHeaderCount(count) {
        this.headerCount = Math.max(0, Number(count) || 0);
        updateToolbarButtons();
      },
      setItemCount(count) {
        this.itemCount = Math.max(0, Number(count) || 0);
        window.__gsoAirPendingItemDirtyCount = this.itemCount;
        updateToolbarButtons();
      },
      setBusy(kind, value) {
        if (kind === "saving") this.isSaving = !!value;
        if (kind === "submitting") this.isSubmitting = !!value;
        updateToolbarButtons();
      },
    };

    window.__gsoAirEditUi = state;
    window.__gsoAirSetItemDirtyCount = (count) => state.setItemCount(count);
    window.__gsoAirGetDirtyCount = () => state.getTotalCount();
    window.__gsoAirHasChanges = () => state.hasChanges();

    return state;
  }

  const toolbarState = ensureToolbarState();
  let headerBaseline = {};

  function updateToolbarButtons() {
    const config = window.__gsoAirEdit || {};
    const saveButton = qs("gsoAirSaveBtn");
    const submitButton = qs("gsoAirSubmitBtn");
    const total = toolbarState.getTotalCount();

    if (saveButton) {
      saveButton.textContent = total > 0 ? `Save (${total})` : "Save";
      saveButton.disabled =
        !!config.isArchived ||
        !config.canEditDraft ||
        toolbarState.isSaving ||
        toolbarState.isSubmitting ||
        total === 0;
    }

    if (submitButton) {
      submitButton.textContent = total > 0 ? "Save and Submit" : "Submit";
      submitButton.disabled =
        !!config.isArchived ||
        !config.canEditDraft ||
        toolbarState.isSaving ||
        toolbarState.isSubmitting;
    }
  }

  function readFormSnapshot() {
    const form = getForm();
    if (!form) return {};
    return formPayload(form);
  }

  function getChangedHeaderFields(current, baseline) {
    const keys = new Set([...Object.keys(current), ...Object.keys(baseline)]);
    const changed = [];

    keys.forEach((key) => {
      const left = current[key] ?? "";
      const right = baseline[key] ?? "";
      if (left !== right) {
        changed.push(key);
      }
    });

    return changed;
  }

  function refreshHeaderDirtyCount() {
    const current = readFormSnapshot();
    const changed = getChangedHeaderFields(current, headerBaseline);
    toolbarState.setHeaderCount(changed.length);
  }

  function resetHeaderBaseline() {
    headerBaseline = readFormSnapshot();
    refreshHeaderDirtyCount();
  }

  function getItemCount() {
    const manager = window.__gsoAirItems;
    if (manager?.getItemCount) {
      return Math.max(0, Number(manager.getItemCount()) || 0);
    }

    return Math.max(0, Number(qs("gsoAirItemCount")?.textContent || 0) || 0);
  }

  function getBlockingItemIssues() {
    const manager = window.__gsoAirItems;
    if (!manager?.getBlockingIssues) {
      return [];
    }

    return manager.getBlockingIssues();
  }

  async function saveDirtyAirItems() {
    const manager = window.__gsoAirItems;
    if (!manager?.saveDirtyRows) {
      return true;
    }

    return manager.saveDirtyRows({ showSuccess: false, showLoading: false });
  }

  async function reloadAirFiles() {
    const manager = window.__gsoAirFiles;
    if (!manager?.reload) return;

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

  async function saveHeaderChanges(options = {}) {
    const config = window.__gsoAirEdit || {};
    const form = getForm();
    if (!form || !config.updateUrl) return false;

    const { showSuccess = true, skipIfClean = false } = options;

    if (skipIfClean && toolbarState.headerCount === 0) {
      return { saved: false, skipped: true };
    }

    clearErrors();

    const parsed = await sendJson(
      config.updateUrl,
      "PUT",
      formPayload(form),
      "The AIR draft could not be saved."
    );

    if (!parsed) {
      return false;
    }

    resetHeaderBaseline();
    await reloadAirFiles();

    if (showSuccess) {
      await Swal.fire({
        icon: "success",
        title: "Saved",
        text: parsed?.message || "Draft saved.",
        timer: 900,
        showConfirmButton: false,
      });
    }

    return { saved: true, parsed };
  }

  async function saveAllChanges(options = {}) {
    const config = window.__gsoAirEdit || {};
    const {
      showSuccess = true,
      showLoading = true,
      loadingTitle = "Saving changes...",
    } = options;

    if (!!config.isArchived || !config.canEditDraft) {
      return false;
    }

    const totalBefore = toolbarState.getTotalCount();
    if (totalBefore === 0) {
      updateToolbarButtons();
      return true;
    }

    toolbarState.setBusy("saving", true);

    if (showLoading) {
      Swal.fire({
        title: loadingTitle,
        allowOutsideClick: false,
        didOpen: () => Swal.showLoading(),
      });
    }

    try {
      const savedParts = [];
      const itemDirtyBeforeSave = Math.max(
        0,
        Number(window.__gsoAirPendingItemDirtyCount || 0) || 0
      );

      const headerResult = await saveHeaderChanges({
        showSuccess: false,
        skipIfClean: true,
      });
      if (headerResult === false) {
        return false;
      }
      if (headerResult?.saved) {
        savedParts.push("draft");
      }

      const itemResult = await saveDirtyAirItems();
      if (itemResult === false) {
        showFormError("Some AIR item row changes still need attention before they can be saved.");
        return false;
      }
      if (
        itemResult === true &&
        itemDirtyBeforeSave > 0 &&
        Number(window.__gsoAirPendingItemDirtyCount || 0) === 0
      ) {
        savedParts.push("items");
      }

      if (showSuccess && savedParts.length > 0) {
        await Swal.fire({
          icon: "success",
          title: "Saved",
          text:
            getItemCount() === 0
              ? "Draft saved. Add at least one item before submitting."
              : savedParts.length === 2
              ? "Draft and item changes saved."
              : savedParts[0] === "draft"
              ? "Draft saved."
              : "Item changes saved.",
          timer: 1200,
          showConfirmButton: false,
        });
      } else if (showLoading) {
        Swal.close();
      }

      return true;
    } catch (error) {
      showFormError(error instanceof Error ? error.message : "Save failed.");
      return false;
    } finally {
      toolbarState.setBusy("saving", false);
      updateToolbarButtons();
    }
  }

  async function submitAir() {
    const config = window.__gsoAirEdit || {};

    const itemCount = getItemCount();
    if (itemCount === 0) {
      await Swal.fire({
        icon: "warning",
        title: "Add item",
        text: "Add at least one item before submitting this AIR.",
      });
      return;
    }

    const initialBlockingIssues = getBlockingItemIssues();
    if (initialBlockingIssues.length > 0) {
      await Swal.fire({
        icon: "warning",
        title: "Fix item units",
        html: `<ul style="text-align:left; margin:0; padding-left:18px;">${initialBlockingIssues
          .map((issue) => `<li>${issue.message}</li>`)
          .join("")}</ul>`,
      });
      return;
    }

    const hasChanges = toolbarState.hasChanges();

    const confirmation = await Swal.fire({
      icon: "question",
      title: hasChanges ? "Save and submit AIR?" : "Submit AIR?",
      text: hasChanges
        ? "Unsaved changes will be saved first, then the draft will be submitted."
        : "This will lock the draft and mark it as submitted.",
      showCancelButton: true,
      confirmButtonText: hasChanges ? "Save and Submit" : "Submit",
      cancelButtonText: "Cancel",
    });

    if (!confirmation.isConfirmed) {
      return;
    }

    toolbarState.setBusy("submitting", true);

    try {
      if (hasChanges) {
        const saved = await saveAllChanges({
          showSuccess: false,
          showLoading: true,
          loadingTitle: "Saving changes...",
        });

        if (!saved) {
          return;
        }
      }

      const blockingIssues = getBlockingItemIssues();
      if (blockingIssues.length > 0) {
        await Swal.fire({
          icon: "warning",
          title: "Fix item units",
          html: `<ul style="text-align:left; margin:0; padding-left:18px;">${blockingIssues
            .map((issue) => `<li>${issue.message}</li>`)
            .join("")}</ul>`,
        });
        return;
      }

      Swal.fire({
        title: "Submitting...",
        allowOutsideClick: false,
        didOpen: () => Swal.showLoading(),
      });

      const parsed = await sendJson(
        config.submitUrl,
        "PUT",
        {},
        "The AIR could not be submitted.",
        true
      );

      if (!parsed) {
        return;
      }

      await Swal.fire({
        icon: "success",
        title: "Submitted",
        timer: 800,
        showConfirmButton: false,
      });

      window.location.href = config.editUrl || window.location.href;
    } catch (error) {
      showFormError(error instanceof Error ? error.message : "Submit failed.");
    } finally {
      toolbarState.setBusy("submitting", false);
    }
  }

  function bindToolbarEvents() {
    qs("gsoAirSaveBtn")?.addEventListener("click", async (event) => {
      event.preventDefault();
      await saveAllChanges({ showSuccess: true, showLoading: true });
    });

    qs("gsoAirSubmitBtn")?.addEventListener("click", async (event) => {
      event.preventDefault();
      await submitAir();
    });

    qs("gsoAirArchiveBtn")?.addEventListener("click", async () => {
      const confirmation = await Swal.fire({
        icon: "warning",
        title: "Archive AIR?",
        showCancelButton: true,
        confirmButtonText: "Archive",
        cancelButtonText: "Cancel",
      });

      if (!confirmation.isConfirmed) return;

      try {
        await sendJson(
          window.__gsoAirEdit?.deleteUrl,
          "DELETE",
          null,
          "The AIR could not be archived.",
          false
        );
        await Swal.fire({
          icon: "success",
          title: "Archived",
          timer: 900,
          showConfirmButton: false,
        });
        window.location.href = window.__gsoAirEdit?.indexUrl || "/";
      } catch (error) {
        showFormError(error instanceof Error ? error.message : "Archive failed.");
      }
    });

    qs("gsoAirRestoreBtn")?.addEventListener("click", async () => {
      const confirmation = await Swal.fire({
        icon: "question",
        title: "Restore AIR?",
        showCancelButton: true,
        confirmButtonText: "Restore",
        cancelButtonText: "Cancel",
      });

      if (!confirmation.isConfirmed) return;

      try {
        await sendJson(
          window.__gsoAirEdit?.restoreUrl,
          "PATCH",
          null,
          "The AIR could not be restored.",
          false
        );
        await Swal.fire({
          icon: "success",
          title: "Restored",
          timer: 900,
          showConfirmButton: false,
        });
        window.location.href = window.__gsoAirEdit?.editUrl || window.location.href;
      } catch (error) {
        showFormError(error instanceof Error ? error.message : "Restore failed.");
      }
    });

    qs("gsoAirForceDeleteBtn")?.addEventListener("click", async () => {
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
        await sendJson(
          window.__gsoAirEdit?.forceDeleteUrl,
          "DELETE",
          null,
          "The AIR could not be permanently deleted.",
          false
        );
        await Swal.fire({
          icon: "success",
          title: "Deleted",
          timer: 900,
          showConfirmButton: false,
        });
        window.location.href = window.__gsoAirEdit?.indexUrl || "/";
      } catch (error) {
        showFormError(error instanceof Error ? error.message : "Delete failed.");
      }
    });
  }

  function bindFormDirtyTracking() {
    const form = getForm();
    if (!form) return;

    form.addEventListener("input", refreshHeaderDirtyCount);
    form.addEventListener("change", refreshHeaderDirtyCount);
  }

  function bindKeyboardSave() {
    document.addEventListener("keydown", async (event) => {
      const isMac = navigator.platform.toUpperCase().includes("MAC");
      const isSave = (isMac ? event.metaKey : event.ctrlKey) && event.key.toLowerCase() === "s";
      if (!isSave) return;

      const active = document.activeElement;
      const isOnDraftEdit =
        !!active &&
        (active.closest?.("#gsoAirEditForm") ||
          active.closest?.("#gsoAirItemList") ||
          active.id === "gsoAirItemSearch" ||
          active.id === "gsoAirSaveBtn");

      if (!isOnDraftEdit) return;

      event.preventDefault();
      await saveAllChanges({ showSuccess: true, showLoading: true });
    });
  }

  function bindAccountableOfficerSuggestions() {
    const suggestUrl = window.__gsoAirEdit?.accountableOfficerSuggestUrl;
    if (!suggestUrl) return;

    attachAccountableOfficerAutocomplete({
      input: qs("gsoAirInspectedByName"),
      suggestUrl,
      title: "Inspected By Suggestions",
    });

    attachAccountableOfficerAutocomplete({
      input: qs("gsoAirAcceptedByName"),
      suggestUrl,
      title: "Accepted By Suggestions",
    });
  }

  function init() {
    const page = qs("gso-air-edit-page");
    if (!page) return;

    attachErrorStyleOnce();
    bindFormDirtyTracking();
    bindToolbarEvents();
    bindKeyboardSave();
    bindAccountableOfficerSuggestions();
    resetHeaderBaseline();
    toolbarState.setItemCount(window.__gsoAirPendingItemDirtyCount || 0);
    updateToolbarButtons();
  }

  onReady(init);
})();
