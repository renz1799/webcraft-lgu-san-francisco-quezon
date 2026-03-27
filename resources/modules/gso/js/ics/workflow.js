import Swal from "sweetalert2";
import "sweetalert2/dist/sweetalert2.min.css";

(function () {
  "use strict";

  const cfg = window.__icsEdit || {};

  function onReady(fn) {
    if (document.readyState === "loading") {
      document.addEventListener("DOMContentLoaded", fn);
      return;
    }

    fn();
  }

  function getCsrf() {
    return document.querySelector('meta[name="csrf-token"]')?.content || "";
  }

  function esc(value) {
    return String(value ?? "")
      .replace(/&/g, "&amp;")
      .replace(/</g, "&lt;")
      .replace(/>/g, "&gt;")
      .replace(/\"/g, "&quot;")
      .replace(/'/g, "&#39;");
  }

  async function parseErrorResponse(res) {
    const ct = res.headers.get("content-type") || "";
    const data = ct.includes("application/json") ? await res.json().catch(() => null) : null;

    return {
      status: res.status,
      message:
        data?.message ||
        (res.status === 401
          ? "Session expired. Please log in again."
          : res.status === 403
            ? "You do not have permission to perform this action."
            : res.status === 422
              ? "Please review the ICS details and items before continuing."
              : `Request failed (HTTP ${res.status}).`),
      data,
    };
  }

  async function postJson(url, body = {}) {
    const res = await fetch(url, {
      method: "POST",
      headers: {
        Accept: "application/json",
        "Content-Type": "application/json",
        "X-CSRF-TOKEN": getCsrf(),
      },
      body: JSON.stringify(body),
    });

    if (!res.ok) {
      throw await parseErrorResponse(res);
    }

    return await res.json().catch(() => ({}));
  }

  function lockBtn(btn, locked, labelWhenLocked) {
    if (!btn) return;

    btn.disabled = !!locked;
    if (labelWhenLocked) {
      btn.dataset._oldText = btn.dataset._oldText || btn.textContent;
    }

    if (locked && labelWhenLocked) {
      btn.textContent = labelWhenLocked;
    }

    if (!locked && btn.dataset._oldText) {
      btn.textContent = btn.dataset._oldText;
    }
  }

  async function showError(err, title = "Error") {
    const errors = err?.data?.errors;

    if (Number(err?.status || 0) === 422 && errors && typeof errors === "object") {
      const list = Object.values(errors)
        .flat()
        .map((line) => `<li>${esc(line)}</li>`)
        .join("");

      await Swal.fire({
        icon: "warning",
        title,
        html: `
          <div style="text-align:left">
            <div style="margin-bottom:8px;">${esc(err?.message || "Please review the current ICS details.")}</div>
            <ul style="margin:0; padding-left:18px;">${list}</ul>
          </div>
        `,
      });
      return;
    }

    await Swal.fire({
      icon: "error",
      title,
      text: err?.message || "Unexpected error.",
    });
  }

  onReady(function () {
    const submitBtn = document.getElementById("icsSubmitBtn");
    const reopenBtn = document.getElementById("icsReopenBtn");
    const finalizeBtn = document.getElementById("icsFinalizeBtn");
    const cancelBtn = document.getElementById("icsCancelBtn");

    if (!submitBtn && !reopenBtn && !finalizeBtn && !cancelBtn) {
      return;
    }

    if (submitBtn && !cfg.submitUrl) {
      console.error("[ics-workflow] Missing submitUrl in window.__icsEdit", cfg);
      return;
    }

    if (reopenBtn && !cfg.reopenUrl) {
      console.error("[ics-workflow] Missing reopenUrl in window.__icsEdit", cfg);
      return;
    }

    if (finalizeBtn && !cfg.finalizeUrl) {
      console.error("[ics-workflow] Missing finalizeUrl in window.__icsEdit", cfg);
      return;
    }

    if (cancelBtn && !cfg.cancelUrl) {
      console.error("[ics-workflow] Missing cancelUrl in window.__icsEdit", cfg);
      return;
    }

    submitBtn?.addEventListener("click", async function () {
      const page = window.__icsEditPage || {};
      const dirtyCount = Number(page.getDirtyCount?.() || 0);
      const itemCount = Number(page.getItemCount?.() ?? cfg.initialItemCount ?? 0);

      if (itemCount <= 0) {
        await Swal.fire({
          icon: "warning",
          title: "No items to submit",
          text: "Add at least one ICS item before submitting.",
        });
        return;
      }

      const confirm = await Swal.fire({
        icon: "question",
        title: dirtyCount > 0 ? "Save and Submit ICS?" : "Submit ICS?",
        html: `
          <div style="text-align:left">
            <div>${
              dirtyCount > 0
                ? `This will save <b>${dirtyCount}</b> pending header change${dirtyCount === 1 ? "" : "s"} and then submit the ICS.`
                : "This will move the ICS forward for finalization."
            }</div>
            <div class="text-xs" style="color:#8c9097;margin-top:6px">
              Status will become <b>SUBMITTED</b>.
            </div>
          </div>
        `,
        showCancelButton: true,
        confirmButtonText: dirtyCount > 0 ? "Save and Submit" : "Submit",
        cancelButtonText: "Cancel",
      });

      if (!confirm.isConfirmed) return;

      try {
        lockBtn(submitBtn, true, dirtyCount > 0 ? "Saving..." : "Submitting...");

        if (dirtyCount > 0 && page.saveChanges) {
          const saved = await page.saveChanges({
            silentSuccess: true,
            silentNoChanges: true,
          });

          if (!saved) {
            lockBtn(submitBtn, false);
            page.refreshDirtyState?.();
            return;
          }

          lockBtn(submitBtn, true, "Submitting...");
        }

        await postJson(cfg.submitUrl, {});

        await Swal.fire({
          icon: "success",
          title: "Submitted",
          text: dirtyCount > 0 ? "ICS was saved and submitted." : "ICS is now submitted.",
          timer: 1000,
          showConfirmButton: false,
        });

        window.location.reload();
      } catch (err) {
        await showError(err, "Unable to submit ICS");
        lockBtn(submitBtn, false);
        page.refreshDirtyState?.();
      }
    });

    reopenBtn?.addEventListener("click", async function () {
      const confirm = await Swal.fire({
        icon: "question",
        title: "Reopen ICS?",
        html: `
          <div style="text-align:left">
            <div>This will move the ICS back to <b>DRAFT</b> so the header and items can be edited again.</div>
            <div class="text-xs" style="color:#8c9097;margin-top:6px">
              No inventory issuance will be posted by reopening.
            </div>
          </div>
        `,
        showCancelButton: true,
        confirmButtonText: "Reopen ICS",
        cancelButtonText: "Cancel",
      });

      if (!confirm.isConfirmed) return;

      try {
        lockBtn(reopenBtn, true, "Reopening...");
        await postJson(cfg.reopenUrl, {});

        await Swal.fire({
          icon: "success",
          title: "Reopened",
          text: "ICS is back in draft and can be edited again.",
          timer: 1000,
          showConfirmButton: false,
        });

        window.location.reload();
      } catch (err) {
        await showError(err, "Unable to reopen ICS");
        lockBtn(reopenBtn, false);
      }
    });

    finalizeBtn?.addEventListener("click", async function () {
      const confirm = await Swal.fire({
        icon: "warning",
        title: "Finalize ICS?",
        html: `
          <div style="text-align:left">
            <div>This will generate the ICS number and issue the selected inventory items to the chosen department and accountable officer.</div>
            <div class="text-xs" style="color:#8c9097;margin-top:6px">
              Status will become <b>FINALIZED</b>.
            </div>
          </div>
        `,
        showCancelButton: true,
        confirmButtonText: "Finalize",
        cancelButtonText: "Cancel",
      });

      if (!confirm.isConfirmed) return;

      try {
        lockBtn(finalizeBtn, true, "Finalizing...");
        await postJson(cfg.finalizeUrl, {});

        await Swal.fire({
          icon: "success",
          title: "Finalized",
          text: "ICS is finalized and inventory items were issued.",
          timer: 1100,
          showConfirmButton: false,
        });

        window.location.reload();
      } catch (err) {
        await showError(err, "Unable to finalize ICS");
        lockBtn(finalizeBtn, false);
      }
    });

    cancelBtn?.addEventListener("click", async function () {
      const result = await Swal.fire({
        icon: "warning",
        title: "Cancel ICS?",
        input: "textarea",
        inputLabel: "Reason (optional)",
        inputPlaceholder: "Add a short reason for cancellation...",
        inputAttributes: { rows: 4 },
        showCancelButton: true,
        confirmButtonText: "Cancel ICS",
        cancelButtonText: "Back",
      });

      if (!result.isConfirmed) return;

      try {
        lockBtn(cancelBtn, true, "Cancelling...");
        await postJson(cfg.cancelUrl, {
          reason: String(result.value || "").trim() || null,
        });

        await Swal.fire({
          icon: "success",
          title: "Cancelled",
          text: "ICS is now cancelled.",
          timer: 1000,
          showConfirmButton: false,
        });

        window.location.reload();
      } catch (err) {
        await showError(err, "Unable to cancel ICS");
        lockBtn(cancelBtn, false);
      }
    });
  });
})();