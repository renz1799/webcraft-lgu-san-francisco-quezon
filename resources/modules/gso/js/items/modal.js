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
      window.__gsoItems?.csrf ||
      ""
    );
  }

  function qs(id) {
    return document.getElementById(id);
  }

  function conversionRowsContainer() {
    return qs("gsoItemConversionRows");
  }

  function escapeAttribute(value) {
    return String(value ?? "")
      .replace(/&/g, "&amp;")
      .replace(/"/g, "&quot;")
      .replace(/</g, "&lt;")
      .replace(/>/g, "&gt;");
  }

  function showFormError(message) {
    const formError = qs("gsoItemFormError");
    if (!formError) return;
    formError.textContent = message || "Something went wrong.";
    formError.classList.remove("hidden");
  }

  function clearErrors() {
    const formError = qs("gsoItemFormError");
    if (formError) {
      formError.classList.add("hidden");
      formError.textContent = "";
    }

    [
      "gsoItemAssetErr",
      "gsoItemNameErr",
      "gsoItemDescriptionErr",
      "gsoItemBaseUnitErr",
      "gsoItemIdentificationErr",
      "gsoItemMajorSubAccountGroupErr",
      "gsoItemTrackingTypeErr",
      "gsoItemRequiresSerialErr",
      "gsoItemSemiExpendableErr",
      "gsoItemIsSelectedErr",
      "gsoItemConversionsErr",
    ].forEach((id) => {
      const element = qs(id);
      if (!element) return;
      element.classList.add("hidden");
      element.textContent = "";
    });

    conversionRowsContainer()
      ?.querySelectorAll("[data-role='conversion-from-unit-error'], [data-role='conversion-multiplier-error']")
      .forEach((element) => {
        element.classList.add("hidden");
        element.textContent = "";
      });
  }

  function createConversionRow(row = {}) {
    const wrapper = document.createElement("div");
    wrapper.className = "gso-item-conversion-row";
    wrapper.innerHTML = `
      <div class="gso-item-conversions-grid">
        <div>
          <input
            type="text"
            class="ti-form-input w-full"
            data-role="conversion-from-unit"
            placeholder="Alternative unit, e.g. box"
            value="${escapeAttribute(row?.from_unit || "")}"
          >
          <div data-role="conversion-from-unit-error" class="text-xs text-danger mt-1 hidden"></div>
        </div>

        <div>
          <input
            type="number"
            min="1"
            class="ti-form-input w-full"
            data-role="conversion-multiplier"
            placeholder="Multiplier"
            value="${row?.multiplier ? Number(row.multiplier) : ""}"
          >
          <div data-role="conversion-multiplier-error" class="text-xs text-danger mt-1 hidden"></div>
        </div>

        <div class="flex justify-end">
          <button type="button" class="ti-btn ti-btn-danger !rounded-full" data-action="remove-conversion">
            <i class="ri-delete-bin-line"></i>
          </button>
        </div>
      </div>
    `;

    return wrapper;
  }

  function renderConversionRows(rows = []) {
    const container = conversionRowsContainer();
    if (!container) return;

    container.innerHTML = "";

    if (!Array.isArray(rows) || rows.length === 0) {
      return;
    }

    rows.forEach((row) => {
      container.appendChild(createConversionRow(row));
    });
  }

  function appendConversionRow(row = {}) {
    conversionRowsContainer()?.appendChild(createConversionRow(row));
  }

  function readConversionRows() {
    return Array.from(conversionRowsContainer()?.querySelectorAll(".gso-item-conversion-row") || [])
      .map((row) => ({
        from_unit: row.querySelector('[data-role="conversion-from-unit"]')?.value?.trim?.() || "",
        multiplier: row.querySelector('[data-role="conversion-multiplier"]')?.value?.trim?.() || "",
      }))
      .filter((row) => row.from_unit !== "" || row.multiplier !== "");
  }

  function syncTrackingControls() {
    const trackingType = qs("gsoItemTrackingType")?.value || "property";
    const requiresSerial = qs("gsoItemRequiresSerial");
    if (!requiresSerial) return;

    const isConsumable = trackingType === "consumable";
    requiresSerial.disabled = isConsumable;
    if (isConsumable) {
      requiresSerial.checked = false;
    }
  }

  function resetForm() {
    clearErrors();
    qs("gsoItemId").value = "";
    qs("gsoItemModalTitle").textContent = "Add Item";

    if (qs("gsoItemAssetId")?.options?.length > 0) {
      qs("gsoItemAssetId").selectedIndex = 0;
    }

    qs("gsoItemName").value = "";
    qs("gsoItemDescription").value = "";
    qs("gsoItemBaseUnit").value = "";
    qs("gsoItemIdentification").value = "";
    qs("gsoItemMajorSubAccountGroup").value = "";
    qs("gsoItemTrackingType").value = "property";
    qs("gsoItemRequiresSerial").checked = false;
    qs("gsoItemSemiExpendable").checked = false;
    qs("gsoItemIsSelected").checked = false;
    renderConversionRows([]);
    syncTrackingControls();
  }

  function populateForm(item) {
    clearErrors();
    qs("gsoItemId").value = item?.id || "";
    qs("gsoItemModalTitle").textContent = "Edit Item";
    qs("gsoItemAssetId").value = item?.asset_id || "";
    qs("gsoItemName").value = item?.item_name || "";
    qs("gsoItemDescription").value = item?.description || "";
    qs("gsoItemBaseUnit").value = item?.base_unit || "";
    qs("gsoItemIdentification").value = item?.item_identification || "";
    qs("gsoItemMajorSubAccountGroup").value = item?.major_sub_account_group || "";
    qs("gsoItemTrackingType").value = item?.tracking_type || "property";
    qs("gsoItemRequiresSerial").checked = Boolean(item?.requires_serial);
    qs("gsoItemSemiExpendable").checked = Boolean(item?.is_semi_expendable);
    qs("gsoItemIsSelected").checked = Boolean(item?.is_selected);
    renderConversionRows(item?.unit_conversions || []);
    syncTrackingControls();
  }

  function closeModal() {
    if (window.HSOverlay) {
      window.HSOverlay.close(qs("gsoItemModal"));
    }
  }

  function applyValidationErrors(errors) {
    const map = {
      asset_id: "gsoItemAssetErr",
      item_name: "gsoItemNameErr",
      description: "gsoItemDescriptionErr",
      base_unit: "gsoItemBaseUnitErr",
      item_identification: "gsoItemIdentificationErr",
      major_sub_account_group: "gsoItemMajorSubAccountGroupErr",
      tracking_type: "gsoItemTrackingTypeErr",
      requires_serial: "gsoItemRequiresSerialErr",
      is_semi_expendable: "gsoItemSemiExpendableErr",
      is_selected: "gsoItemIsSelectedErr",
      unit_conversions: "gsoItemConversionsErr",
    };

    Object.entries(map).forEach(([field, id]) => {
      const element = qs(id);
      const message = errors?.[field]?.[0];
      if (!element || !message) return;
      element.textContent = message;
      element.classList.remove("hidden");
    });

    Array.from(conversionRowsContainer()?.querySelectorAll(".gso-item-conversion-row") || []).forEach(
      (rowElement, index) => {
        const fromUnitError = rowElement.querySelector('[data-role="conversion-from-unit-error"]');
        const multiplierError = rowElement.querySelector('[data-role="conversion-multiplier-error"]');
        const fromUnitMessage = errors?.[`unit_conversions.${index}.from_unit`]?.[0];
        const multiplierMessage = errors?.[`unit_conversions.${index}.multiplier`]?.[0];

        if (fromUnitError && fromUnitMessage) {
          fromUnitError.textContent = fromUnitMessage;
          fromUnitError.classList.remove("hidden");
        }

        if (multiplierError && multiplierMessage) {
          multiplierError.textContent = multiplierMessage;
          multiplierError.classList.remove("hidden");
        }
      },
    );
  }

  async function fetchItem(id) {
    const template = window.__gsoItems?.showUrlTemplate || "";
    if (!id || !template) {
      throw new Error("Missing show endpoint configuration.");
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
            ? "The item could not be found."
            : "The item details could not be loaded."),
      );
    }

    const payload = await response.json();
    return payload?.data || null;
  }

  async function save() {
    const config = window.__gsoItems || {};
    const id = (qs("gsoItemId")?.value || "").trim();
    const payload = {
      asset_id: (qs("gsoItemAssetId")?.value || "").trim(),
      item_name: (qs("gsoItemName")?.value || "").trim(),
      description: (qs("gsoItemDescription")?.value || "").trim(),
      base_unit: (qs("gsoItemBaseUnit")?.value || "").trim(),
      item_identification: (qs("gsoItemIdentification")?.value || "").trim(),
      major_sub_account_group: (qs("gsoItemMajorSubAccountGroup")?.value || "").trim(),
      tracking_type: (qs("gsoItemTrackingType")?.value || "property").trim(),
      requires_serial: Boolean(qs("gsoItemRequiresSerial")?.checked),
      is_semi_expendable: Boolean(qs("gsoItemSemiExpendable")?.checked),
      is_selected: Boolean(qs("gsoItemIsSelected")?.checked),
      unit_conversions: readConversionRows().map((row) => ({
        from_unit: row.from_unit,
        multiplier: row.multiplier === "" ? "" : Number(row.multiplier),
      })),
    };

    clearErrors();
    syncTrackingControls();

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
      title: isEdit ? "Save changes?" : "Create item?",
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

    if (typeof window.__gsoItemsReload === "function") {
      window.__gsoItemsReload();
    }
  }

  onReady(function () {
    if (!window.__gsoItems?.canManage) return;

    qs("gsoItemTrackingType")?.addEventListener("change", syncTrackingControls);
    qs("gsoItemAddConversionBtn")?.addEventListener("click", () => appendConversionRow());
    qs("gsoItemSaveBtn")?.addEventListener("click", save);

    document.addEventListener("click", async (event) => {
      const createButton = event.target.closest('[data-hs-overlay="#gsoItemModal"][data-mode="create"]');
      if (createButton) {
        resetForm();
        return;
      }

      const removeButton = event.target.closest('[data-action="remove-conversion"]');
      if (removeButton) {
        removeButton.closest(".gso-item-conversion-row")?.remove();
        return;
      }

      const editButton = event.target.closest('[data-action="edit-item"]');
      if (!editButton) return;

      const id = editButton.getAttribute("data-id");
      if (!id) return;

      clearErrors();

      try {
        const item = await fetchItem(id);
        populateForm(item);

        if (window.HSOverlay) {
          window.HSOverlay.open(qs("gsoItemModal"));
        }
      } catch (error) {
        await Swal.fire({
          icon: "error",
          title: "Unable to open item",
          text:
            error instanceof Error
              ? error.message
              : "The item details could not be loaded.",
        });
      }
    });
  });
})();
