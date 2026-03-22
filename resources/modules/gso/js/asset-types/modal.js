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
      window.__gsoAssetTypes?.csrf ||
      ""
    );
  }

  function qs(id) {
    return document.getElementById(id);
  }

  function clearErrors() {
    const formError = qs("assetTypeFormError");
    if (formError) {
      formError.classList.add("hidden");
      formError.textContent = "";
    }

    ["assetTypeCodeErr", "assetTypeNameErr"].forEach((id) => {
      const element = qs(id);
      if (!element) return;
      element.classList.add("hidden");
      element.textContent = "";
    });
  }

  function openForCreate() {
    clearErrors();
    qs("assetTypeModalTitle").textContent = "Add Asset Type";
    qs("assetTypeId").value = "";
    qs("assetTypeCode").value = "";
    qs("assetTypeName").value = "";
  }

  function openForEdit(row) {
    clearErrors();
    qs("assetTypeModalTitle").textContent = "Edit Asset Type";
    qs("assetTypeId").value = row?.id || "";
    qs("assetTypeCode").value = row?.type_code || "";
    qs("assetTypeName").value = row?.type_name || "";

    if (window.HSOverlay) {
      window.HSOverlay.open(qs("assetTypeModal"));
    }
  }

  function closeModal() {
    if (window.HSOverlay) {
      window.HSOverlay.close(qs("assetTypeModal"));
    }
  }

  function showFormError(message) {
    const formError = qs("assetTypeFormError");
    if (!formError) return;
    formError.textContent = message || "Something went wrong.";
    formError.classList.remove("hidden");
  }

  function applyValidationErrors(errors) {
    const map = {
      type_code: "assetTypeCodeErr",
      type_name: "assetTypeNameErr",
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
    const config = window.__gsoAssetTypes || {};
    const id = (qs("assetTypeId")?.value || "").trim();
    const payload = {
      type_code: (qs("assetTypeCode")?.value || "").trim(),
      type_name: (qs("assetTypeName")?.value || "").trim(),
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
      title: isEdit ? "Save changes?" : "Create asset type?",
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

    if (typeof window.__gsoAssetTypesReload === "function") {
      window.__gsoAssetTypesReload();
    }
  }

  onReady(function () {
    if (!window.__gsoAssetTypes?.canManage) return;

    document.addEventListener("click", (event) => {
      const createButton = event.target.closest('[data-hs-overlay="#assetTypeModal"][data-mode="create"]');
      if (createButton) {
        openForCreate();
        return;
      }

      const editButton = event.target.closest('[data-action="edit-asset-type"]');
      if (!editButton) return;

      const rowJson = editButton.getAttribute("data-row");
      if (!rowJson) return;

      try {
        openForEdit(JSON.parse(rowJson));
      } catch {
        // ignore malformed row payloads
      }
    });

    qs("assetTypeSaveBtn")?.addEventListener("click", save);
  });
})();
