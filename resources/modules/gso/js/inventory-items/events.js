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
    const errorElement = qs("gsoInventoryEventsError");
    if (!errorElement) return;
    errorElement.textContent = "";
    errorElement.classList.add("hidden");
  }

  function showError(message) {
    const errorElement = qs("gsoInventoryEventsError");
    if (!errorElement) return;
    errorElement.textContent = message || "Something went wrong.";
    errorElement.classList.remove("hidden");
  }

  function openModal() {
    if (window.HSOverlay) {
      window.HSOverlay.open(qs("gsoInventoryEventsModal"));
    }
  }

  function renderEventCard(event) {
    const metaParts = [
      event?.event_date_text || "-",
      event?.movement_text || "No quantity change",
      event?.reference_label || "",
    ]
      .filter(Boolean)
      .join(" | ");

    const notesMarkup = event?.notes
      ? `<div class="mt-2 text-sm text-[#8c9097]">${escapeHtml(event.notes)}</div>`
      : "";

    return `
      <div class="gso-inventory-event-card">
        <div class="flex items-start justify-between gap-3">
          <div>
            <div class="font-medium text-defaulttextcolor dark:text-white">${escapeHtml(
              event?.event_type_text || "Event"
            )}</div>
            <div class="text-xs text-[#8c9097] mt-1">${escapeHtml(metaParts)}</div>
          </div>
          <div class="text-right text-xs text-[#8c9097]">
            <div>Status: ${escapeHtml(event?.status_text || "None")}</div>
            <div>Condition: ${escapeHtml(event?.condition_text || "None")}</div>
          </div>
        </div>
        <div class="mt-3 grid grid-cols-1 gap-2 md:grid-cols-3 text-sm">
          <div><span class="text-[#8c9097]">Department:</span> ${escapeHtml(
            event?.department_label || "None"
          )}</div>
          <div><span class="text-[#8c9097]">Accountable:</span> ${escapeHtml(
            event?.person_accountable || event?.officer_snapshot || "None"
          )}</div>
          <div><span class="text-[#8c9097]">Recorded By:</span> ${escapeHtml(
            event?.performed_by_label || "System"
          )}</div>
        </div>
        ${notesMarkup}
      </div>
    `;
  }

  function renderPayload(payload) {
    const config = window.__gsoInventoryItems || {};
    const inventoryItem = payload?.inventory_item || {};
    const events = Array.isArray(payload?.events) ? payload.events : [];
    const titleElement = qs("gsoInventoryEventsModalTitle");
    const subtitleElement = qs("gsoInventoryEventsModalSubtitle");
    const list = qs("gsoInventoryEventsList");
    const emptyState = qs("gsoInventoryEventsEmpty");
    const formPanel = qs("gsoInventoryEventsFormPanel");

    if (titleElement) {
      titleElement.textContent = inventoryItem?.label || "Inventory History";
    }

    if (subtitleElement) {
      const parts = [
        inventoryItem?.property_number ? `Property: ${inventoryItem.property_number}` : null,
        inventoryItem?.po_number ? `PO: ${inventoryItem.po_number}` : "PO: not set",
      ].filter(Boolean);
      subtitleElement.textContent =
        parts.join(" | ") || "Track lifecycle and custody context for this inventory item.";
    }

    if (list) {
      list.innerHTML = events.map((event) => renderEventCard(event)).join("");
    }

    if (emptyState) {
      emptyState.classList.toggle("hidden", events.length !== 0);
    }

    if (formPanel) {
      const canMutate = Boolean(config.canManage) && !inventoryItem?.is_archived;
      formPanel.classList.toggle("hidden", !config.canManage);
      formPanel.querySelectorAll("input,textarea,select,button").forEach((element) => {
        element.disabled = !canMutate;
      });
    }
  }

  function resetForm() {
    if (qs("gsoInventoryEventType")?.options?.length > 0) {
      qs("gsoInventoryEventType").selectedIndex = 0;
    }
    if (qs("gsoInventoryEventDate")) {
      qs("gsoInventoryEventDate").value = new Date().toISOString().slice(0, 16);
    }
    if (qs("gsoInventoryEventQuantity")) qs("gsoInventoryEventQuantity").value = "0";
    if (qs("gsoInventoryEventDepartmentId")) qs("gsoInventoryEventDepartmentId").value = "";
    if (qs("gsoInventoryEventStatus")) qs("gsoInventoryEventStatus").value = "";
    if (qs("gsoInventoryEventCondition")) qs("gsoInventoryEventCondition").value = "";
    if (qs("gsoInventoryEventPersonAccountable")) qs("gsoInventoryEventPersonAccountable").value = "";
    if (qs("gsoInventoryEventReferenceType")) qs("gsoInventoryEventReferenceType").value = "";
    if (qs("gsoInventoryEventReferenceNo")) qs("gsoInventoryEventReferenceNo").value = "";
    if (qs("gsoInventoryEventNotes")) qs("gsoInventoryEventNotes").value = "";
  }

  let currentInventoryItemId = "";

  async function loadEvents(inventoryItemId) {
    const config = window.__gsoInventoryItems || {};
    const response = await fetch(
      endpointFromTemplate(config.eventIndexUrlTemplate, { "__ID__": inventoryItemId }),
      {
        method: "GET",
        headers: { Accept: "application/json" },
      }
    );

    const data = await response.json().catch(() => ({}));
    if (!response.ok) {
      throw new Error(data?.message || "Inventory history could not be loaded.");
    }

    currentInventoryItemId = inventoryItemId;
    clearError();
    renderPayload(data?.data || {});
    resetForm();
    openModal();
  }

  async function saveEvent() {
    const config = window.__gsoInventoryItems || {};

    if (!currentInventoryItemId) {
      showError("Select an inventory item first.");
      return;
    }

    clearError();

    const payload = {
      event_type: (qs("gsoInventoryEventType")?.value || "").trim(),
      event_date: (qs("gsoInventoryEventDate")?.value || "").trim(),
      quantity: Number(qs("gsoInventoryEventQuantity")?.value || 0),
      department_id: (qs("gsoInventoryEventDepartmentId")?.value || "").trim() || null,
      status: (qs("gsoInventoryEventStatus")?.value || "").trim() || null,
      condition: (qs("gsoInventoryEventCondition")?.value || "").trim() || null,
      person_accountable:
        (qs("gsoInventoryEventPersonAccountable")?.value || "").trim() || null,
      reference_type: (qs("gsoInventoryEventReferenceType")?.value || "").trim() || null,
      reference_no: (qs("gsoInventoryEventReferenceNo")?.value || "").trim() || null,
      notes: (qs("gsoInventoryEventNotes")?.value || "").trim() || null,
    };

    const response = await fetch(
      endpointFromTemplate(config.eventStoreUrlTemplate, { "__ID__": currentInventoryItemId }),
      {
        method: "POST",
        headers: {
          "X-CSRF-TOKEN": getCsrf(),
          Accept: "application/json",
          "Content-Type": "application/json",
        },
        body: JSON.stringify(payload),
      }
    );

    const data = await response.json().catch(() => ({}));
    if (response.status === 422) {
      const errors = data?.errors || {};
      showError(
        errors?.event_type?.[0] ||
          errors?.event_date?.[0] ||
          errors?.quantity?.[0] ||
          data?.message ||
          "Validation failed."
      );
      return;
    }

    if (!response.ok) {
      showError(data?.message || "Unable to add event.");
      return;
    }

    renderPayload(data?.data || {});
    resetForm();

    if (typeof window.__gsoInventoryItemsReload === "function") {
      window.__gsoInventoryItemsReload();
    }

    await Swal.fire({
      icon: "success",
      title: "Event Added",
      timer: 900,
      showConfirmButton: false,
    });
  }

  onReady(function () {
    if (!qs("gsoInventoryEventsModal")) return;

    qs("gsoInventoryEventSaveBtn")?.addEventListener("click", saveEvent);

    document.addEventListener("click", async (event) => {
      const openButton = event.target.closest('[data-action="inventory-item-events"]');
      if (!openButton) return;

      const inventoryItemId = openButton.getAttribute("data-id");
      if (!inventoryItemId) return;

      try {
        await loadEvents(inventoryItemId);
      } catch (error) {
        await Swal.fire({
          icon: "error",
          title: "Unable to load events",
          text:
            error instanceof Error
              ? error.message
              : "The inventory history could not be loaded.",
        });
      }
    });

    qs("gsoInventoryEventsModal")?.addEventListener("hidden.hs.overlay", () => {
      currentInventoryItemId = "";
      clearError();
      if (qs("gsoInventoryEventsList")) qs("gsoInventoryEventsList").innerHTML = "";
      if (qs("gsoInventoryEventsEmpty")) qs("gsoInventoryEventsEmpty").classList.add("hidden");
      resetForm();
    });
  });
})();
