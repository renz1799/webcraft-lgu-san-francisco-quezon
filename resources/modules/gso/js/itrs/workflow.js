import Swal from "sweetalert2";
import "sweetalert2/dist/sweetalert2.min.css";

(function () {
  "use strict";

  const cfg = window.__itrEdit || {};

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
              ? "Please review the ITR details and selected items before continuing."
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
            <div style="margin-bottom:8px;">${esc(err?.message || "Please review the current ITR details.")}</div>
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
    const submitBtn = document.getElementById("itrSubmitBtn");
    const reopenBtn = document.getElementById("itrReopenBtn");
    const finalizeBtn = document.getElementById("itrFinalizeBtn");
    const cancelBtn = document.getElementById("itrCancelBtn");

    if (!submitBtn && !reopenBtn && !finalizeBtn && !cancelBtn) {
      return;
    }

    submitBtn?.addEventListener("click", async function () {
      const page = window.__itrEditPage || {};
      const dirtyCount = Number(page.getDirtyCount?.() || 0);
      const itemCount = Number(page.getItemCount?.() ?? cfg.initialItemCount ?? 0);

      if (itemCount <= 0) {
        await Swal.fire({
          icon: "warning",
          title: "No items to submit",
          text: "Add at least one ITR item before submitting.",
        });
        return;
      }

      const confirm = await Swal.fire({
        icon: "question",
        title: dirtyCount > 0 ? "Save and Submit ITR?" : "Submit ITR?",
        html: `
          <div style="text-align:left">
            <div>${
              dirtyCount > 0
                ? `This will save <b>${dirtyCount}</b> pending header change${dirtyCount === 1 ? "" : "s"} and then submit the ITR.`
                : "This will move the ITR forward for transfer finalization."
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
          text: dirtyCount > 0 ? "ITR was saved and submitted." : "ITR is now submitted.",
          timer: 1000,
          showConfirmButton: false,
        });

        window.location.reload();
      } catch (err) {
        await showError(err, "Unable to submit ITR");
        lockBtn(submitBtn, false);
        page.refreshDirtyState?.();
      }
    });

    reopenBtn?.addEventListener("click", async function () {
      const confirm = await Swal.fire({
        icon: "question",
        title: "Reopen ITR?",
        html: `
          <div style="text-align:left">
            <div>This will move the ITR back to <b>DRAFT</b> so the transfer details and items can be edited again.</div>
            <div class="text-xs" style="color:#8c9097;margin-top:6px">
              No transfer events will be posted by reopening.
            </div>
          </div>
        `,
        showCancelButton: true,
        confirmButtonText: "Reopen ITR",
        cancelButtonText: "Cancel",
      });

      if (!confirm.isConfirmed) return;

      try {
        lockBtn(reopenBtn, true, "Reopening...");
        await postJson(cfg.reopenUrl, {});

        await Swal.fire({
          icon: "success",
          title: "Reopened",
          text: "ITR is back in draft and can be edited again.",
          timer: 1000,
          showConfirmButton: false,
        });

        window.location.reload();
      } catch (err) {
        await showError(err, "Unable to reopen ITR");
        lockBtn(reopenBtn, false);
      }
    });

    finalizeBtn?.addEventListener("click", async function () {
      const confirm = await Swal.fire({
        icon: "warning",
        title: "Finalize ITR?",
        html: `
          <div style="text-align:left">
            <div>This will generate the ITR number and transfer the selected semi-expendable inventory items to the destination department, fund source, and accountable officer.</div>
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
          text: "ITR is finalized and the selected inventory items were transferred.",
          timer: 1100,
          showConfirmButton: false,
        });

        window.location.reload();
      } catch (err) {
        await showError(err, "Unable to finalize ITR");
        lockBtn(finalizeBtn, false);
      }
    });

    cancelBtn?.addEventListener("click", async function () {
      const result = await Swal.fire({
        icon: "warning",
        title: "Cancel ITR?",
        input: "textarea",
        inputLabel: "Reason (optional)",
        inputPlaceholder: "Add a short reason for cancellation...",
        inputAttributes: { rows: 4 },
        showCancelButton: true,
        confirmButtonText: "Cancel ITR",
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
          text: "ITR is now cancelled.",
          timer: 1000,
          showConfirmButton: false,
        });

        window.location.reload();
      } catch (err) {
        await showError(err, "Unable to cancel ITR");
        lockBtn(cancelBtn, false);
      }
    });
  });
})();


