(function () {
  "use strict";

  function onReady(fn) {
    if (document.readyState === "loading") {
      document.addEventListener("DOMContentLoaded", fn);
      return;
    }

    fn();
  }

  function loadLoginLogs() {
    return Promise.all([
      import("./login-logs/table.js"),
      import("./login-logs/filters.js"),
    ]);
  }

  function loadAuditLogs() {
    return Promise.all([
      import("./audit-logs/table.js"),
      import("./audit-logs/filters.js"),
      import("./audit-logs/actions.js"),
    ]);
  }

  function loadAccessUsers() {
    return Promise.all([
      import("./access-users/table.js"),
      import("./access-users/filters.js"),
      import("./access-users/actions.js"),
    ]);
  }

  function loadAccessUsersEdit() {
    return import("./permissions.js");
  }

  function loadAccessRoles() {
    return Promise.all([
      import("./access-roles/table.js"),
      import("./access-roles/filters.js"),
      import("./access-roles/actions.js"),
    ]);
  }

  function loadAccessPermissions() {
    return Promise.all([
      import("./access-permissions/table.js"),
      import("./access-permissions/filters.js"),
      import("./access-permissions/actions.js"),
    ]);
  }

  function loadNotificationsIndex() {
    return import("./notifications/index.js");
  }

  function loadRegisterUserModal() {
    return import("./auth/register-modal.js");
  }

  function loadTasksIndex() {
    return Promise.all([
      import("./tasks/table.js"),
      import("./tasks/filters.js"),
      import("./tasks/actions.js"),
      import("./tasks/stats.js"),
    ]);
  }

  function loadTaskShow() {
    return import("./tasks/show.js");
  }

  onReady(function () {
    if (document.getElementById("login-table")) {
      loadLoginLogs().catch((err) => {
        console.error("Failed to load login logs modules", err);
      });
    }

    if (document.getElementById("audit-table")) {
      loadAuditLogs().catch((err) => {
        console.error("Failed to load audit logs modules", err);
      });
    }

    if (document.getElementById("users-table")) {
      loadAccessUsers().catch((err) => {
        console.error("Failed to load access users modules", err);
      });
    }

    if (document.getElementById("access-user-edit-page")) {
      loadAccessUsersEdit().catch((err) => {
        console.error("Failed to load access user edit module", err);
      });
    }

    if (document.getElementById("roles-table")) {
      loadAccessRoles().catch((err) => {
        console.error("Failed to load access roles modules", err);
      });
    }

    if (document.getElementById("permissions-table")) {
      loadAccessPermissions().catch((err) => {
        console.error("Failed to load access permissions modules", err);
      });
    }

    if (document.getElementById("notifications-index-page")) {
      loadNotificationsIndex().catch((err) => {
        console.error("Failed to load notifications index module", err);
      });
    }

    if (document.getElementById("registerUserModal")) {
      loadRegisterUserModal().catch((err) => {
        console.error("Failed to load register user modal module", err);
      });
    }

    if (document.getElementById("tasks-table")) {
      loadTasksIndex().catch((err) => {
        console.error("Failed to load tasks index modules", err);
      });
    }

    if (document.getElementById("task-show-page")) {
      loadTaskShow().catch((err) => {
        console.error("Failed to load task show module", err);
      });
    }
  });
})();
