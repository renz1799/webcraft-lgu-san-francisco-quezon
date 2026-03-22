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
      window.__gsoFundClusters?.csrf ||
      ""
    );
  }

  function qs(id) {
    return document.getElementById(id);
  }

  function clearErrors() {
    const formError = qs("fundClusterFormError");
    if (formError) {
      formError.classList.add("hidden");
      formError.textContent = "";
    }

    ["fundClusterCodeErr", "fundClusterNameErr", "fundClusterIsActiveErr"].forEach((id) => {
      const element = qs(id);
      if (!element) return;
      element.classList.add("hidden");
      element.textContent = "";
    });
  }

  function openForCreate() {
    clearErrors();
    qs("fundClusterModalTitle").textContent = "Add Fund Cluster";
    qs("fundClusterId").value = "";
    qs("fundClusterCode").value = "";
    qs("fundClusterName").value = "";
    qs("fundClusterIsActive").value = "1";
  }

  function openForEdit(row) {
    clearErrors();
    qs("fundClusterModalTitle").textContent = "Edit Fund Cluster";
    qs("fundClusterId").value = row?.id || "";
    qs("fundClusterCode").value = row?.code || "";
    qs("fundClusterName").value = row?.name || "";
    qs("fundClusterIsActive").value = row?.is_active ? "1" : "0";

    if (window.HSOverlay) {
      window.HSOverlay.open(qs("fundClusterModal"));
    }
  }

  function closeModal() {
    if (window.HSOverlay) {
      window.HSOverlay.close(qs("fundClusterModal"));
    }
  }

  function showFormError(message) {
    const formError = qs("fundClusterFormError");
    if (!formError) return;
    formError.textContent = message || "Something went wrong.";
    formError.classList.remove("hidden");
  }

  function applyValidationErrors(errors) {
    const map = {
      code: "fundClusterCodeErr",
      name: "fundClusterNameErr",
      is_active: "fundClusterIsActiveErr",
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
    const config = window.__gsoFundClusters || {};
    const id = (qs("fundClusterId")?.value || "").trim();
    const payload = {
      code: (qs("fundClusterCode")?.value || "").trim(),
      name: (qs("fundClusterName")?.value || "").trim(),
      is_active: (qs("fundClusterIsActive")?.value || "1") === "1",
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
      title: isEdit ? "Save changes?" : "Create fund cluster?",
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

    if (typeof window.__gsoFundClustersReload === "function") {
      window.__gsoFundClustersReload();
    }
  }

  onReady(function () {
    if (!window.__gsoFundClusters?.canManage) return;

    document.addEventListener("click", (event) => {
      const createButton = event.target.closest('[data-hs-overlay="#fundClusterModal"][data-mode="create"]');
      if (createButton) {
        openForCreate();
        return;
      }

      const editButton = event.target.closest('[data-action="edit-fund-cluster"]');
      if (!editButton) return;

      const rowJson = editButton.getAttribute("data-row");
      if (!rowJson) return;

      try {
        openForEdit(JSON.parse(rowJson));
      } catch {
        // ignore malformed row payloads
      }
    });

    qs("fundClusterSaveBtn")?.addEventListener("click", save);
  });
})();
