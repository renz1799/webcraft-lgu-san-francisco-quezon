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
      window.__gsoFundSources?.csrf ||
      ""
    );
  }

  function qs(id) {
    return document.getElementById(id);
  }

  function clearErrors() {
    const formError = qs("fundSourceFormError");
    if (formError) {
      formError.classList.add("hidden");
      formError.textContent = "";
    }

    [
      "fundSourceFundClusterErr",
      "fundSourceCodeErr",
      "fundSourceNameErr",
      "fundSourceIsActiveErr",
    ].forEach((id) => {
      const element = qs(id);
      if (!element) return;
      element.classList.add("hidden");
      element.textContent = "";
    });
  }

  function openForCreate() {
    clearErrors();
    qs("fundSourceModalTitle").textContent = "Add Fund Source";
    qs("fundSourceId").value = "";
    qs("fundSourceFundClusterId").value = "";
    qs("fundSourceCode").value = "";
    qs("fundSourceName").value = "";
    qs("fundSourceIsActive").value = "1";
  }

  function openForEdit(row) {
    clearErrors();
    qs("fundSourceModalTitle").textContent = "Edit Fund Source";
    qs("fundSourceId").value = row?.id || "";
    qs("fundSourceFundClusterId").value = row?.fund_cluster_id || "";
    qs("fundSourceCode").value = row?.code || "";
    qs("fundSourceName").value = row?.name || "";
    qs("fundSourceIsActive").value = row?.is_active ? "1" : "0";

    if (window.HSOverlay) {
      window.HSOverlay.open(qs("fundSourceModal"));
    }
  }

  function closeModal() {
    if (window.HSOverlay) {
      window.HSOverlay.close(qs("fundSourceModal"));
    }
  }

  function showFormError(message) {
    const formError = qs("fundSourceFormError");
    if (!formError) return;
    formError.textContent = message || "Something went wrong.";
    formError.classList.remove("hidden");
  }

  function applyValidationErrors(errors) {
    const map = {
      fund_cluster_id: "fundSourceFundClusterErr",
      code: "fundSourceCodeErr",
      name: "fundSourceNameErr",
      is_active: "fundSourceIsActiveErr",
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
    const config = window.__gsoFundSources || {};
    const id = (qs("fundSourceId")?.value || "").trim();
    const payload = {
      fund_cluster_id: (qs("fundSourceFundClusterId")?.value || "").trim(),
      code: (qs("fundSourceCode")?.value || "").trim(),
      name: (qs("fundSourceName")?.value || "").trim(),
      is_active: (qs("fundSourceIsActive")?.value || "1") === "1",
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
      title: isEdit ? "Save changes?" : "Create fund source?",
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

    if (typeof window.__gsoFundSourcesReload === "function") {
      window.__gsoFundSourcesReload();
    }
  }

  onReady(function () {
    if (!window.__gsoFundSources?.canManage) return;

    document.addEventListener("click", (event) => {
      const createButton = event.target.closest('[data-hs-overlay="#fundSourceModal"][data-mode="create"]');
      if (createButton) {
        openForCreate();
        return;
      }

      const editButton = event.target.closest('[data-action="edit-fund-source"]');
      if (!editButton) return;

      const rowJson = editButton.getAttribute("data-row");
      if (!rowJson) return;

      try {
        openForEdit(JSON.parse(rowJson));
      } catch {
        // ignore malformed row payloads
      }
    });

    qs("fundSourceSaveBtn")?.addEventListener("click", save);
  });
})();
