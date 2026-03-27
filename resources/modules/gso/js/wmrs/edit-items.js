import Swal from "sweetalert2";
import "sweetalert2/dist/sweetalert2.min.css";

(function () {
  "use strict";

  const cfg = window.__wmrEdit || {};
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

  function syncHeaderLocks(count) {
    const isDraft = String(cfg.status || "draft").toLowerCase() === "draft";
    const help = document.getElementById("wmrItemsHelp");
    const fundCluster = document.querySelector('[name="fund_cluster_id"]');

    if (help) {
      help.textContent = count > 0
        ? "Remove all disposal items first to change the WMR fund cluster."
        : "Save the WMR fund cluster first before managing disposal items.";
    }

    if (isDraft && fundCluster) {
      fundCluster.disabled = count > 0;
    }
  }

  function syncLineConditionalFields(row) {
    const select = row.querySelector('[data-field="disposal_method"]');
    const transferWrap = row.querySelector('[data-transfer-wrap]');
    if (!select || !transferWrap) return;

    const isTransfer = String(select.value || "") === "transferred_without_cost";
    transferWrap.classList.toggle("hidden", !isTransfer);
  }

  function renderList(items, listWrap, emptyEl, countEl) {
    const rows = Array.isArray(items) ? items : [];
    currentItemCount = rows.length;
    if (countEl) countEl.textContent = `${rows.length} item(s)`;
    syncHeaderLocks(rows.length);

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
        <div class="rounded border border-defaultborder p-3 space-y-3" data-wmr-item-row data-id="${esc(item.id)}">
          <div class="flex items-start justify-between gap-3">
            <div class="min-w-0">
              <div class="text-xs text-[#8c9097]">Line ${esc(item.line_no || "-")} • Ref No.: <b>${esc(item.reference_no || "-")}</b></div>
              <div class="text-sm font-semibold mt-1 break-words leading-5">${esc(item.item_name || "-")}</div>
              <div class="text-xs text-[#8c9097] mt-1 whitespace-pre-wrap">${esc(item.description || "-")}</div>
              <div class="grid grid-cols-2 gap-x-4 gap-y-1 text-xs mt-3 text-[#4b5563]">
                <div>Unit: <b>${esc(item.unit || "-")}</b></div>
                <div>Condition: <b>${esc(item.condition || "-")}</b></div>
                <div>Date Acquired: <b>${esc(formatDate(item.date_acquired))}</b></div>
                <div>Acquisition Cost: <b>${esc(formatMoney(item.acquisition_cost))}</b></div>
              </div>
            </div>
            ${cfg.canModify ? `
              <div class="shrink-0 flex items-center gap-2">
                <button type="button" class="ti-btn ti-btn-primary" data-save-line>Save Line</button>
                <button type="button" class="ti-btn ti-btn-light" data-remove>Remove</button>
              </div>
            ` : ""}
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
            <div>
              <label class="ti-form-label">Quantity</label>
              <input type="number" min="1" class="ti-form-input w-full" data-field="quantity" value="${esc(item.quantity || 1)}" ${cfg.canModify ? "" : "disabled"}>
            </div>
            <div>
              <label class="ti-form-label">Disposal Method</label>
              <select class="ti-form-select w-full" data-field="disposal_method" ${cfg.canModify ? "" : "disabled"}>
                <option value="destroyed" ${String(item.disposal_method || "destroyed") === "destroyed" ? "selected" : ""}>Destroyed</option>
                <option value="private_sale" ${String(item.disposal_method || "") === "private_sale" ? "selected" : ""}>Sold at private sale</option>
                <option value="public_auction" ${String(item.disposal_method || "") === "public_auction" ? "selected" : ""}>Sold at public auction</option>
                <option value="transferred_without_cost" ${String(item.disposal_method || "") === "transferred_without_cost" ? "selected" : ""}>Transferred without cost</option>
              </select>
            </div>
          </div>

          <div data-transfer-wrap class="${String(item.disposal_method || "destroyed") === "transferred_without_cost" ? "" : "hidden"}">
            <label class="ti-form-label">Receiving Agency / Entity</label>
            <input class="ti-form-input w-full" data-field="transfer_entity_name" value="${esc(item.transfer_entity_name || "")}" ${cfg.canModify ? "" : "disabled"}>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
            <div>
              <label class="ti-form-label">Official Receipt No.</label>
              <input class="ti-form-input w-full" data-field="official_receipt_no" value="${esc(item.official_receipt_no || "")}" ${cfg.canModify ? "" : "disabled"}>
            </div>
            <div>
              <label class="ti-form-label">Official Receipt Date</label>
              <input type="date" class="ti-form-input w-full" data-field="official_receipt_date" value="${esc(formatDate(item.official_receipt_date) === "-" ? "" : formatDate(item.official_receipt_date))}" ${cfg.canModify ? "" : "disabled"}>
            </div>
            <div>
              <label class="ti-form-label">Official Receipt Amount</label>
              <input type="number" min="0" step="0.01" class="ti-form-input w-full" data-field="official_receipt_amount" value="${esc(item.official_receipt_amount ?? "")}" ${cfg.canModify ? "" : "disabled"}>
            </div>
          </div>
        </div>
      `).join("");

      listWrap.querySelectorAll("[data-wmr-item-row]").forEach((row) => syncLineConditionalFields(row));
    }
  }

  function renderSuggestions(items, listEl, suggestWrap) {
    if (!listEl || !suggestWrap) return;

    const rows = Array.isArray(items) ? items : [];
    if (!rows.length) {
      listEl.innerHTML = '<div class="p-3 text-sm text-[#8c9097]">No matching disposal items found.</div>';
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
        <div class="text-sm font-semibold truncate">${esc(item.reference_no || "-")} - ${esc(item.item_name || "-")}</div>
        <div class="text-xs text-[#8c9097] truncate mt-1">${esc(item.description || "-")}</div>
        <div class="grid grid-cols-2 gap-x-3 gap-y-1 text-[11px] text-[#8c9097] mt-2">
          <div>Qty: <b>${esc(item.quantity || 1)}</b></div>
          <div>Unit: <b>${esc(item.unit || "-")}</b></div>
          <div>Status: <b>${esc(item.status || "-")}</b></div>
          <div>Condition: <b>${esc(item.condition || "-")}</b></div>
          <div>Date Acquired: <b>${esc(formatDate(item.acquisition_date))}</b></div>
          <div>Amount: <b>${esc(formatMoney(item.acquisition_cost))}</b></div>
          <div>Officer: <b>${esc(item.accountable_officer || "-")}</b></div>
          <div>Department: <b>${esc(item.department_label || "-")}</b></div>
          <div class="col-span-2">Fund Cluster: <b>${esc(item.fund_cluster_code || "-")}</b></div>
        </div>
      </button>
    `).join("");

    setSuggestionsVisible(suggestWrap, true);
  }

  onReady(function () {
    const listWrap = document.getElementById("wmrItemsList");
    const emptyEl = document.getElementById("wmrItemsEmpty");
    const countEl = document.getElementById("wmrItemsCount");
    const search = document.getElementById("wmrItemSearch");
    const suggestWrap = document.getElementById("wmrItemSuggestions");
    const suggestList = document.getElementById("wmrItemSuggestList");
    const suggestClose = document.getElementById("wmrItemSuggestClose");

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

    const pageApi = window.__wmrEditPage || {};
    Object.assign(pageApi, {
      getItemCount() {
        return currentItemCount;
      },
      refreshItemList: refreshList,
    });
    window.__wmrEditPage = pageApi;

    refreshList().catch((e) => {
      listWrap.classList.remove("hidden");
      listWrap.innerHTML = `<div class="text-xs text-danger">${esc(e?.message || "Unable to load WMR items.")}</div>`;
    });

    if (!search || !suggestWrap || !suggestList || !cfg.canModify) {
      return;
    }

    function hasSavedHeaderContext() {
      const fundCluster = document.querySelector('[name="fund_cluster_id"]');
      return !!String(fundCluster?.value || "").trim();
    }

    function isHeaderDirty() {
      const page = window.__wmrEditPage;
      return !!(page && typeof page.isFieldDirty === "function" && page.isFieldDirty("fund_cluster_id"));
    }

    function ensureItemActionsAllowed() {
      if (!hasSavedHeaderContext()) {
        throw new Error("Save the WMR fund cluster first before managing disposal items.");
      }

      if (isHeaderDirty()) {
        throw new Error("Save the fund cluster change first before managing disposal items.");
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
          text: "Item added to WMR.",
          timer: 1100,
          showConfirmButton: false,
        });
      } catch (e2) {
        await Swal.fire("Error", e2?.message || "Failed to add item.", "error");
      }
    });

    listWrap.addEventListener("change", (e) => {
      const row = e.target.closest("[data-wmr-item-row]");
      if (!row) return;
      if (e.target.matches('[data-field="disposal_method"]')) {
        syncLineConditionalFields(row);
      }
    });

    listWrap.addEventListener("click", async (e) => {
      const saveBtn = e.target.closest("[data-save-line]");
      const removeBtn = e.target.closest("[data-remove]");
      const row = e.target.closest("[data-wmr-item-row]");
      if (!row) return;

      const id = row.dataset?.id || "";
      if (!id) return;

      if (saveBtn) {
        try {
          const url = String(cfg.itemUpdateUrlTemplate || "").replace("__WMR_ITEM_ID__", encodeURIComponent(id));
          const payload = {
            quantity: Number(row.querySelector('[data-field="quantity"]')?.value || 1),
            disposal_method: String(row.querySelector('[data-field="disposal_method"]')?.value || "destroyed"),
            transfer_entity_name: String(row.querySelector('[data-field="transfer_entity_name"]')?.value || "").trim(),
            official_receipt_no: String(row.querySelector('[data-field="official_receipt_no"]')?.value || "").trim(),
            official_receipt_date: String(row.querySelector('[data-field="official_receipt_date"]')?.value || "").trim(),
            official_receipt_amount: String(row.querySelector('[data-field="official_receipt_amount"]')?.value || "").trim(),
          };

          const res = await fetch(url, {
            method: "PATCH",
            headers: {
              Accept: "application/json",
              "Content-Type": "application/json",
              "X-CSRF-TOKEN": getCsrf(),
            },
            body: JSON.stringify(payload),
          });

          if (!res.ok) throw await parseErrorResponse(res);
          await refreshList();

          await Swal.fire({
            icon: "success",
            title: "Saved",
            text: "Disposal line updated.",
            timer: 1100,
            showConfirmButton: false,
          });
        } catch (err) {
          const validation = err?.data?.errors;
          if (validation && typeof validation === "object") {
            const lines = Object.values(validation).flat().map((x) => `<li>${esc(x)}</li>`).join("");
            await Swal.fire({
              icon: "warning",
              title: "Please correct the line details",
              html: `<div style="text-align:left"><ul style="margin:0; padding-left:18px;">${lines}</ul></div>`,
            });
          } else {
            await Swal.fire("Error", err?.message || "Failed to save line.", "error");
          }
        }
        return;
      }

      if (removeBtn) {
        const confirm = await Swal.fire({
          icon: "warning",
          title: "Remove item?",
          text: "This will remove the disposal line from the WMR draft.",
          showCancelButton: true,
          confirmButtonText: "Remove",
        });

        if (!confirm.isConfirmed) return;

        try {
          const url = String(cfg.itemDeleteUrlTemplate || "").replace("__WMR_ITEM_ID__", encodeURIComponent(id));
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
            text: "Disposal item removed from WMR.",
            timer: 1100,
            showConfirmButton: false,
          });
        } catch (err) {
          await Swal.fire("Error", err?.message || "Failed to remove item.", "error");
        }
      }
    });
  });
})();
