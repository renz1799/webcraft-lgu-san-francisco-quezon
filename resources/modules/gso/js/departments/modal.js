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
      window.__gsoDepartments?.csrf ||
      ""
    );
  }

  function qs(id) {
    return document.getElementById(id);
  }

  function clearErrors() {
    const formError = qs("departmentFormError");
    if (formError) {
      formError.classList.add("hidden");
      formError.textContent = "";
    }

    [
      "departmentCodeErr",
      "departmentNameErr",
      "departmentShortNameErr",
      "departmentTypeErr",
      "departmentIsActiveErr",
    ].forEach((id) => {
      const element = qs(id);
      if (!element) return;
      element.classList.add("hidden");
      element.textContent = "";
    });
  }

  function openForCreate() {
    clearErrors();
    qs("departmentModalTitle").textContent = "Add Department";
    qs("departmentId").value = "";
    qs("departmentCode").value = "";
    qs("departmentName").value = "";
    qs("departmentShortName").value = "";
    qs("departmentType").value = "";
    qs("departmentIsActive").value = "1";
  }

  function openForEdit(row) {
    clearErrors();
    qs("departmentModalTitle").textContent = "Edit Department";
    qs("departmentId").value = row?.id || "";
    qs("departmentCode").value = row?.code || "";
    qs("departmentName").value = row?.name || "";
    qs("departmentShortName").value = row?.short_name || "";
    qs("departmentType").value = row?.type || "";
    qs("departmentIsActive").value = row?.is_active ? "1" : "0";

    if (window.HSOverlay) {
      window.HSOverlay.open(qs("departmentModal"));
    }
  }

  function closeModal() {
    if (window.HSOverlay) {
      window.HSOverlay.close(qs("departmentModal"));
    }
  }

  function showFormError(message) {
    const formError = qs("departmentFormError");
    if (!formError) return;
    formError.textContent = message || "Something went wrong.";
    formError.classList.remove("hidden");
  }

  function applyValidationErrors(errors) {
    const map = {
      code: "departmentCodeErr",
      name: "departmentNameErr",
      short_name: "departmentShortNameErr",
      type: "departmentTypeErr",
      is_active: "departmentIsActiveErr",
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
    const config = window.__gsoDepartments || {};
    const id = (qs("departmentId")?.value || "").trim();
    const payload = {
      code: (qs("departmentCode")?.value || "").trim(),
      name: (qs("departmentName")?.value || "").trim(),
      short_name: (qs("departmentShortName")?.value || "").trim(),
      type: (qs("departmentType")?.value || "").trim(),
      is_active: (qs("departmentIsActive")?.value || "1") === "1",
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
      title: isEdit ? "Save changes?" : "Create department?",
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

    if (typeof window.__gsoDepartmentsReload === "function") {
      window.__gsoDepartmentsReload();
    }
  }

  onReady(function () {
    if (!window.__gsoDepartments?.canManage) return;

    document.addEventListener("click", (event) => {
      const createButton = event.target.closest('[data-hs-overlay="#departmentModal"][data-mode="create"]');
      if (createButton) {
        openForCreate();
        return;
      }

      const editButton = event.target.closest('[data-action="edit-department"]');
      if (!editButton) return;

      const rowJson = editButton.getAttribute("data-row");
      if (!rowJson) return;

      try {
        openForEdit(JSON.parse(rowJson));
      } catch {
        // ignore malformed row payloads
      }
    });

    qs("departmentSaveBtn")?.addEventListener("click", save);
  });
})();
