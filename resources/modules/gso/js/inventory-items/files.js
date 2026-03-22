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

  function escapeHtml(value) {
    return String(value ?? "")
      .replace(/&/g, "&amp;")
      .replace(/</g, "&lt;")
      .replace(/>/g, "&gt;")
      .replace(/"/g, "&quot;")
      .replace(/'/g, "&#39;");
  }

  function getCsrf() {
    return (
      document.querySelector('meta[name="csrf-token"]')?.content ||
      window.__gsoInventoryItems?.csrf ||
      ""
    );
  }

  function endpointFromTemplate(template, replacements) {
    let url = template || "";

    Object.entries(replacements || {}).forEach(([placeholder, value]) => {
      url = url.replace(placeholder, encodeURIComponent(String(value ?? "")));
    });

    return url;
  }

  function clearError() {
    const errorElement = qs("gsoInventoryFilesError");
    if (!errorElement) return;
    errorElement.textContent = "";
    errorElement.classList.add("hidden");
  }

  function showError(message) {
    const errorElement = qs("gsoInventoryFilesError");
    if (!errorElement) return;
    errorElement.textContent = message || "Something went wrong.";
    errorElement.classList.remove("hidden");
  }

  function openModal() {
    if (window.HSOverlay) {
      window.HSOverlay.open(qs("gsoInventoryFilesModal"));
    }
  }

  function renderFileCard(file, inventoryItemId, canManage) {
    const previewMarkup =
      file?.is_image && file?.preview_url
        ? `<a href="${escapeHtml(
            file.preview_url
          )}" target="_blank" rel="noopener"><img class="gso-inventory-file-preview" src="${escapeHtml(
            file.preview_url
          )}" alt="${escapeHtml(file?.original_name || "Inventory file")}"></a>`
        : `<div class="gso-inventory-file-fallback">${escapeHtml(
            file?.type_text || "File"
          )}</div>`;

    const deleteMarkup = canManage
      ? `<button type="button" class="ti-btn ti-btn-sm ti-btn-danger" data-action="delete-inventory-file" data-inventory-item-id="${escapeHtml(
          inventoryItemId
        )}" data-file-id="${escapeHtml(file?.id || "")}">
            <i class="ri-delete-bin-line"></i>
            Delete
        </button>`
      : "";

    return `
      <div class="gso-inventory-file-card">
        ${previewMarkup}
        <div class="p-4">
          <div class="flex items-start justify-between gap-3">
            <div>
              <div class="font-medium text-defaulttextcolor dark:text-white">${escapeHtml(
                file?.original_name || "Inventory File"
              )}</div>
              <div class="text-xs text-[#8c9097] mt-1">
                ${escapeHtml(file?.type_text || "File")}
                ${file?.size_text ? ` • ${escapeHtml(file.size_text)}` : ""}
                ${file?.created_at_text ? ` • ${escapeHtml(file.created_at_text)}` : ""}
              </div>
            </div>
            ${deleteMarkup}
          </div>
        </div>
      </div>
    `;
  }

  function renderPayload(payload) {
    const config = window.__gsoInventoryItems || {};
    const inventoryItem = payload?.inventory_item || {};
    const files = Array.isArray(payload?.files) ? payload.files : [];
    const grid = qs("gsoInventoryFilesGrid");
    const emptyState = qs("gsoInventoryFilesEmpty");
    const titleElement = qs("gsoInventoryFilesModalTitle");
    const subtitleElement = qs("gsoInventoryFilesModalSubtitle");
    const uploadPanel = qs("gsoInventoryFilesUploadPanel");
    const uploadHint = qs("gsoInventoryFilesUploadHint");
    const importPanel = qs("gsoInventoryInspectionImportPanel");

    if (titleElement) {
      titleElement.textContent = inventoryItem?.label || "Inventory Files";
    }

    if (subtitleElement) {
      const bits = [
        inventoryItem?.property_number ? `Property: ${inventoryItem.property_number}` : null,
        inventoryItem?.po_number ? `PO: ${inventoryItem.po_number}` : "PO: not set",
      ].filter(Boolean);
      subtitleElement.textContent =
        bits.join(" | ") || "Review uploaded evidence and linked inspection files.";
    }

    if (uploadPanel || importPanel) {
      const canMutate =
        Boolean(config.canManage) &&
        !inventoryItem?.is_archived &&
        Boolean(inventoryItem?.po_number || inventoryItem?.drive_folder_id);

      [uploadPanel, importPanel].forEach((panel) => {
        if (!panel) return;
        panel.classList.toggle("hidden", !config.canManage);
        panel.querySelectorAll("input,button,select").forEach((element) => {
          element.disabled = !canMutate;
        });
      });

      if (uploadHint) {
        if (inventoryItem?.is_archived) {
          uploadHint.textContent = "Archived inventory items are view-only. Restore the record before changing files.";
        } else if (!inventoryItem?.po_number && !inventoryItem?.drive_folder_id) {
          uploadHint.textContent = "A PO number or existing Drive folder is required before storing inventory files.";
        } else {
          uploadHint.textContent = "Images and PDFs are stored in the inventory item's Google Drive folder.";
        }
      }
    }

    if (grid) {
      grid.innerHTML = files
        .map((file) =>
          renderFileCard(
            file,
            inventoryItem?.id || "",
            Boolean(config.canManage) && !inventoryItem?.is_archived
          )
        )
        .join("");
    }

    if (emptyState) {
      emptyState.classList.toggle("hidden", files.length !== 0);
    }
  }

  let currentInventoryItemId = "";

  async function loadFiles(inventoryItemId) {
    const config = window.__gsoInventoryItems || {};
    const response = await fetch(
      endpointFromTemplate(config.fileIndexUrlTemplate, { "__ID__": inventoryItemId }),
      {
        method: "GET",
        headers: { Accept: "application/json" },
      }
    );

    const data = await response.json().catch(() => ({}));
    if (!response.ok) {
      throw new Error(data?.message || "Inventory files could not be loaded.");
    }

    currentInventoryItemId = inventoryItemId;
    clearError();
    renderPayload(data?.data || {});
    openModal();
  }

  async function uploadFiles() {
    const config = window.__gsoInventoryItems || {};
    const fileInput = qs("gsoInventoryFilesInput");
    const typeInput = qs("gsoInventoryFilesType");
    const files = Array.from(fileInput?.files || []);

    if (!currentInventoryItemId) {
      showError("Select an inventory item first.");
      return;
    }

    if (files.length === 0) {
      showError("Choose at least one file to upload.");
      return;
    }

    clearError();

    const formData = new FormData();
    files.forEach((file) => formData.append("files[]", file));
    if ((typeInput?.value || "").trim() !== "") {
      formData.append("type", typeInput.value.trim());
    }

    const response = await fetch(
      endpointFromTemplate(config.fileStoreUrlTemplate, { "__ID__": currentInventoryItemId }),
      {
        method: "POST",
        headers: {
          "X-CSRF-TOKEN": getCsrf(),
          Accept: "application/json",
        },
        body: formData,
      }
    );

    const data = await response.json().catch(() => ({}));
    if (response.status === 422) {
      const errors = data?.errors || {};
      showError(
        errors?.files?.[0] ||
          errors?.type?.[0] ||
          errors?.po_number?.[0] ||
          data?.message ||
          "Validation failed."
      );
      return;
    }

    if (!response.ok) {
      showError(data?.message || "Upload failed.");
      return;
    }

    if (fileInput) fileInput.value = "";
    renderPayload(data?.data || {});

    if (typeof window.__gsoInventoryItemsReload === "function") {
      window.__gsoInventoryItemsReload();
    }

    await Swal.fire({
      icon: "success",
      title: "Uploaded",
      timer: 900,
      showConfirmButton: false,
    });
  }

  async function importInspectionPhotos() {
    const config = window.__gsoInventoryItems || {};
    const inspectionSelect = qs("gsoInventoryInspectionSelect");
    const inspectionId = (inspectionSelect?.value || "").trim();

    if (!currentInventoryItemId) {
      showError("Select an inventory item first.");
      return;
    }

    if (!inspectionId) {
      showError("Select an inspection to import.");
      return;
    }

    clearError();

    const response = await fetch(
      endpointFromTemplate(config.fileImportInspectionUrlTemplate, { "__ID__": currentInventoryItemId }),
      {
        method: "POST",
        headers: {
          "X-CSRF-TOKEN": getCsrf(),
          Accept: "application/json",
          "Content-Type": "application/json",
        },
        body: JSON.stringify({ inspection_id: inspectionId }),
      }
    );

    const data = await response.json().catch(() => ({}));
    if (response.status === 422) {
      const errors = data?.errors || {};
      showError(errors?.inspection_id?.[0] || data?.message || "Validation failed.");
      return;
    }

    if (!response.ok) {
      showError(data?.message || "Import failed.");
      return;
    }

    renderPayload(data?.data || {});

    if (typeof window.__gsoInventoryItemsReload === "function") {
      window.__gsoInventoryItemsReload();
    }

    await Swal.fire({
      icon: "success",
      title: "Imported",
      timer: 900,
      showConfirmButton: false,
    });
  }

  async function deleteFile(inventoryItemId, fileId) {
    const config = window.__gsoInventoryItems || {};
    const confirmation = await Swal.fire({
      icon: "warning",
      title: "Delete inventory file?",
      text: "This will remove the file from the inventory record.",
      showCancelButton: true,
      confirmButtonText: "Delete",
      cancelButtonText: "Cancel",
    });

    if (!confirmation.isConfirmed) {
      return;
    }

    clearError();

    const response = await fetch(
      endpointFromTemplate(config.fileDestroyUrlTemplate, {
        "__ID__": inventoryItemId,
        "__FILE__": fileId,
      }),
      {
        method: "DELETE",
        headers: {
          "X-CSRF-TOKEN": getCsrf(),
          Accept: "application/json",
        },
      }
    );

    const data = await response.json().catch(() => ({}));
    if (!response.ok) {
      showError(data?.message || "Delete failed.");
      return;
    }

    renderPayload(data?.data || {});

    if (typeof window.__gsoInventoryItemsReload === "function") {
      window.__gsoInventoryItemsReload();
    }

    await Swal.fire({
      icon: "success",
      title: "Deleted",
      timer: 900,
      showConfirmButton: false,
    });
  }

  onReady(function () {
    if (!qs("gsoInventoryFilesModal")) return;

    qs("gsoInventoryFilesUploadBtn")?.addEventListener("click", uploadFiles);
    qs("gsoInventoryInspectionImportBtn")?.addEventListener("click", importInspectionPhotos);

    document.addEventListener("click", async (event) => {
      const openButton = event.target.closest('[data-action="inventory-item-files"]');
      if (openButton) {
        const inventoryItemId = openButton.getAttribute("data-id");
        if (!inventoryItemId) return;

        try {
          await loadFiles(inventoryItemId);
        } catch (error) {
          await Swal.fire({
            icon: "error",
            title: "Unable to load files",
            text:
              error instanceof Error
                ? error.message
                : "The inventory files could not be loaded.",
          });
        }

        return;
      }

      const deleteButton = event.target.closest('[data-action="delete-inventory-file"]');
      if (!deleteButton) return;

      const inventoryItemId = deleteButton.getAttribute("data-inventory-item-id");
      const fileId = deleteButton.getAttribute("data-file-id");
      if (!inventoryItemId || !fileId) return;

      await deleteFile(inventoryItemId, fileId);
    });

    qs("gsoInventoryFilesModal")?.addEventListener("hidden.hs.overlay", () => {
      currentInventoryItemId = "";
      clearError();
      if (qs("gsoInventoryFilesGrid")) qs("gsoInventoryFilesGrid").innerHTML = "";
      if (qs("gsoInventoryFilesEmpty")) qs("gsoInventoryFilesEmpty").classList.add("hidden");
      if (qs("gsoInventoryFilesInput")) qs("gsoInventoryFilesInput").value = "";
      if (qs("gsoInventoryInspectionSelect")) qs("gsoInventoryInspectionSelect").value = "";
      if (qs("gsoInventoryFilesType")) qs("gsoInventoryFilesType").value = "";
    });
  });
})();
