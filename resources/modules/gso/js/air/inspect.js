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

  function normalizeText(value) {
    return String(value ?? "").replace(/\s+/g, " ").trim();
  }

  function buildUrl(template, replacements = {}) {
    let url = String(template || "");

    Object.entries(replacements).forEach(([key, value]) => {
      url = url.replace(key, encodeURIComponent(String(value ?? "")));
    });

    return url;
  }

  function isModalOpen(element) {
    if (!element) return false;

    return (
      element.classList.contains("open") ||
      element.classList.contains("opened") ||
      element.classList.contains("is-open")
    );
  }

  function syncModalBodyState() {
    document.body.classList.toggle(
      "overflow-hidden",
      document.querySelector(".hs-overlay.open, .hs-overlay.opened, .gso-air-inspection-modal.is-open") !== null,
    );
  }

  function setModalOpen(element, open) {
    if (!element) return;

    if (
      window.HSOverlay &&
      typeof window.HSOverlay.open === "function" &&
      typeof window.HSOverlay.close === "function" &&
      element.classList.contains("hs-overlay")
    ) {
      if (open) {
        window.HSOverlay.open(element);
      } else {
        window.HSOverlay.close(element);
      }

      return;
    }

    element.classList.toggle("hidden", !open);
    element.classList.toggle("is-open", open);
    element.classList.toggle("open", open);
    element.classList.toggle("opened", open);

    if (open) {
      element.setAttribute("aria-overlay", "true");
      element.setAttribute("tabindex", "-1");
    } else {
      element.removeAttribute("aria-overlay");
      element.removeAttribute("tabindex");
    }

    syncModalBodyState();
  }

  function validationHtml(errors) {
    if (!errors || typeof errors !== "object") {
      return "";
    }

    const rows = [];
    Object.values(errors).forEach((messages) => {
      (Array.isArray(messages) ? messages : [messages]).forEach((message) => {
        rows.push(`<li>${escapeHtml(message)}</li>`);
      });
    });

    return rows.length > 0 ? `<ul class="pl-4 text-left">${rows.join("")}</ul>` : "";
  }

  function showLoadingAlert(title, text = "") {
    Swal.fire({
      title,
      text,
      allowOutsideClick: false,
      allowEscapeKey: false,
      showConfirmButton: false,
      didOpen: () => Swal.showLoading(),
    });
  }

  function showToast(icon, title) {
    return Swal.fire({
      toast: true,
      position: "top-end",
      icon,
      title,
      showConfirmButton: false,
      timer: 1500,
      timerProgressBar: true,
    });
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
          ? "You do not have permission to manage this AIR inspection."
          : response.status === 404
          ? "The requested AIR inspection record could not be found."
          : response.status === 419
          ? "Your security token expired. Refresh the page and try again."
          : "The request could not be completed."),
    };
  }

  onReady(function () {
    const page = qs("gso-air-inspect-page");
    if (!page) return;

    const config = window.__gsoAirInspection || {};
    let state = {
      air: { ...(config.air || {}) },
      items: Array.isArray(config.items) ? [...config.items] : [],
    };
    let unitState = null;
    let unitRows = [];
    let activeAirItemId = null;
    let activeComponentUnitKey = null;
    let fileState = null;
    let activeUnitId = null;
    let inspectionBaseline = null;
    let inspectionGuardState = null;
    let promotionState = {
      hasEligibleTargets: null,
      isLoading: false,
      targetCount: 0,
    };
    let unitRowsBaseline = [];
    let activeTabletTab = "receiving";

    const toolbar = qs("gsoAirInspectionToolbar");
    const inspectionTabs = qs("gsoAirInspectionTabs");
    const tabletTabButtons = Array.from(
      page.querySelectorAll("[data-air-inspection-tab]"),
    );
    const tabletPanels = Array.from(
      page.querySelectorAll("[data-air-inspection-panel]"),
    );
    const tabletViewport = window.matchMedia("(max-width: 1023.98px)");
    const formError = qs("gsoAirInspectionFormError");
    const itemsContainer = qs("gsoAirInspectionItems");
    const statusText = qs("gsoAirInspectionStatusText");
    const dateInspectedText = qs("gsoAirInspectionDateInspectedText");
    const verifiedText = qs("gsoAirInspectionVerifiedText");
    const itemCountText = qs("gsoAirInspectionItemCount");
    const unitCountText = qs("gsoAirInspectionUnitCount");
    const invoiceNumberInput = qs("gsoAirInspectionInvoiceNumber");
    const invoiceDateInput = qs("gsoAirInspectionInvoiceDate");
    const dateReceivedInput = qs("gsoAirInspectionDateReceived");
    const completenessSelect = qs("gsoAirInspectionReceivedCompleteness");
    const receivedNotesInput = qs("gsoAirInspectionReceivedNotes");
    const saveButton = qs("gsoAirInspectionSaveBtn");
    const finalizeButton = qs("gsoAirInspectionFinalizeBtn");
    const followUpButton = qs("gsoAirInspectionFollowUpBtn");
    const reopenButton = qs("gsoAirInspectionReopenBtn");
    const promoteButton = qs("gsoAirInspectionPromoteBtn");
    const completenessHint = qs("gsoAirInspectionCompletenessHint");
    const finalizeHint = qs("gsoAirInspectionFinalizeHint");

    const unitModal = qs("gsoAirUnitModal");
    const unitModalTitle = qs("gsoAirUnitModalTitle");
    const unitModalSubtitle = qs("gsoAirUnitModalSubtitle");
    const unitRowsContainer = qs("gsoAirUnitRows");
    const unitNotice = qs("gsoAirUnitNotice");
    const unitError = qs("gsoAirUnitError");
    const unitSaveButton = qs("gsoAirUnitSaveBtn");

    const componentModal = qs("gsoAirUnitComponentModal");
    const componentModalTitle = qs("gsoAirUnitComponentModalTitle");
    const componentModalSubtitle = qs("gsoAirUnitComponentModalSubtitle");
    const componentRowsContainer = qs("gsoAirUnitComponentRows");
    const componentEmpty = qs("gsoAirUnitComponentEmpty");
    const componentError = qs("gsoAirUnitComponentError");
    const componentTemplateNote = qs("gsoAirUnitComponentTemplateNote");
    const componentAddRowButton = qs("gsoAirUnitComponentAddRowBtn");

    const fileModal = qs("gsoAirUnitFileModal");
    const fileModalTitle = qs("gsoAirUnitFileModalTitle");
    const fileModalSubtitle = qs("gsoAirUnitFileModalSubtitle");
    const fileGrid = qs("gsoAirUnitFileGrid");
    const fileEmpty = qs("gsoAirUnitFileEmpty");
    const fileError = qs("gsoAirUnitFileError");
    const fileInput = qs("gsoAirUnitFileInput");
    const fileTypeInput = qs("gsoAirUnitFileType");
    const fileCaptionInput = qs("gsoAirUnitFileCaption");
    const fileUploadButton = qs("gsoAirUnitFileUploadBtn");
    const inspectionToolbarState = {
      dirtyCount: 0,
      isSaving: false,
      isFinalizing: false,
      defaultSaveLabel: saveButton?.textContent?.trim() || "Save Inspection",
      defaultFinalizeLabel: finalizeButton?.textContent?.trim() || "Finalize",
      defaultPromoteLabel: promoteButton?.textContent?.trim() || "Promote to Inventory",
    };
    const unitToolbarState = {
      dirtyCount: 0,
      isSaving: false,
      defaultSaveLabel: unitSaveButton?.textContent?.trim() || "Save Unit Rows",
    };
    const fileUploadState = {
      isUploading: false,
      defaultLabel: fileUploadButton?.textContent?.trim() || "Upload Images",
    };

    function canEdit() {
      return !!config.canEditInspection && !!state.air?.can_edit_inspection;
    }

    function canPromote() {
      return (
        !!config.canPromoteInventory &&
        String(state.air?.status || "") === "inspected"
      );
    }

    function getTodayDateString() {
      const now = new Date();
      const timezoneOffset = now.getTimezoneOffset() * 60 * 1000;

      return new Date(now.getTime() - timezoneOffset).toISOString().slice(0, 10);
    }

    function collectInspectionProgress() {
      const unresolvedItems = (state.items || []).filter((item) => {
        const ordered = Math.max(0, Number(item?.qty_ordered || 0));
        const accepted = Math.max(0, Number(item?.qty_accepted || 0));

        return accepted < ordered;
      });
      const needsFollowUp = unresolvedItems.length > 0;
      const singleItemIncomplete =
        (state.items || []).length === 1 && unresolvedItems.length > 0;

      return {
        unresolvedItems,
        needsFollowUp,
        singleItemIncomplete,
        expectedCompleteness: needsFollowUp ? "partial" : "complete",
      };
    }

    function getAutoReceivedCompleteness() {
      return collectInspectionProgress().expectedCompleteness;
    }

    function getDefaultedDateValue(value) {
      const normalized = normalizeText(value || "");

      if (normalized !== "") {
        return normalized;
      }

      return canEdit() ? getTodayDateString() : "";
    }

    function hasPromotionTargets(eligibility) {
      const propertyUnits = Array.isArray(eligibility?.property_units)
        ? eligibility.property_units
        : [];
      const blockedPropertyUnits = Array.isArray(eligibility?.blocked_property_units)
        ? eligibility.blocked_property_units
        : [];
      const consumables = Array.isArray(eligibility?.consumables)
        ? eligibility.consumables
        : [];

      return (
        propertyUnits.length > 0 ||
        blockedPropertyUnits.length > 0 ||
        consumables.length > 0
      );
    }

    function getPromotionTargetCount(eligibility) {
      const propertyUnits = Array.isArray(eligibility?.property_units)
        ? eligibility.property_units
        : [];
      const consumables = Array.isArray(eligibility?.consumables)
        ? eligibility.consumables
        : [];

      return propertyUnits.length + consumables.length;
    }

    function syncPromoteButtonState() {
      if (!promoteButton) return;

      const shouldShow =
        canPromote() && promotionState.hasEligibleTargets === true;

      promoteButton.textContent =
        shouldShow && promotionState.targetCount > 0
          ? `${inspectionToolbarState.defaultPromoteLabel} (${promotionState.targetCount})`
          : inspectionToolbarState.defaultPromoteLabel;
      promoteButton.hidden = !shouldShow;
      promoteButton.classList.toggle("hidden", !shouldShow);
      promoteButton.style.display = shouldShow ? "" : "none";
      promoteButton.setAttribute("aria-hidden", shouldShow ? "false" : "true");
      promoteButton.disabled = !shouldShow || promotionState.isLoading;
    }

    async function refreshPromotionEligibility(options = {}) {
      const { force = false } = options;

      if (!promoteButton) {
        return false;
      }

      if (!canPromote()) {
        promotionState.hasEligibleTargets = false;
        promotionState.isLoading = false;
        promotionState.targetCount = 0;
        syncPromoteButtonState();
        return false;
      }

      if (promotionState.isLoading) {
        return promotionState.hasEligibleTargets === true;
      }

      if (promotionState.hasEligibleTargets !== null && !force) {
        syncPromoteButtonState();
        return promotionState.hasEligibleTargets === true;
      }

      promotionState.isLoading = true;
      syncPromoteButtonState();

      try {
        const parsed = await loadPromotionEligibility();
        const eligibility = parsed.ok ? parsed.data?.data || {} : {};

        promotionState.hasEligibleTargets =
          parsed.ok && hasPromotionTargets(eligibility);
        promotionState.targetCount =
          parsed.ok ? getPromotionTargetCount(eligibility) : 0;

        return promotionState.hasEligibleTargets === true;
      } catch (error) {
        promotionState.hasEligibleTargets = false;
        promotionState.targetCount = 0;
        return false;
      } finally {
        promotionState.isLoading = false;
        syncPromoteButtonState();
      }
    }

    function showFormError(message) {
      if (!formError) return;

      if (!message) {
        formError.classList.add("hidden");
        formError.textContent = "";
        return;
      }

      formError.textContent = message;
      formError.classList.remove("hidden");
    }

    function showUnitError(message) {
      if (!unitError) return;

      if (!message) {
        unitError.classList.add("hidden");
        unitError.textContent = "";
        return;
      }

      unitError.textContent = message;
      unitError.classList.remove("hidden");
    }

    function showFileError(message) {
      if (!fileError) return;

      if (!message) {
        fileError.classList.add("hidden");
        fileError.textContent = "";
        return;
      }

      fileError.textContent = message;
      fileError.classList.remove("hidden");
    }

    function showComponentError(message) {
      if (!componentError) return;

      if (!message) {
        componentError.classList.add("hidden");
        componentError.textContent = "";
        return;
      }

      componentError.textContent = message;
      componentError.classList.remove("hidden");
    }

    function syncHeaderInputs() {
      if (invoiceNumberInput) invoiceNumberInput.value = state.air?.invoice_number || "";
      if (invoiceDateInput) {
        invoiceDateInput.value = getDefaultedDateValue(state.air?.invoice_date || "");
      }
      if (dateReceivedInput) {
        dateReceivedInput.value = getDefaultedDateValue(state.air?.date_received || "");
      }
      if (completenessSelect) {
        completenessSelect.value = getAutoReceivedCompleteness();
        completenessSelect.disabled = true;
        completenessSelect.setAttribute("aria-disabled", "true");
        completenessSelect.title = "Received Completeness is auto-detected from the accepted inspection quantities.";
      }
      if (receivedNotesInput) receivedNotesInput.value = state.air?.received_notes || "";
    }

    function updateInspectionToolbar() {
      const dirtyCount = Math.max(0, Number(inspectionToolbarState.dirtyCount || 0));
      const finalizeBlocked =
        !!inspectionGuardState &&
        (inspectionGuardState.missingFields.length > 0 ||
          inspectionGuardState.completenessMismatch ||
          inspectionGuardState.singleItemIncomplete);

      if (saveButton) {
        saveButton.textContent =
          dirtyCount > 0
            ? `${inspectionToolbarState.defaultSaveLabel} (${dirtyCount})`
            : inspectionToolbarState.defaultSaveLabel;
        saveButton.disabled =
          !canEdit() ||
          dirtyCount === 0 ||
          inspectionToolbarState.isSaving ||
          inspectionToolbarState.isFinalizing;
      }

      if (finalizeButton) {
        finalizeButton.textContent =
          dirtyCount > 0
            ? "Save and Finalize"
            : inspectionToolbarState.defaultFinalizeLabel;
        finalizeButton.disabled =
          !canEdit() ||
          finalizeBlocked ||
          inspectionToolbarState.isSaving ||
          inspectionToolbarState.isFinalizing;
        finalizeButton.title = finalizeBlocked
          ? inspectionGuardState?.finalizeReason || ""
          : "";
      }

      syncStickyOffsets();
    }

    function updateFileUploadButton() {
      if (!fileUploadButton) return;

      fileUploadButton.textContent = fileUploadState.isUploading
        ? "Uploading..."
        : fileUploadState.defaultLabel;
      fileUploadButton.disabled = fileUploadState.isUploading || !canEdit();
    }

    function syncStickyOffsets() {
      if (!page) return;

      const toolbarHeight = toolbar
        ? Math.ceil(toolbar.getBoundingClientRect().height)
        : 0;
      const tabsVisible =
        inspectionTabs &&
        window.getComputedStyle(inspectionTabs).display !== "none";
      const tabsHeight =
        tabsVisible && inspectionTabs
          ? Math.ceil(inspectionTabs.getBoundingClientRect().height)
          : 0;

      page.style.setProperty(
        "--gso-air-inspection-toolbar-height",
        `${toolbarHeight}px`,
      );
      page.style.setProperty(
        "--gso-air-inspection-tabs-height",
        `${tabsHeight}px`,
      );
    }

    function applyTabletLayout() {
      const isTablet = tabletViewport.matches;

      tabletPanels.forEach((panel) => {
        const panelKey = panel.dataset.airInspectionPanel || "";
        panel.classList.toggle(
          "is-tablet-hidden",
          isTablet && panelKey !== activeTabletTab,
        );
      });

      tabletTabButtons.forEach((button) => {
        const active = (button.dataset.airInspectionTab || "") === activeTabletTab;
        button.classList.toggle("is-active", active);
        button.setAttribute("aria-pressed", active ? "true" : "false");
      });

      syncStickyOffsets();
    }

    function setActiveTabletTab(tabKey) {
      if (!tabKey) return;

      activeTabletTab = tabKey;
      applyTabletLayout();
    }

    function renderSummary() {
      if (statusText) statusText.textContent = state.air?.status_text || "Unknown";
      if (dateInspectedText) {
        dateInspectedText.textContent = state.air?.date_inspected_text || "-";
      }
      if (verifiedText) {
        verifiedText.textContent = state.air?.inspection_verified_text || "Pending";
      }
      if (itemCountText) {
        itemCountText.textContent = String(Array.isArray(state.items) ? state.items.length : 0);
      }
      if (unitCountText) {
        const totalUnits = (state.items || []).reduce(
          (sum, item) => sum + Number(item?.units_count || 0),
          0,
        );
        unitCountText.textContent = String(totalUnits);
      }
      syncPromoteButtonState();
      refreshInspectionGuards();
      syncStickyOffsets();
    }

    function renderItems() {
      if (!itemsContainer) return;

      if (!Array.isArray(state.items) || state.items.length === 0) {
        itemsContainer.innerHTML = `
          <div class="rounded border border-dashed border-defaultborder p-5 text-sm text-[#8c9097] dark:text-white/50">
            No AIR item rows are available for this inspection workspace yet.
          </div>
        `;
        return;
      }

      itemsContainer.innerHTML = state.items
        .map((item, index) => {
          const needsUnits = !!item?.needs_units;
          const trackingType = normalizeText(item?.tracking_type_snapshot || "property");
          const orderedQty = Math.max(0, Number(item?.qty_ordered || 0));
          const deliveredQty = Math.max(0, Number(item?.qty_delivered || 0));
          const acceptedQty = Math.max(0, Number(item?.qty_accepted || 0));
          const unitsCount = Math.max(0, Number(item?.units_count || 0));
          const rowNumber = index + 1;
          const isIncompleteDelivery = orderedQty > 0 && deliveredQty < orderedQty;
          const disabled = canEdit() ? "" : "disabled";
          const acceptedDisabled = canEdit() && isIncompleteDelivery ? "disabled" : disabled;
          const serialLabel = item?.requires_serial_snapshot ? "Serial required" : "Serial optional";
          const semiLabel = item?.is_semi_expendable_snapshot ? "ICS / semi-expendable" : "Regular property";
          const itemNote = needsUnits
            ? `${serialLabel}. ${semiLabel}.`
            : "Consumable or non-unit-tracked line.";

          return `
            <div class="gso-air-inspection-item-card rounded-xl border border-defaultborder p-4 shadow-sm" data-air-item-id="${escapeHtml(item?.id || "")}">
              <div class="flex flex-wrap items-start justify-between gap-3">
                <div class="flex items-start gap-3 min-w-0">
                  <span class="gso-air-inspection-row-chip" title="Item ${escapeHtml(rowNumber)}">${escapeHtml(rowNumber)}</span>
                  <div class="min-w-0">
                    <p class="mb-1 text-sm font-semibold">${escapeHtml(item?.item_label || `AIR Item ${rowNumber}`)}</p>
                    <p class="mb-0 text-xs text-[#8c9097] dark:text-white/50">
                      ${escapeHtml(trackingType || "property")}
                      ${item?.stock_no_snapshot ? ` | ${escapeHtml(item.stock_no_snapshot)}` : ""}
                      ${item?.unit_snapshot ? ` | Unit: ${escapeHtml(item.unit_snapshot)}` : ""}
                    </p>
                    <p class="mt-1 mb-0 text-xs text-[#8c9097] dark:text-white/50">
                      Ordered <b>${escapeHtml(orderedQty)}</b>
                      ${item?.unit_snapshot ? `(${escapeHtml(item.unit_snapshot)})` : ""}
                      &middot; Delivered <b>${escapeHtml(deliveredQty)}</b>
                      &middot; Accepted <b>${escapeHtml(acceptedQty)}</b>
                    </p>
                  </div>
                </div>
                ${
                  needsUnits
                    ? `<div class="flex flex-wrap items-center gap-2">
                        <button type="button" class="ti-btn ti-btn-light" data-action="open-units" data-air-item-id="${escapeHtml(
                          item?.id || "",
                        )}">
                          Units
                        </button>
                        <span class="rounded-full bg-light px-3 py-1 text-xs text-[#8c9097] dark:bg-black/20 dark:text-white/50" data-air-item-units-count="${escapeHtml(
                          item?.id || "",
                        )}">
                          ${escapeHtml(unitsCount)} / ${escapeHtml(acceptedQty)} encoded
                        </span>
                      </div>`
                    : `<span class="rounded-full bg-light px-3 py-1 text-xs text-[#8c9097] dark:bg-black/20 dark:text-white/50">No Units</span>`
                }
              </div>
              <div class="mt-3">
                <label class="mb-1 block text-xs text-[#8c9097]">Description / Specs</label>
                <textarea class="ti-form-input w-full" rows="4" data-field="description_snapshot" data-air-item-id="${escapeHtml(
                  item?.id || "",
                )}" ${disabled}>${escapeHtml(item?.description_snapshot || "")}</textarea>
              </div>
              <div class="mt-4 grid gap-3 lg:grid-cols-3">
                <div>
                  <label class="mb-1 block text-xs text-[#8c9097]">Qty Ordered</label>
                  <input type="number" class="ti-form-input w-full" value="${escapeHtml(
                    orderedQty,
                  )}" disabled>
                </div>
                <div>
                  <label class="mb-1 block text-xs text-[#8c9097]">Qty Delivered</label>
                  <input type="number" min="0" class="ti-form-input w-full" data-field="qty_delivered" data-air-item-id="${escapeHtml(
                    item?.id || "",
                  )}" value="${escapeHtml(deliveredQty)}" ${disabled}>
                </div>
                <div>
                  <label class="mb-1 block text-xs text-[#8c9097]">Qty Accepted</label>
                  <input type="number" min="0" class="ti-form-input w-full" data-field="qty_accepted" data-air-item-id="${escapeHtml(
                    item?.id || "",
                  )}" value="${escapeHtml(isIncompleteDelivery ? 0 : acceptedQty)}" ${acceptedDisabled}>
                  ${
                    isIncompleteDelivery
                      ? `<div class="mt-1 text-[11px] text-warning">
                          Accepted quantity stays 0 until the full ordered quantity is delivered.
                        </div>`
                      : ""
                  }
                </div>
              </div>
              <div class="mt-3">
                <label class="mb-1 block text-xs text-[#8c9097]">Remarks</label>
                <input type="text" class="ti-form-input w-full" data-field="remarks" data-air-item-id="${escapeHtml(
                  item?.id || "",
                )}" value="${escapeHtml(item?.remarks || "")}" ${disabled}>
              </div>
              <div class="mt-3 flex flex-wrap items-center justify-between gap-2 text-xs text-[#8c9097] dark:text-white/50">
                <span>${escapeHtml(itemNote)}</span>
                <span>${
                  needsUnits
                    ? "Use Units to manage unit rows, components, and inspection images."
                    : "No encoded unit rows are required for this line."
                }</span>
              </div>
            </div>
          `;
        })
        .join("");
    }

    function applyInspectionState(payload) {
      state = {
        air: { ...(payload?.air || state.air) },
        items: Array.isArray(payload?.items) ? payload.items : state.items,
      };
      promotionState.hasEligibleTargets = null;
      promotionState.isLoading = false;
      promotionState.targetCount = 0;

      syncHeaderInputs();
      renderSummary();
      renderItems();

      if (canPromote()) {
        void refreshPromotionEligibility({ force: true });
      } else {
        syncPromoteButtonState();
      }
    }

    function findItem(id) {
      return state.items.find((item) => String(item?.id) === String(id)) || null;
    }

    function updateItemField(itemId, field, value) {
      const item = findItem(itemId);
      if (!item) return;

      if (field === "qty_delivered") {
        const delivered = Math.max(0, Number(value || 0));
        const ordered = Math.max(0, Number(item?.qty_ordered || 0));
        item.qty_delivered = delivered;

        if (ordered > 0 && delivered < ordered) {
          item.qty_accepted = 0;
        } else {
          item.qty_accepted = Math.min(
            Math.max(0, Number(item?.qty_accepted || 0)),
            delivered,
          );
        }

        syncItemCardState(itemId);
        return;
      }

      if (field === "qty_accepted") {
        const delivered = Math.max(0, Number(item?.qty_delivered || 0));
        const ordered = Math.max(0, Number(item?.qty_ordered || 0));
        item.qty_accepted =
          ordered > 0 && delivered < ordered
            ? 0
            : Math.min(Math.max(0, Number(value || 0)), delivered);
        syncItemCardState(itemId);
        return;
      }

      item[field] = value;
    }

    function getInspectionGuardState() {
      const {
        unresolvedItems,
        needsFollowUp,
        singleItemIncomplete,
        expectedCompleteness,
      } = collectInspectionProgress();
      const completenessValue = expectedCompleteness;
      const missingFields = [];

      if (!normalizeText(invoiceNumberInput?.value || "")) {
        missingFields.push("Invoice / DR / SI No.");
      }
      if (!normalizeText(invoiceDateInput?.value || "")) {
        missingFields.push("Invoice Date");
      }
      if (!normalizeText(dateReceivedInput?.value || "")) {
        missingFields.push("Date Received");
      }
      const completenessMismatch = false;
      let finalizeReason = "";

      if (missingFields.length > 0) {
        finalizeReason = `Finalize is locked until these receiving fields are complete: ${missingFields.join(", ")}.`;
      } else if (singleItemIncomplete) {
        finalizeReason =
          "Single-item AIR inspections cannot be finalized until the ordered quantity is fully accepted.";
      }

      return {
        needsFollowUp,
        singleItemIncomplete,
        unresolvedCount: unresolvedItems.length,
        expectedCompleteness,
        completenessValue,
        completenessMismatch,
        missingFields,
        finalizeReason,
      };
    }

    function syncCompletenessControl(guardState) {
      if (!completenessSelect) return;

      completenessSelect.value = guardState.expectedCompleteness;
      completenessSelect.disabled = true;
      completenessSelect.setAttribute("aria-disabled", "true");

      if (!completenessHint) return;

      if (guardState.singleItemIncomplete) {
        completenessHint.textContent =
          "Received Completeness is auto-set to Partial while this AIR still has only one unresolved delivered item. Finalization stays locked until the full ordered quantity is accepted.";
        return;
      }

      if (guardState.needsFollowUp) {
        completenessHint.textContent =
          "Received Completeness is auto-set to Partial because this AIR still has unresolved items. You can finalize it, then create a follow-up AIR for the remaining quantity.";
        return;
      }

      completenessHint.textContent =
        "Received Completeness is auto-set to Complete because all ordered items are fully accepted.";
    }

    function syncFinalizeControl(guardState) {
      if (!finalizeHint) return;

      finalizeHint.textContent =
        guardState.finalizeReason ||
        (guardState.needsFollowUp
          ? "Finalize will keep this AIR marked Partial so a follow-up AIR can be created afterward."
          : "");
    }

    function refreshInspectionGuards() {
      inspectionGuardState = getInspectionGuardState();
      syncCompletenessControl(inspectionGuardState);
      syncFinalizeControl(inspectionGuardState);
      updateInspectionToolbar();

      return inspectionGuardState;
    }

    function syncItemCardState(itemId) {
      const item = findItem(itemId);
      if (!item || !itemsContainer) return;

      const card = itemsContainer.querySelector(
        `[data-air-item-id="${String(itemId)}"]`,
      );
      if (!card) return;

      const deliveredInput = card.querySelector('[data-field="qty_delivered"]');
      const acceptedInput = card.querySelector('[data-field="qty_accepted"]');
      const unitsCountBadge = card.querySelector(
        `[data-air-item-units-count="${String(itemId)}"]`,
      );
      const ordered = Math.max(0, Number(item?.qty_ordered || 0));
      const delivered = Math.max(0, Number(item?.qty_delivered || 0));
      const accepted = Math.max(0, Number(item?.qty_accepted || 0));
      const isIncompleteDelivery = ordered > 0 && delivered < ordered;

      if (deliveredInput) {
        deliveredInput.value = String(delivered);
      }

      if (acceptedInput) {
        acceptedInput.value = String(isIncompleteDelivery ? 0 : accepted);

        if (canEdit() && isIncompleteDelivery) {
          acceptedInput.setAttribute("disabled", "disabled");
        } else if (canEdit()) {
          acceptedInput.removeAttribute("disabled");
        }
      }

      let incompleteNote = card.querySelector("[data-incomplete-note]");

      if (isIncompleteDelivery) {
        if (!incompleteNote && acceptedInput?.parentElement) {
          incompleteNote = document.createElement("div");
          incompleteNote.setAttribute("data-incomplete-note", "1");
          incompleteNote.className = "mt-1 text-[11px] text-warning";
          acceptedInput.parentElement.appendChild(incompleteNote);
        }

        if (incompleteNote) {
          incompleteNote.textContent =
            "Accepted quantity stays at 0 until the full ordered quantity is delivered.";
        }
      } else if (incompleteNote) {
        incompleteNote.remove();
      }

      if (unitsCountBadge) {
        unitsCountBadge.textContent = `${Math.max(
          0,
          Number(item?.units_count || 0),
        )} / ${accepted} encoded`;
      }

      refreshInspectionGuards();
    }

    function buildInspectionPayload() {
      return {
        invoice_number: normalizeText(invoiceNumberInput?.value || ""),
        invoice_date: normalizeText(invoiceDateInput?.value || ""),
        date_received: normalizeText(dateReceivedInput?.value || ""),
        received_completeness: getAutoReceivedCompleteness(),
        received_notes: String(receivedNotesInput?.value || "").trim(),
        items: (state.items || []).map((item) => ({
          id: item.id,
          description_snapshot: String(item?.description_snapshot || "").trim(),
          qty_delivered: Math.max(0, Number(item?.qty_delivered || 0)),
          qty_accepted: Math.max(0, Number(item?.qty_accepted || 0)),
          remarks: String(item?.remarks || "").trim(),
        })),
      };
    }

    function getInspectionSnapshot() {
      const payload = buildInspectionPayload();

      return {
        header: {
          invoice_number: payload.invoice_number,
          invoice_date: payload.invoice_date,
          date_received: payload.date_received,
          received_completeness: payload.received_completeness,
          received_notes: payload.received_notes,
        },
        items: (payload.items || [])
          .map((item) => ({
            id: String(item?.id || ""),
            description_snapshot: String(item?.description_snapshot || "").trim(),
            qty_delivered: Math.max(0, Number(item?.qty_delivered || 0)),
            qty_accepted: Math.max(0, Number(item?.qty_accepted || 0)),
            remarks: String(item?.remarks || "").trim(),
          }))
          .sort((left, right) => left.id.localeCompare(right.id)),
      };
    }

    function getPersistedInspectionSnapshot() {
      return {
        header: {
          invoice_number: normalizeText(state.air?.invoice_number || ""),
          invoice_date: normalizeText(state.air?.invoice_date || ""),
          date_received: normalizeText(state.air?.date_received || ""),
          received_completeness: normalizeText(state.air?.received_completeness || ""),
          received_notes: String(state.air?.received_notes || "").trim(),
        },
        items: (state.items || [])
          .map((item) => ({
            id: String(item?.id || ""),
            description_snapshot: String(item?.description_snapshot || "").trim(),
            qty_delivered: Math.max(0, Number(item?.qty_delivered || 0)),
            qty_accepted: Math.max(0, Number(item?.qty_accepted || 0)),
            remarks: String(item?.remarks || "").trim(),
          }))
          .sort((left, right) => left.id.localeCompare(right.id)),
      };
    }

    function countInspectionDirtyFields(current, baseline) {
      if (!baseline) {
        return 0;
      }

      let count = 0;
      const headerKeys = [
        "invoice_number",
        "invoice_date",
        "date_received",
        "received_completeness",
        "received_notes",
      ];

      headerKeys.forEach((key) => {
        if ((current.header?.[key] || "") !== (baseline.header?.[key] || "")) {
          count += 1;
        }
      });

      const currentItems = new Map((current.items || []).map((item) => [item.id, item]));
      const baselineItems = new Map((baseline.items || []).map((item) => [item.id, item]));
      const itemIds = new Set([...currentItems.keys(), ...baselineItems.keys()]);

      itemIds.forEach((itemId) => {
        const currentItem = currentItems.get(itemId) || {};
        const baselineItem = baselineItems.get(itemId) || {};

        [
          "description_snapshot",
          "qty_delivered",
          "qty_accepted",
          "remarks",
        ].forEach((key) => {
          if ((currentItem[key] ?? "") !== (baselineItem[key] ?? "")) {
            count += 1;
          }
        });
      });

      return count;
    }

    function refreshInspectionDirtyCount() {
      inspectionToolbarState.dirtyCount = countInspectionDirtyFields(
        getInspectionSnapshot(),
        inspectionBaseline,
      );
      refreshInspectionGuards();
    }

    function resetInspectionBaseline() {
      inspectionBaseline = getPersistedInspectionSnapshot();
      refreshInspectionDirtyCount();
    }

    async function requestJson(url, options = {}) {
      const response = await fetch(url, {
        headers: {
          Accept: "application/json",
          "X-CSRF-TOKEN": config.csrf || "",
          ...(options.body ? { "Content-Type": "application/json" } : {}),
          ...(options.headers || {}),
        },
        ...options,
      });

      return parseResponse(response);
    }

    async function requestForm(url, formData) {
      const response = await fetch(url, {
        method: "POST",
        headers: {
          Accept: "application/json",
          "X-CSRF-TOKEN": config.csrf || "",
        },
        body: formData,
      });

      return parseResponse(response);
    }

    async function saveInspection(options = {}) {
      const {
        showSuccess = true,
        showLoading = true,
        loadingTitle = "Saving inspection...",
      } = options;

      if (!canEdit()) {
        return false;
      }

      if (inspectionToolbarState.dirtyCount === 0) {
        updateInspectionToolbar();
        return true;
      }

      showFormError("");

      inspectionToolbarState.isSaving = true;
      updateInspectionToolbar();

      if (showLoading) {
        Swal.fire({
          title: loadingTitle,
          allowOutsideClick: false,
          didOpen: () => Swal.showLoading(),
        });
      }

      try {
        const parsed = await requestJson(config.saveUrl, {
          method: "PUT",
          body: JSON.stringify(buildInspectionPayload()),
        });

        if (!parsed.ok) {
          showFormError(parsed.message);

          await Swal.fire({
            icon: parsed.status === 422 ? "warning" : "error",
            title: parsed.status === 422 ? "Validation failed" : "Save failed",
            html: validationHtml(parsed.data?.errors || {}) || escapeHtml(parsed.message),
          });

          return false;
        }

        applyInspectionState(parsed.data?.data || {});
        showFormError("");
        resetInspectionBaseline();

        if (showSuccess) {
          await Swal.fire({
            icon: "success",
            title: "Inspection saved",
            timer: 900,
            showConfirmButton: false,
          });
        } else if (showLoading) {
          Swal.close();
        }

        return true;
      } finally {
        inspectionToolbarState.isSaving = false;
        updateInspectionToolbar();
      }
    }

    async function finalizeInspection() {
      const hasChanges = inspectionToolbarState.dirtyCount > 0;
      const guardState = refreshInspectionGuards();

      if (
        guardState.missingFields.length > 0 ||
        guardState.completenessMismatch ||
        guardState.singleItemIncomplete
      ) {
        await Swal.fire({
          icon: "warning",
          title: "Finalize blocked",
          text:
            guardState.finalizeReason ||
            "Complete the required inspection fields before finalizing this AIR.",
        });
        return;
      }

      const confirmation = await Swal.fire({
        icon: "question",
        title: hasChanges ? "Save and finalize inspection?" : "Finalize inspection?",
        text: hasChanges
          ? "Unsaved inspection changes will be saved first, then the AIR will be marked as inspected."
          : "This moves the AIR into inspected status and locks further editing in this workspace.",
        showCancelButton: true,
        confirmButtonText: hasChanges ? "Save and Finalize" : "Finalize",
        cancelButtonText: "Cancel",
      });

      if (!confirmation.isConfirmed) {
        return;
      }

      inspectionToolbarState.isFinalizing = true;
      updateInspectionToolbar();

      try {
        if (hasChanges) {
          const saved = await saveInspection({
            showSuccess: false,
            showLoading: true,
            loadingTitle: "Saving changes...",
          });

          if (!saved) {
            return;
          }
        }

        showLoadingAlert(
          "Finalizing inspection...",
          "The AIR is being locked and the inspection workspace will refresh after completion.",
        );

        const parsed = await requestJson(config.finalizeUrl, { method: "PUT" });

        if (!parsed.ok) {
          showFormError(parsed.message);

          await Swal.fire({
            icon: parsed.status === 422 ? "warning" : "error",
            title: parsed.status === 422 ? "Finalize blocked" : "Finalize failed",
            html: validationHtml(parsed.data?.errors || {}) || escapeHtml(parsed.message),
          });

          return;
        }

        applyInspectionState(parsed.data?.data || {});
        resetInspectionBaseline();
        setModalOpen(unitModal, false);
        setModalOpen(componentModal, false);
        setModalOpen(fileModal, false);

        await Swal.fire({
          icon: "success",
          title: "Inspection finalized",
          text: "Reloading the inspection workspace...",
          timer: 1200,
          showConfirmButton: false,
        });

        window.location.reload();
        return;
      } finally {
        inspectionToolbarState.isFinalizing = false;
        updateInspectionToolbar();
      }
    }

    async function createFollowUpAir() {
      if (!config.followUpCreateUrl) return;

      const confirmation = await Swal.fire({
        icon: "question",
        title: "Create follow-up AIR?",
        text: "This will create a new AIR draft for the unresolved quantities from this inspection.",
        showCancelButton: true,
        confirmButtonText: "Create Follow-up AIR",
        cancelButtonText: "Cancel",
      });

      if (!confirmation.isConfirmed) {
        return;
      }

      showLoadingAlert(
        "Creating follow-up AIR...",
        "The unresolved inspection quantities are being prepared in a new AIR draft.",
      );

      try {
        const parsed = await requestJson(config.followUpCreateUrl, {
          method: "POST",
        });

        if (!parsed.ok) {
          await Swal.fire({
            icon: parsed.status === 422 ? "warning" : "error",
            title:
              parsed.status === 422
                ? "Follow-up AIR blocked"
                : "Follow-up AIR failed",
            html:
              validationHtml(parsed.data?.errors || {}) ||
              escapeHtml(parsed.message),
          });
          return;
        }

        await Swal.fire({
          icon: "success",
          title: "Follow-up AIR ready",
          text:
            parsed.message || "Redirecting to the follow-up AIR workspace...",
          timer: 1100,
          showConfirmButton: false,
        });

        const redirectUrl = normalizeText(
          parsed.data?.data?.redirect_url || "",
        );
        window.location.href = redirectUrl || config.editUrl || config.indexUrl;
      } finally {
        Swal.close();
      }
    }

    async function reopenInspection() {
      if (!config.reopenUrl) return;

      const confirmation = await Swal.fire({
        icon: "warning",
        title: "Reopen inspection?",
        text: "This will move the AIR back to Submitted so it can be edited again.",
        input: "textarea",
        inputLabel: "Reason (optional)",
        inputAttributes: {
          maxlength: "500",
          placeholder:
            "Add a brief note about why this AIR is being reopened.",
        },
        showCancelButton: true,
        confirmButtonText: "Reopen Inspection",
        cancelButtonText: "Cancel",
      });

      if (!confirmation.isConfirmed) {
        return;
      }

      showLoadingAlert(
        "Reopening inspection...",
        "The AIR is being moved back to an editable inspection status.",
      );

      try {
        const parsed = await requestJson(config.reopenUrl, {
          method: "POST",
          body: JSON.stringify({
            reason: String(confirmation.value || "").trim(),
          }),
        });

        if (!parsed.ok) {
          await Swal.fire({
            icon: parsed.status === 422 ? "warning" : "error",
            title:
              parsed.status === 422
                ? "Reopen blocked"
                : "Reopen failed",
            html:
              validationHtml(parsed.data?.errors || {}) ||
              escapeHtml(parsed.message),
          });
          return;
        }

        await Swal.fire({
          icon: "success",
          title: "Inspection reopened",
          text: "Reloading the inspection workspace...",
          timer: 1100,
          showConfirmButton: false,
        });

        window.location.reload();
      } finally {
        Swal.close();
      }
    }

    async function loadPromotionEligibility() {
      return requestJson(config.promoteEligibleUrl, { method: "GET" });
    }

    function pluralize(count, singular, plural = `${singular}s`) {
      return Number(count || 0) === 1 ? singular : plural;
    }

    function buildPromotionMetricCard(label, value, tone = "default") {
      const tones = {
        default: {
          border: "#dbeafe",
          background: "#f8fbff",
          label: "#64748b",
          value: "#0f172a",
        },
        success: {
          border: "#bbf7d0",
          background: "#f0fdf4",
          label: "#166534",
          value: "#14532d",
        },
        warning: {
          border: "#fde68a",
          background: "#fffbeb",
          label: "#92400e",
          value: "#78350f",
        },
        danger: {
          border: "#fecaca",
          background: "#fef2f2",
          label: "#b91c1c",
          value: "#7f1d1d",
        },
      };
      const palette = tones[tone] || tones.default;

      return `
        <div style="border:1px solid ${palette.border}; border-radius:14px; background:${palette.background}; padding:12px 14px;">
          <div style="font-size:11px; letter-spacing:0.04em; text-transform:uppercase; color:${palette.label};">${escapeHtml(label)}</div>
          <div style="margin-top:6px; font-size:22px; font-weight:700; color:${palette.value};">${escapeHtml(value)}</div>
        </div>
      `;
    }

    function buildPromotionFieldGrid(fields) {
      const rows = fields
        .filter((field) => normalizeText(field?.value || "") !== "")
        .map(
          (field) => `
            <div style="min-width:0;">
              <div style="font-size:11px; text-transform:uppercase; letter-spacing:0.03em; color:#64748b;">${escapeHtml(field.label || "")}</div>
              <div style="margin-top:3px; font-size:13px; line-height:1.35; color:#0f172a; word-break:break-word; font-weight:${field?.strong ? "600" : "500"};">${escapeHtml(field.value || "")}</div>
            </div>
          `,
        )
        .join("");

      if (rows === "") {
        return "";
      }

      return `
        <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(150px, 1fr)); gap:10px 14px; margin-top:10px;">
          ${rows}
        </div>
      `;
    }

    function buildPromotionPropertyCards(rows, options = {}) {
      const { blocked = false, completed = false } = options;

      if (!Array.isArray(rows) || rows.length === 0) {
        return "";
      }

      return rows
        .map((row, index) => {
          const description = normalizeText(row?.description || "");
          const brandModel = [normalizeText(row?.brand || ""), normalizeText(row?.model || "")]
            .filter(Boolean)
            .join(" / ");
          const propertyNumber =
            normalizeText(row?.property_number || "") ||
            (completed ? "" : "Auto-generated on promotion");
          const chips = [
            row?.classification ? `${row.classification} item` : "",
            row?.file_count !== undefined
              ? `${escapeHtml(row.file_count || 0)} ${pluralize(
                  row?.file_count || 0,
                  "image",
                )}`
              : "",
            row?.component_count !== undefined
              ? `${escapeHtml(row.component_count || 0)} ${pluralize(
                  row?.component_count || 0,
                  "component",
                )}`
              : "",
            row?.copied_files !== undefined
              ? `${escapeHtml(row.copied_files || 0)} ${pluralize(
                  row?.copied_files || 0,
                  "file",
                )} copied`
              : "",
            row?.copied_components !== undefined
              ? `${escapeHtml(row.copied_components || 0)} ${pluralize(
                  row?.copied_components || 0,
                  "component",
                )} copied`
              : "",
          ]
            .filter(Boolean)
            .map(
              (chip) => `
                <span style="display:inline-flex; align-items:center; border:1px solid #dbeafe; border-radius:999px; padding:4px 9px; background:#f8fbff; font-size:11px; color:#1d4ed8; white-space:nowrap;">
                  ${chip}
                </span>
              `,
            )
            .join("");

          const statusNote = blocked
            ? `<div style="margin-top:10px; border:1px solid #fecaca; border-radius:12px; background:#fef2f2; padding:10px 12px; font-size:12px; color:#991b1b;"><strong>Blocked:</strong> ${escapeHtml(row?.promotion_blocked_reason || "This unit cannot be promoted yet.")}</div>`
            : normalizeText(row?.promotion_warning || "") !== ""
              ? `<div style="margin-top:10px; border:1px solid #fde68a; border-radius:12px; background:#fffbeb; padding:10px 12px; font-size:12px; color:#92400e;"><strong>Check before promoting:</strong> ${escapeHtml(row?.promotion_warning || "")}</div>`
              : "";

          return `
            <div style="border:1px solid ${blocked ? "#fecaca" : "#dbeafe"}; border-radius:16px; background:${blocked ? "#fff7f7" : "#ffffff"}; padding:14px 16px;">
              <div style="display:flex; flex-wrap:wrap; align-items:flex-start; justify-content:space-between; gap:10px;">
                <div>
                  <div style="font-size:15px; font-weight:700; color:#0f172a;">${escapeHtml(row?.item_label || `Property Unit ${index + 1}`)}</div>
                  <div style="margin-top:3px; font-size:12px; color:#64748b;">${escapeHtml(row?.unit_label || `Inspection Unit ${index + 1}`)}</div>
                </div>
                <div style="display:flex; flex-wrap:wrap; gap:6px; justify-content:flex-end;">
                  ${chips}
                </div>
              </div>
              ${
                description !== ""
                  ? `<div style="margin-top:10px; font-size:12px; line-height:1.5; color:#334155;"><strong>Description:</strong> ${escapeHtml(description)}</div>`
                  : ""
              }
              ${buildPromotionFieldGrid([
                { label: "Property Number", value: propertyNumber, strong: true },
                { label: "Stock No.", value: normalizeText(row?.stock_no || "") },
                { label: "Serial Number", value: normalizeText(row?.serial_number || "") },
                { label: "Brand / Model", value: brandModel },
                { label: "Condition", value: normalizeText(row?.condition_status_text || "") },
                {
                  label: completed ? "Recorded Cost" : "Unit Cost",
                  value:
                    row?.acquisition_cost !== null && row?.acquisition_cost !== undefined
                      ? `PHP ${formatCurrency(row.acquisition_cost)}`
                      : "",
                  strong: true,
                },
                { label: "Accountable Officer", value: normalizeText(row?.accountable_officer || "") },
                { label: "Condition Notes", value: normalizeText(row?.condition_notes || "") },
              ])}
              ${statusNote}
            </div>
          `;
        })
        .join("");
    }

    function buildPromotionConsumableCards(rows, options = {}) {
      const { completed = false } = options;

      if (!Array.isArray(rows) || rows.length === 0) {
        return "";
      }

      return rows
        .map((row, index) => {
          const description = normalizeText(row?.description || "");
          const acceptedText =
            normalizeText(row?.unit_snapshot || "") !== ""
              ? `${escapeHtml(row?.qty_accepted || 0)} ${escapeHtml(row?.unit_snapshot || "")}`
              : String(row?.qty_accepted || 0);
          const stockText =
            normalizeText(row?.base_unit || "") !== ""
              ? `${escapeHtml(row?.base_qty || 0)} ${escapeHtml(row?.base_unit || "")}`
              : String(row?.base_qty || 0);

          return `
            <div style="border:1px solid #dbeafe; border-radius:16px; background:#ffffff; padding:14px 16px;">
              <div style="font-size:15px; font-weight:700; color:#0f172a;">${escapeHtml(row?.item_label || `Consumable ${index + 1}`)}</div>
              ${
                description !== ""
                  ? `<div style="margin-top:4px; font-size:12px; line-height:1.5; color:#334155;"><strong>Description:</strong> ${escapeHtml(description)}</div>`
                  : ""
              }
              ${buildPromotionFieldGrid([
                { label: "Stock No.", value: normalizeText(row?.stock_no || "") },
                {
                  label: completed ? "Accepted Qty" : "Accepted Qty for Posting",
                  value: acceptedText,
                  strong: true,
                },
                { label: "Stock Ledger Qty", value: stockText, strong: true },
                {
                  label: "Conversion",
                  value:
                    Number(row?.multiplier || 1) > 1
                      ? `1 ${normalizeText(row?.unit_snapshot || "unit")} = ${escapeHtml(row?.multiplier || 1)} ${escapeHtml(row?.base_unit || "base")}`
                      : `1:1 (${escapeHtml(row?.base_unit || row?.unit_snapshot || "unit")})`,
                },
              ])}
            </div>
          `;
        })
        .join("");
    }

    function buildPromotionSummaryHtml(eligibility) {
      const propertyUnits = Array.isArray(eligibility?.property_units)
        ? eligibility.property_units
        : [];
      const blockedPropertyUnits = Array.isArray(eligibility?.blocked_property_units)
        ? eligibility.blocked_property_units
        : [];
      const consumables = Array.isArray(eligibility?.consumables)
        ? eligibility.consumables
        : [];
      const summary = eligibility?.summary || {};

      return `
        <div style="text-align:left;">
          <div style="margin-bottom:14px; font-size:13px; color:#475569;">
            Review every line below before promotion. Property items will create inventory records, while consumables will post incoming stock movements.
          </div>
          <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(140px, 1fr)); gap:10px; margin-bottom:16px;">
            ${buildPromotionMetricCard("Property Units Ready", summary.property_units_count || 0, "success")}
            ${buildPromotionMetricCard("Blocked Units", summary.blocked_property_units_count || 0, blockedPropertyUnits.length > 0 ? "danger" : "default")}
            ${buildPromotionMetricCard("Consumable Lines", summary.consumable_lines_count || 0, consumables.length > 0 ? "warning" : "default")}
            ${buildPromotionMetricCard("Accepted Consumable Qty", summary.consumable_qty_accepted || 0, "default")}
          </div>
          <div style="max-height:56vh; overflow:auto; padding-right:6px;">
            ${
              propertyUnits.length > 0
                ? `
                  <div style="margin-bottom:16px;">
                    <div style="margin-bottom:8px; font-size:12px; letter-spacing:0.04em; text-transform:uppercase; color:#1d4ed8;">Ready Property Units</div>
                    <div style="display:grid; gap:12px;">
                      ${buildPromotionPropertyCards(propertyUnits)}
                    </div>
                  </div>
                `
                : ""
            }
            ${
              blockedPropertyUnits.length > 0
                ? `
                  <div style="margin-bottom:16px;">
                    <div style="margin-bottom:8px; font-size:12px; letter-spacing:0.04em; text-transform:uppercase; color:#b91c1c;">Blocked Property Units</div>
                    <div style="display:grid; gap:12px;">
                      ${buildPromotionPropertyCards(blockedPropertyUnits, { blocked: true })}
                    </div>
                  </div>
                `
                : ""
            }
            ${
              consumables.length > 0
                ? `
                  <div>
                    <div style="margin-bottom:8px; font-size:12px; letter-spacing:0.04em; text-transform:uppercase; color:#92400e;">Consumables to Post</div>
                    <div style="display:grid; gap:12px;">
                      ${buildPromotionConsumableCards(consumables)}
                    </div>
                  </div>
                `
                : ""
            }
          </div>
        </div>
      `;
    }

    function buildPromotionResultHtml(result, message) {
      const propertyDetails = Array.isArray(result?.property_created_details)
        ? result.property_created_details
        : [];
      const consumableDetails = Array.isArray(result?.consumable_posted_details)
        ? result.consumable_posted_details
        : [];

      return `
        <div style="text-align:left;">
          <div style="margin-bottom:14px; font-size:13px; color:#475569;">${escapeHtml(
            message || "AIR promoted successfully.",
          )}</div>
          <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(140px, 1fr)); gap:10px; margin-bottom:16px;">
            ${buildPromotionMetricCard("Property Created", result?.property_created || 0, "success")}
            ${buildPromotionMetricCard("Property Skipped", result?.property_skipped || 0, result?.property_skipped > 0 ? "warning" : "default")}
            ${buildPromotionMetricCard("Consumables Posted", result?.consumable_posted || 0, "warning")}
            ${buildPromotionMetricCard("Consumables Skipped", result?.consumable_skipped || 0, result?.consumable_skipped > 0 ? "warning" : "default")}
            ${buildPromotionMetricCard("Files Copied", result?.copied_files || 0, "default")}
            ${buildPromotionMetricCard("Components Copied", result?.components_copied || 0, "default")}
          </div>
          <div style="max-height:56vh; overflow:auto; padding-right:6px;">
            ${
              propertyDetails.length > 0
                ? `
                  <div style="margin-bottom:16px;">
                    <div style="margin-bottom:8px; font-size:12px; letter-spacing:0.04em; text-transform:uppercase; color:#166534;">Created Property Records</div>
                    <div style="display:grid; gap:12px;">
                      ${buildPromotionPropertyCards(propertyDetails, { completed: true })}
                    </div>
                  </div>
                `
                : ""
            }
            ${
              consumableDetails.length > 0
                ? `
                  <div>
                    <div style="margin-bottom:8px; font-size:12px; letter-spacing:0.04em; text-transform:uppercase; color:#92400e;">Posted Consumables</div>
                    <div style="display:grid; gap:12px;">
                      ${buildPromotionConsumableCards(consumableDetails, { completed: true })}
                    </div>
                  </div>
                `
                : ""
            }
          </div>
        </div>
      `;
    }

    async function promoteInventory() {
      if (!canPromote()) {
        await Swal.fire({
          icon: "info",
          title: "Promotion unavailable",
          text: "Finalize the AIR inspection first before promoting it to inventory.",
        });
        return;
      }

      const eligibilityParsed = await loadPromotionEligibility();

      if (!eligibilityParsed.ok) {
        await Swal.fire({
          icon: eligibilityParsed.status === 422 ? "warning" : "error",
          title:
            eligibilityParsed.status === 422
              ? "Promotion blocked"
              : "Could not load promotion preview",
          html:
            validationHtml(eligibilityParsed.data?.errors || {}) ||
            escapeHtml(eligibilityParsed.message),
        });
        return;
      }

      const eligibility = eligibilityParsed.data?.data || {};
      const propertyUnits = Array.isArray(eligibility.property_units)
        ? eligibility.property_units
        : [];
      const blockedPropertyUnits = Array.isArray(eligibility.blocked_property_units)
        ? eligibility.blocked_property_units
        : [];
      const consumables = Array.isArray(eligibility.consumables)
        ? eligibility.consumables
        : [];

      if (propertyUnits.length === 0 && consumables.length === 0) {
        await Swal.fire({
          icon: "info",
          title:
            blockedPropertyUnits.length > 0
              ? "Nothing ready to promote"
              : "Nothing new to promote",
          html:
            blockedPropertyUnits.length > 0
              ? buildPromotionSummaryHtml(eligibility)
              : "This AIR no longer has eligible property units or consumable lines to promote.",
          width: blockedPropertyUnits.length > 0 ? 920 : undefined,
        });
        return;
      }

      const confirmation = await Swal.fire({
        icon: "question",
        title: "Promote AIR to inventory?",
        html: buildPromotionSummaryHtml(eligibility),
        showCancelButton: true,
        confirmButtonText:
          propertyUnits.length + consumables.length > 0
            ? `Promote (${propertyUnits.length + consumables.length})`
            : "Promote",
        cancelButtonText: "Cancel",
        width: 920,
      });

      if (!confirmation.isConfirmed) return;

      showLoadingAlert(
        "Promoting to inventory...",
        "Creating inventory records and copying related AIR data.",
      );

      try {
        const parsed = await requestJson(config.promoteUrl, {
          method: "POST",
          body: JSON.stringify({
            air_item_unit_ids: propertyUnits
              .map((row) => row?.air_item_unit_id || "")
              .filter((value) => String(value).trim() !== ""),
          }),
        });

        Swal.close();

        if (!parsed.ok) {
          await Swal.fire({
            icon: parsed.status === 422 ? "warning" : "error",
            title: parsed.status === 422 ? "Promotion blocked" : "Promotion failed",
            html:
              validationHtml(parsed.data?.errors || {}) ||
              escapeHtml(parsed.message),
          });
          return;
        }

        const result = parsed.data?.data || {};

        await Swal.fire({
          icon: "success",
          title: "Promotion complete",
          html: buildPromotionResultHtml(
            result,
            parsed.message || "AIR promoted successfully.",
          ),
          width: 920,
        });
        promotionState.hasEligibleTargets = null;
        promotionState.targetCount = 0;
        void refreshPromotionEligibility({ force: true });
      } catch (error) {
        Swal.close();
        const message =
          error instanceof Error && error.message
            ? error.message
            : "The AIR could not be promoted right now.";

        await Swal.fire({
          icon: "error",
          title: "Promotion failed",
          text: message,
        });
      }
    }

    function formatCurrency(value) {
      const amount = Number(value || 0);

      return amount.toLocaleString(undefined, {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
      });
    }

    function createComponentRow(row = {}) {
      const rawCost = row.component_cost;

      return {
        __key:
          row.__key ||
          row.id ||
          `component-${Date.now()}-${Math.random().toString(16).slice(2)}`,
        id: row.id || "",
        name: row.name || "",
        quantity: Math.max(1, Number(row.quantity || 1)),
        unit: row.unit || "",
        component_cost:
          rawCost === null || rawCost === undefined || rawCost === ""
            ? ""
            : String(rawCost),
        serial_number: row.serial_number || "",
        condition: row.condition || "",
        is_present:
          row.is_present === undefined || row.is_present === null
            ? true
            : !!row.is_present,
        remarks: row.remarks || "",
      };
    }

    function cloneComponentRows(rows) {
      return Array.isArray(rows) ? rows.map((row) => createComponentRow(row)) : [];
    }

    function getDefaultComponentRows() {
      return cloneComponentRows(unitState?.air_item?.default_components || []);
    }

    function createUnitRow(row = {}) {
      const defaultComponents =
        !row.id && !Array.isArray(row.components) && Array.isArray(row.default_components)
          ? row.default_components
          : [];

      return {
        __key:
          row.__key ||
          row.id ||
          `unit-${Date.now()}-${Math.random().toString(16).slice(2)}`,
        id: row.id || "",
        brand: row.brand || "",
        model: row.model || "",
        serial_number: row.serial_number || "",
        property_number: row.property_number || "",
        condition_status: row.condition_status || "",
        condition_notes: row.condition_notes || "",
        file_count: Number(row.file_count || 0),
        components: cloneComponentRows(row.components || defaultComponents),
        component_cost_warning: row.component_cost_warning || "",
      };
    }

    function cloneServerUnitRows(rows) {
      return Array.isArray(rows) ? rows.map((row) => createUnitRow(row)) : [];
    }

    function normalizeUnitRowsToAcceptedQty(
      rows = [],
      acceptedQty = 0,
      defaultComponents = [],
    ) {
      const normalized = Array.isArray(rows)
        ? rows.map((row) => createUnitRow(row))
        : [];
      const targetCount = Math.max(0, Number(acceptedQty || 0));

      while (normalized.length < targetCount) {
        normalized.push(
          createUnitRow({
            default_components: defaultComponents,
          }),
        );
      }

      return normalized.slice(0, targetCount);
    }

    function unitRowHasMeaningfulContent(row = {}) {
      return (
        normalizeText(row.id || "") !== "" ||
        [
          row.brand,
          row.model,
          row.serial_number,
          row.property_number,
          row.condition_status,
          row.condition_notes,
        ].some((value) => normalizeText(value || "") !== "")
      );
    }

    function serializeComponentRow(row = {}) {
      return JSON.stringify({
        id: String(row.id || ""),
        name: normalizeText(row.name || ""),
        quantity: Math.max(1, Number(row.quantity || 1)),
        unit: normalizeText(row.unit || ""),
        component_cost: normalizeText(row.component_cost ?? ""),
        serial_number: normalizeText(row.serial_number || ""),
        condition: normalizeText(row.condition || ""),
        is_present: row.is_present !== false,
        remarks: String(row.remarks || "").trim(),
      });
    }

    function serializeUnitRow(row = {}) {
      return JSON.stringify({
        id: String(row.id || ""),
        brand: normalizeText(row.brand || ""),
        model: normalizeText(row.model || ""),
        serial_number: normalizeText(row.serial_number || ""),
        property_number: normalizeText(row.property_number || ""),
        condition_status: normalizeText(row.condition_status || ""),
        condition_notes: String(row.condition_notes || "").trim(),
        components: (row.components || []).map((component) => serializeComponentRow(component)),
      });
    }

    function unitRowIdentity(row, index) {
      return String(row?.id || row?.__key || `unit-${index}`);
    }

    function countDirtyUnitRows(currentRows = unitRows, baselineRows = unitRowsBaseline) {
      const currentMap = new Map(
        (currentRows || []).map((row, index) => [unitRowIdentity(row, index), serializeUnitRow(row)]),
      );
      const baselineMap = new Map(
        (baselineRows || []).map((row, index) => [unitRowIdentity(row, index), serializeUnitRow(row)]),
      );
      const keys = new Set([...currentMap.keys(), ...baselineMap.keys()]);
      let count = 0;

      keys.forEach((key) => {
        if ((currentMap.get(key) || "") !== (baselineMap.get(key) || "")) {
          count += 1;
        }
      });

      return count;
    }

    function renderUnitWorkspaceChrome() {
      if (!unitState) {
        return;
      }

      const acceptedQty = Math.max(0, Number(unitState.air_item?.qty_accepted || 0));
      const savedRows = Math.max(0, Number(unitState.air_item?.units_count || 0));
      const stagedRows = unitRows.filter((row) => unitRowHasMeaningfulContent(row)).length;
      const remainingSlots = Math.max(0, acceptedQty - stagedRows);
      const templateCount = Array.isArray(unitState.air_item?.default_components)
        ? unitState.air_item.default_components.length
        : 0;

      unitToolbarState.dirtyCount = countDirtyUnitRows();

      if (unitRowsContainer) {
        unitRowsContainer.classList.toggle(
          "gso-air-inspection-unit-grid--single",
          unitRows.length <= 1,
        );
      }

      if (unitNotice) {
        unitNotice.innerHTML = unitState.air_item?.needs_units
          ? `
              <div class="gso-air-inspection-unit-summary">
                <div class="gso-air-inspection-unit-summary-card">
                  <span class="gso-air-inspection-unit-summary-label">Accepted Qty</span>
                  <span class="gso-air-inspection-unit-summary-value">${escapeHtml(acceptedQty)}</span>
                </div>
                <div class="gso-air-inspection-unit-summary-card">
                  <span class="gso-air-inspection-unit-summary-label">Saved Rows</span>
                  <span class="gso-air-inspection-unit-summary-value">${escapeHtml(savedRows)}</span>
                </div>
                <div class="gso-air-inspection-unit-summary-card">
                  <span class="gso-air-inspection-unit-summary-label">Rows With Data</span>
                  <span class="gso-air-inspection-unit-summary-value">${escapeHtml(stagedRows)}</span>
                </div>
                <div class="gso-air-inspection-unit-summary-card">
                  <span class="gso-air-inspection-unit-summary-label">Slots Remaining</span>
                  <span class="gso-air-inspection-unit-summary-value">${escapeHtml(remainingSlots)}</span>
                </div>
              </div>
              <div class="gso-air-inspection-unit-workspace-note">
                ${
                  unitToolbarState.dirtyCount > 0
                    ? `${escapeHtml(unitToolbarState.dirtyCount)} unit row${unitToolbarState.dirtyCount === 1 ? "" : "s"} still have unsaved changes.`
                    : "Blank unit slots are opened automatically based on the saved accepted quantity."
                }
                ${
                  templateCount > 0
                    ? ` New rows preload ${escapeHtml(templateCount)} component template${templateCount === 1 ? "" : "s"}.`
                    : ""
                }
              </div>
            `
          : "This AIR item does not require inspection unit rows.";
      }

      if (unitSaveButton) {
        unitSaveButton.textContent =
          unitToolbarState.dirtyCount > 0
            ? `${unitToolbarState.defaultSaveLabel} (${unitToolbarState.dirtyCount})`
            : unitToolbarState.defaultSaveLabel;
        unitSaveButton.disabled =
          !canEdit() ||
          unitToolbarState.isSaving ||
          unitToolbarState.dirtyCount === 0;
      }
    }

    function resetUnitRowsBaseline(rows = unitRows) {
      unitRowsBaseline = Array.isArray(rows) ? rows.map((row) => createUnitRow(row)) : [];
      renderUnitWorkspaceChrome();
    }

    function buildUnitComponentSummary(row) {
      const components = Array.isArray(row?.components) ? row.components : [];

      if (components.length === 0) {
        return {
          count: 0,
          label: "No component schedule recorded yet.",
          detail: "Open Components to record bundled parts or accessories for this unit.",
          hasWarning: false,
        };
      }

      const total = components.reduce((sum, component) => {
        const quantity = Math.max(1, Number(component?.quantity || 1));
        const cost = Number(component?.component_cost || 0);

        return sum + quantity * cost;
      }, 0);
      const allPresent = components.every((component) => component?.is_present !== false);

      return {
        count: components.length,
        label: `${components.length} component${components.length === 1 ? "" : "s"} recorded`,
        detail: `Total ${formatCurrency(total)} | ${
          allPresent ? "All recorded components are present" : "Missing components are recorded"
        }`,
        hasWarning: !allPresent || normalizeText(row?.component_cost_warning || "") !== "",
      };
    }

    function renderUnitRows() {
      if (!unitRowsContainer || !unitState) return;

      if (unitRows.length === 0) {
        unitRowsContainer.innerHTML = `
          <div class="gso-air-inspection-empty-state text-sm text-[#8c9097] dark:text-white/50">
            <div class="font-medium text-defaulttextcolor dark:text-white">No unit rows required yet</div>
            <div class="mt-2">
              Save an accepted quantity on the main inspection page first to open unit slots here.
            </div>
          </div>
        `;
        renderUnitWorkspaceChrome();
        return;
      }

      const options = Object.entries(config.conditionStatuses || {})
        .map(
          ([value, label]) =>
            `<option value="${escapeHtml(value)}">${escapeHtml(label)}</option>`,
        )
        .join("");

      unitRowsContainer.innerHTML = unitRows
        .map((row, index) => {
          const fileButton =
            row.id && row.id !== ""
              ? `<button type="button" class="ti-btn ti-btn-light" data-action="open-unit-files" data-key="${escapeHtml(
                  row.__key,
                )}">Images (${escapeHtml(row.file_count)})</button>`
              : `<span class="rounded-full bg-light px-3 py-1 text-xs text-[#8c9097] dark:bg-black/20 dark:text-white/50">Save row first to manage images</span>`;
          const componentSummary = buildUnitComponentSummary(row);
          const unitNumber = index + 1;

          return `
            <div class="gso-air-inspection-unit-card rounded-xl border border-defaultborder p-4 shadow-sm" data-unit-key="${escapeHtml(
              row.__key,
            )}">
              <div class="flex flex-wrap items-start justify-between gap-3">
                <div class="flex items-start gap-3">
                  <span class="gso-air-inspection-row-chip" title="Unit ${escapeHtml(unitNumber)}">${escapeHtml(unitNumber)}</span>
                  <div>
                    <p class="mb-1 text-sm font-semibold">${escapeHtml(
                      row.serial_number || row.property_number || `Inspection Unit ${unitNumber}`,
                    )}</p>
                    <p class="mb-0 text-xs text-[#8c9097] dark:text-white/50">
                      ${row.id ? "Saved row" : "Unsaved row"}
                    </p>
                  </div>
                </div>
                <div class="flex flex-wrap items-center gap-2">
                  <button type="button" class="ti-btn ti-btn-light" data-action="open-unit-components" data-key="${escapeHtml(
                    row.__key,
                  )}">Components (${escapeHtml(componentSummary.count)})</button>
                  ${fileButton}
                  ${
                    canEdit()
                      ? `<button type="button" class="ti-btn ti-btn-danger" data-action="delete-unit-row" data-key="${escapeHtml(
                          row.__key,
                        )}">${row.id ? "Delete" : "Clear"}</button>`
                      : ""
                  }
                </div>
              </div>
              <div class="mt-3 rounded-lg bg-light p-3 text-xs dark:bg-black/20">
                <div class="font-medium text-defaulttextcolor dark:text-white">${escapeHtml(
                  componentSummary.label,
                )}</div>
                <div class="mt-1 text-[#8c9097] dark:text-white/50">${escapeHtml(
                  componentSummary.detail,
                )}</div>
                ${
                  normalizeText(row.component_cost_warning || "") !== ""
                    ? `<div class="mt-2 text-warning">${escapeHtml(
                        row.component_cost_warning || "",
                      )}</div>`
                    : ""
                }
              </div>
              <div class="mt-4 grid gap-3 lg:grid-cols-2">
                <div>
                  <label class="mb-1 block text-xs text-[#8c9097]">Brand</label>
                  <input type="text" class="ti-form-input w-full" data-field="brand" data-key="${escapeHtml(
                    row.__key,
                  )}" value="${escapeHtml(row.brand || "")}" ${canEdit() ? "" : "disabled"}>
                </div>
                <div>
                  <label class="mb-1 block text-xs text-[#8c9097]">Model</label>
                  <input type="text" class="ti-form-input w-full" data-field="model" data-key="${escapeHtml(
                    row.__key,
                  )}" value="${escapeHtml(row.model || "")}" ${canEdit() ? "" : "disabled"}>
                </div>
                <div>
                  <label class="mb-1 block text-xs text-[#8c9097]">Serial Number</label>
                  <input type="text" class="ti-form-input w-full" data-field="serial_number" data-key="${escapeHtml(
                    row.__key,
                  )}" value="${escapeHtml(row.serial_number || "")}" ${canEdit() ? "" : "disabled"}>
                </div>
                <div>
                  <label class="mb-1 block text-xs text-[#8c9097]">Property Number</label>
                  <input type="text" class="ti-form-input w-full" data-field="property_number" data-key="${escapeHtml(
                    row.__key,
                  )}" value="${escapeHtml(row.property_number || "")}" placeholder="Auto-generated when promoted to inventory" readonly aria-readonly="true">
                  <p class="mt-1 text-[11px] text-[#8c9097]">Generated automatically once this AIR is promoted to inventory.</p>
                </div>
                <div>
                  <label class="mb-1 block text-xs text-[#8c9097]">Condition</label>
                  <select class="ti-form-select w-full" data-field="condition_status" data-key="${escapeHtml(
                    row.__key,
                  )}" ${canEdit() ? "" : "disabled"}>
                    <option value="">Select condition</option>
                    ${options}
                  </select>
                </div>
              </div>
              <div class="mt-3">
                <label class="mb-1 block text-xs text-[#8c9097]">Condition Notes</label>
                <textarea class="ti-form-input w-full" rows="3" data-field="condition_notes" data-key="${escapeHtml(
                  row.__key,
                )}" ${canEdit() ? "" : "disabled"}>${escapeHtml(row.condition_notes || "")}</textarea>
              </div>
            </div>
          `;
        })
        .join("");

      unitRows.forEach((row) => {
        const select = unitRowsContainer.querySelector(
          `select[data-key="${String(row.__key).replace(/"/g, '\\"')}"][data-field="condition_status"]`,
        );
        if (select) {
          select.value = row.condition_status || "";
        }
      });

      renderUnitWorkspaceChrome();
    }

    function updateItemUnitCount(airItemId, count) {
      const item = findItem(airItemId);
      if (!item) return;

      item.units_count = count;
      renderItems();
      renderSummary();
    }

    async function openUnitModal(airItemId) {
      const url = buildUrl(config.unitsIndexUrlTemplate, {
        "__AIR_ITEM__": airItemId,
      });
      const parsed = await requestJson(url, { method: "GET" });

      if (!parsed.ok) {
        await Swal.fire({
          icon: "error",
          title: "Could not open units",
          text: parsed.message,
        });
        return;
      }

      activeAirItemId = airItemId;
      unitState = parsed.data?.data || {};
      unitRows = normalizeUnitRowsToAcceptedQty(
        unitState.units || [],
        unitState.air_item?.qty_accepted || 0,
        getDefaultComponentRows(),
      );
      resetUnitRowsBaseline(unitRows);
      showUnitError("");
      if (unitModalTitle) unitModalTitle.textContent = unitState.air_item?.label || "Inspection Units";
      if (unitModalSubtitle) {
        unitModalSubtitle.textContent = `Accepted quantity: ${
          unitState.air_item?.qty_accepted || 0
        }. Save inspection first after changing quantities on the main page.`;
      }
      renderUnitRows();
      setModalOpen(unitModal, true);
    }

    async function saveUnitRows() {
      if (!unitState || !activeAirItemId) return;

      if (unitToolbarState.dirtyCount === 0) {
        renderUnitWorkspaceChrome();
        return;
      }

      unitToolbarState.isSaving = true;
      renderUnitWorkspaceChrome();

      try {
        const url = buildUrl(config.unitsSaveUrlTemplate, {
          "__AIR_ITEM__": activeAirItemId,
        });
        const parsed = await requestJson(url, {
          method: "PUT",
          body: JSON.stringify({
            units: unitRows.map((row) => ({
              id: row.id || null,
              brand: normalizeText(row.brand || ""),
              model: normalizeText(row.model || ""),
              serial_number: normalizeText(row.serial_number || ""),
              property_number: normalizeText(row.property_number || ""),
              condition_status: normalizeText(row.condition_status || ""),
              condition_notes: String(row.condition_notes || "").trim(),
              components: (row.components || []).map((component) => ({
                id: component.id || null,
                name: normalizeText(component.name || ""),
                quantity: Math.max(1, Number(component.quantity || 1)),
                unit: normalizeText(component.unit || ""),
                component_cost: normalizeText(component.component_cost ?? ""),
                serial_number: normalizeText(component.serial_number || ""),
                condition: normalizeText(component.condition || ""),
                is_present: component.is_present !== false,
                remarks: String(component.remarks || "").trim(),
              })),
            })),
          }),
        });

        if (!parsed.ok) {
          showUnitError(parsed.message);
          await Swal.fire({
            icon: parsed.status === 422 ? "warning" : "error",
            title: parsed.status === 422 ? "Validation failed" : "Save failed",
            html: validationHtml(parsed.data?.errors || {}) || escapeHtml(parsed.message),
          });
          return;
        }

        unitState = parsed.data?.data || unitState;
        unitRows = normalizeUnitRowsToAcceptedQty(
          unitState.units || [],
          unitState.air_item?.qty_accepted || 0,
          getDefaultComponentRows(),
        );
        resetUnitRowsBaseline(unitRows);
        updateItemUnitCount(
          activeAirItemId,
          Number(unitState.air_item?.units_count || 0),
        );
        renderUnitRows();
        if (isModalOpen(componentModal)) {
          activeComponentUnitKey = null;
          setModalOpen(componentModal, false);
        }
        showUnitError("");

        await Swal.fire({
          icon: "success",
          title: "Unit rows saved",
          timer: 900,
          showConfirmButton: false,
        });
      } finally {
        unitToolbarState.isSaving = false;
        renderUnitWorkspaceChrome();
      }
    }

    function findUnitRow(key) {
      return unitRows.find((row) => String(row.__key) === String(key)) || null;
    }

    function findComponentRow(key) {
      const unitRow = findUnitRow(activeComponentUnitKey);
      if (!unitRow || !Array.isArray(unitRow.components)) return null;

      return (
        unitRow.components.find((row) => String(row.__key) === String(key)) || null
      );
    }

    function renderComponentRows() {
      if (!componentRowsContainer || !componentEmpty) return;

      const unitRow = findUnitRow(activeComponentUnitKey);
      if (!unitRow) {
        componentRowsContainer.innerHTML = "";
        componentEmpty.classList.remove("hidden");
        return;
      }

      const defaultComponents = getDefaultComponentRows();
      const components = Array.isArray(unitRow.components) ? unitRow.components : [];
      const conditionOptions = Object.entries(config.conditionStatuses || {})
        .map(
          ([value, label]) =>
            `<option value="${escapeHtml(value)}">${escapeHtml(label)}</option>`,
        )
        .join("");

      if (componentTemplateNote) {
        if (defaultComponents.length > 0) {
          componentTemplateNote.textContent = `This AIR item has ${defaultComponents.length} item template component${
            defaultComponents.length === 1 ? "" : "s"
          } available.`;
          componentTemplateNote.classList.remove("hidden");
        } else {
          componentTemplateNote.classList.add("hidden");
          componentTemplateNote.textContent = "";
        }
      }

      if (componentAddRowButton) {
        componentAddRowButton.classList.toggle("hidden", !canEdit());
      }

      componentEmpty.classList.toggle("hidden", components.length > 0);

      if (components.length === 0) {
        componentRowsContainer.innerHTML = "";
        return;
      }

      componentRowsContainer.innerHTML = components
        .map(
          (row) => `
            <div class="rounded-xl border border-defaultborder p-4 shadow-sm" data-component-key="${escapeHtml(
              row.__key,
            )}">
              <div class="flex flex-wrap items-start justify-between gap-3">
                <div>
                  <p class="mb-1 text-sm font-semibold">${escapeHtml(
                    row.name || "Component Row",
                  )}</p>
                  <p class="mb-0 text-xs text-[#8c9097] dark:text-white/50">
                    ${row.id ? "Saved component row" : "Unsaved component row"}
                  </p>
                </div>
                ${
                  canEdit()
                    ? `<button type="button" class="ti-btn ti-btn-danger" data-action="delete-component-row" data-key="${escapeHtml(
                        row.__key,
                      )}">Delete</button>`
                    : ""
                }
              </div>
              <div class="mt-4 grid gap-3 lg:grid-cols-3">
                <div>
                  <label class="mb-1 block text-xs text-[#8c9097]">Component Name</label>
                  <input type="text" class="ti-form-input w-full" data-field="name" data-key="${escapeHtml(
                    row.__key,
                  )}" value="${escapeHtml(row.name || "")}" ${canEdit() ? "" : "disabled"}>
                </div>
                <div>
                  <label class="mb-1 block text-xs text-[#8c9097]">Quantity</label>
                  <input type="number" min="1" class="ti-form-input w-full" data-field="quantity" data-key="${escapeHtml(
                    row.__key,
                  )}" value="${escapeHtml(row.quantity || 1)}" ${canEdit() ? "" : "disabled"}>
                </div>
                <div>
                  <label class="mb-1 block text-xs text-[#8c9097]">Unit</label>
                  <input type="text" class="ti-form-input w-full" data-field="unit" data-key="${escapeHtml(
                    row.__key,
                  )}" value="${escapeHtml(row.unit || "")}" ${canEdit() ? "" : "disabled"}>
                </div>
                <div>
                  <label class="mb-1 block text-xs text-[#8c9097]">Component Cost</label>
                  <input type="number" min="0" step="0.01" class="ti-form-input w-full" data-field="component_cost" data-key="${escapeHtml(
                    row.__key,
                  )}" value="${escapeHtml(row.component_cost || "")}" ${canEdit() ? "" : "disabled"}>
                </div>
                <div>
                  <label class="mb-1 block text-xs text-[#8c9097]">Serial Number</label>
                  <input type="text" class="ti-form-input w-full" data-field="serial_number" data-key="${escapeHtml(
                    row.__key,
                  )}" value="${escapeHtml(row.serial_number || "")}" ${canEdit() ? "" : "disabled"}>
                </div>
                <div>
                  <label class="mb-1 block text-xs text-[#8c9097]">Condition</label>
                  <select class="ti-form-select w-full" data-field="condition" data-key="${escapeHtml(
                    row.__key,
                  )}" ${canEdit() ? "" : "disabled"}>
                    <option value="">Select condition</option>
                    ${conditionOptions}
                  </select>
                </div>
              </div>
              <div class="mt-3 flex items-center gap-3 rounded bg-light px-3 py-2 text-sm dark:bg-black/20">
                <input type="checkbox" class="form-check-input" data-field="is_present" data-key="${escapeHtml(
                  row.__key,
                )}" ${row.is_present !== false ? "checked" : ""} ${canEdit() ? "" : "disabled"}>
                <span class="text-[#8c9097] dark:text-white/50">Mark this component as present in the inspected delivery.</span>
              </div>
              <div class="mt-3">
                <label class="mb-1 block text-xs text-[#8c9097]">Remarks</label>
                <textarea class="ti-form-input w-full" rows="3" data-field="remarks" data-key="${escapeHtml(
                  row.__key,
                )}" ${canEdit() ? "" : "disabled"}>${escapeHtml(row.remarks || "")}</textarea>
              </div>
            </div>
          `,
        )
        .join("");

      components.forEach((row) => {
        const select = componentRowsContainer.querySelector(
          `select[data-key="${String(row.__key).replace(/"/g, '\\"')}"][data-field="condition"]`,
        );
        if (select) {
          select.value = row.condition || "";
        }
      });
    }

    async function openComponentModal(unitKey) {
      const row = findUnitRow(unitKey);
      if (!row) return;

      if (
        canEdit() &&
        (!Array.isArray(row.components) || row.components.length === 0) &&
        !row.id
      ) {
        row.components = getDefaultComponentRows();
      }

      activeComponentUnitKey = unitKey;
      if (componentModalTitle) {
        componentModalTitle.textContent = row.serial_number || row.property_number || "Unit Components";
      }
      if (componentModalSubtitle) {
        componentModalSubtitle.textContent = `Manage the component schedule for ${
          row.serial_number || row.property_number || "this inspection unit"
        }.`;
      }
      showComponentError("");
      renderComponentRows();
      setModalOpen(componentModal, true);
    }

    function deleteComponentRow(key) {
      const unitRow = findUnitRow(activeComponentUnitKey);
      if (!unitRow || !Array.isArray(unitRow.components)) return;

      unitRow.components = unitRow.components.filter(
        (row) => String(row.__key) !== String(key),
      );
      renderComponentRows();
      renderUnitRows();
    }

    async function deleteUnitRow(key) {
      const row = findUnitRow(key);
      if (!row) return;

      if (!row.id) {
        unitRows = unitRows.map((unit) =>
          String(unit.__key) === String(key)
            ? createUnitRow({ default_components: getDefaultComponentRows() })
            : unit,
        );
        renderUnitRows();
        return;
      }

      const confirmation = await Swal.fire({
        icon: "warning",
        title: "Delete unit row?",
        text: "Any remaining unit images must already be removed before deleting this row.",
        showCancelButton: true,
        confirmButtonText: "Delete",
        cancelButtonText: "Cancel",
      });

      if (!confirmation.isConfirmed) return;

      const url = buildUrl(config.unitsDestroyUrlTemplate, {
        "__AIR_ITEM__": activeAirItemId,
        "__UNIT__": row.id,
      });
      const parsed = await requestJson(url, { method: "DELETE" });

      if (!parsed.ok) {
        showUnitError(parsed.message);
        await Swal.fire({
          icon: parsed.status === 422 ? "warning" : "error",
          title: parsed.status === 422 ? "Delete blocked" : "Delete failed",
          html: validationHtml(parsed.data?.errors || {}) || escapeHtml(parsed.message),
        });
        return;
      }

      unitState = parsed.data?.data || unitState;
      unitRows = normalizeUnitRowsToAcceptedQty(
        unitState.units || [],
        unitState.air_item?.qty_accepted || 0,
        getDefaultComponentRows(),
      );
      resetUnitRowsBaseline(unitRows);
      updateItemUnitCount(
        activeAirItemId,
        Number(unitState.air_item?.units_count || 0),
      );
      renderUnitRows();
      showUnitError("");
    }

    async function openFileModal(unitKey) {
      const row = findUnitRow(unitKey);
      if (!row || !row.id) return;

      activeUnitId = row.id;
      const url = buildUrl(config.unitFilesIndexUrlTemplate, {
        "__AIR_ITEM__": activeAirItemId,
        "__UNIT__": row.id,
      });
      const parsed = await requestJson(url, { method: "GET" });

      if (!parsed.ok) {
        await Swal.fire({
          icon: "error",
          title: "Could not open unit images",
          text: parsed.message,
        });
        return;
      }

      fileState = parsed.data?.data || {};
      if (fileModalTitle) {
        fileModalTitle.textContent = fileState.unit?.label || "Unit Images";
      }
      if (fileModalSubtitle) {
        fileModalSubtitle.textContent = `Condition: ${
          fileState.unit?.condition_status_text || "Unknown"
        }. Images uploaded here stay attached to this saved inspection unit row.`;
      }
      renderFiles();
      showFileError("");
      updateFileUploadButton();
      setModalOpen(fileModal, true);
    }

    function updateUnitRowFileCount(unitId, count) {
      unitRows = unitRows.map((row) =>
        String(row.id) === String(unitId) ? { ...row, file_count: count } : row,
      );
      if (unitRowsContainer && isModalOpen(unitModal)) {
        renderUnitRows();
      }
    }

    function resetUnitWorkspace() {
      activeAirItemId = null;
      unitState = null;
      unitRows = [];
      unitRowsBaseline = [];
      unitToolbarState.dirtyCount = 0;
      unitToolbarState.isSaving = false;
      showUnitError("");

      if (unitRowsContainer) {
        unitRowsContainer.innerHTML = "";
        unitRowsContainer.classList.remove("gso-air-inspection-unit-grid--single");
      }

      if (unitNotice) {
        unitNotice.innerHTML = "";
      }
    }

    function resetComponentWorkspace() {
      activeComponentUnitKey = null;
      showComponentError("");

      if (componentRowsContainer) {
        componentRowsContainer.innerHTML = "";
      }

      if (componentEmpty) {
        componentEmpty.classList.add("hidden");
      }

      if (componentTemplateNote) {
        componentTemplateNote.classList.add("hidden");
        componentTemplateNote.textContent = "";
      }
    }

    function resetFileWorkspace() {
      activeUnitId = null;
      fileState = null;
      showFileError("");

      if (fileGrid) {
        fileGrid.innerHTML = "";
      }

      if (fileEmpty) {
        fileEmpty.classList.add("hidden");
      }

      if (fileInput) {
        fileInput.value = "";
      }

      if (fileTypeInput) {
        fileTypeInput.value = "photo";
      }

      if (fileCaptionInput) {
        fileCaptionInput.value = "";
      }

      updateFileUploadButton();
    }

    function bindOverlayCloseReset(element, handler) {
      ["close.hs.overlay", "hidden.hs.overlay"].forEach((eventName) => {
        element?.addEventListener(eventName, handler);
      });
    }

    function renderFiles() {
      if (!fileGrid || !fileEmpty || !fileState) return;

      const files = Array.isArray(fileState.files) ? fileState.files : [];
      fileEmpty.classList.toggle("hidden", files.length > 0);

      if (files.length === 0) {
        fileGrid.innerHTML = "";
        updateUnitRowFileCount(activeUnitId, 0);
        return;
      }

      fileGrid.innerHTML = files
        .map((file) => {
          const preview = file.is_image
            ? `<img src="${escapeHtml(file.preview_url || "")}" alt="${escapeHtml(
                file.original_name || "Unit image",
              )}" class="gso-air-inspection-file-preview">`
            : `<div class="gso-air-inspection-file-fallback">${escapeHtml(
                file.type_text || "File",
              )}</div>`;

          return `
            <div class="rounded-xl border border-defaultborder overflow-hidden shadow-sm">
              ${preview}
              <div class="p-4">
                <div class="flex items-start justify-between gap-3">
                  <div>
                    <p class="mb-1 text-sm font-semibold">${escapeHtml(
                      file.original_name || file.type_text || "File",
                    )}</p>
                    <p class="mb-0 text-xs text-[#8c9097] dark:text-white/50">
                      ${escapeHtml(file.type_text || "File")}
                      ${file.size_text ? ` | ${escapeHtml(file.size_text)}` : ""}
                      ${file.is_primary ? " | Primary" : ""}
                    </p>
                    ${
                      file.caption
                        ? `<p class="mt-2 mb-0 text-xs text-[#8c9097] dark:text-white/50">${escapeHtml(
                            file.caption,
                          )}</p>`
                        : ""
                    }
                  </div>
                  <a class="ti-btn ti-btn-light" href="${escapeHtml(
                    file.preview_url || "#",
                  )}" target="_blank" rel="noreferrer">Preview</a>
                </div>
                <div class="mt-3 flex flex-wrap items-center gap-2">
                  ${
                    canEdit() && !file.is_primary
                      ? `<button type="button" class="ti-btn ti-btn-success" data-action="set-primary-file" data-file-id="${escapeHtml(
                          file.id || "",
                        )}">Set Primary</button>`
                      : ""
                  }
                  ${
                    canEdit()
                      ? `<button type="button" class="ti-btn ti-btn-danger" data-action="delete-file" data-file-id="${escapeHtml(
                          file.id || "",
                        )}">Delete</button>`
                      : ""
                  }
                </div>
              </div>
            </div>
          `;
        })
        .join("");

      updateUnitRowFileCount(activeUnitId, files.length);
    }

    async function uploadFiles() {
      if (!fileInput || !activeUnitId || !activeAirItemId) return;

      const files = Array.from(fileInput.files || []);
      if (files.length === 0) {
        await Swal.fire({
          icon: "warning",
          title: "No images selected",
          text: "Choose at least one image to upload.",
        });
        return;
      }

      const formData = new FormData();
      files.forEach((file) => formData.append("photos[]", file));
      formData.append("type", fileTypeInput?.value || "photo");
      formData.append("caption", String(fileCaptionInput?.value || "").trim());

      const url = buildUrl(config.unitFilesStoreUrlTemplate, {
        "__AIR_ITEM__": activeAirItemId,
        "__UNIT__": activeUnitId,
      });
      fileUploadState.isUploading = true;
      updateFileUploadButton();
      showLoadingAlert(
        "Uploading images...",
        `${files.length} image${files.length === 1 ? "" : "s"} ${files.length === 1 ? "is" : "are"} being attached to this inspection unit.`,
      );

      try {
        const parsed = await requestForm(url, formData);
        Swal.close();

        if (!parsed.ok) {
          showFileError(parsed.message);
          await Swal.fire({
            icon: parsed.status === 422 ? "warning" : "error",
            title: parsed.status === 422 ? "Upload blocked" : "Upload failed",
            html: validationHtml(parsed.data?.errors || {}) || escapeHtml(parsed.message),
          });
          return;
        }

        fileState = parsed.data?.data || fileState;
        renderFiles();
        fileInput.value = "";
        if (fileTypeInput) {
          fileTypeInput.value = "photo";
        }
        if (fileCaptionInput) {
          fileCaptionInput.value = "";
        }
        showFileError("");
        showToast(
          "success",
          files.length === 1 ? "Image uploaded" : `${files.length} images uploaded`,
        );
      } catch (error) {
        Swal.close();
        const message =
          error instanceof Error && error.message
            ? error.message
            : "The images could not be uploaded right now.";
        showFileError(message);
        await Swal.fire({
          icon: "error",
          title: "Upload failed",
          text: message,
        });
      } finally {
        fileUploadState.isUploading = false;
        updateFileUploadButton();
      }
    }

    async function deleteFile(fileId) {
      const confirmation = await Swal.fire({
        icon: "warning",
        title: "Delete file?",
        showCancelButton: true,
        confirmButtonText: "Delete",
        cancelButtonText: "Cancel",
      });

      if (!confirmation.isConfirmed) return;

      const url = buildUrl(config.unitFilesDestroyUrlTemplate, {
        "__AIR_ITEM__": activeAirItemId,
        "__UNIT__": activeUnitId,
        "__FILE__": fileId,
      });
      const parsed = await requestJson(url, { method: "DELETE" });

      if (!parsed.ok) {
        showFileError(parsed.message);
        await Swal.fire({
          icon: parsed.status === 422 ? "warning" : "error",
          title: parsed.status === 422 ? "Delete blocked" : "Delete failed",
          html: validationHtml(parsed.data?.errors || {}) || escapeHtml(parsed.message),
        });
        return;
      }

      fileState = parsed.data?.data || fileState;
      renderFiles();
      showFileError("");
    }

    async function setPrimaryFile(fileId) {
      const url = buildUrl(config.unitFilesPrimaryUrlTemplate, {
        "__AIR_ITEM__": activeAirItemId,
        "__UNIT__": activeUnitId,
        "__FILE__": fileId,
      });
      const parsed = await requestJson(url, { method: "PUT" });

      if (!parsed.ok) {
        showFileError(parsed.message);
        await Swal.fire({
          icon: parsed.status === 422 ? "warning" : "error",
          title: parsed.status === 422 ? "Primary blocked" : "Primary update failed",
          html: validationHtml(parsed.data?.errors || {}) || escapeHtml(parsed.message),
        });
        return;
      }

      fileState = parsed.data?.data || fileState;
      renderFiles();
      showFileError("");
    }

    saveButton?.addEventListener("click", async () => {
      await saveInspection();
    });

    finalizeButton?.addEventListener("click", async () => {
      await finalizeInspection();
    });

    followUpButton?.addEventListener("click", async () => {
      await createFollowUpAir();
    });

    reopenButton?.addEventListener("click", async () => {
      await reopenInspection();
    });

    promoteButton?.addEventListener("click", async () => {
      await promoteInventory();
    });

    [
      invoiceNumberInput,
      invoiceDateInput,
      dateReceivedInput,
      completenessSelect,
      receivedNotesInput,
    ].forEach((input) => {
      input?.addEventListener("input", refreshInspectionDirtyCount);
      input?.addEventListener("change", refreshInspectionDirtyCount);
    });

    page.addEventListener("input", (event) => {
      const field = event.target?.dataset?.field;
      const airItemId = event.target?.dataset?.airItemId;
      if (!field || !airItemId) return;
      updateItemField(airItemId, field, event.target.value);
      refreshInspectionDirtyCount();
    });

    page.addEventListener("change", (event) => {
      const field = event.target?.dataset?.field;
      const airItemId = event.target?.dataset?.airItemId;
      if (!field || !airItemId) return;
      updateItemField(airItemId, field, event.target.value);
      refreshInspectionDirtyCount();
    });

    page.addEventListener("click", async (event) => {
      const unitsButton = event.target.closest('[data-action="open-units"]');
      if (unitsButton) {
        await openUnitModal(unitsButton.dataset.airItemId || "");
      }
    });

    unitRowsContainer?.addEventListener("input", (event) => {
      const field = event.target?.dataset?.field;
      const key = event.target?.dataset?.key;
      const row = findUnitRow(key);
      if (!row || !field) return;
      row[field] = event.target.value;
      renderUnitWorkspaceChrome();
    });

    unitRowsContainer?.addEventListener("change", (event) => {
      const field = event.target?.dataset?.field;
      const key = event.target?.dataset?.key;
      const row = findUnitRow(key);
      if (!row || !field) return;
      row[field] = event.target.value;
      renderUnitWorkspaceChrome();
    });

    unitRowsContainer?.addEventListener("click", async (event) => {
      const componentsButton = event.target.closest(
        '[data-action="open-unit-components"]',
      );
      if (componentsButton) {
        await openComponentModal(componentsButton.dataset.key || "");
        return;
      }

      const deleteButton = event.target.closest('[data-action="delete-unit-row"]');
      if (deleteButton) {
        await deleteUnitRow(deleteButton.dataset.key || "");
        return;
      }

      const filesButton = event.target.closest('[data-action="open-unit-files"]');
      if (filesButton) {
        await openFileModal(filesButton.dataset.key || "");
      }
    });

    unitSaveButton?.addEventListener("click", async () => {
      await saveUnitRows();
    });

    [qs("gsoAirUnitModalClose"), qs("gsoAirUnitCloseBtn")].forEach((button) => {
      button?.addEventListener("click", () => {
        activeComponentUnitKey = null;
        setModalOpen(componentModal, false);
        setModalOpen(unitModal, false);
      });
    });

    [qs("gsoAirUnitComponentModalClose"), qs("gsoAirUnitComponentCloseBtn")].forEach(
      (button) => {
        button?.addEventListener("click", () => {
          activeComponentUnitKey = null;
          setModalOpen(componentModal, false);
        });
      },
    );

    [qs("gsoAirUnitFileModalClose"), qs("gsoAirUnitFileCloseBtn")].forEach((button) => {
      button?.addEventListener("click", () => {
        setModalOpen(fileModal, false);
      });
    });

    bindOverlayCloseReset(componentModal, resetComponentWorkspace);
    bindOverlayCloseReset(fileModal, resetFileWorkspace);
    bindOverlayCloseReset(unitModal, () => {
      resetFileWorkspace();
      resetComponentWorkspace();
      resetUnitWorkspace();
    });

    componentRowsContainer?.addEventListener("input", (event) => {
      const field = event.target?.dataset?.field;
      const key = event.target?.dataset?.key;
      const row = findComponentRow(key);
      if (!row || !field) return;

      if (field === "is_present") {
        return;
      }

      row[field] = event.target.value;
      renderUnitRows();
      renderUnitWorkspaceChrome();
    });

    componentRowsContainer?.addEventListener("change", (event) => {
      const field = event.target?.dataset?.field;
      const key = event.target?.dataset?.key;
      const row = findComponentRow(key);
      if (!row || !field) return;

      if (field === "is_present") {
        row.is_present = !!event.target.checked;
      } else if (field === "quantity") {
        row.quantity = Math.max(1, Number(event.target.value || 1));
      } else {
        row[field] = event.target.value;
      }

      renderComponentRows();
      renderUnitRows();
      renderUnitWorkspaceChrome();
    });

    componentRowsContainer?.addEventListener("click", (event) => {
      const deleteButton = event.target.closest('[data-action="delete-component-row"]');
      if (deleteButton) {
        deleteComponentRow(deleteButton.dataset.key || "");
      }
    });

    componentAddRowButton?.addEventListener("click", async () => {
      const unitRow = findUnitRow(activeComponentUnitKey);
      if (!unitRow || !canEdit()) return;

      unitRow.components = Array.isArray(unitRow.components) ? unitRow.components : [];
      unitRow.components = [...unitRow.components, createComponentRow()];
      renderComponentRows();
      renderUnitRows();
      renderUnitWorkspaceChrome();
    });

    fileUploadButton?.addEventListener("click", async () => {
      await uploadFiles();
    });

    fileInput?.addEventListener("change", updateFileUploadButton);

    tabletTabButtons.forEach((button) => {
      button.addEventListener("click", () => {
        setActiveTabletTab(button.dataset.airInspectionTab || "receiving");
      });
    });

    const syncInspectionLayout = () => applyTabletLayout();
    if (typeof tabletViewport.addEventListener === "function") {
      tabletViewport.addEventListener("change", syncInspectionLayout);
    } else if (typeof tabletViewport.addListener === "function") {
      tabletViewport.addListener(syncInspectionLayout);
    }
    window.addEventListener("resize", syncInspectionLayout);

    fileGrid?.addEventListener("click", async (event) => {
      const deleteButton = event.target.closest('[data-action="delete-file"]');
      if (deleteButton) {
        await deleteFile(deleteButton.dataset.fileId || "");
        return;
      }

      const primaryButton = event.target.closest('[data-action="set-primary-file"]');
      if (primaryButton) {
        await setPrimaryFile(primaryButton.dataset.fileId || "");
      }
    });

    syncHeaderInputs();
    renderSummary();
    renderItems();
    resetInspectionBaseline();
    updateFileUploadButton();
    applyTabletLayout();
    if (canPromote()) {
      void refreshPromotionEligibility({ force: true });
    } else {
      syncPromoteButtonState();
    }
  });
})();
