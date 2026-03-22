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
      window.__gsoAssetCategories?.csrf ||
      ""
    );
  }

  function qs(id) {
    return document.getElementById(id);
  }

  function clearErrors() {
    const formError = qs("assetCategoryFormError");
    if (formError) {
      formError.classList.add("hidden");
      formError.textContent = "";
    }

    [
      "assetCategoryAssetTypeErr",
      "assetCategoryCodeErr",
      "assetCategoryNameErr",
      "assetCategoryAccountGroupErr",
    ].forEach((id) => {
      const element = qs(id);
      if (!element) return;
      element.classList.add("hidden");
      element.textContent = "";
    });
  }

  function openForCreate() {
    clearErrors();
    qs("assetCategoryModalTitle").textContent = "Add Asset Category";
    qs("assetCategoryId").value = "";
    if (qs("assetCategoryAssetTypeId").options.length > 0) {
      qs("assetCategoryAssetTypeId").selectedIndex = 0;
    }
    qs("assetCategoryCode").value = "";
    qs("assetCategoryName").value = "";
    qs("assetCategoryAccountGroup").value = "";
  }

  function openForEdit(row) {
    clearErrors();
    qs("assetCategoryModalTitle").textContent = "Edit Asset Category";
    qs("assetCategoryId").value = row?.id || "";
    qs("assetCategoryAssetTypeId").value = row?.asset_type_id || "";
    qs("assetCategoryCode").value = row?.asset_code || "";
    qs("assetCategoryName").value = row?.asset_name || "";
    qs("assetCategoryAccountGroup").value = row?.account_group || "";

    if (window.HSOverlay) {
      window.HSOverlay.open(qs("assetCategoryModal"));
    }
  }

  function closeModal() {
    if (window.HSOverlay) {
      window.HSOverlay.close(qs("assetCategoryModal"));
    }
  }

  function showFormError(message) {
    const formError = qs("assetCategoryFormError");
    if (!formError) return;
    formError.textContent = message || "Something went wrong.";
    formError.classList.remove("hidden");
  }

  function applyValidationErrors(errors) {
    const map = {
      asset_type_id: "assetCategoryAssetTypeErr",
      asset_code: "assetCategoryCodeErr",
      asset_name: "assetCategoryNameErr",
      account_group: "assetCategoryAccountGroupErr",
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
    const config = window.__gsoAssetCategories || {};
    const id = (qs("assetCategoryId")?.value || "").trim();
    const payload = {
      asset_type_id: (qs("assetCategoryAssetTypeId")?.value || "").trim(),
      asset_code: (qs("assetCategoryCode")?.value || "").trim(),
      asset_name: (qs("assetCategoryName")?.value || "").trim(),
      account_group: (qs("assetCategoryAccountGroup")?.value || "").trim(),
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
      title: isEdit ? "Save changes?" : "Create asset category?",
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

    if (typeof window.__gsoAssetCategoriesReload === "function") {
      window.__gsoAssetCategoriesReload();
    }
  }

  onReady(function () {
    if (!window.__gsoAssetCategories?.canManage) return;

    document.addEventListener("click", (event) => {
      const createButton = event.target.closest('[data-hs-overlay="#assetCategoryModal"][data-mode="create"]');
      if (createButton) {
        openForCreate();
        return;
      }

      const editButton = event.target.closest('[data-action="edit-asset-category"]');
      if (!editButton) return;

      const rowJson = editButton.getAttribute("data-row");
      if (!rowJson) return;

      try {
        openForEdit(JSON.parse(rowJson));
      } catch {
        // ignore malformed row payloads
      }
    });

    qs("assetCategorySaveBtn")?.addEventListener("click", save);
  });
})();
