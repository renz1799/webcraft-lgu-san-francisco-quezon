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

  function getCsrf() {
    return (
      document.querySelector('meta[name="csrf-token"]')?.content ||
      window.__gsoInspections?.csrf ||
      ""
    );
  }

  function clearErrors() {
    const formError = qs("gsoInspectionFormError");
    if (formError) {
      formError.classList.add("hidden");
      formError.textContent = "";
    }

    [
      "gsoInspectionItemErr",
      "gsoInspectionDepartmentErr",
      "gsoInspectionStatusErr",
      "gsoInspectionItemNameErr",
      "gsoInspectionOfficeDepartmentErr",
      "gsoInspectionAccountableOfficerErr",
      "gsoInspectionPoNumberErr",
      "gsoInspectionDvNumberErr",
      "gsoInspectionQuantityErr",
      "gsoInspectionConditionErr",
      "gsoInspectionAcquisitionDateErr",
      "gsoInspectionAcquisitionCostErr",
      "gsoInspectionBrandErr",
      "gsoInspectionModelErr",
      "gsoInspectionSerialNumberErr",
      "gsoInspectionObservedDescriptionErr",
      "gsoInspectionRemarksErr",
    ].forEach((id) => {
      const element = qs(id);
      if (!element) return;
      element.classList.add("hidden");
      element.textContent = "";
    });
  }

  function showFormError(message) {
    const formError = qs("gsoInspectionFormError");
    if (!formError) return;
    formError.textContent = message || "Something went wrong.";
    formError.classList.remove("hidden");
  }

  function closeModal() {
    if (window.HSOverlay) {
      window.HSOverlay.close(qs("gsoInspectionModal"));
    }
  }

  function syncItemNameSuggestion() {
    const itemSelect = qs("gsoInspectionItemId");
    const itemNameInput = qs("gsoInspectionItemName");
    if (!itemSelect || !itemNameInput || itemNameInput.value.trim() !== "") return;

    const option = itemSelect.options[itemSelect.selectedIndex];
    const itemName = option?.dataset?.itemName || "";

    if (option?.value && itemName) {
      itemNameInput.value = itemName;
    }
  }

  function syncDepartmentSuggestion() {
    const departmentSelect = qs("gsoInspectionDepartmentId");
    const officeDepartmentInput = qs("gsoInspectionOfficeDepartment");
    if (!departmentSelect || !officeDepartmentInput || officeDepartmentInput.value.trim() !== "") return;

    const option = departmentSelect.options[departmentSelect.selectedIndex];
    const label = option?.dataset?.departmentLabel || "";

    if (option?.value && label) {
      officeDepartmentInput.value = label;
    }
  }

  function resetForm() {
    clearErrors();
    qs("gsoInspectionId").value = "";
    qs("gsoInspectionModalTitle").textContent = "Add Inspection";

    qs("gsoInspectionItemId").value = "";
    qs("gsoInspectionDepartmentId").value = "";
    qs("gsoInspectionStatus").value = "draft";
    qs("gsoInspectionItemName").value = "";
    qs("gsoInspectionOfficeDepartment").value = "";
    qs("gsoInspectionAccountableOfficer").value = "";
    qs("gsoInspectionPoNumber").value = "";
    qs("gsoInspectionDvNumber").value = "";
    qs("gsoInspectionQuantity").value = "1";
    qs("gsoInspectionCondition").selectedIndex = 0;
    qs("gsoInspectionAcquisitionDate").value = "";
    qs("gsoInspectionAcquisitionCost").value = "";
    qs("gsoInspectionBrand").value = "";
    qs("gsoInspectionModel").value = "";
    qs("gsoInspectionSerialNumber").value = "";
    qs("gsoInspectionObservedDescription").value = "";
    qs("gsoInspectionRemarks").value = "";
  }

  function populateForm(row) {
    clearErrors();
    qs("gsoInspectionId").value = row?.id || "";
    qs("gsoInspectionModalTitle").textContent = "Edit Inspection";
    qs("gsoInspectionItemId").value = row?.item_id || "";
    qs("gsoInspectionDepartmentId").value = row?.department_id || "";
    qs("gsoInspectionStatus").value = row?.status || "draft";
    qs("gsoInspectionItemName").value = row?.item_name || "";
    qs("gsoInspectionOfficeDepartment").value = row?.office_department || "";
    qs("gsoInspectionAccountableOfficer").value = row?.accountable_officer || "";
    qs("gsoInspectionPoNumber").value = row?.po_number || "";
    qs("gsoInspectionDvNumber").value = row?.dv_number || "";
    qs("gsoInspectionQuantity").value = row?.quantity || 1;
    qs("gsoInspectionCondition").value = row?.condition || "good";
    qs("gsoInspectionAcquisitionDate").value = row?.acquisition_date || "";
    qs("gsoInspectionAcquisitionCost").value = row?.acquisition_cost || "";
    qs("gsoInspectionBrand").value = row?.brand || "";
    qs("gsoInspectionModel").value = row?.model || "";
    qs("gsoInspectionSerialNumber").value = row?.serial_number || "";
    qs("gsoInspectionObservedDescription").value = row?.observed_description || "";
    qs("gsoInspectionRemarks").value = row?.remarks || "";
  }

  function applyValidationErrors(errors) {
    const map = {
      item_id: "gsoInspectionItemErr",
      department_id: "gsoInspectionDepartmentErr",
      status: "gsoInspectionStatusErr",
      item_name: "gsoInspectionItemNameErr",
      office_department: "gsoInspectionOfficeDepartmentErr",
      accountable_officer: "gsoInspectionAccountableOfficerErr",
      po_number: "gsoInspectionPoNumberErr",
      dv_number: "gsoInspectionDvNumberErr",
      quantity: "gsoInspectionQuantityErr",
      condition: "gsoInspectionConditionErr",
      acquisition_date: "gsoInspectionAcquisitionDateErr",
      acquisition_cost: "gsoInspectionAcquisitionCostErr",
      brand: "gsoInspectionBrandErr",
      model: "gsoInspectionModelErr",
      serial_number: "gsoInspectionSerialNumberErr",
      observed_description: "gsoInspectionObservedDescriptionErr",
      remarks: "gsoInspectionRemarksErr",
    };

    Object.entries(map).forEach(([field, id]) => {
      const element = qs(id);
      const message = errors?.[field]?.[0];
      if (!element || !message) return;
      element.textContent = message;
      element.classList.remove("hidden");
    });
  }

  async function fetchInspection(id) {
    const template = window.__gsoInspections?.showUrlTemplate || "";
    if (!id || !template) {
      throw new Error("Missing inspection endpoint configuration.");
    }

    const response = await fetch(template.replace("__ID__", encodeURIComponent(id)), {
      method: "GET",
      headers: {
        Accept: "application/json",
      },
    });

    if (!response.ok) {
      const contentType = response.headers.get("content-type") || "";
      const data = contentType.includes("application/json")
        ? await response.json().catch(() => null)
        : null;

      throw new Error(
        data?.message ||
          (response.status === 404
            ? "The inspection could not be found."
            : "The inspection details could not be loaded.")
      );
    }

    const payload = await response.json();
    return payload?.data || null;
  }

  async function save() {
    const config = window.__gsoInspections || {};
    const id = (qs("gsoInspectionId")?.value || "").trim();
    const payload = {
      item_id: (qs("gsoInspectionItemId")?.value || "").trim(),
      department_id: (qs("gsoInspectionDepartmentId")?.value || "").trim(),
      status: (qs("gsoInspectionStatus")?.value || "draft").trim(),
      item_name: (qs("gsoInspectionItemName")?.value || "").trim(),
      office_department: (qs("gsoInspectionOfficeDepartment")?.value || "").trim(),
      accountable_officer: (qs("gsoInspectionAccountableOfficer")?.value || "").trim(),
      po_number: (qs("gsoInspectionPoNumber")?.value || "").trim(),
      dv_number: (qs("gsoInspectionDvNumber")?.value || "").trim(),
      quantity: Number(qs("gsoInspectionQuantity")?.value || 1),
      condition: (qs("gsoInspectionCondition")?.value || "good").trim(),
      acquisition_date: (qs("gsoInspectionAcquisitionDate")?.value || "").trim(),
      acquisition_cost: (qs("gsoInspectionAcquisitionCost")?.value || "").trim(),
      brand: (qs("gsoInspectionBrand")?.value || "").trim(),
      model: (qs("gsoInspectionModel")?.value || "").trim(),
      serial_number: (qs("gsoInspectionSerialNumber")?.value || "").trim(),
      observed_description: (qs("gsoInspectionObservedDescription")?.value || "").trim(),
      remarks: (qs("gsoInspectionRemarks")?.value || "").trim(),
    };

    if (payload.item_id === "") payload.item_id = null;
    if (payload.department_id === "") payload.department_id = null;
    if (payload.item_name === "") payload.item_name = null;
    if (payload.office_department === "") payload.office_department = null;
    if (payload.accountable_officer === "") payload.accountable_officer = null;
    if (payload.po_number === "") payload.po_number = null;
    if (payload.dv_number === "") payload.dv_number = null;
    if (payload.acquisition_date === "") payload.acquisition_date = null;
    if (payload.acquisition_cost === "") payload.acquisition_cost = null;
    if (payload.brand === "") payload.brand = null;
    if (payload.model === "") payload.model = null;
    if (payload.serial_number === "") payload.serial_number = null;
    if (payload.observed_description === "") payload.observed_description = null;
    if (payload.remarks === "") payload.remarks = null;

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
      title: isEdit ? "Save changes?" : "Create inspection?",
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
      const contentType = response.headers.get("content-type") || "";
      const data = contentType.includes("application/json")
        ? await response.json().catch(() => null)
        : null;

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

    if (typeof window.__gsoInspectionsReload === "function") {
      window.__gsoInspectionsReload();
    }
  }

  onReady(function () {
    if (!window.__gsoInspections?.canManage) return;

    qs("gsoInspectionItemId")?.addEventListener("change", syncItemNameSuggestion);
    qs("gsoInspectionDepartmentId")?.addEventListener("change", syncDepartmentSuggestion);
    qs("gsoInspectionSaveBtn")?.addEventListener("click", save);

    document.addEventListener("click", async (event) => {
      const createButton = event.target.closest(
        '[data-hs-overlay="#gsoInspectionModal"][data-mode="create"]'
      );
      if (createButton) {
        resetForm();
        return;
      }

      const editButton = event.target.closest('[data-action="edit-inspection"]');
      if (!editButton) return;

      const id = editButton.getAttribute("data-id");
      if (!id) return;

      clearErrors();

      try {
        const inspection = await fetchInspection(id);
        populateForm(inspection);

        if (window.HSOverlay) {
          window.HSOverlay.open(qs("gsoInspectionModal"));
        }
      } catch (error) {
        await Swal.fire({
          icon: "error",
          title: "Unable to open inspection",
          text:
            error instanceof Error
              ? error.message
              : "The inspection details could not be loaded.",
        });
      }
    });
  });
})();
