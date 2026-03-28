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
      window.__gsoInventoryItems?.csrf ||
      ""
    );
  }

  function clearErrors() {
    const formError = qs("gsoInventoryItemFormError");
    if (formError) {
      formError.classList.add("hidden");
      formError.textContent = "";
    }

    [
      "gsoInventoryItemItemErr",
      "gsoInventoryItemDepartmentErr",
      "gsoInventoryItemFundSourceErr",
      "gsoInventoryItemPropertyNumberErr",
      "gsoInventoryItemAcquisitionDateErr",
      "gsoInventoryItemAcquisitionCostErr",
      "gsoInventoryItemDescriptionErr",
      "gsoInventoryItemQuantityErr",
      "gsoInventoryItemUnitErr",
      "gsoInventoryItemStockNumberErr",
      "gsoInventoryItemServiceLifeErr",
      "gsoInventoryItemClassificationErr",
      "gsoInventoryItemCustodyStateErr",
      "gsoInventoryItemStatusErr",
      "gsoInventoryItemConditionErr",
      "gsoInventoryItemAccountableOfficerIdErr",
      "gsoInventoryItemAccountableOfficerErr",
      "gsoInventoryItemBrandErr",
      "gsoInventoryItemModelErr",
      "gsoInventoryItemSerialNumberErr",
      "gsoInventoryItemPoNumberErr",
      "gsoInventoryItemRemarksErr",
    ].forEach((id) => {
      const element = qs(id);
      if (!element) return;
      element.classList.add("hidden");
      element.textContent = "";
    });
  }

  function showFormError(message) {
    const formError = qs("gsoInventoryItemFormError");
    if (!formError) return;
    formError.textContent = message || "Something went wrong.";
    formError.classList.remove("hidden");
  }

  function closeModal() {
    if (window.HSOverlay) {
      window.HSOverlay.close(qs("gsoInventoryItemModal"));
    }
  }

  function syncItemHints() {
    const itemSelect = qs("gsoInventoryItemItemId");
    const unitInput = qs("gsoInventoryItemUnit");
    const serialHint = qs("gsoInventoryItemSerialHint");
    if (!itemSelect) return;

    const option = itemSelect.options[itemSelect.selectedIndex];
    const baseUnit = option?.dataset?.baseUnit || "";
    const requiresSerial = option?.dataset?.requiresSerial === "1";

    if (unitInput && !unitInput.value.trim() && baseUnit) {
      unitInput.value = baseUnit;
    }

    if (serialHint) {
      serialHint.textContent = requiresSerial
        ? "The selected item requires a serial number."
        : "Serial number is optional unless the selected item requires it.";
    }
  }

  function syncAccountableOfficerName() {
    const officerSelect = qs("gsoInventoryItemAccountableOfficerId");
    const officerInput = qs("gsoInventoryItemAccountableOfficer");
    if (!officerSelect || !officerInput) return;

    const option = officerSelect.options[officerSelect.selectedIndex];
    if (!option || !option.value) return;

    officerInput.value = option.textContent?.trim?.() || "";
  }

  function resetForm() {
    clearErrors();
    qs("gsoInventoryItemId").value = "";
    qs("gsoInventoryItemModalTitle").textContent = "Add Inventory Item";

    if (qs("gsoInventoryItemItemId")?.options?.length > 0) {
      qs("gsoInventoryItemItemId").selectedIndex = 0;
    }
    if (qs("gsoInventoryItemDepartmentId")?.options?.length > 0) {
      qs("gsoInventoryItemDepartmentId").selectedIndex = 0;
    }

    qs("gsoInventoryItemFundSourceId").value = "";
    qs("gsoInventoryItemPropertyNumber").value = "";
    qs("gsoInventoryItemPoNumber").value = "";
    qs("gsoInventoryItemStockNumber").value = "";
    qs("gsoInventoryItemAcquisitionDate").value = "";
    qs("gsoInventoryItemAcquisitionCost").value = "";
    qs("gsoInventoryItemDescription").value = "";
    qs("gsoInventoryItemQuantity").value = "1";
    qs("gsoInventoryItemUnit").value = "";
    qs("gsoInventoryItemServiceLife").value = "";
    qs("gsoInventoryItemClassification").value = "ppe";
    qs("gsoInventoryItemCustodyState").value = "pool";
    qs("gsoInventoryItemStatus").selectedIndex = 0;
    qs("gsoInventoryItemCondition").selectedIndex = 0;
    qs("gsoInventoryItemAccountableOfficerId").value = "";
    qs("gsoInventoryItemAccountableOfficer").value = "";
    qs("gsoInventoryItemBrand").value = "";
    qs("gsoInventoryItemModel").value = "";
    qs("gsoInventoryItemSerialNumber").value = "";
    qs("gsoInventoryItemRemarks").value = "";
    syncItemHints();
  }

  function populateForm(row) {
    clearErrors();
    qs("gsoInventoryItemId").value = row?.id || "";
    qs("gsoInventoryItemModalTitle").textContent = "Edit Inventory Item";
    qs("gsoInventoryItemItemId").value = row?.item_id || "";
    qs("gsoInventoryItemDepartmentId").value = row?.department_id || "";
    qs("gsoInventoryItemFundSourceId").value = row?.fund_source_id || "";
    qs("gsoInventoryItemPropertyNumber").value = row?.property_number || "";
    qs("gsoInventoryItemPoNumber").value = row?.po_number || "";
    qs("gsoInventoryItemStockNumber").value = row?.stock_number || "";
    qs("gsoInventoryItemAcquisitionDate").value = row?.acquisition_date || "";
    qs("gsoInventoryItemAcquisitionCost").value = row?.acquisition_cost || "";
    qs("gsoInventoryItemDescription").value = row?.description || "";
    qs("gsoInventoryItemQuantity").value = row?.quantity || 1;
    qs("gsoInventoryItemUnit").value = row?.unit || "";
    qs("gsoInventoryItemServiceLife").value = row?.service_life ?? "";
    qs("gsoInventoryItemClassification").value = row?.classification || "ppe";
    qs("gsoInventoryItemCustodyState").value = row?.custody_state || "pool";
    qs("gsoInventoryItemStatus").value = row?.status || "";
    qs("gsoInventoryItemCondition").value = row?.condition || "";
    qs("gsoInventoryItemAccountableOfficerId").value = row?.accountable_officer_id || "";
    qs("gsoInventoryItemAccountableOfficer").value = row?.accountable_officer || "";
    qs("gsoInventoryItemBrand").value = row?.brand || "";
    qs("gsoInventoryItemModel").value = row?.model || "";
    qs("gsoInventoryItemSerialNumber").value = row?.serial_number || "";
    qs("gsoInventoryItemRemarks").value = row?.remarks || "";
    syncItemHints();
  }

  function applyValidationErrors(errors) {
    const map = {
      item_id: "gsoInventoryItemItemErr",
      department_id: "gsoInventoryItemDepartmentErr",
      fund_source_id: "gsoInventoryItemFundSourceErr",
      property_number: "gsoInventoryItemPropertyNumberErr",
      acquisition_date: "gsoInventoryItemAcquisitionDateErr",
      acquisition_cost: "gsoInventoryItemAcquisitionCostErr",
      description: "gsoInventoryItemDescriptionErr",
      quantity: "gsoInventoryItemQuantityErr",
      unit: "gsoInventoryItemUnitErr",
      stock_number: "gsoInventoryItemStockNumberErr",
      service_life: "gsoInventoryItemServiceLifeErr",
      is_ics: "gsoInventoryItemClassificationErr",
      custody_state: "gsoInventoryItemCustodyStateErr",
      status: "gsoInventoryItemStatusErr",
      condition: "gsoInventoryItemConditionErr",
      accountable_officer_id: "gsoInventoryItemAccountableOfficerIdErr",
      accountable_officer: "gsoInventoryItemAccountableOfficerErr",
      brand: "gsoInventoryItemBrandErr",
      model: "gsoInventoryItemModelErr",
      serial_number: "gsoInventoryItemSerialNumberErr",
      po_number: "gsoInventoryItemPoNumberErr",
      remarks: "gsoInventoryItemRemarksErr",
    };

    Object.entries(map).forEach(([field, id]) => {
      const element = qs(id);
      const message = errors?.[field]?.[0];
      if (!element || !message) return;
      element.textContent = message;
      element.classList.remove("hidden");
    });
  }

  async function fetchInventoryItem(id) {
    const template = window.__gsoInventoryItems?.editDataUrlTemplate || "";
    if (!id || !template) {
      throw new Error("Missing edit-data endpoint configuration.");
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
            ? "The inventory item could not be found."
            : "The inventory item details could not be loaded.")
      );
    }

    const payload = await response.json();
    return payload?.data || null;
  }

  async function save() {
    const config = window.__gsoInventoryItems || {};
    const id = (qs("gsoInventoryItemId")?.value || "").trim();
    const payload = {
      item_id: (qs("gsoInventoryItemItemId")?.value || "").trim(),
      department_id: (qs("gsoInventoryItemDepartmentId")?.value || "").trim(),
      fund_source_id: (qs("gsoInventoryItemFundSourceId")?.value || "").trim(),
      property_number: (qs("gsoInventoryItemPropertyNumber")?.value || "").trim(),
      po_number: (qs("gsoInventoryItemPoNumber")?.value || "").trim(),
      stock_number: (qs("gsoInventoryItemStockNumber")?.value || "").trim(),
      acquisition_date: (qs("gsoInventoryItemAcquisitionDate")?.value || "").trim(),
      acquisition_cost: (qs("gsoInventoryItemAcquisitionCost")?.value || "").trim(),
      description: (qs("gsoInventoryItemDescription")?.value || "").trim(),
      quantity: Number(qs("gsoInventoryItemQuantity")?.value || 1),
      unit: (qs("gsoInventoryItemUnit")?.value || "").trim(),
      service_life: (qs("gsoInventoryItemServiceLife")?.value || "").trim(),
      is_ics: (qs("gsoInventoryItemClassification")?.value || "ppe") === "ics",
      custody_state: (qs("gsoInventoryItemCustodyState")?.value || "pool").trim(),
      status: (qs("gsoInventoryItemStatus")?.value || "").trim(),
      condition: (qs("gsoInventoryItemCondition")?.value || "").trim(),
      accountable_officer_id: (qs("gsoInventoryItemAccountableOfficerId")?.value || "").trim(),
      accountable_officer: (qs("gsoInventoryItemAccountableOfficer")?.value || "").trim(),
      brand: (qs("gsoInventoryItemBrand")?.value || "").trim(),
      model: (qs("gsoInventoryItemModel")?.value || "").trim(),
      serial_number: (qs("gsoInventoryItemSerialNumber")?.value || "").trim(),
      remarks: (qs("gsoInventoryItemRemarks")?.value || "").trim(),
    };

    if (payload.fund_source_id === "") payload.fund_source_id = null;
    if (payload.property_number === "") payload.property_number = null;
    if (payload.stock_number === "") payload.stock_number = null;
    if (payload.description === "") payload.description = null;
    if (payload.service_life === "") payload.service_life = null;
    if (payload.accountable_officer_id === "") payload.accountable_officer_id = null;
    if (payload.accountable_officer === "") payload.accountable_officer = null;
    if (payload.brand === "") payload.brand = null;
    if (payload.model === "") payload.model = null;
    if (payload.serial_number === "") payload.serial_number = null;
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
      title: isEdit ? "Save changes?" : "Create inventory item?",
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

    if (typeof window.__gsoInventoryItemsReload === "function") {
      window.__gsoInventoryItemsReload();
    }
  }

  onReady(function () {
    if (!window.__gsoInventoryItems?.canManage) return;

    qs("gsoInventoryItemItemId")?.addEventListener("change", syncItemHints);
    qs("gsoInventoryItemAccountableOfficerId")?.addEventListener("change", syncAccountableOfficerName);
    qs("gsoInventoryItemSaveBtn")?.addEventListener("click", save);

    document.addEventListener("click", async (event) => {
      const createButton = event.target.closest(
        '[data-hs-overlay="#gsoInventoryItemModal"][data-mode="create"]'
      );
      if (createButton) {
        resetForm();
        return;
      }

      const editButton = event.target.closest('[data-action="edit-inventory-item"]');
      if (!editButton) return;

      const id = editButton.getAttribute("data-id");
      if (!id) return;

      clearErrors();

      try {
        const inventoryItem = await fetchInventoryItem(id);
        populateForm(inventoryItem);

        if (window.HSOverlay) {
          window.HSOverlay.open(qs("gsoInventoryItemModal"));
        }
      } catch (error) {
        await Swal.fire({
          icon: "error",
          title: "Unable to open inventory item",
          text:
            error instanceof Error
              ? error.message
              : "The inventory item details could not be loaded.",
        });
      }
    });

    syncItemHints();
  });
})();
