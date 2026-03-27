import Swal from "sweetalert2";
import "sweetalert2/dist/sweetalert2.min.css";

(function () {
  "use strict";

  const cfg = window.__itrEdit || {};
  let currentItemCount = Number(cfg.initialItemCount || 0);

  function getCsrf() {
    return document.querySelector('meta[name="csrf-token"]')?.content || "";
  }

  function esc(s) {
    return String(s ?? "")
      .replace(/&/g, "&amp;")
      .replace(/</g, "&lt;")
      .replace(/>/g, "&gt;")
      .replace(/\"/g, "&quot;")
      .replace(/'/g, "&#39;");
  }

  function formatMoney(value) {
    if (value === null || value === undefined || value === "") return "-";
    const num = Number(value);
    if (!Number.isFinite(num)) return "-";
    return num.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
  }

  function formatDate(value) {
    const text = String(value || "").trim();
    if (!text) return "-";
    return text.slice(0, 10);
  }

  function debounce(fn, wait = 250) {
    let t = null;
    return (...args) => {
      clearTimeout(t);
      t = setTimeout(() => fn.apply(null, args), wait);
    };
  }

  async function parseErrorResponse(res) {
    const ct = res.headers.get("content-type") || "";
    let data = null;
    if (ct.includes("application/json")) {
      data = await res.json().catch(() => null);
    }

    return {
      status: res.status,
      message:
        data?.message ||
        (res.status === 401
          ? "Session expired. Please log in again."
          : res.status === 403
            ? "You do not have permission."
            : `Request failed (HTTP ${res.status}).`),
      data,
    };
  }

  function onReady(fn) {
    if (document.readyState === "loading") {
      document.addEventListener("DOMContentLoaded", fn);
    } else {
      fn();
    }
  }

  function setSuggestionsVisible(el, visible) {
    if (!el) return;
    if (visible) el.classList.remove("hidden");
    else el.classList.add("hidden");
  }

  function syncSourceLocks(count) {
    const isDraft = String(cfg.status || "draft").toLowerCase() === "draft";
    const help = document.getElementById("itrItemsHelp");
    const sourceFields = [
      document.querySelector('[name="from_department_id"]'),
      document.querySelector('[name="from_accountable_officer"]'),
      document.querySelector('[name="from_fund_source_id"]'),
    ];

    if (help) {
      help.textContent = count > 0
        ? "Remove all ITR items first to change the source department, source officer, or source fund source."
        : "Save the source department and fund source first. If a source accountable officer is provided, suggestions are narrowed to that officer.";
    }

    if (isDraft) {
      sourceFields.forEach((field) => {
        if (field) field.disabled = count > 0;
      });
    }
  }

  function renderList(items, listWrap, emptyEl, countEl) {
    const rows = Array.isArray(items) ? items : [];
    currentItemCount = rows.length;
    if (countEl) countEl.textContent = `${rows.length} item(s)`;
    syncSourceLocks(rows.length);

    if (!rows.length) {
      if (emptyEl) emptyEl.classList.remove("hidden");
      if (listWrap) {
        listWrap.classList.add("hidden");
        listWrap.innerHTML = "";
      }
      return;
    }

    if (emptyEl) emptyEl.classList.add("hidden");
    if (listWrap) {
      listWrap.classList.remove("hidden");
      listWrap.innerHTML = rows.map((item) => `
        <div class="rounded border border-defaultborder p-3" data-itr-item-row data-id="${esc(item.id)}">
          <div class="flex items-start justify-between gap-3">
            <div class="min-w-0">
              <div class="text-xs text-[#8c9097]">
                Inventory No.: <b>${esc(item.inventory_item_no || "-")}</b>
              </div>
              <div class="text-sm font-semibold mt-1 break-words leading-5">${esc(item.item_name || "-")}</div>
              <div class="text-xs text-[#8c9097] mt-1 whitespace-pre-wrap">${esc(item.description || "-")}</div>
              <div class="grid grid-cols-2 gap-x-4 gap-y-1 text-xs mt-3 text-[#4b5563]">
                <div>Quantity: <b>${esc(item.quantity || 1)}</b></div>
                <div>Amount: <b>${esc(formatMoney(item.amount))}</b></div>
                <div>Date Acquired: <b>${esc(formatDate(item.date_acquired))}</b></div>
                <div>Useful Life: <b>${esc(item.estimated_useful_life || "-")}</b></div>
                <div class="col-span-2">Condition: <b>${esc(item.condition || "-")}</b></div>
              </div>
            </div>
            ${cfg.canModify ? `
              <div class="shrink-0">
                <button type="button" class="ti-btn ti-btn-light" data-remove>Remove</button>
              </div>
            ` : ""}
          </div>
        </div>
      `).join("");
    }
  }

  function renderSuggestions(items, listEl, suggestWrap) {
    if (!listEl || !suggestWrap) return;

    const rows = Array.isArray(items) ? items : [];
    if (!rows.length) {
      listEl.innerHTML = '<div class="p-3 text-sm text-[#8c9097]">No matching transferable ICS items found.</div>';
      setSuggestionsVisible(suggestWrap, true);
      return;
    }

    listEl.innerHTML = rows.map((item) => `
      <button
        type="button"
        class="w-full text-left px-3 py-2 border-b border-defaultborder hover:bg-slate-100 dark:hover:bg-white/5"
        data-suggest
        data-inventory-item-id="${esc(item.inventory_item_id)}"
      >
        <div class="text-sm font-semibold truncate">${esc(item.inventory_item_no || "-")} - ${esc(item.item_name || "-")}</div>
        <div class="text-xs text-[#8c9097] truncate mt-1">${esc(item.description || "-")}</div>
        <div class="grid grid-cols-2 gap-x-3 gap-y-1 text-[11px] text-[#8c9097] mt-2">
          <div>Qty: <b>${esc(item.quantity || 1)}</b></div>
          <div>Amount: <b>${esc(formatMoney(item.amount))}</b></div>
          <div>Date Acquired: <b>${esc(formatDate(item.date_acquired))}</b></div>
          <div>Useful Life: <b>${esc(item.estimated_useful_life || "-")}</b></div>
          <div>Officer: <b>${esc(item.accountable_officer || "-")}</b></div>
          <div>Condition: <b>${esc(item.condition || "-")}</b></div>
          <div class="col-span-2">Fund: <b>${esc(item.fund_source_label || "-")}</b></div>
        </div>
      </button>
    `).join("");

    setSuggestionsVisible(suggestWrap, true);
  }

  onReady(function () {
    const listWrap = document.getElementById("itrItemsList");
    const emptyEl = document.getElementById("itrItemsEmpty");
    const countEl = document.getElementById("itrItemsCount");
    const search = document.getElementById("itrItemSearch");
    const suggestWrap = document.getElementById("itrItemSuggestions");
    const suggestList = document.getElementById("itrItemSuggestList");
    const suggestClose = document.getElementById("itrItemSuggestClose");

    if (!listWrap || !emptyEl || !countEl || !cfg.itemListUrl) return;

    async function refreshList() {
      const res = await fetch(cfg.itemListUrl, {
        method: "GET",
        headers: {
          Accept: "application/json",
          "X-CSRF-TOKEN": getCsrf(),
        },
      });

      if (!res.ok) throw await parseErrorResponse(res);
      const data = await res.json().catch(() => ({}));
      renderList(data?.items || [], listWrap, emptyEl, countEl);
    }

    const pageApi = window.__itrEditPage || {};
    Object.assign(pageApi, {
      getItemCount() {
        return currentItemCount;
      },
      refreshItemList: refreshList,
    });
    window.__itrEditPage = pageApi;

    refreshList().catch((e) => {
      listWrap.classList.remove("hidden");
      listWrap.innerHTML = `<div class="text-xs text-danger">${esc(e?.message || "Unable to load ITR items.")}</div>`;
    });

    if (!search || !suggestWrap || !suggestList || !cfg.canModify) {
      return;
    }

    function hasSavedSourceSide() {
      const fromDepartment = document.querySelector('[name="from_department_id"]');
      const fromFundSource = document.querySelector('[name="from_fund_source_id"]');
      return !!String(fromDepartment?.value || "").trim() && !!String(fromFundSource?.value || "").trim();
    }

    function isSourceSideDirty() {
      const page = window.__itrEditPage;
      return !!(
        page &&
        typeof page.isFieldDirty === "function" &&
        (
          page.isFieldDirty("from_department_id") ||
          page.isFieldDirty("from_accountable_officer") ||
          page.isFieldDirty("from_fund_source_id")
        )
      );
    }

    function ensureItemActionsAllowed() {
      if (!hasSavedSourceSide()) {
        throw new Error("Save the source department and source fund source first before managing ITR items.");
      }

      if (isSourceSideDirty()) {
        throw new Error("Save the source-side changes first before managing ITR items.");
      }
    }

    const doSearch = debounce(async () => {
      const q = String(search.value || "").trim();
      if (!q) {
        suggestList.innerHTML = "";
        setSuggestionsVisible(suggestWrap, false);
        return;
      }

      if (q.length < 2) {
        suggestList.innerHTML = '<div class="p-3 text-sm text-[#8c9097]">Type at least 2 characters.</div>';
        setSuggestionsVisible(suggestWrap, true);
        return;
      }

      try {
        ensureItemActionsAllowed();

        const url = new URL(cfg.itemSuggestUrl, window.location.origin);
        url.searchParams.set("q", q);

        const res = await fetch(url.toString(), {
          method: "GET",
          headers: {
            Accept: "application/json",
            "X-CSRF-TOKEN": getCsrf(),
          },
        });

        if (!res.ok) throw await parseErrorResponse(res);
        const data = await res.json().catch(() => ({}));
        renderSuggestions(data?.items || [], suggestList, suggestWrap);
      } catch (e) {
        suggestList.innerHTML = `<div class="p-3 text-sm text-danger">${esc(e?.message || "Unable to load suggestions.")}</div>`;
        setSuggestionsVisible(suggestWrap, true);
      }
    }, 250);

    search.addEventListener("input", doSearch);
    search.addEventListener("focus", doSearch);

    suggestClose?.addEventListener("click", (e) => {
      e.preventDefault();
      setSuggestionsVisible(suggestWrap, false);
    });

    document.addEventListener("pointerdown", (e) => {
      const inside = suggestWrap.contains(e.target) || search.contains(e.target);
      if (!inside) setSuggestionsVisible(suggestWrap, false);
    });

    suggestList.addEventListener("click", async (e) => {
      const btn = e.target.closest("[data-suggest]");
      if (!btn) return;

      try {
        ensureItemActionsAllowed();

        const inventoryItemId = btn.getAttribute("data-inventory-item-id") || "";
        if (!inventoryItemId) return;

        const fd = new FormData();
        fd.append("inventory_item_id", inventoryItemId);

        const res = await fetch(cfg.itemStoreUrl, {
          method: "POST",
          headers: {
            Accept: "application/json",
            "X-CSRF-TOKEN": getCsrf(),
          },
          body: fd,
        });

        if (!res.ok) throw await parseErrorResponse(res);
        await res.json().catch(() => ({}));

        search.value = "";
        setSuggestionsVisible(suggestWrap, false);
        await refreshList();

        await Swal.fire({
          icon: "success",
          title: "Added",
          text: "Item added to ITR.",
          timer: 1100,
          showConfirmButton: false,
        });
      } catch (e2) {
        await Swal.fire("Error", e2?.message || "Failed to add item.", "error");
      }
    });

    listWrap.addEventListener("click", async (e) => {
      const btn = e.target.closest("[data-remove]");
      if (!btn) return;

      const row = btn.closest("[data-itr-item-row]");
      const id = row?.dataset?.id || "";
      if (!id) return;

      const confirm = await Swal.fire({
        icon: "warning",
        title: "Remove item?",
        text: "This will remove the item from this ITR draft.",
        showCancelButton: true,
        confirmButtonText: "Remove",
      });

      if (!confirm.isConfirmed) return;

      try {
        const url = String(cfg.itemDeleteUrlTemplate || "").replace("__ITR_ITEM_ID__", encodeURIComponent(id));
        const res = await fetch(url, {
          method: "DELETE",
          headers: {
            Accept: "application/json",
            "X-CSRF-TOKEN": getCsrf(),
          },
        });

        if (!res.ok) throw await parseErrorResponse(res);
        await refreshList();

        await Swal.fire({
          icon: "success",
          title: "Removed",
          text: "Item removed from ITR.",
          timer: 1100,
          showConfirmButton: false,
        });
      } catch (err) {
        await Swal.fire("Error", err?.message || "Failed to remove item.", "error");
      }
    });
  });
})();


