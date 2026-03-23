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

  function setModalOpen(element, open) {
    if (!element) return;
    element.classList.toggle("is-open", open);
    document.body.classList.toggle(
      "overflow-hidden",
      document.querySelector(".gso-air-inspection-modal.is-open") !== null,
    );
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
    const promoteButton = qs("gsoAirInspectionPromoteBtn");

    const unitModal = qs("gsoAirUnitModal");
    const unitModalTitle = qs("gsoAirUnitModalTitle");
    const unitModalSubtitle = qs("gsoAirUnitModalSubtitle");
    const unitRowsContainer = qs("gsoAirUnitRows");
    const unitNotice = qs("gsoAirUnitNotice");
    const unitError = qs("gsoAirUnitError");
    const unitAddRowButton = qs("gsoAirUnitAddRowBtn");
    const unitSaveButton = qs("gsoAirUnitSaveBtn");

    const componentModal = qs("gsoAirUnitComponentModal");
    const componentModalTitle = qs("gsoAirUnitComponentModalTitle");
    const componentModalSubtitle = qs("gsoAirUnitComponentModalSubtitle");
    const componentRowsContainer = qs("gsoAirUnitComponentRows");
    const componentEmpty = qs("gsoAirUnitComponentEmpty");
    const componentError = qs("gsoAirUnitComponentError");
    const componentTemplateNote = qs("gsoAirUnitComponentTemplateNote");
    const componentUseDefaultsButton = qs("gsoAirUnitComponentUseDefaultsBtn");
    const componentAddRowButton = qs("gsoAirUnitComponentAddRowBtn");

    const fileModal = qs("gsoAirUnitFileModal");
    const fileModalTitle = qs("gsoAirUnitFileModalTitle");
    const fileModalSubtitle = qs("gsoAirUnitFileModalSubtitle");
    const fileGrid = qs("gsoAirUnitFileGrid");
    const fileEmpty = qs("gsoAirUnitFileEmpty");
    const fileError = qs("gsoAirUnitFileError");
    const fileInput = qs("gsoAirUnitFileInput");
    const fileUploadButton = qs("gsoAirUnitFileUploadBtn");

    function canEdit() {
      return !!config.canEditInspection && !!state.air?.can_edit_inspection;
    }

    function canPromote() {
      return (
        !!config.canPromoteInventory &&
        String(state.air?.status || "") === "inspected"
      );
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
      if (invoiceDateInput) invoiceDateInput.value = state.air?.invoice_date || "";
      if (dateReceivedInput) dateReceivedInput.value = state.air?.date_received || "";
      if (completenessSelect) completenessSelect.value = state.air?.received_completeness || "";
      if (receivedNotesInput) receivedNotesInput.value = state.air?.received_notes || "";
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
      if (saveButton) saveButton.disabled = !canEdit();
      if (finalizeButton) finalizeButton.disabled = !canEdit();
      if (promoteButton) promoteButton.disabled = !canPromote();
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
        .map((item) => {
          const needsUnits = !!item?.needs_units;
          const trackingType = normalizeText(item?.tracking_type_snapshot || "property");
          const orderedQty = Math.max(0, Number(item?.qty_ordered || 0));
          const deliveredQty = Math.max(0, Number(item?.qty_delivered || 0));
          const acceptedQty = Math.max(0, Number(item?.qty_accepted || 0));
          const unitsCount = Math.max(0, Number(item?.units_count || 0));
          const isIncompleteDelivery = orderedQty > 0 && deliveredQty < orderedQty;
          const disabled = canEdit() ? "" : "disabled";
          const acceptedDisabled = canEdit() && isIncompleteDelivery ? "disabled" : disabled;
          const serialLabel = item?.requires_serial_snapshot ? "Serial required" : "Serial optional";
          const semiLabel = item?.is_semi_expendable_snapshot ? "ICS / semi-expendable" : "Regular property";
          const itemNote = needsUnits
            ? `${serialLabel}. ${semiLabel}.`
            : "Consumable or non-unit-tracked line.";

          return `
            <div class="rounded-xl border border-defaultborder p-4 shadow-sm" data-air-item-id="${escapeHtml(item?.id || "")}">
              <div class="flex flex-wrap items-start justify-between gap-3">
                <div class="min-w-0">
                  <p class="mb-1 text-sm font-semibold">${escapeHtml(item?.item_label || "AIR Item")}</p>
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
                ${
                  needsUnits
                    ? `<div class="flex flex-wrap items-center gap-2">
                        <button type="button" class="ti-btn ti-btn-light" data-action="open-units" data-air-item-id="${escapeHtml(
                          item?.id || "",
                        )}">
                          Units
                        </button>
                        <span class="rounded-full bg-light px-3 py-1 text-xs text-[#8c9097] dark:bg-black/20 dark:text-white/50">
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
                    ? "Use Units to manage unit rows, components, and evidence files."
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

      syncHeaderInputs();
      renderSummary();
      renderItems();
    }

    function findItem(id) {
      return state.items.find((item) => String(item?.id) === String(id)) || null;
    }

    function updateItemField(itemId, field, value) {
      const item = findItem(itemId);
      if (!item) return;

      item[field] = value;
    }

    function buildInspectionPayload() {
      return {
        invoice_number: normalizeText(invoiceNumberInput?.value || ""),
        invoice_date: normalizeText(invoiceDateInput?.value || ""),
        date_received: normalizeText(dateReceivedInput?.value || ""),
        received_completeness: normalizeText(completenessSelect?.value || ""),
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

    async function saveInspection() {
      showFormError("");

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

      await Swal.fire({
        icon: "success",
        title: "Inspection saved",
        timer: 900,
        showConfirmButton: false,
      });

      return true;
    }

    async function finalizeInspection() {
      const saved = await saveInspection();
      if (!saved) {
        return;
      }

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
      setModalOpen(unitModal, false);
      setModalOpen(componentModal, false);
      setModalOpen(fileModal, false);

      await Swal.fire({
        icon: "success",
        title: "Inspection finalized",
        timer: 1200,
        showConfirmButton: false,
      });
    }

    async function loadPromotionEligibility() {
      return requestJson(config.promoteEligibleUrl, { method: "GET" });
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

      const propertyPreview = propertyUnits
        .slice(0, 4)
        .map(
          (row) =>
            `<li>${escapeHtml(
              row?.unit_label || row?.item_label || "Property unit",
            )} <span class="text-[#8c9097]">(${escapeHtml(
              row?.classification || "PPE",
            )})</span></li>`,
        )
        .join("");
      const blockedPreview = blockedPropertyUnits
        .slice(0, 4)
        .map(
          (row) =>
            `<li>${escapeHtml(
              row?.unit_label || row?.item_label || "Blocked unit",
            )} <span class="text-danger">${escapeHtml(
              row?.promotion_blocked_reason || "Blocked from promotion",
            )}</span></li>`,
        )
        .join("");
      const warningPreview = propertyUnits
        .filter((row) => normalizeText(row?.promotion_warning || "") !== "")
        .slice(0, 4)
        .map(
          (row) =>
            `<li>${escapeHtml(
              row?.unit_label || row?.item_label || "Property unit",
            )} <span class="text-warning">${escapeHtml(
              row?.promotion_warning || "",
            )}</span></li>`,
        )
        .join("");
      const consumablePreview = consumables
        .slice(0, 4)
        .map(
          (row) =>
            `<li>${escapeHtml(row?.item_label || "Consumable line")} <span class="text-[#8c9097]">(${escapeHtml(
              row?.qty_accepted || 0,
            )} ${escapeHtml(
              row?.unit_snapshot || row?.base_unit || "unit",
            )})</span></li>`,
        )
        .join("");

      return `
        <div class="text-left">
          <div class="mb-3 rounded bg-light p-3 text-sm text-[#475569]">
            <div><strong>Property units:</strong> ${escapeHtml(
              summary.property_units_count || 0,
            )}</div>
            <div><strong>Blocked property units:</strong> ${escapeHtml(
              summary.blocked_property_units_count || 0,
            )}</div>
            <div><strong>Consumable lines:</strong> ${escapeHtml(
              summary.consumable_lines_count || 0,
            )}</div>
            <div><strong>Total accepted consumable qty:</strong> ${escapeHtml(
              summary.consumable_qty_accepted || 0,
            )}</div>
          </div>
          ${
            propertyPreview
              ? `<div class="mb-3"><div class="mb-1 text-xs uppercase tracking-wide text-[#8c9097]">Property Preview</div><ul class="pl-4 text-sm">${propertyPreview}</ul></div>`
              : ""
          }
          ${
            blockedPreview
              ? `<div class="mb-3"><div class="mb-1 text-xs uppercase tracking-wide text-danger">Blocked Property Units</div><ul class="pl-4 text-sm">${blockedPreview}</ul></div>`
              : ""
          }
          ${
            warningPreview
              ? `<div class="mb-3"><div class="mb-1 text-xs uppercase tracking-wide text-warning">Promotion Warnings</div><ul class="pl-4 text-sm">${warningPreview}</ul></div>`
              : ""
          }
          ${
            consumablePreview
              ? `<div><div class="mb-1 text-xs uppercase tracking-wide text-[#8c9097]">Consumable Preview</div><ul class="pl-4 text-sm">${consumablePreview}</ul></div>`
              : ""
          }
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
          width: blockedPropertyUnits.length > 0 ? 720 : undefined,
        });
        return;
      }

      const confirmation = await Swal.fire({
        icon: "question",
        title: "Promote AIR to inventory?",
        html: buildPromotionSummaryHtml(eligibility),
        showCancelButton: true,
        confirmButtonText: "Promote",
        cancelButtonText: "Cancel",
        width: 720,
      });

      if (!confirmation.isConfirmed) return;

      const parsed = await requestJson(config.promoteUrl, {
        method: "POST",
        body: JSON.stringify({
          air_item_unit_ids: propertyUnits
            .map((row) => row?.air_item_unit_id || "")
            .filter((value) => String(value).trim() !== ""),
        }),
      });

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
        html: `
          <div class="text-left">
            <p class="mb-3">${escapeHtml(parsed.message || "AIR promoted successfully.")}</p>
            <div class="rounded bg-light p-3 text-sm text-[#475569]">
              <div><strong>Property created:</strong> ${escapeHtml(result.property_created || 0)}</div>
              <div><strong>Consumables posted:</strong> ${escapeHtml(result.consumable_posted || 0)}</div>
              <div><strong>Copied files:</strong> ${escapeHtml(result.copied_files || 0)}</div>
              <div><strong>Copied components:</strong> ${escapeHtml(result.components_copied || 0)}</div>
            </div>
          </div>
        `,
      });
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
          <div class="rounded border border-dashed border-defaultborder p-5 text-sm text-[#8c9097] dark:text-white/50">
            No unit rows saved yet for this AIR item.
          </div>
        `;
        return;
      }

      const options = Object.entries(config.conditionStatuses || {})
        .map(
          ([value, label]) =>
            `<option value="${escapeHtml(value)}">${escapeHtml(label)}</option>`,
        )
        .join("");

      unitRowsContainer.innerHTML = unitRows
        .map((row) => {
          const fileButton =
            row.id && row.id !== ""
              ? `<button type="button" class="ti-btn ti-btn-light" data-action="open-unit-files" data-key="${escapeHtml(
                  row.__key,
                )}">Files (${escapeHtml(row.file_count)})</button>`
              : `<span class="rounded-full bg-light px-3 py-1 text-xs text-[#8c9097] dark:bg-black/20 dark:text-white/50">Save row first to manage files</span>`;
          const componentSummary = buildUnitComponentSummary(row);

          return `
            <div class="rounded-xl border border-defaultborder p-4 shadow-sm" data-unit-key="${escapeHtml(
              row.__key,
            )}">
              <div class="flex flex-wrap items-start justify-between gap-3">
                <div>
                  <p class="mb-1 text-sm font-semibold">${escapeHtml(
                    row.serial_number || row.property_number || "Inspection Unit",
                  )}</p>
                  <p class="mb-0 text-xs text-[#8c9097] dark:text-white/50">
                    ${row.id ? "Saved row" : "Unsaved row"}
                  </p>
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
                        )}">Delete</button>`
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
                  )}" value="${escapeHtml(row.property_number || "")}" ${canEdit() ? "" : "disabled"}>
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
      unitRows = cloneServerUnitRows(unitState.units || []);
      showUnitError("");
      if (unitModalTitle) unitModalTitle.textContent = unitState.air_item?.label || "Inspection Units";
      if (unitModalSubtitle) {
        unitModalSubtitle.textContent = `Accepted quantity: ${
          unitState.air_item?.qty_accepted || 0
        }. Save inspection first after changing quantities on the main page.`;
      }
      if (unitNotice) {
        const templateCount = Array.isArray(unitState.air_item?.default_components)
          ? unitState.air_item.default_components.length
          : 0;
        unitNotice.textContent = unitState.air_item?.needs_units
          ? `Saved rows: ${unitState.air_item?.units_count || 0}. Remaining available slots: ${
              unitState.air_item?.remaining_unit_slots || 0
            }.${
              templateCount > 0
                ? ` New unit rows will preload ${templateCount} component template${
                    templateCount === 1 ? "" : "s"
                  }.`
                : ""
            }`
          : "This AIR item does not require inspection unit rows.";
      }
      renderUnitRows();
      setModalOpen(unitModal, true);
    }

    async function saveUnitRows() {
      if (!unitState || !activeAirItemId) return;

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
      unitRows = cloneServerUnitRows(unitState.units || []);
      updateItemUnitCount(activeAirItemId, unitRows.length);
      if (unitNotice) {
        const templateCount = Array.isArray(unitState.air_item?.default_components)
          ? unitState.air_item.default_components.length
          : 0;
        unitNotice.textContent = `Saved rows: ${unitState.air_item?.units_count || 0}. Remaining available slots: ${
          unitState.air_item?.remaining_unit_slots || 0
        }.${
          templateCount > 0
            ? ` New unit rows will preload ${templateCount} component template${
                templateCount === 1 ? "" : "s"
              }.`
            : ""
        }`;
      }
      renderUnitRows();
      if (componentModal?.classList.contains("is-open")) {
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

      if (componentUseDefaultsButton) {
        componentUseDefaultsButton.classList.toggle(
          "hidden",
          !canEdit() || defaultComponents.length === 0,
        );
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
                  <input type="text" class="ti-form-input w-full" data-field="condition" data-key="${escapeHtml(
                    row.__key,
                  )}" value="${escapeHtml(row.condition || "")}" ${canEdit() ? "" : "disabled"}>
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
    }

    async function useDefaultComponents() {
      const defaultComponents = getDefaultComponentRows();
      const unitRow = findUnitRow(activeComponentUnitKey);

      if (!unitRow || defaultComponents.length === 0 || !canEdit()) {
        return;
      }

      const hasExisting = Array.isArray(unitRow.components) && unitRow.components.length > 0;
      if (hasExisting) {
        const confirmation = await Swal.fire({
          icon: "question",
          title: "Replace current components?",
          text: "This will replace the staged component rows with the item template defaults.",
          showCancelButton: true,
          confirmButtonText: "Use Template",
          cancelButtonText: "Cancel",
        });

        if (!confirmation.isConfirmed) return;
      }

      unitRow.components = defaultComponents;
      showComponentError("");
      renderComponentRows();
      renderUnitRows();
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
        unitRows = unitRows.filter((unit) => String(unit.__key) !== String(key));
        renderUnitRows();
        return;
      }

      const confirmation = await Swal.fire({
        icon: "warning",
        title: "Delete unit row?",
        text: "Any remaining unit files must already be removed before deleting this row.",
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
      unitRows = cloneServerUnitRows(unitState.units || []);
      updateItemUnitCount(activeAirItemId, unitRows.length);
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
          title: "Could not open unit files",
          text: parsed.message,
        });
        return;
      }

      fileState = parsed.data?.data || {};
      if (fileModalTitle) {
        fileModalTitle.textContent = fileState.unit?.label || "Unit Files";
      }
      if (fileModalSubtitle) {
        fileModalSubtitle.textContent = `Condition: ${
          fileState.unit?.condition_status_text || "Unknown"
        }. Files uploaded here stay attached to this saved inspection unit row.`;
      }
      renderFiles();
      showFileError("");
      setModalOpen(fileModal, true);
    }

    function updateUnitRowFileCount(unitId, count) {
      unitRows = unitRows.map((row) =>
        String(row.id) === String(unitId) ? { ...row, file_count: count } : row,
      );
      if (unitRowsContainer && unitModal?.classList.contains("is-open")) {
        renderUnitRows();
      }
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
                file.original_name || "Unit file",
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
          title: "No files selected",
          text: "Choose at least one image or PDF to upload.",
        });
        return;
      }

      const formData = new FormData();
      files.forEach((file) => formData.append("files[]", file));

      const url = buildUrl(config.unitFilesStoreUrlTemplate, {
        "__AIR_ITEM__": activeAirItemId,
        "__UNIT__": activeUnitId,
      });
      const parsed = await requestForm(url, formData);

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
      showFileError("");
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
      const confirmation = await Swal.fire({
        icon: "question",
        title: "Finalize inspection?",
        text: "This moves the AIR into inspected status and locks further editing in this workspace.",
        showCancelButton: true,
        confirmButtonText: "Finalize",
        cancelButtonText: "Cancel",
      });

      if (!confirmation.isConfirmed) return;
      await finalizeInspection();
    });

    promoteButton?.addEventListener("click", async () => {
      await promoteInventory();
    });

    page.addEventListener("input", (event) => {
      const field = event.target?.dataset?.field;
      const airItemId = event.target?.dataset?.airItemId;
      if (!field || !airItemId) return;
      updateItemField(airItemId, field, event.target.value);
    });

    page.addEventListener("change", (event) => {
      const field = event.target?.dataset?.field;
      const airItemId = event.target?.dataset?.airItemId;
      if (!field || !airItemId) return;
      updateItemField(airItemId, field, event.target.value);
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
    });

    unitRowsContainer?.addEventListener("change", (event) => {
      const field = event.target?.dataset?.field;
      const key = event.target?.dataset?.key;
      const row = findUnitRow(key);
      if (!row || !field) return;
      row[field] = event.target.value;
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

    unitAddRowButton?.addEventListener("click", async () => {
      if (!unitState?.air_item || !canEdit()) return;

      if (unitRows.length >= Number(unitState.air_item?.qty_accepted || 0)) {
        await Swal.fire({
          icon: "warning",
          title: "No more unit slots",
          text: "The number of unit rows cannot exceed the saved accepted quantity for this AIR item.",
        });
        return;
      }

      unitRows = [...unitRows, createUnitRow()];
      const latest = unitRows[unitRows.length - 1];
      if (latest && (!latest.components || latest.components.length === 0)) {
        latest.components = getDefaultComponentRows();
      }
      renderUnitRows();
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

    unitModal?.addEventListener("click", (event) => {
      if (event.target === unitModal) {
        activeComponentUnitKey = null;
        setModalOpen(componentModal, false);
        setModalOpen(unitModal, false);
      }
    });

    componentModal?.addEventListener("click", (event) => {
      if (event.target === componentModal) {
        activeComponentUnitKey = null;
        setModalOpen(componentModal, false);
      }
    });

    fileModal?.addEventListener("click", (event) => {
      if (event.target === fileModal) {
        setModalOpen(fileModal, false);
      }
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
    });

    componentRowsContainer?.addEventListener("click", (event) => {
      const deleteButton = event.target.closest('[data-action="delete-component-row"]');
      if (deleteButton) {
        deleteComponentRow(deleteButton.dataset.key || "");
      }
    });

    componentUseDefaultsButton?.addEventListener("click", async () => {
      await useDefaultComponents();
    });

    componentAddRowButton?.addEventListener("click", async () => {
      const unitRow = findUnitRow(activeComponentUnitKey);
      if (!unitRow || !canEdit()) return;

      unitRow.components = Array.isArray(unitRow.components) ? unitRow.components : [];
      unitRow.components = [...unitRow.components, createComponentRow()];
      renderComponentRows();
      renderUnitRows();
    });

    fileUploadButton?.addEventListener("click", async () => {
      await uploadFiles();
    });

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
  });
})();
