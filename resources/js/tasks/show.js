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

  onReady(() => {
    if (!document.getElementById("task-show-page")) {
      return;
    }

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
          confirmButtonColor: "#f59e0b", // optional: matches warning vibe
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
            // try JSON, fallback generic
            let data = null;
            try {
              data = await response.json();
            } catch (e) {
              data = null;
            }
            throw new Error(
              data?.message ||
                (response.status === 403
                  ? "You don’t have permission to do that."
                  : response.status === 419
                  ? "Security token expired. Please refresh the page and try again."
                  : "Request failed")
            );
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
