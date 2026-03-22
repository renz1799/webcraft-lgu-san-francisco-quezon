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
      window.__gsoAccountableOfficers?.csrf ||
      ""
    );
  }

  function qs(id) {
    return document.getElementById(id);
  }

  function clearErrors() {
    const formError = qs("accountableOfficerFormError");
    if (formError) {
      formError.classList.add("hidden");
      formError.textContent = "";
    }

    [
      "accountableOfficerFullNameErr",
      "accountableOfficerDesignationErr",
      "accountableOfficerOfficeErr",
      "accountableOfficerDepartmentErr",
      "accountableOfficerIsActiveErr",
    ].forEach((id) => {
      const element = qs(id);
      if (!element) return;
      element.classList.add("hidden");
      element.textContent = "";
    });
  }

  function openForCreate() {
    clearErrors();
    qs("accountableOfficerModalTitle").textContent = "Add Accountable Officer";
    qs("accountableOfficerId").value = "";
    qs("accountableOfficerFullName").value = "";
    qs("accountableOfficerDesignation").value = "";
    qs("accountableOfficerOffice").value = "";
    qs("accountableOfficerDepartmentId").value = "";
    qs("accountableOfficerIsActive").value = "1";
  }

  function openForEdit(row) {
    clearErrors();
    qs("accountableOfficerModalTitle").textContent = "Edit Accountable Officer";
    qs("accountableOfficerId").value = row?.id || "";
    qs("accountableOfficerFullName").value = row?.full_name || "";
    qs("accountableOfficerDesignation").value = row?.designation || "";
    qs("accountableOfficerOffice").value = row?.office || "";
    qs("accountableOfficerDepartmentId").value = row?.department_id || "";
    qs("accountableOfficerIsActive").value = row?.is_active ? "1" : "0";

    if (window.HSOverlay) {
      window.HSOverlay.open(qs("accountableOfficerModal"));
    }
  }

  function closeModal() {
    if (window.HSOverlay) {
      window.HSOverlay.close(qs("accountableOfficerModal"));
    }
  }

  function showFormError(message) {
    const formError = qs("accountableOfficerFormError");
    if (!formError) return;
    formError.textContent = message || "Something went wrong.";
    formError.classList.remove("hidden");
  }

  function applyValidationErrors(errors) {
    const map = {
      full_name: "accountableOfficerFullNameErr",
      designation: "accountableOfficerDesignationErr",
      office: "accountableOfficerOfficeErr",
      department_id: "accountableOfficerDepartmentErr",
      is_active: "accountableOfficerIsActiveErr",
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

  async function save() {
    const config = window.__gsoAccountableOfficers || {};
    const id = (qs("accountableOfficerId")?.value || "").trim();
    const payload = {
      full_name: (qs("accountableOfficerFullName")?.value || "").trim(),
      designation: (qs("accountableOfficerDesignation")?.value || "").trim(),
      office: (qs("accountableOfficerOffice")?.value || "").trim(),
      department_id: (qs("accountableOfficerDepartmentId")?.value || "").trim(),
      is_active: (qs("accountableOfficerIsActive")?.value || "1") === "1",
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
      title: isEdit ? "Save changes?" : "Create accountable officer?",
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
      const data = await response.json().catch(() => ({}));
      showFormError(data?.message || "Save failed.");
      return;
    }

    closeModal();

    await Swal.fire({
      icon: "success",
      title: isEdit ? "Updated" : "Created",
      timer: 900,
      showConfirmButton: false,
    });

    if (typeof window.__gsoAccountableOfficersReload === "function") {
      window.__gsoAccountableOfficersReload();
    }
  }

  onReady(function () {
    if (!window.__gsoAccountableOfficers?.canManage) return;

    document.addEventListener("click", (event) => {
      const createButton = event.target.closest('[data-hs-overlay="#accountableOfficerModal"][data-mode="create"]');
      if (createButton) {
        openForCreate();
        return;
      }

      const editButton = event.target.closest('[data-action="edit-accountable-officer"]');
      if (!editButton) return;

      const rowJson = editButton.getAttribute("data-row");
      if (!rowJson) return;

      try {
        openForEdit(JSON.parse(rowJson));
      } catch {
        // ignore malformed row payloads
      }
    });

    qs("accountableOfficerSaveBtn")?.addEventListener("click", save);
  });
})();
