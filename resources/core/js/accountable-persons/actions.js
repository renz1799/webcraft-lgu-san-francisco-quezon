import Swal from "sweetalert2";
import "sweetalert2/dist/sweetalert2.min.css";

(function () {
  "use strict";

  if (window.__accountablePersonsActionsBound) return;
  window.__accountablePersonsActionsBound = true;

  function onReady(fn) {
    if (document.readyState === "loading") {
      document.addEventListener("DOMContentLoaded", fn);
      return;
    }

    fn();
  }

  function getConfig() {
    return window.__accountablePersons || {};
  }

  function getCsrf() {
    return (
      document.querySelector('meta[name="csrf-token"]')?.content ||
      getConfig().csrf ||
      ""
    );
  }

  function qs(id) {
    return document.getElementById(id);
  }

  async function parseErrorResponse(response) {
    const contentType = response.headers.get("content-type") || "";
    const data = contentType.includes("application/json")
      ? await response.json().catch(() => null)
      : null;

    return (
      data?.message ||
      data?.error ||
      (response.status === 401
        ? "Your session has expired. Please sign in again."
        : response.status === 403
        ? "You do not have permission to manage accountable persons."
        : response.status === 419
        ? "Your security token expired. Refresh the page and try again."
        : response.status === 404
        ? "The accountable person could not be found."
        : "The request could not be completed.")
    );
  }

  function clearErrors() {
    const formError = qs("accountablePersonFormError");
    if (formError) {
      formError.classList.add("hidden");
      formError.textContent = "";
    }

    [
      "accountablePersonFullNameErr",
      "accountablePersonDesignationErr",
      "accountablePersonOfficeErr",
      "accountablePersonDepartmentErr",
      "accountablePersonIsActiveErr",
    ].forEach((id) => {
      const element = qs(id);
      if (!element) return;
      element.classList.add("hidden");
      element.textContent = "";
    });
  }

  function openForCreate() {
    clearErrors();
    qs("accountablePersonModalTitle").textContent = "Add Accountable Person";
    qs("accountablePersonId").value = "";
    qs("accountablePersonFullName").value = "";
    qs("accountablePersonDesignation").value = "";
    qs("accountablePersonOffice").value = "";
    qs("accountablePersonDepartmentId").value = "";
    qs("accountablePersonIsActive").value = "1";
  }

  function openForEdit(row) {
    clearErrors();
    qs("accountablePersonModalTitle").textContent = "Edit Accountable Person";
    qs("accountablePersonId").value = row?.id || "";
    qs("accountablePersonFullName").value = row?.full_name || "";
    qs("accountablePersonDesignation").value = row?.designation || "";
    qs("accountablePersonOffice").value = row?.office || "";
    qs("accountablePersonDepartmentId").value = row?.department_id || "";
    qs("accountablePersonIsActive").value = row?.is_active ? "1" : "0";

    if (window.HSOverlay) {
      window.HSOverlay.open(qs("accountablePersonModal"));
    }
  }

  function closeModal() {
    if (window.HSOverlay) {
      window.HSOverlay.close(qs("accountablePersonModal"));
    }
  }

  function showFormError(message) {
    const formError = qs("accountablePersonFormError");
    if (!formError) return;
    formError.textContent = message || "Something went wrong.";
    formError.classList.remove("hidden");
  }

  function applyValidationErrors(errors) {
    const map = {
      full_name: "accountablePersonFullNameErr",
      designation: "accountablePersonDesignationErr",
      office: "accountablePersonOfficeErr",
      department_id: "accountablePersonDepartmentErr",
      is_active: "accountablePersonIsActiveErr",
    };

    Object.entries(map).forEach(([field, id]) => {
      const element = qs(id);
      if (!element) return;
      const message = errors?.[field]?.[0];
      if (!message) return;
      element.textContent = message;
      element.classList.remove("hidden");
    });
  }

  function reloadTable() {
    if (typeof window.__accountablePersonsReload === "function") {
      window.__accountablePersonsReload();
      return;
    }

    if (
      window.__accountablePersonsTable &&
      typeof window.__accountablePersonsTable.setData === "function"
    ) {
      window.__accountablePersonsTable.setData();
    }
  }

  async function save() {
    const config = getConfig();
    const id = (qs("accountablePersonId")?.value || "").trim();
    const payload = {
      full_name: (qs("accountablePersonFullName")?.value || "").trim(),
      designation: (qs("accountablePersonDesignation")?.value || "").trim(),
      office: (qs("accountablePersonOffice")?.value || "").trim(),
      department_id: (qs("accountablePersonDepartmentId")?.value || "").trim(),
      is_active: (qs("accountablePersonIsActive")?.value || "1") === "1",
    };

    clearErrors();

    const isEdit = id !== "";
    const endpoint = isEdit
      ? (config.updateUrlTemplate || "").replace("__ID__", encodeURIComponent(id))
      : config.storeUrl || "";

    if (!endpoint) {
      showFormError("Missing endpoint configuration.");
      return;
    }

    const confirmation = await Swal.fire({
      icon: "question",
      title: isEdit ? "Save changes?" : "Create accountable person?",
      showCancelButton: true,
      confirmButtonText: "Save",
      cancelButtonText: "Cancel",
    });

    if (!confirmation.isConfirmed) return;

    const response = await fetch(endpoint, {
      method: isEdit ? "PUT" : "POST",
      headers: {
        "X-CSRF-TOKEN": getCsrf(),
        Accept: "application/json",
        "Content-Type": "application/json",
      },
      body: JSON.stringify(payload),
    });

    if (response.status === 422) {
      const data = await response.json().catch(() => ({}));
      applyValidationErrors(data?.errors || {});
      showFormError(data?.message || "Validation failed.");
      return;
    }

    if (!response.ok) {
      showFormError(await parseErrorResponse(response));
      return;
    }

    closeModal();

    await Swal.fire({
      icon: "success",
      title: isEdit ? "Updated" : "Created",
      timer: 900,
      showConfirmButton: false,
    });

    reloadTable();
  }

  async function runLifecycleAction({
    id,
    action,
    title,
    text,
    successTitle,
    errorTitle,
  }) {
    const config = getConfig();
    const template =
      action === "restore"
        ? config.restoreUrlTemplate
        : config.deleteUrlTemplate;

    if (!id || !template) return;

    const confirmation = await Swal.fire({
      icon: action === "restore" ? "question" : "warning",
      title,
      text,
      showCancelButton: true,
      confirmButtonText: action === "restore" ? "Restore" : "Archive",
      cancelButtonText: "Cancel",
    });

    if (!confirmation.isConfirmed) return;

    const response = await fetch(template.replace("__ID__", encodeURIComponent(id)), {
      method: action === "restore" ? "PATCH" : "DELETE",
      headers: {
        "X-CSRF-TOKEN": getCsrf(),
        Accept: "application/json",
      },
    });

    if (!response.ok) {
      await Swal.fire({
        icon: "error",
        title: errorTitle,
        text: await parseErrorResponse(response),
      });
      return;
    }

    await Swal.fire({
      icon: "success",
      title: successTitle,
      timer: 900,
      showConfirmButton: false,
    });

    reloadTable();
  }

  onReady(function () {
    const tableElement = document.getElementById("accountable-persons-table");
    if (!tableElement) return;

    document.addEventListener("click", async (event) => {
      const config = getConfig();

      const createButton = event.target.closest(
        '[data-hs-overlay="#accountablePersonModal"][data-mode="create"]'
      );
      if (createButton && config.canManage) {
        openForCreate();
        return;
      }

      const editButton = event.target.closest('[data-action="edit-accountable-person"]');
      if (editButton && config.canManage) {
        const rowJson = editButton.getAttribute("data-row");
        if (!rowJson) return;

        try {
          openForEdit(JSON.parse(rowJson));
        } catch (_error) {
          // Ignore malformed row payloads.
        }

        return;
      }

      const deleteButton = event.target.closest('[data-action="delete-accountable-person"]');
      if (deleteButton && config.canManage) {
        await runLifecycleAction({
          id: deleteButton.dataset.id,
          action: "delete",
          title: "Archive accountable person?",
          text: "This will archive the accountable person.",
          successTitle: "Archived",
          errorTitle: "Archive failed",
        });
        return;
      }

      const restoreButton = event.target.closest('[data-action="restore-accountable-person"]');
      if (restoreButton && config.canManage) {
        await runLifecycleAction({
          id: restoreButton.dataset.id,
          action: "restore",
          title: "Restore accountable person?",
          text: "This will restore the accountable person.",
          successTitle: "Restored",
          errorTitle: "Restore failed",
        });
      }
    });

    qs("accountablePersonSaveBtn")?.addEventListener("click", save);
  });
})();
