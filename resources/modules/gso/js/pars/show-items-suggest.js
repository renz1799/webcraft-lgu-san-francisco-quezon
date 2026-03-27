import Swal from "sweetalert2";
import "sweetalert2/dist/sweetalert2.min.css";

(function () {
  "use strict";

  function onReady(fn) {
    if (document.readyState === "loading") {
      document.addEventListener("DOMContentLoaded", fn);
    } else {
      fn();
    }
  }

  function esc(value) {
    return String(value ?? "")
      .replace(/&/g, "&amp;")
      .replace(/</g, "&lt;")
      .replace(/>/g, "&gt;")
      .replace(/\"/g, "&quot;")
      .replace(/'/g, "&#39;");
  }

  function debounce(fn, wait = 250) {
    let timer = null;
    return (...args) => {
      clearTimeout(timer);
      timer = setTimeout(() => fn.apply(null, args), wait);
    };
  }

  function getCsrf(cfg) {
    return document.querySelector('meta[name="csrf-token"]')?.content || cfg?.csrf || "";
  }

  function formatMoney(value) {
    if (value === null || value === undefined || value === "") return "-";
    const num = Number(value);
    if (!Number.isFinite(num)) return "-";
    return num.toLocaleString(undefined, {
      minimumFractionDigits: 2,
      maximumFractionDigits: 2,
    });
  }

  async function parseErrorResponse(res) {
    const contentType = (res.headers.get("content-type") || "").toLowerCase();
    let data = null;

    if (contentType.includes("application/json")) {
      data = await res.json().catch(() => null);
    }

    return {
      status: res.status,
      message:
        data?.message ||
        (res.status === 401
          ? "Session expired. Please log in again."
          : res.status === 403
            ? "You do not have permission to modify this PAR."
            : `Request failed (HTTP ${res.status}).`),
      data,
    };
  }

  function renderSuggestionRow(row) {
    const propertyNumber = esc(row.property_number || "-");
    const itemName = esc(row.item_name || "-");
    const description = esc(String(row.description || "").trim() || "No description available.");
    const quantity = esc(row.quantity || 1);
    const unit = esc(row.unit || "-");
    const unitCost = esc(formatMoney(row.unit_cost));
    const totalCost = esc(formatMoney(row.total_cost));
    const inventoryItemId = esc(row.inventory_item_id || "");
    const fundSourceLabel = esc(row.fund_source_label || "-");

    return `
      <button
        type="button"
        class="w-full text-left px-3 py-2 border-b border-defaultborder hover:bg-slate-100 dark:hover:bg-white/5"
        data-action="par-add-item"
        data-inv-id="${inventoryItemId}"
      >
        <div class="text-sm font-semibold truncate">${propertyNumber} - ${itemName}</div>
        <div class="text-xs text-[#8c9097] truncate mt-1">${description}</div>
        <div class="grid grid-cols-2 gap-x-3 gap-y-1 text-[11px] text-[#8c9097] mt-2">
          <div>Qty: <b>${quantity}</b> ${unit}</div>
          <div>Unit Cost: <b>${unitCost}</b></div>
          <div>Total Cost: <b>${totalCost}</b></div>
          <div>Fund: <b>${fundSourceLabel}</b></div>
        </div>
      </button>
    `;
  }

  function renderParItemCardHtml(item) {
    const deleteUrl = esc(item.delete_url || "");
    const parItemId = esc(item.id || "");
    const propertyNumber = esc(item.property_number || "-");
    const itemName = esc(item.item_name || "-");
    const description = esc(String(item.description || "").trim() || "No description available.");
    const unit = esc(item.unit || "-");
    const quantity = esc(item.quantity || 1);
    const unitCost = esc(formatMoney(item.unit_cost ?? item.amount));
    const totalCost = esc(formatMoney(item.total_cost ?? item.amount));

    return `
      <div class="rounded border border-defaultborder p-3" data-par-item-row="${parItemId}">
        <div class="flex items-start justify-between gap-3">
          <div class="min-w-0">
            <div class="text-xs text-[#8c9097]">
              Property No.: <b>${propertyNumber}</b>
            </div>
            <div class="text-sm font-semibold mt-1 break-words leading-5">${itemName}</div>
            <div class="text-xs text-[#8c9097] mt-1 whitespace-pre-wrap">${description}</div>
            <div class="grid grid-cols-2 gap-x-4 gap-y-1 text-xs mt-3 text-[#4b5563]">
              <div>Quantity: <b>${quantity}</b></div>
              <div>Unit: <b>${unit}</b></div>
              <div>Unit Cost: <b>${unitCost}</b></div>
              <div>Total Cost: <b>${totalCost}</b></div>
            </div>
          </div>

          <div class="shrink-0">
            <button
              type="button"
              class="ti-btn ti-btn-light"
              data-action="par-item-remove"
              data-par-item-id="${parItemId}"
              data-delete-url="${deleteUrl}"
            >
              Remove
            </button>
          </div>
        </div>
      </div>
    `;
  }

  onReady(function () {
    const cfg = window.__parShow || {};
    if (cfg.canModify === false) return;

    const search = document.getElementById("par-item-search");
    const dropdown = document.getElementById("par-item-suggest");
    const list = document.getElementById("par-suggest-list");
    const closeBtn = document.getElementById("par-suggest-close");
    const help = document.getElementById("parItemSearchHelp");
    const countEl = document.getElementById("par-items-count");
    const emptyEl = document.getElementById("par-items-empty");
    const itemsList = document.getElementById("parItemsList");
    const fundSourceSelect = document.getElementById("parFundSourceSelect");

    const suggestUrl = document.getElementById("par-suggest-endpoint")?.value;
    const addUrl = document.getElementById("par-add-item-endpoint")?.value;

    if (!search || !dropdown || !list || !itemsList || !suggestUrl || !addUrl) return;

    let isOpen = false;

    function openDropdown() {
      dropdown.classList.remove("hidden");
      isOpen = true;
    }

    function closeDropdown() {
      dropdown.classList.add("hidden");
      isOpen = false;
    }

    function getItemCount() {
      return document.querySelectorAll("#parItemsList [data-par-item-row]").length;
    }

    function setCountText(count) {
      if (!countEl) return;
      countEl.textContent = `${Math.max(0, Number(count || 0))} item(s)`;
    }

    function getBlockedReason() {
      const fundSourceId = String(fundSourceSelect?.value || "").trim();
      const page = window.__parEditPage || {};
      const isFundSourceDirty = !!(page && typeof page.isFieldDirty === "function" && page.isFieldDirty("fund_source_id"));

      if (!fundSourceId) {
        return "Save a Fund Source first before managing PAR items.";
      }

      if (isFundSourceDirty) {
        return "Save the Fund Source change first before managing PAR items.";
      }

      return "";
    }

    function syncItemGuidance() {
      const blockedReason = getBlockedReason();
      const itemCount = getItemCount();

      if (help) {
        help.textContent = blockedReason || "Type at least 2 characters. Only items from the GSO pool under the same Fund Cluster are suggested.";
      }

      if (search) {
        search.placeholder = blockedReason || "Search property number / item...";
      }

      if (emptyEl && itemCount === 0) {
        emptyEl.textContent = blockedReason || "No items yet. Add property items from the GSO pool under the selected Fund Cluster.";
      }

      setCountText(itemCount);
    }

    function ensureItemActionsAllowed() {
      const blockedReason = getBlockedReason();
      if (blockedReason) {
        throw new Error(blockedReason);
      }
    }

    function showNoMatches() {
      list.innerHTML = '<div class="p-3 text-sm text-[#8c9097]">No matching property items found in the GSO pool for the selected Fund Cluster.</div>';
      openDropdown();
    }

    function appendItemRow(item) {
      const rowHtml = renderParItemCardHtml(item);
      itemsList.insertAdjacentHTML("beforeend", rowHtml);

      itemsList.classList.remove("hidden");
      if (emptyEl) emptyEl.classList.add("hidden");

      syncItemGuidance();
    }

    closeBtn?.addEventListener("click", (e) => {
      e.preventDefault();
      closeDropdown();
    });

    document.addEventListener("pointerdown", (e) => {
      if (!isOpen) return;
      const inside = dropdown.contains(e.target) || search.contains(e.target);
      if (!inside) closeDropdown();
    });

    async function loadSuggestions(query) {
      const trimmed = String(query || "").trim();
      syncItemGuidance();

      try {
        ensureItemActionsAllowed();
      } catch (err) {
        list.innerHTML = `<div class="p-3 text-sm text-[#8c9097]">${esc(err?.message || "Save the Fund Source first before searching PAR items.")}</div>`;
        openDropdown();
        return;
      }

      if (trimmed.length < 2) {
        list.innerHTML = '<div class="p-3 text-sm text-[#8c9097]">Type at least 2 characters.</div>';
        openDropdown();
        return;
      }

      list.innerHTML = '<div class="p-3 text-sm text-[#8c9097]">Searching...</div>';
      openDropdown();

      const url = new URL(suggestUrl, window.location.origin);
      url.searchParams.set("q", trimmed);

      const res = await fetch(url.toString(), {
        method: "GET",
        headers: {
          "X-Requested-With": "XMLHttpRequest",
          Accept: "application/json",
        },
      });

      if (!res.ok) {
        throw await parseErrorResponse(res);
      }

      const data = await res.json().catch(() => null);
      if (!data?.ok) {
        throw new Error(data?.message || "Unable to fetch suggestions.");
      }

      const rows = Array.isArray(data?.data) ? data.data : [];
      if (rows.length === 0) {
        showNoMatches();
        return;
      }

      list.innerHTML = rows.map(renderSuggestionRow).join("");
    }

    const debouncedSearch = debounce(async () => {
      try {
        await loadSuggestions(search.value);
      } catch (err) {
        const message = err?.message || "Unable to load suggestions.";
        list.innerHTML = `<div class="p-3 text-sm text-danger">${esc(message)}</div>`;
        openDropdown();
      }
    }, 250);

    search.addEventListener("input", debouncedSearch);
    search.addEventListener("focus", debouncedSearch);

    fundSourceSelect?.addEventListener("change", () => {
      syncItemGuidance();
      if (!String(search.value || "").trim()) {
        closeDropdown();
      }
    });

    document.addEventListener("par:header-saved", () => {
      syncItemGuidance();
      if (String(search.value || "").trim().length >= 2) {
        debouncedSearch();
      }
    });

    dropdown.addEventListener("click", async (e) => {
      const button = e.target.closest('button[data-action="par-add-item"]');
      if (!button) return;

      const inventoryItemId = button.getAttribute("data-inv-id") || "";
      if (!inventoryItemId) return;

      button.disabled = true;

      try {
        ensureItemActionsAllowed();

        const fd = new FormData();
        fd.append("inventory_item_id", inventoryItemId);
        fd.append("quantity", "1");

        const res = await fetch(addUrl, {
          method: "POST",
          headers: {
            "X-Requested-With": "XMLHttpRequest",
            Accept: "application/json",
            "X-CSRF-TOKEN": getCsrf(cfg),
          },
          body: fd,
        });

        if (!res.ok) {
          throw await parseErrorResponse(res);
        }

        const data = await res.json().catch(() => null);
        if (!data?.ok || !data?.item) {
          throw new Error(data?.message || "Unable to add item.");
        }

        appendItemRow(data.item);
        search.value = "";
        closeDropdown();

        await Swal.fire({
          icon: "success",
          title: "Added",
          text: data?.message || "Item added to PAR.",
          timer: 1100,
          showConfirmButton: false,
        });
      } catch (err) {
        await Swal.fire({
          icon: "error",
          title: "Error",
          text: String(err?.message || err),
        });
      } finally {
        button.disabled = false;
      }
    });

    syncItemGuidance();
  });
})();
