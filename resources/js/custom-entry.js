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
  });
})();
