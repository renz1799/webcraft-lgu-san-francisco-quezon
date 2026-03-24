import Swal from "sweetalert2";
import "sweetalert2/dist/sweetalert2.min.css";

(function () {
  "use strict";

  const config = window.__ris || {};
  let currentItemCount = 0;

  function getCsrf() {
    return document.querySelector('meta[name="csrf-token"]')?.content || config.csrf || "";
  }

  function esc(value) {
    return String(value ?? "")
      .replace(/&/g, "&amp;")
      .replace(/</g, "&lt;")
      .replace(/>/g, "&gt;")
      .replace(/"/g, "&quot;")
      .replace(/'/g, "&#39;");
  }

  function debounce(fn, wait = 350) {
    let timeout = null;
    return (...args) => {
      clearTimeout(timeout);
      timeout = setTimeout(() => fn.apply(null, args), wait);
    };
  }

  async function parseErrorResponse(response) {
    const contentType = response.headers.get("content-type") || "";
    const data = contentType.includes("application/json")
      ? await response.json().catch(() => null)
      : null;

    return {
      status: response.status,
      message:
        data?.message ||
        (response.status === 401
          ? "Session expired. Please log in again."
          : response.status === 403
          ? "You do not have permission."
          : `Request failed (HTTP ${response.status}).`),
      data,
    };
  }

  function onReady(fn) {
    if (document.readyState === "loading") {
      document.addEventListener("DOMContentLoaded", fn);
      return;
    }

    fn();
  }

  function setSuggestionsVisible(element, visible) {
    if (!element) return;

    if (visible) {
      element.classList.remove("hidden");
      return;
    }

    element.classList.add("hidden");
  }

  function renderSuggestMessage(listElement, wrapper, message, tone = "muted") {
    if (!listElement || !wrapper) return;

    const toneClass = tone === "danger" ? "text-danger" : "text-[#8c9097]";
    listElement.innerHTML = `<div class="p-3 text-sm ${toneClass}">${esc(message)}</div>`;
    setSuggestionsVisible(wrapper, true);
  }

  function getFundSourceSelect() {
    return document.querySelector('[name="fund_source_id"]');
  }

  function hasSavedFundSource() {
    return !!String(getFundSourceSelect()?.value || "").trim();
  }

  function isFundSourceDirty() {
    return !!window.__risEditPage?.isFieldDirty?.("fund_source_id");
  }

  function getItemActionBlockReason(canModify) {
    if (!canModify) {
      return "Items are locked while this RIS is not in draft.";
    }

    if (!hasSavedFundSource()) {
      return "Select and save a Fund Source first before managing RIS items.";
    }

    if (isFundSourceDirty()) {
      return "Save the Fund Source change first before managing RIS items.";
    }

    return "";
  }

  function syncPanelGuidance(helpElement, emptyElement, search, canModify) {
    const blockReason = getItemActionBlockReason(canModify);

    if (search) {
      search.placeholder = blockReason ? "Save Fund Source first..." : "Search stock no./item...";
    }

    if (helpElement) {
      helpElement.textContent =
        blockReason ||
        "Requested quantity and available stock are shown in base unit. Only consumables from the saved Fund Source are suggested.";
    }

    if (emptyElement && currentItemCount === 0) {
      emptyElement.textContent =
        blockReason ||
        "No items yet. Add consumable items that match the saved Fund Source.";
    }
  }

  function showEmptyState(listWrapper, emptyElement, countElement, message) {
    currentItemCount = 0;

    if (countElement) countElement.textContent = "0 item(s)";
    if (emptyElement) {
      emptyElement.textContent = message;
      emptyElement.classList.remove("hidden");
    }

    if (listWrapper) {
      listWrapper.classList.add("hidden");
      listWrapper.innerHTML = "";
    }
  }

  function renderList(items, listWrapper, emptyElement, countElement, canModify, helpElement, search) {
    const rows = Array.isArray(items) ? items : [];
    currentItemCount = rows.length;

    if (countElement) countElement.textContent = `${rows.length} item(s)`;

    syncPanelGuidance(helpElement, emptyElement, search, canModify);

    if (!rows.length) {
      showEmptyState(
        listWrapper,
        emptyElement,
        countElement,
        getItemActionBlockReason(canModify) ||
          "No items yet. Add consumable items that match the saved Fund Source."
      );
      return;
    }

    if (emptyElement) emptyElement.classList.add("hidden");
    if (!listWrapper) return;

    listWrapper.classList.remove("hidden");
    listWrapper.innerHTML = rows
      .map((item) => {
        const stockNo = String(item.stock_no || "").trim();
        const description = String(item.description || "").trim();
        const unit = esc(item.unit || item.base_unit || "-");
        const available = Number(item.on_hand_base ?? 0);
        const availableClass = available <= 0 ? "text-red-500" : "text-green-600";

        return `
          <div class="rounded border border-defaultborder p-3" data-row data-id="${esc(item.id)}">
            <div class="flex items-start justify-between gap-3">
              <div class="min-w-0">
                <div class="text-xs text-[#8c9097]">
                  Stock No.: <b>${stockNo ? esc(stockNo) : "-"}</b>
                </div>
                <div class="text-sm font-semibold mt-1 break-words leading-5">${esc(item.item_name || "-")}</div>
                <div class="text-xs text-[#8c9097] mt-1 whitespace-pre-wrap">${description ? esc(description) : "No description available."}</div>
                <div class="grid grid-cols-2 gap-x-4 gap-y-1 text-xs mt-3 text-[#4b5563]">
                  <div>Qty Requested: <b>${esc(item.qty_requested ?? 0)}</b></div>
                  <div>Available: <b class="${availableClass}">${available}</b></div>
                  <div>Unit: <b>${unit}</b></div>
                  <div>Qty Issued: <b>${esc(item.qty_issued ?? 0)}</b></div>
                </div>
              </div>
              ${
                canModify
                  ? `
                <div class="shrink-0">
                  <button type="button" class="ti-btn ti-btn-light" data-remove>Remove</button>
                </div>
              `
                  : ""
              }
            </div>
          </div>
        `;
      })
      .join("");
  }

  function renderSuggestions(items, listElement, wrapper) {
    if (!listElement || !wrapper) return;

    const rows = Array.isArray(items) ? items : [];
    if (!rows.length) {
      renderSuggestMessage(listElement, wrapper, "No matching RIS consumables found.");
      return;
    }

    listElement.innerHTML = rows
      .map((item) => {
        const stockNo = String(item.stock_no || "").trim();
        const description = String(item.description || "").trim();
        const baseUnit = esc(item.base_unit || "-");
        const onHand = Number(item.on_hand_base ?? 0);
        const fundLabel = esc(item.fund_label || "-");
        const isAllowed = !!item.is_allowed;
        const disabledReason = esc(item.disabled_reason || "");
        const availabilityClass = onHand <= 0 ? "text-red-500" : "text-green-600";
        const statusText =
          onHand <= 0
            ? "Out of stock"
            : isAllowed
            ? "Ready to add"
            : disabledReason || "Different fund source";

        return `
          <button
            type="button"
            class="w-full text-left px-3 py-2 border-b border-defaultborder hover:bg-slate-100 dark:hover:bg-white/5 ${
              !isAllowed || onHand <= 0 ? "opacity-70" : ""
            }"
            data-suggest
            data-item-id="${esc(item.item_id)}"
            data-name="${esc(item.item_name || "")}"
            data-base-unit="${baseUnit}"
            data-on-hand="${onHand}"
            data-fund-source-id="${esc(item.fund_source_id || "")}"
            data-is-allowed="${isAllowed ? "1" : "0"}"
            data-disabled-reason="${disabledReason}"
          >
            <div class="text-sm font-semibold truncate">${stockNo ? `${esc(stockNo)} - ` : ""}${esc(item.item_name || "-")}</div>
            <div class="text-xs text-[#8c9097] truncate mt-1">${description ? esc(description) : "No description available."}</div>
            <div class="grid grid-cols-2 gap-x-3 gap-y-1 text-[11px] text-[#8c9097] mt-2">
              <div>Available: <b class="${availabilityClass}">${onHand} ${baseUnit}</b></div>
              <div>Unit: <b>${baseUnit}</b></div>
              <div>Fund: <b>${fundLabel}</b></div>
              <div>Status: <b>${statusText}</b></div>
            </div>
          </button>
        `;
      })
      .join("");

    setSuggestionsVisible(wrapper, true);
  }

  onReady(function () {
    const canModify = String(config.status || "draft").toLowerCase() === "draft";
    const listWrapper = document.getElementById("risItemsList");
    const emptyElement = document.getElementById("risItemsEmpty");
    const countElement = document.getElementById("risItemsCount");
    const helpElement = document.getElementById("risItemsHelp");
    const search = document.getElementById("risItemSearch");
    const suggestionsWrapper = document.getElementById("risItemSuggestions");
    const suggestionsList = document.getElementById("risItemSuggestList");
    const suggestionsClose = document.getElementById("risItemSuggestClose");
    const fundSourceSelect = getFundSourceSelect();

    if (!listWrapper || !emptyElement || !countElement || !config.itemListUrl) {
      return;
    }

    syncPanelGuidance(helpElement, emptyElement, search, canModify);

    async function fetchList() {
      const response = await fetch(config.itemListUrl, {
        method: "GET",
        headers: {
          Accept: "application/json",
          "X-CSRF-TOKEN": getCsrf(),
        },
      });

      if (!response.ok) {
        throw await parseErrorResponse(response);
      }

      const data = await response.json().catch(() => ({}));
      return data?.items || [];
    }

    async function refreshList() {
      const items = await fetchList();
      renderList(items, listWrapper, emptyElement, countElement, canModify, helpElement, search);
    }

    const pageApi = window.__risEditPage || {};
    Object.assign(pageApi, {
      getItemCount() {
        return currentItemCount;
      },
      refreshItemList: refreshList,
    });
    window.__risEditPage = pageApi;

    refreshList().catch((error) => {
      if (Number(error?.status || 0) === 422) {
        syncPanelGuidance(helpElement, emptyElement, search, canModify);
        showEmptyState(
          listWrapper,
          emptyElement,
          countElement,
          error?.message || "Select and save a Fund Source first before managing RIS items."
        );
        return;
      }

      listWrapper.classList.remove("hidden");
      listWrapper.innerHTML = `<div class="text-xs text-danger">${esc(error?.message || "Unable to load RIS items.")}</div>`;
    });

    if (!search || !suggestionsWrapper || !suggestionsList || !canModify) {
      return;
    }

    function getBlockReason() {
      return getItemActionBlockReason(canModify);
    }

    async function fetchSuggestions(query) {
      const url = new URL(config.itemSuggestUrl, window.location.origin);
      url.searchParams.set("q", query);

      const response = await fetch(url.toString(), {
        method: "GET",
        headers: {
          Accept: "application/json",
          "X-CSRF-TOKEN": getCsrf(),
        },
      });

      if (!response.ok) {
        throw await parseErrorResponse(response);
      }

      const data = await response.json().catch(() => ({}));
      return data?.items || [];
    }

    const doSearch = debounce(async () => {
      syncPanelGuidance(helpElement, emptyElement, search, canModify);

      const query = String(search.value || "").trim();
      if (!query) {
        suggestionsList.innerHTML = "";
        setSuggestionsVisible(suggestionsWrapper, false);
        return;
      }

      const blockReason = getBlockReason();
      if (blockReason) {
        renderSuggestMessage(suggestionsList, suggestionsWrapper, blockReason);
        return;
      }

      if (query.length < 2) {
        renderSuggestMessage(suggestionsList, suggestionsWrapper, "Type at least 2 characters.");
        return;
      }

      try {
        const items = await fetchSuggestions(query);
        renderSuggestions(items, suggestionsList, suggestionsWrapper);
      } catch (error) {
        renderSuggestMessage(
          suggestionsList,
          suggestionsWrapper,
          error?.message || "Unable to load item suggestions.",
          Number(error?.status || 0) === 422 ? "muted" : "danger"
        );
      }
    }, 250);

    search.addEventListener("input", doSearch);
    search.addEventListener("focus", doSearch);

    fundSourceSelect?.addEventListener("input", () => {
      syncPanelGuidance(helpElement, emptyElement, search, canModify);
      suggestionsList.innerHTML = "";
      setSuggestionsVisible(suggestionsWrapper, false);
    });

    fundSourceSelect?.addEventListener("change", () => {
      syncPanelGuidance(helpElement, emptyElement, search, canModify);
      suggestionsList.innerHTML = "";
      setSuggestionsVisible(suggestionsWrapper, false);
    });

    suggestionsClose?.addEventListener("click", (event) => {
      event.preventDefault();
      setSuggestionsVisible(suggestionsWrapper, false);
    });

    document.addEventListener("pointerdown", (event) => {
      const inside = suggestionsWrapper.contains(event.target) || search.contains(event.target);
      if (!inside) {
        setSuggestionsVisible(suggestionsWrapper, false);
      }
    });

    suggestionsList.addEventListener("click", async (event) => {
      const button = event.target.closest("[data-suggest]");
      if (!button) return;

      const blockReason = getBlockReason();
      if (blockReason) {
        await Swal.fire({
          icon: "info",
          title: "Save Fund Source first",
          text: blockReason,
        });
        return;
      }

      const isAllowed = String(button.dataset.isAllowed || "0") === "1";
      const disabledReason = button.dataset.disabledReason || "";
      const onHand = Number(button.dataset.onHand ?? 0);

      if (!isAllowed) {
        await Swal.fire({
          icon: "info",
          title: "Not allowed",
          text: disabledReason || "This item belongs to a different fund source than this RIS.",
        });
        return;
      }

      if (onHand <= 0) {
        await Swal.fire({
          icon: "info",
          title: "Out of stock",
          text: "This item has no available stock for this RIS.",
        });
        return;
      }

      const itemId = button.dataset.itemId || "";
      const fundSourceId = button.dataset.fundSourceId || "";
      const name = button.dataset.name || "Item";
      const baseUnit = button.dataset.baseUnit || "";

      const result = await Swal.fire({
        title: "Add Item",
        html: `
          <div style="text-align:left">
            <div><b>${esc(name)}</b></div>
            <div class="text-xs" style="color:#8c9097;margin-top:4px;">
              Available: <b>${onHand} ${esc(baseUnit)}</b>
            </div>
          </div>
        `,
        input: "number",
        inputLabel: "Qty Requested (base)",
        inputValue: 1,
        inputAttributes: { min: 1, max: onHand, step: 1 },
        showCancelButton: true,
        confirmButtonText: "Add",
        cancelButtonText: "Cancel",
        preConfirm: (value) => {
          const quantity = Number(value);
          if (!Number.isFinite(quantity) || quantity < 1 || quantity > onHand) {
            Swal.showValidationMessage(`Quantity must be between 1 and ${onHand}.`);
            return false;
          }
          return Math.floor(quantity);
        },
      });

      if (result.value === undefined) return;

      try {
        const formData = new FormData();
        formData.append("item_id", itemId);
        formData.append("qty_requested", String(result.value));
        if (fundSourceId) {
          formData.append("fund_source_id", fundSourceId);
        }

        const response = await fetch(config.itemAddUrl, {
          method: "POST",
          headers: {
            Accept: "application/json",
            "X-CSRF-TOKEN": getCsrf(),
          },
          body: formData,
        });

        if (!response.ok) {
          throw await parseErrorResponse(response);
        }

        search.value = "";
        setSuggestionsVisible(suggestionsWrapper, false);
        await refreshList();

        await Swal.fire({
          icon: "success",
          title: "Added",
          text: "Item added to RIS.",
          timer: 1100,
          showConfirmButton: false,
        });
      } catch (error) {
        await Swal.fire("Error", error?.message || "Failed to add item.", "error");
      }
    });

    listWrapper.addEventListener("click", async (event) => {
      const button = event.target.closest("[data-remove]");
      if (!button) return;

      const row = button.closest("[data-row]");
      const id = row?.dataset?.id || "";
      if (!id) return;

      const result = await Swal.fire({
        icon: "warning",
        title: "Remove item?",
        text: "This will permanently remove this item from the RIS draft.",
        showCancelButton: true,
        confirmButtonText: "Remove",
      });

      if (!result.isConfirmed) return;

      try {
        const url = String(config.itemRemoveUrlTemplate || "").replace("__ID__", id);

        const response = await fetch(url, {
          method: "DELETE",
          headers: {
            Accept: "application/json",
            "X-CSRF-TOKEN": getCsrf(),
          },
        });

        if (!response.ok) {
          throw await parseErrorResponse(response);
        }

        await refreshList();

        await Swal.fire({
          icon: "success",
          title: "Removed",
          text: "Item removed from RIS.",
          timer: 1100,
          showConfirmButton: false,
        });
      } catch (error) {
        await Swal.fire("Error", error?.message || "Failed to remove item.", "error");
      }
    });
  });
})();
