import Swal from "sweetalert2";
import "sweetalert2/dist/sweetalert2.min.css";

(function () {
  "use strict";

  const cfg = window.__wmrEdit || {};

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
              ? "Please review the WMR details and selected disposal items before continuing."
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
            <div style="margin-bottom:8px;">${esc(err?.message || "Please review the current WMR details.")}</div>
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
    const submitBtn = document.getElementById("wmrSubmitBtn");
    const approveBtn = document.getElementById("wmrApproveBtn");
    const reopenBtn = document.getElementById("wmrReopenBtn");
    const finalizeBtn = document.getElementById("wmrFinalizeBtn");
    const cancelBtn = document.getElementById("wmrCancelBtn");

    if (!submitBtn && !approveBtn && !reopenBtn && !finalizeBtn && !cancelBtn) {
      return;
    }

    submitBtn?.addEventListener("click", async function () {
      const page = window.__wmrEditPage || {};
      const dirtyCount = Number(page.getDirtyCount?.() || 0);
      const itemCount = Number(page.getItemCount?.() ?? cfg.initialItemCount ?? 0);

      if (itemCount <= 0) {
        await Swal.fire({
          icon: "warning",
          title: "No disposal items",
          text: "Add at least one disposal item before submitting the WMR.",
        });
        return;
      }

      const confirm = await Swal.fire({
        icon: "question",
        title: dirtyCount > 0 ? "Save and Submit WMR?" : "Submit WMR?",
        html: `
          <div style="text-align:left">
            <div>${
              dirtyCount > 0
                ? `This will save <b>${dirtyCount}</b> pending header change${dirtyCount === 1 ? "" : "s"} and then submit the WMR.`
                : "This will move the WMR forward for disposal approval."
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
          text: dirtyCount > 0 ? "WMR was saved and submitted." : "WMR is now submitted.",
          timer: 1000,
          showConfirmButton: false,
        });

        window.location.reload();
      } catch (err) {
        await showError(err, "Unable to submit WMR");
        lockBtn(submitBtn, false);
        page.refreshDirtyState?.();
      }
    });

    approveBtn?.addEventListener("click", async function () {
      const confirm = await Swal.fire({
        icon: "question",
        title: "Approve Disposal?",
        html: `
          <div style="text-align:left">
            <div>This will mark the WMR as approved for disposal and prepare it for final certification.</div>
            <div class="text-xs" style="color:#8c9097;margin-top:6px">
              Status will become <b>APPROVED</b>.
            </div>
          </div>
        `,
        showCancelButton: true,
        confirmButtonText: "Approve Disposal",
        cancelButtonText: "Cancel",
      });

      if (!confirm.isConfirmed) return;

      try {
        lockBtn(approveBtn, true, "Approving...");
        await postJson(cfg.approveUrl, {});

        await Swal.fire({
          icon: "success",
          title: "Approved",
          text: "WMR is approved for disposal.",
          timer: 1000,
          showConfirmButton: false,
        });

        window.location.reload();
      } catch (err) {
        await showError(err, "Unable to approve WMR");
        lockBtn(approveBtn, false);
      }
    });

    reopenBtn?.addEventListener("click", async function () {
      const confirm = await Swal.fire({
        icon: "question",
        title: "Reopen WMR?",
        html: `
          <div style="text-align:left">
            <div>This will move the WMR back to <b>DRAFT</b> so the disposal details and lines can be edited again.</div>
            <div class="text-xs" style="color:#8c9097;margin-top:6px">
              No disposal events will be posted by reopening.
            </div>
          </div>
        `,
        showCancelButton: true,
        confirmButtonText: "Reopen WMR",
        cancelButtonText: "Cancel",
      });

      if (!confirm.isConfirmed) return;

      try {
        lockBtn(reopenBtn, true, "Reopening...");
        await postJson(cfg.reopenUrl, {});

        await Swal.fire({
          icon: "success",
          title: "Reopened",
          text: "WMR is back in draft and can be edited again.",
          timer: 1000,
          showConfirmButton: false,
        });

        window.location.reload();
      } catch (err) {
        await showError(err, "Unable to reopen WMR");
        lockBtn(reopenBtn, false);
      }
    });

    finalizeBtn?.addEventListener("click", async function () {
      const confirm = await Swal.fire({
        icon: "warning",
        title: "Finalize Disposal?",
        html: `
          <div style="text-align:left">
            <div>This will generate the WMR number and mark the selected items as disposed or transferred, based on each line's disposal method.</div>
            <div class="text-xs" style="color:#8c9097;margin-top:6px">
              Status will become <b>DISPOSED</b>.
            </div>
          </div>
        `,
        showCancelButton: true,
        confirmButtonText: "Finalize Disposal",
        cancelButtonText: "Cancel",
      });

      if (!confirm.isConfirmed) return;

      try {
        lockBtn(finalizeBtn, true, "Finalizing...");
        await postJson(cfg.finalizeUrl, {});

        await Swal.fire({
          icon: "success",
          title: "Finalized",
          text: "WMR is finalized and the selected items were updated.",
          timer: 1100,
          showConfirmButton: false,
        });

        window.location.reload();
      } catch (err) {
        await showError(err, "Unable to finalize WMR");
        lockBtn(finalizeBtn, false);
      }
    });

    cancelBtn?.addEventListener("click", async function () {
      const result = await Swal.fire({
        icon: "warning",
        title: "Cancel WMR?",
        input: "textarea",
        inputLabel: "Reason (optional)",
        inputPlaceholder: "Add a short reason for cancellation...",
        inputAttributes: { rows: 4 },
        showCancelButton: true,
        confirmButtonText: "Cancel WMR",
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
          text: "WMR is now cancelled.",
          timer: 1000,
          showConfirmButton: false,
        });

        window.location.reload();
      } catch (err) {
        await showError(err, "Unable to cancel WMR");
        lockBtn(cancelBtn, false);
      }
    });
  });
})();