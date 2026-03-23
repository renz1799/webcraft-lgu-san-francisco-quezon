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

  function getCsrf(config) {
    return (
      document.querySelector('meta[name="csrf-token"]')?.content ||
      config.csrf ||
      ""
    );
  }

  async function parseResponse(response) {
    const contentType = response.headers.get("content-type") || "";
    const data = contentType.includes("application/json")
      ? await response.json().catch(() => null)
      : null;

    return {
      ok: response.ok,
      status: response.status,
      data,
      message:
        data?.message ||
        data?.error ||
        (response.status === 401
          ? "Your session expired. Please sign in again."
          : response.status === 403
          ? "You do not have permission to manage AIR files."
          : response.status === 404
          ? "The AIR file could not be found."
          : response.status === 419
          ? "Your security token expired. Refresh the page and try again."
          : "The request could not be completed."),
    };
  }

  function showError(message) {
    const errorElement = qs("gsoAirFileError");
    if (!errorElement) return;

    errorElement.textContent = message || "Something went wrong.";
    errorElement.classList.remove("hidden");
  }

  function clearError() {
    const errorElement = qs("gsoAirFileError");
    if (!errorElement) return;

    errorElement.textContent = "";
    errorElement.classList.add("hidden");
  }

  function canMutateFiles(config, air) {
    return Boolean(config.canManage) && !air?.is_archived && Boolean(air?.po_number);
  }

  function renderFileCard(file, config, air) {
    const mutable = canMutateFiles(config, air);
    const previewUrl = file?.preview_url || "";
    const previewMarkup =
      previewUrl && file?.is_image
        ? `<a href="${escapeHtml(
            previewUrl
          )}" target="_blank" rel="noopener"><img class="gso-air-file-preview" src="${escapeHtml(
            previewUrl
          )}" alt="${escapeHtml(file?.original_name || "AIR File")}"></a>`
        : `<a href="${escapeHtml(
            previewUrl
          )}" target="_blank" rel="noopener" class="gso-air-file-fallback">${escapeHtml(
            file?.type_text || "File"
          )}</a>`;

    const primaryBadge = file?.is_primary
      ? '<span class="rounded-full bg-success/10 px-3 py-1 text-xs font-medium text-success">Primary</span>'
      : "";

    const primaryAction =
      mutable && !file?.is_primary
        ? `<button type="button" class="ti-btn ti-btn-sm ti-btn-light" data-action="set-air-file-primary" data-id="${escapeHtml(
            file?.id || ""
          )}">
            Set Primary
          </button>`
        : "";

    const deleteAction = mutable
      ? `<button type="button" class="ti-btn ti-btn-sm ti-btn-danger" data-action="delete-air-file" data-id="${escapeHtml(
          file?.id || ""
        )}">
          Delete
        </button>`
      : "";

    return `
      <div class="gso-air-file-card">
        ${previewMarkup}
        <div class="p-4 space-y-3">
          <div class="flex items-start justify-between gap-3">
            <div>
              <div class="font-medium text-defaulttextcolor dark:text-white">${escapeHtml(
                file?.original_name || "AIR File"
              )}</div>
              <div class="mt-1 text-xs text-[#8c9097] dark:text-white/50">
                ${escapeHtml(file?.type_text || "File")}
                ${file?.size_text ? ` | ${escapeHtml(file.size_text)}` : ""}
                ${file?.created_at_text ? ` | ${escapeHtml(file.created_at_text)}` : ""}
              </div>
            </div>
            ${primaryBadge}
          </div>
          <div class="flex flex-wrap items-center gap-2">
            <a href="${escapeHtml(
              previewUrl
            )}" target="_blank" rel="noopener" class="ti-btn ti-btn-sm ti-btn-primary">
              Open
            </a>
            ${primaryAction}
            ${deleteAction}
          </div>
        </div>
      </div>
    `;
  }

  function renderPayload(payload) {
    const config = window.__gsoAirEdit || {};
    const air = payload?.air || {};
    const files = Array.isArray(payload?.files) ? payload.files : [];
    const grid = qs("gsoAirFileGrid");
    const emptyState = qs("gsoAirFileEmpty");
    const uploadPanel = qs("gsoAirFileUploadPanel");
    const uploadHint = qs("gsoAirFileUploadHint");
    const uploadButton = qs("gsoAirFileUploadBtn");
    const uploadInput = qs("gsoAirFilesInput");
    const uploadType = qs("gsoAirFilesType");
    const countElement = qs("gsoAirFileCount");
    const countSummaryElement = qs("gsoAirFileCountSummary");
    const driveFolderStatusElement = qs("gsoAirDriveFolderStatus");
    const mutable = canMutateFiles(config, air);

    if (countElement) {
      countElement.textContent = String(files.length);
    }

    if (countSummaryElement) {
      countSummaryElement.textContent = String(files.length);
    }

    if (driveFolderStatusElement) {
      driveFolderStatusElement.textContent = air?.drive_folder_id
        ? "Ready"
        : air?.po_number
        ? "Pending upload"
        : "Needs PO number";
    }

    if (uploadPanel) {
      uploadPanel.classList.toggle("hidden", !config.canManage);
    }

    [uploadButton, uploadInput, uploadType].forEach((element) => {
      if (!element) return;
      element.disabled = !mutable;
    });

    if (uploadHint) {
      if (!config.canManage) {
        uploadHint.textContent =
          "You can review AIR documents here, but only users with AIR modification access can upload or change them.";
      } else if (air?.is_archived) {
        uploadHint.textContent =
          "Archived AIR records are view-only. Restore the record before changing document files.";
      } else if (!air?.po_number) {
        uploadHint.textContent =
          "Save a PO number in the AIR header before uploading documents.";
      } else {
        uploadHint.textContent =
          "Images and PDFs are stored in the AIR document folder on Google Drive.";
      }
    }

    if (grid) {
      grid.innerHTML = files
        .map((file) => renderFileCard(file, config, air))
        .join("");
    }

    if (emptyState) {
      emptyState.classList.toggle("hidden", files.length !== 0);
    }
  }

  async function loadFiles() {
    const config = window.__gsoAirEdit || {};

    clearError();

    const response = await fetch(config.filesIndexUrl, {
      method: "GET",
      headers: {
        Accept: "application/json",
      },
    });

    const parsed = await parseResponse(response);

    if (!parsed.ok) {
      throw new Error(parsed.message);
    }

    renderPayload(parsed.data?.data || {});
  }

  async function uploadFiles() {
    const config = window.__gsoAirEdit || {};
    const input = qs("gsoAirFilesInput");
    const typeInput = qs("gsoAirFilesType");
    const files = Array.from(input?.files || []);

    if (files.length === 0) {
      showError("Choose at least one image or PDF to upload.");
      return;
    }

    clearError();

    const formData = new FormData();
    files.forEach((file) => {
      formData.append("files[]", file);
    });

    if ((typeInput?.value || "").trim() !== "") {
      formData.append("type", typeInput.value.trim());
    }

    const response = await fetch(config.filesStoreUrl, {
      method: "POST",
      headers: {
        "X-CSRF-TOKEN": getCsrf(config),
        Accept: "application/json",
      },
      body: formData,
    });

    const parsed = await parseResponse(response);

    if (parsed.status === 422) {
      const errors = parsed.data?.errors || {};
      showError(
        errors?.files?.[0] ||
          errors?.["files.0"]?.[0] ||
          errors?.type?.[0] ||
          errors?.po_number?.[0] ||
          parsed.message
      );
      return;
    }

    if (!parsed.ok) {
      showError(parsed.message);
      return;
    }

    if (input) {
      input.value = "";
    }

    renderPayload(parsed.data?.data || {});

    await Swal.fire({
      icon: "success",
      title: "Uploaded",
      timer: 900,
      showConfirmButton: false,
    });
  }

  async function setPrimary(fileId) {
    const config = window.__gsoAirEdit || {};

    clearError();

    const response = await fetch(
      config.filePrimaryUrlTemplate.replace("__FILE__", encodeURIComponent(fileId)),
      {
        method: "PUT",
        headers: {
          "X-CSRF-TOKEN": getCsrf(config),
          Accept: "application/json",
        },
      }
    );

    const parsed = await parseResponse(response);

    if (!parsed.ok) {
      showError(parsed.message);
      return;
    }

    renderPayload(parsed.data?.data || {});
  }

  async function deleteFile(fileId) {
    const config = window.__gsoAirEdit || {};
    const confirmation = await Swal.fire({
      icon: "warning",
      title: "Delete AIR file?",
      text: "This will remove the document from the AIR record.",
      showCancelButton: true,
      confirmButtonText: "Delete",
      cancelButtonText: "Cancel",
    });

    if (!confirmation.isConfirmed) {
      return;
    }

    clearError();

    const response = await fetch(
      config.fileDestroyUrlTemplate.replace("__FILE__", encodeURIComponent(fileId)),
      {
        method: "DELETE",
        headers: {
          "X-CSRF-TOKEN": getCsrf(config),
          Accept: "application/json",
        },
      }
    );

    const parsed = await parseResponse(response);

    if (!parsed.ok) {
      showError(parsed.message);
      return;
    }

    renderPayload(parsed.data?.data || {});

    await Swal.fire({
      icon: "success",
      title: "Deleted",
      timer: 900,
      showConfirmButton: false,
    });
  }

  onReady(function () {
    const page = qs("gso-air-edit-page");
    if (!page) return;

    const hasFileWorkspace = Boolean(
      qs("gsoAirFileGrid") ||
        qs("gsoAirFileEmpty") ||
        qs("gsoAirFileUploadPanel") ||
        qs("gsoAirFileError")
    );

    if (!hasFileWorkspace) {
      window.__gsoAirFiles = {
        async reload() {},
      };

      return;
    }

    const config = window.__gsoAirEdit || {};
    if (!config.filesIndexUrl) return;

    qs("gsoAirFileUploadBtn")?.addEventListener("click", uploadFiles);

    document.addEventListener("click", async (event) => {
      const primaryButton = event.target.closest('[data-action="set-air-file-primary"]');
      if (primaryButton) {
        const fileId = primaryButton.getAttribute("data-id");
        if (!fileId) return;

        await setPrimary(fileId);
        return;
      }

      const deleteButton = event.target.closest('[data-action="delete-air-file"]');
      if (!deleteButton) return;

      const fileId = deleteButton.getAttribute("data-id");
      if (!fileId) return;

      await deleteFile(fileId);
    });

    window.__gsoAirFiles = {
      async reload() {
        await loadFiles();
      },
    };

    loadFiles().catch((error) => {
      showError(
        error instanceof Error ? error.message : "The AIR documents could not be loaded."
      );
    });
  });
})();
