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

  function debounce(fn, wait = 250) {
    let timer = null;

    return (...args) => {
      clearTimeout(timer);
      timer = setTimeout(() => fn(...args), wait);
    };
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

  function normalizeTextarea(value) {
    return String(value ?? "").replace(/\r\n/g, "\n").trim();
  }

  function normalizeUnit(value) {
    return normalizeText(value).toLowerCase();
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
          ? "You do not have permission to manage AIR items."
          : response.status === 404
          ? "The AIR item could not be found."
          : response.status === 419
          ? "Your security token expired. Refresh the page and try again."
          : "The request could not be completed."),
    };
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

    if (rows.length === 0) {
      return "";
    }

    return `<ul class="text-left pl-4">${rows.join("")}</ul>`;
  }

  function moneyText(value) {
    if (value === null || value === undefined || value === "") {
      return "-";
    }

    const number = Number(value);

    if (!Number.isFinite(number)) {
      return "-";
    }

    return number.toLocaleString(undefined, {
      minimumFractionDigits: 2,
      maximumFractionDigits: 2,
    });
  }

  function normalizeAvailableUnits(units) {
    if (!Array.isArray(units)) {
      return [];
    }

    const seen = new Set();

    return units
      .map((unit) => ({
        value: normalizeText(unit?.value),
        label: normalizeText(unit?.label) || normalizeText(unit?.value),
        is_base: !!unit?.is_base,
        multiplier: Math.max(1, Number(unit?.multiplier) || 1),
      }))
      .filter((unit) => {
        const key = normalizeUnit(unit.value);
        if (!key || seen.has(key)) {
          return false;
        }

        seen.add(key);
        return true;
      });
  }

  onReady(function () {
    const page = document.getElementById("gso-air-edit-page");
    if (!page) return;

    const config = window.__gsoAirEdit || {};
    const canEditDraft = !!config.canEditDraft;
    const listElement = document.getElementById("gsoAirItemList");
    const searchInput = document.getElementById("gsoAirItemSearch");
    const suggestionsElement = document.getElementById("gsoAirItemSuggestions");
    const countElement = document.getElementById("gsoAirItemCount");
    const countSummaryElement = document.getElementById("gsoAirItemCountSummary");
    const errorElement = document.getElementById("gsoAirItemError");

    if (!listElement) {
      return;
    }

    let items = [];
    let suggestions = [];
    const originals = new Map();
    const dirty = new Set();

    function setCount(value) {
      const count = Math.max(0, Number(value) || 0);

      if (countElement) {
        countElement.textContent = String(count);
      }

      if (countSummaryElement) {
        countSummaryElement.textContent = String(count);
      }
    }

    function showError(message) {
      if (!errorElement) return;

      if (!message) {
        errorElement.classList.add("hidden");
        errorElement.textContent = "";
        return;
      }

      errorElement.textContent = message;
      errorElement.classList.remove("hidden");
    }

    function setSuggestionsVisible(visible) {
      if (!suggestionsElement) {
        return;
      }

      if (visible) {
        suggestionsElement.classList.remove("hidden");
        return;
      }

      suggestionsElement.classList.add("hidden");
    }

    function setItems(nextItems) {
      items = Array.isArray(nextItems)
        ? nextItems.map((item) => ({
            ...item,
            available_units: normalizeAvailableUnits(item?.available_units),
          }))
        : [];

      originals.clear();
      dirty.clear();

      items.forEach((item) => {
        originals.set(String(item.id), snapshot(item));
      });

      setCount(items.length);
      renderList();
    }

    function snapshot(item) {
      return {
        description_snapshot: normalizeTextarea(item?.description_snapshot),
        unit_snapshot: normalizeText(item?.unit_snapshot),
        qty_ordered: Math.max(1, Number(item?.qty_ordered) || 1),
        acquisition_cost:
          item?.acquisition_cost === null ||
          item?.acquisition_cost === undefined ||
          item?.acquisition_cost === ""
            ? null
            : Number(item.acquisition_cost),
      };
    }

    function findItem(id) {
      return items.find((item) => String(item?.id) === String(id)) || null;
    }

    function markDirty(id) {
      const item = findItem(id);
      if (!item) return;

      const before = originals.get(String(id));
      const after = snapshot(item);
      const changed = JSON.stringify(before) !== JSON.stringify(after);

      if (changed) {
        dirty.add(String(id));
      } else {
        dirty.delete(String(id));
      }

      renderList();
    }

    function getIssueForItem(item) {
      const label = normalizeText(item?.item_label) || "AIR Item";
      const availableUnits = normalizeAvailableUnits(item?.available_units);
      const selectedUnit = normalizeText(item?.unit_snapshot);

      if (availableUnits.length === 0) {
        return `${label}: this item has no configured units. Update the item setup first.`;
      }

      if (selectedUnit === "") {
        return `${label}: unit is required.`;
      }

      const unitMatch = availableUnits.some(
        (option) => normalizeUnit(option.value) === normalizeUnit(selectedUnit)
      );

      if (!unitMatch) {
        const allowed = availableUnits.map((option) => option.value).join(", ");
        return `${label}: choose one of the configured units (${allowed}).`;
      }

      const quantity = Number(item?.qty_ordered);
      if (!Number.isFinite(quantity) || quantity < 1) {
        return `${label}: quantity ordered must be at least 1.`;
      }

      const cost =
        item?.acquisition_cost === null ||
        item?.acquisition_cost === undefined ||
        item?.acquisition_cost === ""
          ? null
          : Number(item.acquisition_cost);

      if (cost !== null && (!Number.isFinite(cost) || cost < 0)) {
        return `${label}: acquisition cost must be 0 or greater.`;
      }

      return null;
    }

    function renderList() {
      if (items.length === 0) {
        listElement.innerHTML = `
          <div class="rounded border border-dashed border-defaultborder p-4 text-sm text-[#8c9097] dark:text-white/50">
            No AIR item rows yet. Search the item catalog above to add the first row.
          </div>
        `;
        return;
      }

      listElement.innerHTML = items
        .map((item) => {
          const id = String(item?.id || "");
          const isDirty = dirty.has(id);
          const issue = getIssueForItem(item);
          const availableUnits = normalizeAvailableUnits(item?.available_units);
          const unitOptions = availableUnits
            .map((option) => {
              const selected =
                normalizeUnit(option.value) === normalizeUnit(item?.unit_snapshot)
                  ? "selected"
                  : "";
              return `<option value="${escapeHtml(option.value)}" ${selected}>${escapeHtml(
                option.label || option.value
              )}</option>`;
            })
            .join("");

          const serialText = item?.requires_serial_snapshot ? "Serial Required" : "No Serial";
          const semiText = item?.is_semi_expendable_snapshot ? "Semi-Expendable" : "Regular";

          return `
            <div class="rounded-xl border border-defaultborder p-4 shadow-sm" data-air-item-id="${escapeHtml(id)}">
              <div class="flex flex-wrap items-start justify-between gap-3">
                <div>
                  <p class="mb-1 text-sm font-semibold">${escapeHtml(item?.item_label || "AIR Item")}</p>
                  <p class="mb-0 text-xs text-[#8c9097] dark:text-white/50">
                    ${escapeHtml(item?.tracking_type_text || "Property")}
                    ${item?.stock_no_snapshot ? ` | ${escapeHtml(item.stock_no_snapshot)}` : ""}
                    | ${escapeHtml(serialText)}
                    | ${escapeHtml(semiText)}
                  </p>
                </div>
                <div class="flex flex-wrap items-center gap-2">
                  ${
                    isDirty
                      ? `<span class="rounded-full bg-warning/10 px-3 py-1 text-xs font-medium text-warning">Unsaved</span>`
                      : `<span class="rounded-full bg-success/10 px-3 py-1 text-xs font-medium text-success">Saved</span>`
                  }
                  ${
                    canEditDraft
                      ? `<button type="button" class="ti-btn ti-btn-sm ti-btn-danger" data-action="delete-air-item" data-id="${escapeHtml(id)}">Remove</button>`
                      : ""
                  }
                </div>
              </div>
              <div class="mt-4 grid gap-3 lg:grid-cols-3">
                <div>
                  <label class="mb-1 block text-xs text-[#8c9097]">Quantity Ordered</label>
                  <input
                    type="number"
                    min="1"
                    step="1"
                    class="ti-form-input w-full"
                    data-field="qty_ordered"
                    data-id="${escapeHtml(id)}"
                    value="${escapeHtml(item?.qty_ordered ?? 1)}"
                    ${canEditDraft ? "" : "disabled"}
                  >
                </div>
                <div>
                  <label class="mb-1 block text-xs text-[#8c9097]">Unit</label>
                  <select
                    class="ti-form-select w-full"
                    data-field="unit_snapshot"
                    data-id="${escapeHtml(id)}"
                    ${canEditDraft ? "" : "disabled"}
                  >
                    <option value="">Select unit</option>
                    ${unitOptions}
                  </select>
                </div>
                <div>
                  <label class="mb-1 block text-xs text-[#8c9097]">Acquisition Cost</label>
                  <input
                    type="number"
                    min="0"
                    step="0.01"
                    class="ti-form-input w-full"
                    data-field="acquisition_cost"
                    data-id="${escapeHtml(id)}"
                    value="${escapeHtml(item?.acquisition_cost ?? "")}"
                    ${canEditDraft ? "" : "disabled"}
                  >
                </div>
              </div>
              <div class="mt-3">
                <label class="mb-1 block text-xs text-[#8c9097]">Description Snapshot</label>
                <textarea
                  class="ti-form-input w-full"
                  rows="3"
                  data-field="description_snapshot"
                  data-id="${escapeHtml(id)}"
                  ${canEditDraft ? "" : "disabled"}
                >${escapeHtml(item?.description_snapshot || "")}</textarea>
              </div>
              <div class="mt-3 flex flex-wrap items-center justify-between gap-2 text-xs text-[#8c9097] dark:text-white/50">
                <span>Accepted ${escapeHtml(item?.qty_accepted ?? 0)} of ${escapeHtml(item?.qty_ordered ?? 0)} | Delivered ${escapeHtml(item?.qty_delivered ?? 0)} | Cost ${escapeHtml(moneyText(item?.acquisition_cost))}</span>
                ${issue ? `<span class="text-danger">${escapeHtml(issue)}</span>` : `<span>Ready for draft save</span>`}
              </div>
            </div>
          `;
        })
        .join("");
    }

    function renderSuggestions() {
      if (!suggestionsElement) {
        return;
      }

      if (suggestions.length === 0) {
        suggestionsElement.innerHTML = `
          <div class="p-3 text-sm text-[#8c9097] dark:text-white/50">
            No matching items found.
          </div>
        `;
        return;
      }

      suggestionsElement.innerHTML = suggestions
        .map((item) => {
          const units = normalizeAvailableUnits(item?.available_units)
            .map((unit) => unit.value)
            .join(", ");

          return `
            <button
              type="button"
              class="flex w-full flex-col border-b border-defaultborder px-4 py-3 text-left transition hover:bg-light dark:hover:bg-black/20"
              data-action="select-suggested-air-item"
              data-id="${escapeHtml(item?.id || "")}"
            >
              <span class="font-medium">${escapeHtml(item?.item_name || "Item")}</span>
              <span class="mt-1 text-xs text-[#8c9097] dark:text-white/50">
                ${escapeHtml(item?.stock_no || "No stock number")}
                | ${escapeHtml(item?.tracking_type_text || "Property")}
                | Units: ${escapeHtml(units || "None")}
              </span>
              ${
                item?.description
                  ? `<span class="mt-1 text-xs text-[#8c9097] dark:text-white/50">${escapeHtml(
                      item.description
                    )}</span>`
                  : ""
              }
            </button>
          `;
        })
        .join("");
    }

    async function requestJson(url, options = {}) {
      const response = await fetch(url, {
        headers: {
          Accept: "application/json",
          "X-CSRF-TOKEN": getCsrf(config),
          ...(options.body ? { "Content-Type": "application/json" } : {}),
          ...(options.headers || {}),
        },
        ...options,
      });

      return parseResponse(response);
    }

    async function loadItems() {
      showError("");

      const parsed = await requestJson(config.itemListUrl, { method: "GET" });

      if (!parsed.ok) {
        showError(parsed.message);
        return;
      }

      setItems(parsed.data?.data || []);
    }

    async function saveDirtyRows(options = {}) {
      if (!canEditDraft || dirty.size === 0) {
        return true;
      }

      const payload = [];
      const errors = {};

      dirty.forEach((id) => {
        const item = findItem(id);
        if (!item) return;

        const issue = getIssueForItem(item);
        if (issue) {
          errors[id] = [issue];
          return;
        }

        payload.push({
          id,
          description_snapshot: normalizeTextarea(item.description_snapshot),
          unit_snapshot: normalizeText(item.unit_snapshot),
          qty_ordered: Math.max(1, Number(item.qty_ordered) || 1),
          acquisition_cost:
            item.acquisition_cost === null ||
            item.acquisition_cost === undefined ||
            item.acquisition_cost === ""
              ? null
              : Number(item.acquisition_cost),
        });
      });

      if (Object.keys(errors).length > 0) {
        showError("Some AIR item rows still need attention before they can be saved.");

        if (options.showValidationModal !== false) {
          await Swal.fire({
            icon: "warning",
            title: "Fix AIR item rows",
            html: validationHtml(errors),
          });
        }

        renderList();
        return false;
      }

      const parsed = await requestJson(config.itemBulkUpdateUrl, {
        method: "PUT",
        body: JSON.stringify({ items: payload }),
      });

      if (!parsed.ok) {
        showError(parsed.message);

        if (parsed.status === 422) {
          await Swal.fire({
            icon: "warning",
            title: "Validation failed",
            html: validationHtml(parsed.data?.errors || {}) || escapeHtml(parsed.message),
          });
        } else {
          await Swal.fire({
            icon: "error",
            title: "Save failed",
            text: parsed.message,
          });
        }

        return false;
      }

      setItems(parsed.data?.data || []);

      if (options.showSuccess !== false) {
        await Swal.fire({
          icon: "success",
          title: "AIR items saved",
          timer: 900,
          showConfirmButton: false,
        });
      }

      return true;
    }

    async function promptAddItem(suggestedItem) {
      const itemId = String(suggestedItem?.id || "");
      const units = normalizeAvailableUnits(suggestedItem?.available_units);

      if (!itemId || units.length === 0) {
        await Swal.fire({
          icon: "warning",
          title: "Item unavailable",
          text: "This item cannot be added because it has no configured units yet.",
        });
        return;
      }

      const optionsHtml = units
        .map(
          (unit, index) =>
            `<option value="${escapeHtml(unit.value)}" ${index === 0 ? "selected" : ""}>${escapeHtml(
              unit.label || unit.value
            )}</option>`
        )
        .join("");

      const result = await Swal.fire({
        title: escapeHtml(suggestedItem?.item_name || "Add AIR Item"),
        html: `
          <div class="space-y-3 text-left">
            <div class="rounded bg-light p-3 text-xs text-[#8c9097] dark:bg-black/20 dark:text-white/50">
              ${escapeHtml(suggestedItem?.stock_no || "No stock number")}
              | ${escapeHtml(suggestedItem?.tracking_type_text || "Property")}
            </div>
            <div>
              <label class="mb-1 block text-xs text-[#8c9097]">Quantity Ordered</label>
              <input id="gsoAirItemAddQty" type="number" min="1" step="1" class="ti-form-input w-full" value="1">
            </div>
            <div>
              <label class="mb-1 block text-xs text-[#8c9097]">Unit</label>
              <select id="gsoAirItemAddUnit" class="ti-form-select w-full">${optionsHtml}</select>
            </div>
            <div>
              <label class="mb-1 block text-xs text-[#8c9097]">Acquisition Cost</label>
              <input id="gsoAirItemAddCost" type="number" min="0.01" step="0.01" class="ti-form-input w-full" value="">
            </div>
            <div>
              <label class="mb-1 block text-xs text-[#8c9097]">Description Override</label>
              <textarea id="gsoAirItemAddDescription" class="ti-form-input w-full" rows="3">${escapeHtml(
                suggestedItem?.description || ""
              )}</textarea>
            </div>
          </div>
        `,
        focusConfirm: false,
        showCancelButton: true,
        confirmButtonText: "Add Item",
        cancelButtonText: "Cancel",
        preConfirm: () => {
          const qty = Number(document.getElementById("gsoAirItemAddQty")?.value || 0);
          const unit = normalizeText(document.getElementById("gsoAirItemAddUnit")?.value || "");
          const cost = Number(document.getElementById("gsoAirItemAddCost")?.value || 0);
          const description = normalizeTextarea(
            document.getElementById("gsoAirItemAddDescription")?.value || ""
          );

          if (!Number.isFinite(qty) || qty < 1) {
            Swal.showValidationMessage("Quantity ordered must be at least 1.");
            return false;
          }

          if (!unit) {
            Swal.showValidationMessage("Choose a configured unit.");
            return false;
          }

          if (!Number.isFinite(cost) || cost <= 0) {
            Swal.showValidationMessage("Acquisition cost must be greater than 0.");
            return false;
          }

          return {
            item_id: itemId,
            qty_ordered: qty,
            unit_snapshot: unit,
            acquisition_cost: cost,
            description_snapshot: description || null,
          };
        },
      });

      if (!result.isConfirmed || !result.value) {
        return;
      }

      const parsed = await requestJson(config.itemStoreUrl, {
        method: "POST",
        body: JSON.stringify(result.value),
      });

      if (!parsed.ok) {
        await Swal.fire({
          icon: parsed.status === 422 ? "warning" : "error",
          title: parsed.status === 422 ? "Validation failed" : "Add failed",
          html: validationHtml(parsed.data?.errors || {}) || escapeHtml(parsed.message),
        });
        return;
      }

      setItems(parsed.data?.data || []);
      showError("");
      if (searchInput) {
        searchInput.value = "";
      }
      suggestions = [];
      renderSuggestions();
      setSuggestionsVisible(false);

      await Swal.fire({
        icon: "success",
        title: "AIR item added",
        timer: 900,
        showConfirmButton: false,
      });
    }

    const fetchSuggestions = debounce(async () => {
      if (!searchInput || !suggestionsElement) {
        return;
      }

      const query = normalizeText(searchInput.value);

      if (query.length < 2) {
        suggestions = [];
        renderSuggestions();
        setSuggestionsVisible(false);
        return;
      }

      const url = `${config.itemSuggestUrl}?q=${encodeURIComponent(query)}`;
      const parsed = await requestJson(url, { method: "GET" });

      if (!parsed.ok) {
        suggestions = [];
        renderSuggestions();
        setSuggestionsVisible(false);
        return;
      }

      suggestions = Array.isArray(parsed.data?.data) ? parsed.data.data : [];
      renderSuggestions();
      setSuggestionsVisible(true);
    }, 250);

    searchInput?.addEventListener("input", fetchSuggestions);
    searchInput?.addEventListener("focus", fetchSuggestions);

    document.addEventListener("click", async (event) => {
      if (
        searchInput &&
        suggestionsElement &&
        !searchInput.contains(event.target) &&
        !suggestionsElement.contains(event.target)
      ) {
        setSuggestionsVisible(false);
      }

      const suggestionButton = event.target.closest('[data-action="select-suggested-air-item"]');
      if (suggestionButton) {
        const suggestedItem = suggestions.find(
          (item) => String(item?.id || "") === String(suggestionButton.dataset.id || "")
        );

        if (suggestedItem) {
          await promptAddItem(suggestedItem);
        }

        return;
      }

      const deleteButton = event.target.closest('[data-action="delete-air-item"]');
      if (!deleteButton) {
        return;
      }

      const airItemId = String(deleteButton.dataset.id || "");
      if (!airItemId) {
        return;
      }

      const confirmation = await Swal.fire({
        icon: "warning",
        title: "Remove AIR item?",
        text: "This row will be removed from the draft immediately.",
        showCancelButton: true,
        confirmButtonText: "Remove",
        cancelButtonText: "Cancel",
      });

      if (!confirmation.isConfirmed) {
        return;
      }

      const parsed = await requestJson(
        config.itemDeleteUrlTemplate.replace("__ID__", encodeURIComponent(airItemId)),
        { method: "DELETE" }
      );

      if (!parsed.ok) {
        await Swal.fire({
          icon: "error",
          title: "Remove failed",
          text: parsed.message,
        });
        return;
      }

      setItems(parsed.data?.data || []);
      showError("");
      await Swal.fire({
        icon: "success",
        title: "AIR item removed",
        timer: 900,
        showConfirmButton: false,
      });
    });

    listElement.addEventListener("input", (event) => {
      const field = event.target?.dataset?.field;
      const id = event.target?.dataset?.id;

      if (!field || !id) {
        return;
      }

      const item = findItem(id);
      if (!item) {
        return;
      }

      if (field === "qty_ordered") {
        item.qty_ordered = event.target.value;
      } else if (field === "acquisition_cost") {
        item.acquisition_cost = event.target.value;
      } else if (field === "description_snapshot") {
        item.description_snapshot = event.target.value;
      }

      markDirty(id);
    });

    listElement.addEventListener("change", (event) => {
      const field = event.target?.dataset?.field;
      const id = event.target?.dataset?.id;

      if (!field || !id) {
        return;
      }

      const item = findItem(id);
      if (!item) {
        return;
      }

      if (field === "unit_snapshot") {
        item.unit_snapshot = event.target.value;
      } else if (field === "qty_ordered") {
        item.qty_ordered = event.target.value;
      } else if (field === "acquisition_cost") {
        item.acquisition_cost = event.target.value;
      }

      markDirty(id);
    });

    window.__gsoAirItems = {
      async saveDirtyRows(options = {}) {
        return saveDirtyRows(options);
      },
      hasDirtyChanges() {
        return dirty.size > 0;
      },
      getItemCount() {
        return items.length;
      },
      getBlockingIssues() {
        return items
          .map((item) => getIssueForItem(item))
          .filter(Boolean);
      },
      async reload() {
        await loadItems();
      },
    };

    loadItems().catch((error) => {
      showError(error instanceof Error ? error.message : "Could not load AIR items.");
    });
  });
})();
