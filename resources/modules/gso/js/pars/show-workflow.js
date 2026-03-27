import Swal from "sweetalert2";
import "sweetalert2/dist/sweetalert2.min.css";

(function () {
  const cfg = window.__parShow || {};
  const csrf = cfg.csrf || "";

  async function postJson(url) {
    const res = await fetch(url, {
      method: "POST",
      headers: {
        "X-CSRF-TOKEN": csrf,
        Accept: "application/json",
        "Content-Type": "application/json",
      },
      body: JSON.stringify({}),
    });

    let data = null;
    try {
      data = await res.json();
    } catch (e) {
      data = null;
    }

    return { res, data };
  }

  function buildHtmlList(lines = []) {
    const safe = (s) =>
      String(s ?? "")
        .replaceAll("&", "&amp;")
        .replaceAll("<", "&lt;")
        .replaceAll(">", "&gt;");

    const items = (lines || []).slice(0, 50).map((x) => `<li>${safe(x)}</li>`).join("");
    return `<ul style="text-align:left; margin: 0; padding-left: 18px;">${items}</ul>`;
  }

  function extractPrettyErrors(data) {
    const errs = data?.errors || {};
    const notInPool = Array.isArray(errs.not_in_pool) ? errs.not_in_pool : [];
    const fundMismatch = Array.isArray(errs.fund_cluster_mismatch) ? errs.fund_cluster_mismatch : [];

    const other = [];
    if (!notInPool.length && !fundMismatch.length && errs && typeof errs === "object") {
      for (const k of Object.keys(errs)) {
        const v = errs[k];
        if (Array.isArray(v)) other.push(...v);
      }
    }

    return { notInPool, fundMismatch, other };
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
      delete btn.dataset._oldText;
    }
  }

  async function handleWorkflow(form, kind) {
    const endpoint = form.dataset.endpoint || form.action;
    if (!endpoint) return;

    const page = window.__parEditPage || {};
    const dirtyCount = Number(page.getDirtyCount?.() || 0);
    const itemCount = Number(page.getItemCount?.() || 0);
    const actionBtn = form.querySelector('button[type="submit"]');

    if (kind === "submit" && itemCount <= 0) {
      await Swal.fire({
        icon: "warning",
        title: "No items to submit",
        text: "Add at least one PAR item before submitting.",
      });
      return;
    }

    const title = kind === "finalize"
      ? "Finalize PAR?"
      : kind === "reopen"
        ? "Reopen PAR?"
        : dirtyCount > 0
          ? "Save and Submit PAR?"
          : "Submit PAR?";

    const html = kind === "finalize"
      ? `
        <div style="text-align:left">
          <div>Finalizing will generate the PAR number (if missing) and create issuance events.</div>
        </div>
      `
      : kind === "reopen"
        ? `
        <div style="text-align:left">
          <div>This will move the PAR back to <b>DRAFT</b> so the header and items can be edited again.</div>
          <div class="text-xs" style="color:#8c9097;margin-top:6px">
            No issuance events will be posted by reopening.
          </div>
        </div>
      `
        : `
        <div style="text-align:left">
          <div>${
            dirtyCount > 0
              ? `This will save <b>${dirtyCount}</b> pending header change${dirtyCount === 1 ? "" : "s"} and then submit the PAR.`
              : "This will move the PAR forward for finalization."
          }</div>
          <div class="text-xs" style="color:#8c9097;margin-top:6px">
            After submission, you can no longer edit header details or add/remove items until the PAR is reopened.
          </div>
        </div>
      `;

    const ok = await Swal.fire({
      title,
      html,
      icon: "question",
      showCancelButton: true,
      confirmButtonText: kind === "finalize"
        ? "Yes, finalize"
        : kind === "reopen"
          ? "Reopen PAR"
          : dirtyCount > 0
            ? "Save and Submit"
            : "Yes, submit",
      cancelButtonText: "Cancel",
    });

    if (!ok.isConfirmed) return;

    try {
      if (kind === "submit") {
        lockBtn(actionBtn, true, dirtyCount > 0 ? "Saving..." : "Submitting...");

        if (dirtyCount > 0 && page.saveChanges) {
          const saved = await page.saveChanges({
            silentSuccess: true,
            silentNoChanges: true,
          });

          if (!saved) {
            lockBtn(actionBtn, false);
            page.refreshDirtyState?.();
            return;
          }

          lockBtn(actionBtn, true, "Submitting...");
        }
      } else if (kind === "reopen") {
        lockBtn(actionBtn, true, "Reopening...");
      }

      const { res, data } = await postJson(endpoint);

      if (res.ok) {
        await Swal.fire({
          title: kind === "finalize" ? "Finalized" : kind === "reopen" ? "Reopened" : "Submitted",
          text: data?.message || (kind === "finalize"
            ? "PAR finalized successfully."
            : kind === "reopen"
              ? "PAR is back in draft and can be edited again."
              : dirtyCount > 0
                ? "PAR was saved and submitted successfully."
                : "PAR submitted successfully."),
          icon: "success",
          timer: 1200,
          showConfirmButton: false,
        });
        window.location.reload();
        return;
      }

      if (res.status === 422 || res.status === 409 || res.status === 403) {
        const msg = data?.message;
        const { notInPool, fundMismatch, other } = extractPrettyErrors(data);

        if (notInPool.length || fundMismatch.length || other.length) {
          const blocks = [];

          if (fundMismatch.length) {
            blocks.push(`<div style="font-weight:600; text-align:left; margin-bottom:6px;">Fund Cluster mismatch:</div>`);
            blocks.push(buildHtmlList(fundMismatch));
          }

          if (notInPool.length) {
            blocks.push(`<div style="font-weight:600; text-align:left; margin:12px 0 6px;">Items not in GSO pool:</div>`);
            blocks.push(buildHtmlList(notInPool));
          }

          if (!fundMismatch.length && !notInPool.length && other.length) {
            blocks.push(buildHtmlList(other));
          }

          await Swal.fire({
            title: "Cannot proceed",
            html: blocks.join(""),
            icon: "error",
          });
          if (kind === "submit") {
            lockBtn(actionBtn, false);
            page.refreshDirtyState?.();
          } else if (kind === "reopen") {
            lockBtn(actionBtn, false);
          }
          return;
        }

        await Swal.fire({
          title: "Cannot proceed",
          text: msg || "Unable to proceed. Please review the details.",
          icon: "error",
        });
        if (kind === "submit") {
          lockBtn(actionBtn, false);
          page.refreshDirtyState?.();
        } else if (kind === "reopen") {
          lockBtn(actionBtn, false);
        }
        return;
      }

      await Swal.fire({
        title: "Error",
        text: data?.message || `Request failed (HTTP ${res.status}).`,
        icon: "error",
      });
      if (kind === "submit") {
        lockBtn(actionBtn, false);
        page.refreshDirtyState?.();
      } else if (kind === "reopen") {
        lockBtn(actionBtn, false);
      }
    } catch (err) {
      await Swal.fire({
        title: "Network error",
        text: "Please try again.",
        icon: "error",
      });
      if (kind === "submit") {
        lockBtn(actionBtn, false);
        page.refreshDirtyState?.();
      } else if (kind === "reopen") {
        lockBtn(actionBtn, false);
      }
    }
  }

  document.addEventListener("submit", async (e) => {
    const form = e.target;
    if (!(form instanceof HTMLFormElement)) return;

    if (form.dataset.action === "par-submit-form") {
      e.preventDefault();
      await handleWorkflow(form, "submit");
    }

    if (form.dataset.action === "par-reopen-form") {
      e.preventDefault();
      await handleWorkflow(form, "reopen");
    }

    if (form.dataset.action === "par-finalize-form") {
      e.preventDefault();
      await handleWorkflow(form, "finalize");
    }
  });
})();
