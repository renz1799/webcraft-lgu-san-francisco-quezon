import Swal from "sweetalert2";

(function () {
  "use strict";

  function onReady(fn) {
    if (document.readyState === "loading") {
      document.addEventListener("DOMContentLoaded", fn);
      return;
    }

    fn();
  }

  function getCsrfToken() {
    return document.querySelector('meta[name="csrf-token"]')?.content || "";
  }

  function getResponseError(response, data) {
    if (data?.message) {
      return String(data.message);
    }

    if (response.status === 403) {
      return "You don't have permission to do that.";
    }

    if (response.status === 419) {
      return "Security token expired. Please refresh the page and try again.";
    }

    return response.statusText || "Request failed";
  }

  function bindExtensionPostButtons() {
    document.querySelectorAll(".js-task-extension-post").forEach((btn) => {
      btn.addEventListener("click", async function () {
        const endpoint = String(btn.dataset.taskPostEndpoint || "").trim();
        if (!endpoint) {
          return;
        }

        const confirmText = String(btn.dataset.taskPostConfirm || "Proceed with this action?");
        const successText = String(btn.dataset.taskPostSuccess || "Action completed successfully.");
        const redirectKey = String(btn.dataset.taskPostRedirectKey || "redirect_url");

        const ask = await Swal.fire({
          title: "Are you sure?",
          text: confirmText,
          icon: "question",
          showCancelButton: true,
          confirmButtonText: "Yes, continue",
          cancelButtonText: "Cancel",
        });

        if (!ask.isConfirmed) {
          return;
        }

        const originalDisabled = btn.hasAttribute("disabled");
        btn.setAttribute("disabled", "disabled");

        try {
          const response = await fetch(endpoint, {
            method: "POST",
            headers: {
              "X-CSRF-TOKEN": getCsrfToken(),
              Accept: "application/json",
              "X-Requested-With": "XMLHttpRequest",
            },
          });

          const data = await response.json().catch(() => ({}));

          if (!response.ok) {
            throw new Error(getResponseError(response, data));
          }

          await Swal.fire({
            icon: "success",
            title: "Success",
            text: data?.message || successText,
            timer: 1500,
            showConfirmButton: false,
          });

          const redirectUrl = data?.[redirectKey];
          if (redirectUrl) {
            window.location.href = String(redirectUrl);
            return;
          }

          window.location.reload();
        } catch (error) {
          await Swal.fire({
            icon: "error",
            title: "Error",
            text: error?.message || "Something went wrong.",
          });
        } finally {
          if (!originalDisabled) {
            btn.removeAttribute("disabled");
          }
        }
      });
    });
  }

  onReady(() => {
    if (!document.getElementById("task-show-page")) {
      return;
    }

    bindExtensionPostButtons();

    // -------------------------
    // Status Update
    // -------------------------
    document.querySelectorAll(".js-task-status-form").forEach((form) => {
      form.addEventListener("submit", function (e) {
        e.preventDefault();

        Swal.fire({
          title: "Update task status?",
          text: "This will update the task and add it to the timeline.",
          icon: "question",
          showCancelButton: true,
          confirmButtonText: "Yes, update",
          cancelButtonText: "Cancel",
        }).then((result) => {
          if (result.isConfirmed) {
            submitForm(form, {
              successText: "Task updated successfully.",
            });
          }
        });
      });
    });

    // -------------------------
    // Comment
    // -------------------------
    document.querySelectorAll(".js-task-comment-form").forEach((form) => {
      form.addEventListener("submit", function (e) {
        e.preventDefault();

        submitForm(form, {
          successText: "Comment posted successfully.",
        });
      });
    });

    // -------------------------
    // Reassign
    // -------------------------
    document.querySelectorAll(".js-task-reassign-form").forEach((form) => {
      form.addEventListener("submit", function (e) {
        e.preventDefault();

        const sel = form.querySelector('[name="assignee_user_id"]');
        const newAssignee = (sel?.value || "").trim();

        if (!newAssignee) {
          Swal.fire({
            icon: "warning",
            title: "Select an assignee",
            text: "Please select a user to reassign this task to.",
          });
          return;
        }

        Swal.fire({
          title: "Reassign this task?",
          text: "This will change the assignee and add it to the timeline.",
          icon: "question",
          showCancelButton: true,
          confirmButtonText: "Yes, reassign",
          cancelButtonText: "Cancel",
          confirmButtonColor: "#f59e0b",
        }).then((result) => {
          if (result.isConfirmed) {
            submitForm(form, {
              successText: "Task reassigned successfully.",
            });
          }
        });
      });
    });

    // -------------------------
    // Shared submit handler
    // -------------------------
    function submitForm(form, opts = {}) {
      const formData = new FormData(form);

      fetch(form.action, {
        method: "POST",
        headers: {
          "X-CSRF-TOKEN": formData.get("_token"),
          Accept: "application/json",
        },
        body: formData,
      })
        .then(async (response) => {
          if (!response.ok) {
            let data = null;
            try {
              data = await response.json();
            } catch (e) {
              data = null;
            }
            throw new Error(getResponseError(response, data));
          }
          return response.json().catch(() => ({}));
        })
        .then(() => {
          Swal.fire({
            icon: "success",
            title: "Success",
            text: opts.successText || "Task updated successfully.",
            timer: 1500,
            showConfirmButton: false,
          }).then(() => {
            window.location.reload();
          });
        })
        .catch((error) => {
          Swal.fire({
            icon: "error",
            title: "Error",
            text: error.message || "Something went wrong.",
          });
        });
    }
  });
})();
