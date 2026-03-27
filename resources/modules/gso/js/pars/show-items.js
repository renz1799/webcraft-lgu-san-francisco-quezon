import Swal from "sweetalert2";
import "sweetalert2/dist/sweetalert2.min.css";

(function () {
  "use strict";

  function onReady(fn) {
    if (document.readyState === "loading") document.addEventListener("DOMContentLoaded", fn);
    else fn();
  }

  function esc(s) {
    return String(s ?? "")
      .replace(/&/g, "&amp;")
      .replace(/</g, "&lt;")
      .replace(/>/g, "&gt;")
      .replace(/"/g, "&quot;")
      .replace(/'/g, "&#39;");
  }

  function getCsrf(cfg) {
    return document.querySelector('meta[name="csrf-token"]')?.content || cfg?.csrf || "";
  }

  function parseCountText(s) {
    const m = String(s || "").match(/(\d+)/);
    return m ? Number(m[1]) : 0;
  }

  function setCountText(el, n) {
    if (!el) return;
    el.textContent = `${Math.max(0, Number(n || 0))} item(s)`;
  }

  function renderNotInPoolHtml(lines) {
    const items = Array.isArray(lines) ? lines : [];
    if (!items.length) return "";

    return `
      <div class="text-left">
        <div class="text-sm mb-2">
          These item(s) were already taken by another PAR / moved out of the GSO pool:
        </div>
        <ul class="text-sm list-disc pl-5 space-y-1">
          ${items.map((x) => `<li>${esc(x)}</li>`).join("")}
        </ul>
        <div class="text-xs text-[#8c9097] mt-2">
          Tip: refresh this page to update the item list.
        </div>
      </div>
    `;
  }

  async function safeJson(r) {
    const ct = (r.headers.get("content-type") || "").toLowerCase();
    if (!ct.includes("application/json")) return null;
    try {
      return await r.json();
    } catch (e) {
      return null;
    }
  }

  onReady(function () {
    const cfg = window.__parShow || {};
    if (cfg.canModify === false) return;

    const itemsCountEl = document.getElementById("par-items-count");

    // ==========================================================
    // REMOVE ITEM
    // ==========================================================
    document.addEventListener("click", async function (e) {
      const btn = e.target.closest('button[data-action="par-item-remove"]');
      if (!btn) return;

      const deleteUrl = btn.getAttribute("data-delete-url") || "";
      const parItemId = btn.getAttribute("data-par-item-id") || "";
      if (!deleteUrl || !parItemId) return;

      const res = await Swal.fire({
        title: "Remove item?",
        text: "This will remove the item from this PAR draft.",
        icon: "warning",
        showCancelButton: true,
        confirmButtonText: "Yes, remove",
        cancelButtonText: "Cancel",
        confirmButtonColor: "#d33",
      });
      if (!res.isConfirmed) return;

      btn.disabled = true;

      try {
        const csrf = getCsrf(cfg);

        const r = await fetch(deleteUrl, {
          method: "DELETE",
          headers: {
            "X-Requested-With": "XMLHttpRequest",
            Accept: "application/json",
            "X-CSRF-TOKEN": csrf,
          },
        });

        const data = await safeJson(r);

        if (!r.ok || !data?.ok) {
          throw new Error(data?.message || "Failed to remove item.");
        }

        const row = document.querySelector(`[data-par-item-row="${CSS.escape(parItemId)}"]`);
        if (row) row.remove();

        if (itemsCountEl) {
          const cur = parseCountText(itemsCountEl.textContent);
          setCountText(itemsCountEl, cur - 1);
        }

        const itemsList = document.getElementById("parItemsList");
        if (itemsList && itemsList.querySelectorAll("[data-par-item-row]").length === 0) {
          const empty = document.getElementById("par-items-empty");
          itemsList.classList.add("hidden");
          if (empty) empty.classList.remove("hidden");
        }

        await Swal.fire({
          icon: "success",
          title: "Removed",
          text: data?.message || "Item removed from PAR.",
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
        btn.disabled = false;
      }
    });

    // ==========================================================
    // FINALIZE PAR (intercept FORM submit; this prevents reload)
    // ==========================================================
    const finalizeForm = document.getElementById("par-finalize-form");

    if (finalizeForm) {
      finalizeForm.addEventListener("submit", async function (e) {
        e.preventDefault();
        e.stopPropagation();

        const btn = finalizeForm.querySelector('button[type="submit"]');
        const finalizeUrl = finalizeForm.getAttribute("action") || "";
        if (!finalizeUrl) return;

        const ask = await Swal.fire({
          title: "Finalize PAR?",
          text: "This will generate issuance events and lock the PAR.",
          icon: "question",
          showCancelButton: true,
          confirmButtonText: "Yes, finalize",
          cancelButtonText: "Cancel",
        });

        if (!ask.isConfirmed) return;

        if (btn) btn.disabled = true;

        try {
          const csrf = getCsrf(cfg);
          const fd = new FormData(finalizeForm);

          const r = await fetch(finalizeUrl, {
            method: "POST",
            headers: {
              "X-Requested-With": "XMLHttpRequest",
              Accept: "application/json",
              "X-CSRF-TOKEN": csrf,
            },
            body: fd,
          });

          const data = await safeJson(r);

          if (r.status === 422) {
            const lines = data?.errors?.not_in_pool || [];
            if (Array.isArray(lines) && lines.length) {
              await Swal.fire({
                icon: "warning",
                title: "Some items are no longer available",
                html: renderNotInPoolHtml(lines),
              });
              return;
            }

            await Swal.fire({
              icon: "warning",
              title: "Cannot finalize",
              text: data?.message || "Validation error.",
            });
            return;
          }

          if (!r.ok || !data?.ok) {
            throw new Error(data?.message || "Failed to finalize PAR.");
          }

          await Swal.fire({
            icon: "success",
            title: "Finalized",
            text: data?.message || "PAR finalized.",
            timer: 1200,
            showConfirmButton: false,
          });

          window.location.href = data?.redirect || window.location.href;
        } catch (err) {
          await Swal.fire({
            icon: "error",
            title: "Error",
            text: String(err?.message || err),
          });
        } finally {
          if (btn) btn.disabled = false;
        }
      });
    }
  });
})();
