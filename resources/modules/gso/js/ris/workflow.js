import Swal from "sweetalert2";
import "sweetalert2/dist/sweetalert2.min.css";

(function () {
  "use strict";

  const config = window.__ris || {};

  function onReady(fn) {
    if (document.readyState === "loading") {
      document.addEventListener("DOMContentLoaded", fn);
      return;
    }

    fn();
  }

  function getCsrf() {
    return document.querySelector('meta[name="csrf-token"]')?.content || config.csrf || "";
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
          ? "You do not have permission to perform this action."
          : response.status === 422
          ? "Please complete the required RIS fields before continuing."
          : `Request failed (HTTP ${response.status}).`),
      data,
    };
  }

  async function postJson(url, body = {}) {
    const response = await fetch(url, {
      method: "POST",
      headers: {
        Accept: "application/json",
        "Content-Type": "application/json",
        "X-CSRF-TOKEN": getCsrf(),
      },
      body: JSON.stringify(body),
    });

    if (!response.ok) {
      throw await parseErrorResponse(response);
    }

    return await response.json().catch(() => ({}));
  }

  function lockButton(button, locked, labelWhenLocked) {
    if (!button) return;

    button.disabled = !!locked;
    if (labelWhenLocked) {
      button.dataset._oldText = button.dataset._oldText || button.textContent;
    }

    if (locked && labelWhenLocked) {
      button.textContent = labelWhenLocked;
    } else if (!locked && button.dataset._oldText) {
      button.textContent = button.dataset._oldText;
    }
  }

  function collectValidationMessages(errors) {
    const messages = [];

    Object.values(errors || {}).forEach((value) => {
      const list = Array.isArray(value) ? value : [value];
      list.forEach((message) => {
        const text = String(message || "").trim();
        if (text) {
          messages.push(text);
        }
      });
    });

    return messages;
  }

  async function showValidationErrors(title, errors) {
    const messages = collectValidationMessages(errors);
    if (messages.length <= 0) {
      return false;
    }

    await Swal.fire({
      icon: "warning",
      title,
      html: `
        <div style="text-align:left">
          <ul style="margin:0; padding-left:18px;">
            ${messages.map((message) => `<li>${message}</li>`).join("")}
          </ul>
        </div>
      `,
    });

    return true;
  }

  onReady(function () {
    const submitButton = document.getElementById("risSubmitBtn");
    const approveButton = document.getElementById("risApproveBtn");
    const rejectButton = document.getElementById("risRejectBtn");
    const reopenButton = document.getElementById("risReopenBtn");
    const revertButton = document.getElementById("risRevertBtn");

    if (!submitButton && !approveButton && !rejectButton && !reopenButton && !revertButton) {
      return;
    }

    if (submitButton) {
      submitButton.addEventListener("click", async () => {
        const editor = window.__risEditPage || null;
        const dirtyCount = Number(editor?.getDirtyCount?.() || 0);
        const itemCount = Number(editor?.getItemCount?.() || 0);

        if (itemCount <= 0) {
          await Swal.fire({
            icon: "warning",
            title: "No items to submit",
            text: "Add at least one RIS item before submitting.",
          });
          return;
        }

        const result = await Swal.fire({
          icon: "question",
          title: dirtyCount > 0 ? "Save and Submit RIS?" : "Submit RIS?",
          html: `
            <div style="text-align:left">
              <div>${
                dirtyCount > 0
                  ? `This will save <b>${dirtyCount}</b> pending header change${dirtyCount === 1 ? "" : "s"} and then submit the RIS.`
                  : "This will lock editing and forward it for approval."
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

        if (!result.isConfirmed) return;

        try {
          lockButton(submitButton, true, dirtyCount > 0 ? "Saving..." : "Submitting...");

          if (dirtyCount > 0 && editor?.saveChanges) {
            const saved = await editor.saveChanges({
              silentSuccess: true,
              silentNoChanges: true,
            });

            if (!saved) {
              lockButton(submitButton, false);
              editor?.refreshDirtyState?.();
              return;
            }

            lockButton(submitButton, true, "Submitting...");
          }

          await postJson(config.submitUrl, {});

          await Swal.fire({
            icon: "success",
            title: "Submitted",
            text: dirtyCount > 0 ? "RIS was saved and submitted." : "RIS is now submitted.",
            timer: 900,
            showConfirmButton: false,
          });

          window.location.reload();
        } catch (error) {
          const handled =
            Number(error?.status || 0) === 422
              ? await showValidationErrors("Complete the RIS first", error?.data?.errors)
              : false;

          if (!handled) {
            await Swal.fire({
              icon: "error",
              title: "Error",
              text: error.message || "Failed to submit.",
            });
          }

          lockButton(submitButton, false);
          editor?.refreshDirtyState?.();
        }
      });
    }

    if (approveButton) {
      approveButton.addEventListener("click", async () => {
        const result = await Swal.fire({
          icon: "warning",
          title: "Issue RIS?",
          html: `
            <div style="text-align:left">
              <div>This will issue the RIS and deduct stocks based on the RIS items.</div>
              <div class="text-xs" style="color:#8c9097;margin-top:6px">
                Status will become <b>ISSUED</b>.
              </div>
            </div>
          `,
          showCancelButton: true,
          confirmButtonText: "Issue RIS",
          cancelButtonText: "Cancel",
        });

        if (!result.isConfirmed) return;

        try {
          lockButton(approveButton, true, "Issuing...");
          await postJson(config.approveUrl, {});

          await Swal.fire({
            icon: "success",
            title: "Issued",
            text: "RIS is now issued and stocks were deducted.",
            timer: 1100,
            showConfirmButton: false,
          });

          window.location.reload();
        } catch (error) {
          const handled =
            Number(error?.status || 0) === 422
              ? await showValidationErrors("RIS is not ready to issue", error?.data?.errors)
              : false;

          if (!handled) {
            await Swal.fire({
              icon: "error",
              title: "Error",
              text: error.message || "Failed to issue.",
            });
          }

          lockButton(approveButton, false);
        }
      });
    }

    if (rejectButton) {
      rejectButton.addEventListener("click", async () => {
        const result = await Swal.fire({
          icon: "warning",
          title: "Reject RIS",
          input: "textarea",
          inputLabel: "Reason (required)",
          inputPlaceholder: "Explain why this RIS should be rejected",
          inputAttributes: { rows: 4 },
          showCancelButton: true,
          confirmButtonText: "Reject",
          cancelButtonText: "Cancel",
          preConfirm: (value) => {
            const reason = String(value || "").trim();
            if (!reason) {
              Swal.showValidationMessage("Rejection reason is required.");
              return false;
            }
            return reason;
          },
        });

        if (!result.value) return;

        try {
          lockButton(rejectButton, true, "Rejecting...");
          await postJson(config.rejectUrl, { reason: result.value });

          await Swal.fire({
            icon: "success",
            title: "Rejected",
            text: "RIS has been rejected.",
            timer: 1100,
            showConfirmButton: false,
          });

          window.location.reload();
        } catch (error) {
          await Swal.fire({
            icon: "error",
            title: "Error",
            text: error.message || "Failed to reject.",
          });

          lockButton(rejectButton, false);
        }
      });
    }

    if (reopenButton) {
      reopenButton.addEventListener("click", async () => {
        const result = await Swal.fire({
          icon: "question",
          title: "Reopen RIS?",
          html: `
            <div style="text-align:left">
              <div>This will move the RIS back to <b>DRAFT</b> so it can be edited again.</div>
              <div class="text-xs" style="color:#8c9097;margin-top:6px">
                No stock movement will be changed.
              </div>
            </div>
          `,
          showCancelButton: true,
          confirmButtonText: "Reopen",
          cancelButtonText: "Cancel",
        });

        if (!result.isConfirmed) return;

        try {
          lockButton(reopenButton, true, "Reopening...");
          await postJson(config.reopenUrl, {});

          await Swal.fire({
            icon: "success",
            title: "Reopened",
            text: "RIS is back to draft and can be edited again.",
            timer: 1000,
            showConfirmButton: false,
          });

          window.location.reload();
        } catch (error) {
          await Swal.fire({
            icon: "error",
            title: "Error",
            text: error.message || "Failed to reopen.",
          });

          lockButton(reopenButton, false);
        }
      });
    }

    if (revertButton) {
      revertButton.addEventListener("click", async () => {
        const result = await Swal.fire({
          icon: "warning",
          title: "Revert to Draft?",
          html: `
            <div style="text-align:left">
              <div>This will revert the RIS back to <b>DRAFT</b>.</div>
              <div class="text-xs" style="color:#8c9097;margin-top:6px">
                If this RIS was previously issued, stocks will be restored.
              </div>
            </div>
          `,
          showCancelButton: true,
          confirmButtonText: "Revert",
          cancelButtonText: "Cancel",
        });

        if (!result.isConfirmed) return;

        try {
          lockButton(revertButton, true, "Reverting...");
          await postJson(config.revertUrl, {});

          await Swal.fire({
            icon: "success",
            title: "Reverted",
            text: "RIS is back to draft.",
            timer: 1000,
            showConfirmButton: false,
          });

          window.location.reload();
        } catch (error) {
          await Swal.fire({
            icon: "error",
            title: "Error",
            text: error.message || "Failed to revert.",
          });

          lockButton(revertButton, false);
        }
      });
    }
  });
})();
