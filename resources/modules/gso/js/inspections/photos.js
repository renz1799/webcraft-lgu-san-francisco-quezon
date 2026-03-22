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
      window.__gsoInspections?.csrf ||
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

  function showError(message) {
    const errorElement = qs("gsoInspectionPhotoError");
    if (!errorElement) return;

    errorElement.textContent = message || "Something went wrong.";
    errorElement.classList.remove("hidden");
  }

  function clearError() {
    const errorElement = qs("gsoInspectionPhotoError");
    if (!errorElement) return;

    errorElement.textContent = "";
    errorElement.classList.add("hidden");
  }

  function openModal() {
    if (window.HSOverlay) {
      window.HSOverlay.open(qs("gsoInspectionPhotoModal"));
    }
  }

  function parseResponseMessage(response, data) {
    return (
      data?.message ||
      data?.error ||
      (response.status === 401
        ? "Your session has expired. Please sign in again."
        : response.status === 403
        ? "You do not have permission to manage inspection photos."
        : response.status === 404
        ? "The inspection photo could not be found."
        : response.status === 419
        ? "Your security token expired. Refresh the page and try again."
        : "The request could not be completed.")
    );
  }

  function renderPhotoCard(photo, canDelete, inspectionId) {
    const previewUrl = photo?.preview_url || "";
    const previewMarkup =
      previewUrl && photo?.previewable
        ? `<a href="${escapeHtml(
            previewUrl
          )}" target="_blank" rel="noopener"><img class="gso-inspection-photo-preview" src="${escapeHtml(
            previewUrl
          )}" alt="${escapeHtml(photo?.original_name || "Inspection photo")}"></a>`
        : '<div class="gso-inspection-photo-fallback">Preview unavailable for this file.</div>';

    const captionMarkup = photo?.caption
      ? `<div class="mt-2 text-xs text-[#8c9097]">${escapeHtml(photo.caption)}</div>`
      : "";

    const deleteMarkup = canDelete
      ? `<button type="button" class="ti-btn ti-btn-sm ti-btn-danger" data-action="delete-inspection-photo" data-inspection-id="${escapeHtml(
          inspectionId
        )}" data-photo-id="${escapeHtml(photo?.id || "")}">
            <i class="ri-delete-bin-line"></i>
            Delete
        </button>`
      : "";

    return `
      <div class="gso-inspection-photo-card">
        ${previewMarkup}
        <div class="p-4">
          <div class="flex items-start justify-between gap-3">
            <div>
              <div class="font-medium text-defaulttextcolor dark:text-white">${escapeHtml(
                photo?.original_name || "Inspection Photo"
              )}</div>
              <div class="text-xs text-[#8c9097] mt-1">
                ${escapeHtml(photo?.created_at_text || "-")}
                ${photo?.size_text ? ` • ${escapeHtml(photo.size_text)}` : ""}
              </div>
            </div>
            ${deleteMarkup}
          </div>
          ${captionMarkup}
        </div>
      </div>
    `;
  }

  function renderPayload(payload) {
    const config = window.__gsoInspections || {};
    const inspection = payload?.inspection || {};
    const photos = Array.isArray(payload?.photos) ? payload.photos : [];
    const grid = qs("gsoInspectionPhotoGrid");
    const emptyState = qs("gsoInspectionPhotoEmpty");
    const uploadPanel = qs("gsoInspectionPhotoUploadPanel");
    const uploadHint = qs("gsoInspectionPhotoUploadHint");
    const titleElement = qs("gsoInspectionPhotoModalTitle");
    const subtitleElement = qs("gsoInspectionPhotoModalSubtitle");

    if (titleElement) {
      titleElement.textContent = inspection?.label || "Inspection Photos";
    }

    if (subtitleElement) {
      const fragments = [
        inspection?.status_text ? `Status: ${inspection.status_text}` : null,
        inspection?.po_number ? `PO: ${inspection.po_number}` : "PO: not set",
      ].filter(Boolean);

      subtitleElement.textContent =
        fragments.join(" | ") || "Review uploaded inspection evidence.";
    }

    if (uploadPanel) {
      const canUpload =
        Boolean(config.canManage) &&
        !inspection?.is_archived &&
        Boolean(inspection?.po_number);
      uploadPanel.classList.toggle("hidden", !config.canManage);
      uploadPanel.querySelectorAll("input,button").forEach((element) => {
        element.disabled = !canUpload;
      });

      if (uploadHint) {
        if (inspection?.is_archived) {
          uploadHint.textContent = "Archived inspections are view-only. Restore the record before changing photos.";
        } else if (!inspection?.po_number) {
          uploadHint.textContent = "A PO number is required before inspection photos can be uploaded.";
        } else {
          uploadHint.textContent = "Images are stored in the inspection's Google Drive folder.";
        }
      }
    }

    if (grid) {
      grid.innerHTML = photos
        .map((photo) =>
          renderPhotoCard(
            photo,
            Boolean(config.canManage) && !inspection?.is_archived,
            inspection?.id || ""
          )
        )
        .join("");
    }

    if (emptyState) {
      emptyState.classList.toggle("hidden", photos.length !== 0);
    }
  }

  let currentInspectionId = "";

  async function loadInspectionPhotos(inspectionId) {
    const config = window.__gsoInspections || {};
    const url = endpointFromTemplate(config.photoIndexUrlTemplate, {
      "__INSPECTION__": inspectionId,
    });

    clearError();

    const response = await fetch(url, {
      method: "GET",
      headers: {
        Accept: "application/json",
      },
    });

    const data = await response.json().catch(() => ({}));

    if (!response.ok) {
      throw new Error(parseResponseMessage(response, data));
    }

    renderPayload(data?.data || {});
    currentInspectionId = inspectionId;
    openModal();
  }

  async function uploadPhotos() {
    const config = window.__gsoInspections || {};
    const fileInput = qs("gsoInspectionPhotoFiles");
    const files = Array.from(fileInput?.files || []);

    if (!currentInspectionId) {
      showError("Select an inspection first.");
      return;
    }

    if (files.length === 0) {
      showError("Choose at least one image to upload.");
      return;
    }

    clearError();

    const formData = new FormData();
    files.forEach((file) => {
      formData.append("photos[]", file);
    });

    const response = await fetch(
      endpointFromTemplate(config.photoStoreUrlTemplate, {
        "__INSPECTION__": currentInspectionId,
      }),
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
      const firstError =
        errors?.photos?.[0] ||
        errors?.["photos.0"]?.[0] ||
        errors?.po_number?.[0] ||
        data?.message ||
        "Validation failed.";
      showError(firstError);
      return;
    }

    if (!response.ok) {
      showError(parseResponseMessage(response, data));
      return;
    }

    if (fileInput) {
      fileInput.value = "";
    }

    renderPayload(data?.data || {});

    if (typeof window.__gsoInspectionsReload === "function") {
      window.__gsoInspectionsReload();
    }

    await Swal.fire({
      icon: "success",
      title: "Uploaded",
      timer: 900,
      showConfirmButton: false,
    });
  }

  async function deletePhoto(inspectionId, photoId) {
    const config = window.__gsoInspections || {};

    const confirmation = await Swal.fire({
      icon: "warning",
      title: "Delete inspection photo?",
      text: "This will remove the photo from the inspection record.",
      showCancelButton: true,
      confirmButtonText: "Delete",
      cancelButtonText: "Cancel",
    });

    if (!confirmation.isConfirmed) {
      return;
    }

    clearError();

    const response = await fetch(
      endpointFromTemplate(config.photoDestroyUrlTemplate, {
        "__INSPECTION__": inspectionId,
        "__PHOTO__": photoId,
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
      showError(parseResponseMessage(response, data));
      return;
    }

    renderPayload(data?.data || {});

    if (typeof window.__gsoInspectionsReload === "function") {
      window.__gsoInspectionsReload();
    }

    await Swal.fire({
      icon: "success",
      title: "Deleted",
      timer: 900,
      showConfirmButton: false,
    });
  }

  onReady(function () {
    if (!qs("gsoInspectionPhotoModal")) return;

    qs("gsoInspectionPhotoUploadBtn")?.addEventListener("click", uploadPhotos);

    document.addEventListener("click", async (event) => {
      const openButton = event.target.closest('[data-action="inspection-photos"]');
      if (openButton) {
        const inspectionId = openButton.getAttribute("data-id");
        if (!inspectionId) return;

        try {
          await loadInspectionPhotos(inspectionId);
        } catch (error) {
          await Swal.fire({
            icon: "error",
            title: "Unable to load photos",
            text:
              error instanceof Error
                ? error.message
                : "The inspection photos could not be loaded.",
          });
        }

        return;
      }

      const deleteButton = event.target.closest('[data-action="delete-inspection-photo"]');
      if (!deleteButton) return;

      const inspectionId = deleteButton.getAttribute("data-inspection-id");
      const photoId = deleteButton.getAttribute("data-photo-id");

      if (!inspectionId || !photoId) return;

      await deletePhoto(inspectionId, photoId);
    });

    qs("gsoInspectionPhotoModal")?.addEventListener("hidden.hs.overlay", () => {
      currentInspectionId = "";
      clearError();
      const grid = qs("gsoInspectionPhotoGrid");
      const emptyState = qs("gsoInspectionPhotoEmpty");
      const fileInput = qs("gsoInspectionPhotoFiles");

      if (grid) grid.innerHTML = "";
      if (emptyState) emptyState.classList.add("hidden");
      if (fileInput) fileInput.value = "";
    });
  });
})();
