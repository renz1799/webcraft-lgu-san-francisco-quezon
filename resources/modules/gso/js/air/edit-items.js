import Swal from "sweetalert2";
import "sweetalert2/dist/sweetalert2.min.css";

(function () {
  "use strict";

  const cfg = window.__gsoAirEdit || {};
  const dirtyMap = new Map();
  const originalMap = new Map();
  const currentItemMap = new Map();
  const suggestionMap = new Map();
  let suggestTimer = null;

  const onReady = (fn) => {
    if (document.readyState === "loading") {
      document.addEventListener("DOMContentLoaded", fn);
      return;
    }

    fn();
  };

  const el = (id) => document.getElementById(id);
  const esc = (value) =>
    String(value ?? "")
      .replace(/&/g, "&amp;")
      .replace(/</g, "&lt;")
      .replace(/>/g, "&gt;")
      .replace(/"/g, "&quot;")
      .replace(/'/g, "&#39;");

  const getCsrf = () =>
    document.querySelector('meta[name="csrf-token"]')?.content || cfg.csrf || "";

  const setSuggestionsVisible = (node, visible) => {
    if (!node) return;
    node.classList.toggle("hidden", !visible);
  };

  const setCount = (value) => {
    const count = Math.max(0, Number(value) || 0);
    const visible = el("gsoAirItemCount");
    const summary = el("gsoAirItemCountSummary");
    if (visible) visible.textContent = String(count);
    if (summary) summary.textContent = String(count);
  };

  const showError = (message) => {
    const node = el("gsoAirItemError");
    if (!node) return;
    if (!message) {
      node.textContent = "";
      node.classList.add("hidden");
      return;
    }

    node.textContent = String(message);
    node.classList.remove("hidden");
  };

  const formatMoney = (value) => {
    if (value === null || value === undefined || value === "") return "-";
    const number = Number(value);
    if (!Number.isFinite(number)) return "-";
    return number.toLocaleString(undefined, {
      minimumFractionDigits: 2,
      maximumFractionDigits: 2,
    });
  };

  const normalizeQty = (value) => {
    const number = Number(value);
    return Number.isFinite(number) && number > 0 ? number : 1;
  };

  const normalizeUnit = (value) => String(value ?? "").trim();
  const normalizeUnitKey = (value) => normalizeUnit(value).toLowerCase();

  const normalizeCost = (value) => {
    if (value === null || value === undefined) return null;
    const raw = String(value).trim();
    if (raw === "") return null;
    const number = Number(raw);
    return Number.isFinite(number) ? number : null;
  };

  const normalizeDesc = (value) => String(value ?? "").replace(/\r\n/g, "\n").trim();

  const validationHtml = (errors) => {
    if (!errors || typeof errors !== "object") return "";
    const rows = [];

    Object.values(errors).forEach((messages) => {
      (Array.isArray(messages) ? messages : [messages]).forEach((message) => {
        rows.push(`<li>${esc(message)}</li>`);
      });
    });

    return rows.length > 0
      ? `<ul style="text-align:left; margin:0; padding-left:18px;">${rows.join("")}</ul>`
      : "";
  };

  async function parseErrorResponse(response) {
    const contentType = response.headers.get("content-type") || "";
    const data = contentType.includes("application/json")
      ? await response.json().catch(() => null)
      : null;

    return {
      data,
      message:
        data?.message ||
        data?.error ||
        (response.status === 401
          ? "Your session expired. Please sign in again."
          : response.status === 403
          ? "You do not have permission to manage AIR items."
          : response.status === 404
          ? "The AIR item could not be found."
          : response.status === 409
          ? "This AIR draft is no longer editable."
          : response.status === 419
          ? "Your security token expired. Refresh the page and try again."
          : response.status === 422
          ? "Please check the required AIR item details."
          : "The request could not be completed."),
    };
  }

  function normalizeAvailableUnits(units) {
    if (!Array.isArray(units)) return [];

    const seen = new Set();
    return units
      .map((unit) => ({
        value: normalizeUnit(unit?.value),
        label: normalizeUnit(unit?.label) || normalizeUnit(unit?.value),
      }))
      .filter((unit) => {
        const key = normalizeUnitKey(unit.value);
        if (!key || seen.has(key)) return false;
        seen.add(key);
        return true;
      });
  }

  const getItemName = (item) =>
    item?.item_name_snapshot || item?.item_name || item?.item_label || item?.name || "Item";

  function setCurrentItems(items) {
    currentItemMap.clear();
    (Array.isArray(items) ? items : []).forEach((item) => {
      const id = String(item?.id || "");
      if (!id) return;
      currentItemMap.set(id, {
        ...item,
        available_units: normalizeAvailableUnits(item?.available_units),
      });
    });
  }

  const getCurrentItem = (id) => currentItemMap.get(String(id || "")) || null;

  const snapshotItem = (item) => ({
    qty_ordered: normalizeQty(item?.qty_ordered),
    unit_snapshot: normalizeUnit(item?.unit_snapshot),
    acquisition_cost: normalizeCost(item?.acquisition_cost),
    description_snapshot: normalizeDesc(item?.description_snapshot),
  });

  function captureOriginalItems(items) {
    originalMap.clear();
    dirtyMap.clear();
    syncToolbarItemDirtyCount();

    (Array.isArray(items) ? items : []).forEach((item) => {
      const id = String(item?.id || "");
      if (!id) return;
      originalMap.set(id, snapshotItem(item));
    });
  }

  function readCurrentItemState(id) {
    const item = getCurrentItem(id);

    return {
      qty_ordered: normalizeQty(
        document.querySelector(`[data-gso-air-item-qty="${id}"]`)?.value ?? item?.qty_ordered
      ),
      unit_snapshot: normalizeUnit(
        document.querySelector(`[data-gso-air-item-unit="${id}"]`)?.value ?? item?.unit_snapshot
      ),
      acquisition_cost: normalizeCost(
        document.querySelector(`[data-gso-air-item-cost="${id}"]`)?.value ??
          item?.acquisition_cost
      ),
      description_snapshot: normalizeDesc(
        document.querySelector(`[data-gso-air-item-desc="${id}"]`)?.value ??
          item?.description_snapshot
      ),
    };
  }

  function getBlockingUnitIssue(item, selectedUnit) {
    const units = normalizeAvailableUnits(item?.available_units);
    const safeSelectedUnit = normalizeUnit(selectedUnit);

    if (units.length === 0) {
      return "This item has no configured units. Update the item setup first.";
    }

    if (!safeSelectedUnit) {
      return "Choose a configured unit before saving.";
    }

    const hasMatch = units.some(
      (option) => normalizeUnitKey(option.value) === normalizeUnitKey(safeSelectedUnit)
    );

    if (hasMatch) return null;

    return `Saved unit "${safeSelectedUnit}" is no longer valid. Choose one of: ${units
      .map((option) => option.value)
      .join(", ")}.`;
  }

  function getBlockingItemIssues() {
    const issues = [];

    currentItemMap.forEach((item, id) => {
      const current = readCurrentItemState(id);
      const issue = getBlockingUnitIssue(item, current.unit_snapshot);
      if (!issue) return;
      issues.push({ id, message: `${getItemName(item)}: ${issue}` });
    });

    return issues;
  }

  function syncToolbarItemDirtyCount() {
    const count = dirtyMap.size;
    window.__gsoAirPendingItemDirtyCount = count;

    if (typeof window.__gsoAirSetItemDirtyCount === "function") {
      window.__gsoAirSetItemDirtyCount(count);
    }
  }

  function syncDirtyForItem(id) {
    const key = String(id || "");
    if (!key || !originalMap.has(key)) return;

    const original = originalMap.get(key);
    const current = readCurrentItemState(key);
    const patch = { id: key };
    let changed = false;

    ["qty_ordered", "unit_snapshot", "acquisition_cost", "description_snapshot"].forEach(
      (field) => {
        if (current[field] !== original[field]) {
          patch[field] = current[field];
          changed = true;
        }
      }
    );

    if (changed) dirtyMap.set(key, patch);
    else dirtyMap.delete(key);

    syncToolbarItemDirtyCount();
  }

  function renderSelectOptions(options, selectedUnit, config = {}) {
    const placeholder = config.placeholder || "Select unit";
    const invalidLabel = config.invalidLabel || null;
    const normalizedSelected = normalizeUnitKey(selectedUnit);
    const hasSelected = normalizedSelected !== "";
    const hasMatch = options.some(
      (option) => normalizeUnitKey(option.value) === normalizedSelected
    );
    const rows = [];

    if (!hasSelected) {
      rows.push(`<option value="" selected disabled>${esc(placeholder)}</option>`);
    } else if (!hasMatch) {
      rows.push(
        `<option value="${esc(selectedUnit)}" selected disabled>${esc(
          invalidLabel || `Invalid saved unit: ${selectedUnit}`
        )}</option>`
      );
    } else {
      rows.push(`<option value="" disabled>${esc(placeholder)}</option>`);
    }

    options.forEach((option) => {
      const selected =
        hasMatch && normalizeUnitKey(option.value) === normalizedSelected ? "selected" : "";
      rows.push(
        `<option value="${esc(option.value)}" ${selected}>${esc(
          option.label || option.value
        )}</option>`
      );
    });

    return rows.join("");
  }

  const renderUnitHelpText = (options, issue) => {
    if (issue) {
      return `<div class="text-[11px] text-danger mt-1">${esc(issue)}</div>`;
    }

    return options.length > 0
      ? `<div class="text-[11px] text-[#8c9097] mt-1">Available: ${esc(
          options.map((option) => option.label).join(", ")
        )}</div>`
      : "";
  };

  function renderItems(items) {
    const list = el("gsoAirItemList");
    if (!list) return;

    setCurrentItems(items);
    captureOriginalItems(items);

    if (!Array.isArray(items) || items.length === 0) {
      list.innerHTML =
        '<div class="text-xs text-[#8c9097]">No items added yet. Add at least one item before submitting this AIR.</div>';
      setCount(0);
      return;
    }

    setCount(items.length);

    list.innerHTML = items
      .map((item) => {
        const id = String(item?.id || "");
        const name = getItemName(item);
        const stockNo = item?.stock_no_snapshot || "-";
        const description =
          String(item?.description_snapshot || "").trim() || "No description available.";
        const tracking =
          item?.tracking_type_snapshot || item?.tracking_type_text || item?.tracking_type || "-";
        const serial = item?.requires_serial_snapshot ? "Required" : "Not Required";
        const semiExpendable = item?.is_semi_expendable_snapshot ? "Yes" : "No";
        const qty = item?.qty_ordered ?? 1;
        const unit = item?.unit_snapshot ?? "";
        const cost = item?.acquisition_cost ?? null;
        const totalCost = cost !== null ? Number(qty || 0) * Number(cost || 0) : null;
        const unitOptions = normalizeAvailableUnits(item?.available_units);
        const unitIssue = getBlockingUnitIssue(item, unit);
        const unitSelectHtml =
          unitOptions.length > 0
            ? `
              <select class="ti-form-select w-full text-sm" data-gso-air-item-unit="${esc(id)}" ${
                cfg.canEditDraft ? "" : "disabled"
              }>
                ${renderSelectOptions(unitOptions, unit, { placeholder: "Select unit" })}
              </select>
            `
            : `
              <select class="ti-form-select w-full text-sm" data-gso-air-item-unit="${esc(id)}" disabled>
                ${renderSelectOptions(unitOptions, unit, {
                  placeholder: "No configured units",
                  invalidLabel: unit ? `Configured unit missing: ${unit}` : "No configured units",
                })}
              </select>
            `;

        return `
          <div class="rounded border border-defaultborder p-3" data-gso-air-item-row="${esc(id)}">
            <div class="flex items-start justify-between gap-3">
              <div class="min-w-0 w-full">
                <div class="text-xs text-[#8c9097]">Stock No.: <b>${esc(stockNo)}</b></div>
                <div class="text-sm font-semibold mt-1 break-words leading-5">${esc(name)}</div>
                <div class="text-xs text-[#8c9097] mt-1 whitespace-pre-wrap">${esc(description)}</div>

                <div class="grid grid-cols-2 gap-x-4 gap-y-1 text-xs mt-3 text-[#4b5563]">
                  <div>Tracking: <b>${esc(tracking)}</b></div>
                  <div>Serial: <b>${esc(serial)}</b></div>
                  <div>Semi-Expendable: <b>${esc(semiExpendable)}</b></div>
                  <div>Total Cost: <b>${esc(formatMoney(totalCost))}</b></div>
                </div>

                <div class="mt-3 grid grid-cols-1 md:grid-cols-3 gap-2">
                  <div>
                    <label class="ti-form-label text-xs">Qty Ordered</label>
                    <input type="number" min="1" class="ti-form-input w-full text-sm" data-gso-air-item-qty="${esc(
                      id
                    )}" value="${esc(qty)}" ${cfg.canEditDraft ? "" : "disabled"} />
                  </div>

                  <div>
                    <label class="ti-form-label text-xs">Unit</label>
                    ${unitSelectHtml}
                    ${renderUnitHelpText(unitOptions, unitIssue)}
                  </div>

                  <div>
                    <label class="ti-form-label text-xs">Unit Cost</label>
                    <input type="number" min="0" step="0.01" class="ti-form-input w-full text-sm" data-gso-air-item-cost="${esc(
                      id
                    )}" value="${cost !== null ? esc(cost) : ""}" placeholder="e.g. 24500.00" ${
          cfg.canEditDraft ? "" : "disabled"
        } />
                  </div>
                </div>

                <div class="mt-3">
                  <label class="ti-form-label text-xs">Description (PO/AIR Specs)</label>
                  <textarea class="ti-form-input w-full text-sm" rows="3" data-gso-air-item-desc="${esc(
                    id
                  )}" placeholder="Paste PO description/specs here (this prints in AIR)..." ${
          cfg.canEditDraft ? "" : "disabled"
        }>${esc(item?.description_snapshot ?? "")}</textarea>
                  <div class="text-[11px] text-[#8c9097] mt-1">Tip: Keep this aligned with the PO wording/specs (this is what will print).</div>
                </div>
              </div>

              <div class="shrink-0">
                <button type="button" class="ti-btn ti-btn-light" data-gso-air-item-remove="${esc(
                  id
                )}" ${cfg.canEditDraft ? "" : "disabled"}>Remove</button>
              </div>
            </div>
          </div>
        `;
      })
      .join("");
  }

  async function fetchItems() {
    if (!cfg.itemListUrl) return false;

    showError("");

    const response = await fetch(cfg.itemListUrl, {
      headers: { Accept: "application/json" },
    });

    if (!response.ok) {
      const { message } = await parseErrorResponse(response);
      const list = el("gsoAirItemList");
      if (list) list.innerHTML = `<div class="text-xs text-danger">${esc(message)}</div>`;
      showError(message);
      return false;
    }

    const output = await response.json().catch(() => ({}));
    renderItems(output?.data || output?.items || []);
    return true;
  }

  function renderSuggestions(rows) {
    const wrap = el("gsoAirItemSuggestions");
    const list = el("gsoAirItemSuggestList");
    if (!wrap || !list) return;

    suggestionMap.clear();

    if (!Array.isArray(rows) || rows.length === 0) {
      list.innerHTML = '<div class="p-3 text-sm text-[#8c9097]">No matching AIR items found.</div>';
      setSuggestionsVisible(wrap, true);
      return;
    }

    list.innerHTML = rows
      .map((row) => {
        const itemId = String(row?.id || row?.item_id || "");
        const normalizedRow = {
          ...row,
          id: itemId,
          available_units: normalizeAvailableUnits(row?.available_units),
        };

        suggestionMap.set(itemId, normalizedRow);

        const name = getItemName(normalizedRow);
        const reference = normalizedRow?.stock_no || normalizedRow?.asset?.asset_code || "-";
        const description =
          String(normalizedRow?.description || "").trim() || "No description available.";
        const assetName = normalizedRow?.asset?.asset_name || "-";
        const tracking =
          normalizedRow?.tracking_type ||
          normalizedRow?.tracking_type_text ||
          normalizedRow?.tracking_type_snapshot ||
          "-";
        const serial = normalizedRow?.requires_serial ? "Required" : "Not Required";
        const semiExpendable = normalizedRow?.is_semi_expendable ? "Yes" : "No";
        const unitsSummary =
          normalizedRow.available_units.length > 0
            ? normalizedRow.available_units.map((option) => option.label).join(", ")
            : "No configured units. Update the item setup first.";
        const addDisabled = !cfg.canEditDraft || normalizedRow.available_units.length === 0;

        return `
          <button
            type="button"
            class="w-full text-left px-3 py-2 border-b border-defaultborder hover:bg-slate-100 dark:hover:bg-white/5 ${
              addDisabled ? "opacity-60 cursor-not-allowed" : ""
            }"
            data-gso-air-item-add="${esc(itemId)}"
            data-gso-air-item-add-name="${esc(name)}"
            ${addDisabled ? "disabled" : ""}
          >
            <div class="text-sm font-semibold truncate">${esc(reference)} - ${esc(name)}</div>
            <div class="text-xs text-[#8c9097] truncate mt-1">${esc(description)}</div>
            <div class="grid grid-cols-2 gap-x-3 gap-y-1 text-[11px] text-[#8c9097] mt-2">
              <div>Category: <b>${esc(assetName)}</b></div>
              <div>Tracking: <b>${esc(tracking)}</b></div>
              <div>Serial: <b>${esc(serial)}</b></div>
              <div>Semi-Expendable: <b>${esc(semiExpendable)}</b></div>
            </div>
            <div class="text-[11px] text-[#8c9097] mt-2">Units: <b>${esc(unitsSummary)}</b></div>
            ${
              normalizedRow.available_units.length === 0
                ? '<div class="mt-2 text-xs text-danger">This item cannot be added until its unit setup is completed.</div>'
                : ""
            }
          </button>
        `;
      })
      .join("");

    setSuggestionsVisible(wrap, true);
  }

  async function fetchSuggestions(query) {
    const wrap = el("gsoAirItemSuggestions");
    const list = el("gsoAirItemSuggestList");
    if (!wrap || !list) return;

    if (!cfg.itemSuggestUrl) {
      setSuggestionsVisible(wrap, false);
      return;
    }

    const url = new URL(cfg.itemSuggestUrl, window.location.origin);
    url.searchParams.set("q", query);

    const response = await fetch(url.toString(), {
      headers: { Accept: "application/json" },
    });

    if (!response.ok) {
      list.innerHTML = '<div class="p-3 text-sm text-danger">Unable to load suggestions.</div>';
      setSuggestionsVisible(wrap, true);
      return;
    }

    const output = await response.json().catch(() => ({}));
    renderSuggestions(output?.data || output?.items || []);
  }

  async function promptQtyUnit(itemName, availableUnits) {
    const units = normalizeAvailableUnits(availableUnits);
    if (units.length === 0) return null;

    const result = await Swal.fire({
      title: "Add item",
      html: `
        <div style="text-align:left; font-size:13px; margin-bottom:8px;">
          <div><b>${esc(itemName)}</b></div>
          <div style="color:#6b7280; margin-top:2px;">Enter quantity, unit, and acquisition cost for this AIR item.</div>
        </div>
        <div style="display:grid; grid-template-columns: 1fr 1fr; gap:10px; text-align:left;">
          <div>
            <label style="display:block; font-size:12px; color:#6b7280; margin-bottom:4px;">Qty Ordered</label>
            <input id="swalQty" type="number" min="1" value="1" class="swal2-input" style="margin:0; width:100%;" />
          </div>
          <div>
            <label style="display:block; font-size:12px; color:#6b7280; margin-bottom:4px;">Unit</label>
            <select id="swalUnit" class="swal2-select" style="margin:0; width:100%; height:2.625em;">
              ${renderSelectOptions(units, "", { placeholder: "Select unit" })}
            </select>
          </div>
        </div>
        <div style="margin-top:10px; text-align:left;">
          <label style="display:block; font-size:12px; color:#6b7280; margin-bottom:4px;">Acquisition Cost (per unit)</label>
          <input id="swalCost" type="number" min="0" step="0.01" placeholder="e.g. 24500.00" class="swal2-input" style="margin:0; width:100%;" />
        </div>
      `,
      showCancelButton: true,
      confirmButtonText: "Add",
      cancelButtonText: "Cancel",
      focusConfirm: false,
      preConfirm: () => {
        const qty = Number(document.getElementById("swalQty")?.value || 0);
        const unit = document.getElementById("swalUnit")?.value?.trim();
        const cost = Number(document.getElementById("swalCost")?.value || 0);

        if (!qty || qty < 1) {
          Swal.showValidationMessage("Quantity must be at least 1.");
          return false;
        }
        if (!unit) {
          Swal.showValidationMessage("Unit is required.");
          return false;
        }
        if (cost <= 0) {
          Swal.showValidationMessage("Acquisition cost must be greater than zero.");
          return false;
        }

        return { qty, unit, acquisition_cost: cost };
      },
    });

    if (!result.isConfirmed) return null;
    return result.value;
  }

  async function addItem(row) {
    if (!cfg.canEditDraft || !cfg.itemStoreUrl) return;

    const itemId = String(row?.id || row?.item_id || "");
    const units = normalizeAvailableUnits(row?.available_units);
    if (!itemId) return;

    if (units.length === 0) {
      await Swal.fire({
        icon: "warning",
        title: "Unit setup required",
        text: "This item has no configured units. Update the item setup first.",
      });
      return;
    }

    setSuggestionsVisible(el("gsoAirItemSuggestions"), false);
    const list = el("gsoAirItemSuggestList");
    if (list) list.innerHTML = "";
    el("gsoAirItemSearch")?.blur();

    const extra = await promptQtyUnit(getItemName(row), units);
    if (!extra) return;

    Swal.fire({
      title: "Adding...",
      allowOutsideClick: false,
      didOpen: () => Swal.showLoading(),
    });

    const response = await fetch(cfg.itemStoreUrl, {
      method: "POST",
      headers: {
        "X-CSRF-TOKEN": getCsrf(),
        Accept: "application/json",
        "Content-Type": "application/json",
      },
      body: JSON.stringify({
        item_id: itemId,
        qty_ordered: extra.qty,
        unit_snapshot: extra.unit,
        acquisition_cost: extra.acquisition_cost,
      }),
    });

    if (!response.ok) {
      const { message, data } = await parseErrorResponse(response);
      await Swal.fire({
        icon: response.status === 422 ? "warning" : "error",
        title: "Failed",
        html:
          response.status === 422
            ? validationHtml(data?.errors || null) || `<div>${esc(data?.message || message)}</div>`
            : `<div>${esc(data?.message || message)}</div>`,
      });
      return;
    }

    await response.json().catch(() => ({}));
    await Swal.fire({ icon: "success", title: "Added", timer: 700, showConfirmButton: false });
    suggestionMap.clear();
    const search = el("gsoAirItemSearch");
    if (search) search.value = "";
    showError("");
    await fetchItems();
  }

  async function removeItem(airItemId) {
    if (!cfg.canEditDraft || !cfg.itemDeleteUrlTemplate) return;

    const confirmation = await Swal.fire({
      icon: "warning",
      title: "Remove item?",
      text: "This will remove the item from the AIR draft.",
      showCancelButton: true,
      confirmButtonText: "Remove",
      cancelButtonText: "Cancel",
    });

    if (!confirmation.isConfirmed) return;

    const response = await fetch(
      cfg.itemDeleteUrlTemplate.replace("__ID__", encodeURIComponent(airItemId)),
      {
        method: "DELETE",
        headers: {
          "X-CSRF-TOKEN": getCsrf(),
          Accept: "application/json",
        },
      }
    );

    if (!response.ok) {
      const { message } = await parseErrorResponse(response);
      await Swal.fire({ icon: "error", title: "Failed", text: message });
      return;
    }

    await Swal.fire({ icon: "success", title: "Removed", timer: 600, showConfirmButton: false });
    dirtyMap.delete(String(airItemId));
    syncToolbarItemDirtyCount();
    await fetchItems();
  }

  async function bulkSaveItems(options = {}) {
    if (!cfg.canEditDraft) return false;

    if (!cfg.itemBulkUpdateUrl) {
      await Swal.fire({
        icon: "error",
        title: "Missing config",
        text: "Bulk update URL is not configured.",
      });
      return false;
    }

    const blockingIssues = getBlockingItemIssues();
    if (blockingIssues.length > 0) {
      showError("Some AIR item rows still need attention before they can be saved.");
      await Swal.fire({
        icon: "warning",
        title: "Fix item units",
        html: validationHtml({ items: blockingIssues.map((issue) => issue.message) }),
      });
      return false;
    }

    if (dirtyMap.size === 0) return true;

    if (options.showLoading !== false) {
      Swal.fire({
        title: "Saving item changes...",
        allowOutsideClick: false,
        didOpen: () => Swal.showLoading(),
      });
    }

    const response = await fetch(cfg.itemBulkUpdateUrl, {
      method: "PUT",
      headers: {
        "X-CSRF-TOKEN": getCsrf(),
        Accept: "application/json",
        "Content-Type": "application/json",
      },
      body: JSON.stringify({ items: Array.from(dirtyMap.values()) }),
    });

    if (!response.ok) {
      const { message, data } = await parseErrorResponse(response);
      if (response.status === 422) {
        await Swal.fire({
          icon: "warning",
          title: "Failed to save",
          html: validationHtml(data?.errors || null) || `<div>${esc(message)}</div>`,
        });
      } else {
        await Swal.fire({ icon: "error", title: "Failed to save", text: message });
      }
      return false;
    }

    const output = await response.json().catch(() => ({}));
    showError("");
    await fetchItems();

    if (options.showSuccess !== false) {
      await Swal.fire({
        icon: "success",
        title: "Items saved",
        text: output?.message || "Changes saved successfully.",
        timer: 900,
        showConfirmButton: false,
      });
    } else if (options.showLoading !== false) {
      Swal.close();
    }

    return true;
  }

  function bindEvents() {
    const input = el("gsoAirItemSearch");
    const suggestionsWrap = el("gsoAirItemSuggestions");
    const suggestionsClose = el("gsoAirItemSuggestClose");
    const suggestionsList = el("gsoAirItemSuggestList");

    input?.addEventListener("input", () => {
      const query = String(input.value || "").trim();
      clearTimeout(suggestTimer);

      if (query.length < 2) {
        suggestionMap.clear();
        setSuggestionsVisible(suggestionsWrap, false);
        if (suggestionsList) suggestionsList.innerHTML = "";
        return;
      }

      suggestTimer = setTimeout(() => fetchSuggestions(query), 250);
    });

    input?.addEventListener("focus", () => {
      const query = String(input.value || "").trim();
      if (query.length >= 2) fetchSuggestions(query);
    });

    suggestionsClose?.addEventListener("click", (event) => {
      event.preventDefault();
      setSuggestionsVisible(suggestionsWrap, false);
    });

    document.addEventListener("click", (event) => {
      const target = event.target;

      const addButton = target?.closest?.("[data-gso-air-item-add]");
      if (addButton) {
        event.preventDefault();
        const itemId = String(addButton.getAttribute("data-gso-air-item-add") || "");
        addItem(
          suggestionMap.get(itemId) || {
            id: itemId,
            item_name: addButton.getAttribute("data-gso-air-item-add-name") || "Item",
            available_units: [],
          }
        );
        return;
      }

      const removeButton = target?.closest?.("[data-gso-air-item-remove]");
      if (removeButton) {
        event.preventDefault();
        removeItem(removeButton.getAttribute("data-gso-air-item-remove"));
        return;
      }

      if (suggestionsWrap && !suggestionsWrap.contains(target) && target !== input) {
        setSuggestionsVisible(suggestionsWrap, false);
      }
    });

    const syncDirty = (event) => {
      const qtyElement = event.target?.closest?.("[data-gso-air-item-qty]");
      if (qtyElement) {
        syncDirtyForItem(qtyElement.getAttribute("data-gso-air-item-qty"));
        return;
      }

      const unitElement = event.target?.closest?.("[data-gso-air-item-unit]");
      if (unitElement) {
        syncDirtyForItem(unitElement.getAttribute("data-gso-air-item-unit"));
        return;
      }

      const costElement = event.target?.closest?.("[data-gso-air-item-cost]");
      if (costElement) {
        syncDirtyForItem(costElement.getAttribute("data-gso-air-item-cost"));
        return;
      }

      const descElement = event.target?.closest?.("[data-gso-air-item-desc]");
      if (descElement) {
        syncDirtyForItem(descElement.getAttribute("data-gso-air-item-desc"));
      }
    };

    document.addEventListener("input", syncDirty);
    document.addEventListener("change", syncDirty);
  }

  function init() {
    if (!el("gsoAirItemList")) return;

    window.__gsoAirItems = {
      async saveDirtyRows(options = {}) {
        return bulkSaveItems(options);
      },
      hasDirtyChanges() {
        return dirtyMap.size > 0;
      },
      getItemCount() {
        return currentItemMap.size;
      },
      getBlockingIssues() {
        return getBlockingItemIssues();
      },
      async reload() {
        await fetchItems();
      },
    };

    fetchItems().catch((error) => {
      showError(error instanceof Error ? error.message : "Could not load AIR items.");
    });
    bindEvents();
    syncToolbarItemDirtyCount();
  }

  onReady(init);
})();
