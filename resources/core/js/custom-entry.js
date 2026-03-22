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
      import("../../modules/tasks/js/table.js"),
      import("../../modules/tasks/js/filters.js"),
      import("../../modules/tasks/js/actions.js"),
      import("../../modules/tasks/js/stats.js"),
    ]);
  }

  function loadTaskShow() {
    return import("../../modules/tasks/js/show.js");
  }

  function loadGsoAssetTypesIndex() {
    return Promise.all([
      import("../../modules/gso/js/asset-types/index.js"),
      import("../../modules/gso/js/asset-types/modal.js"),
    ]);
  }

  function loadGsoAssetCategoriesIndex() {
    return Promise.all([
      import("../../modules/gso/js/asset-categories/index.js"),
      import("../../modules/gso/js/asset-categories/modal.js"),
    ]);
  }

  function loadGsoDepartmentsIndex() {
    return Promise.all([
      import("../../modules/gso/js/departments/index.js"),
      import("../../modules/gso/js/departments/modal.js"),
    ]);
  }

  function loadGsoFundClustersIndex() {
    return Promise.all([
      import("../../modules/gso/js/fund-clusters/index.js"),
      import("../../modules/gso/js/fund-clusters/modal.js"),
    ]);
  }

  function loadGsoFundSourcesIndex() {
    return Promise.all([
      import("../../modules/gso/js/fund-sources/index.js"),
      import("../../modules/gso/js/fund-sources/modal.js"),
    ]);
  }

  function loadGsoAccountableOfficersIndex() {
    return Promise.all([
      import("../../modules/gso/js/accountable-officers/index.js"),
      import("../../modules/gso/js/accountable-officers/modal.js"),
    ]);
  }

  function loadGsoItemsIndex() {
    return Promise.all([
      import("../../modules/gso/js/items/index.js"),
      import("../../modules/gso/js/items/modal.js"),
    ]);
  }

  function loadGsoAirIndex() {
    return import("../../modules/gso/js/air/index.js");
  }

  function loadGsoAirEdit() {
    return Promise.all([
      import("../../modules/gso/js/air/edit.js"),
      import("../../modules/gso/js/air/edit-files.js"),
      import("../../modules/gso/js/air/edit-items.js"),
    ]);
  }

  function loadGsoAirInspect() {
    return import("../../modules/gso/js/air/inspect.js");
  }

  function loadGsoInventoryItemsIndex() {
    return Promise.all([
      import("../../modules/gso/js/inventory-items/index.js"),
      import("../../modules/gso/js/inventory-items/modal.js"),
      import("../../modules/gso/js/inventory-items/files.js"),
      import("../../modules/gso/js/inventory-items/events.js"),
    ]);
  }

  function loadGsoInspectionsIndex() {
    return Promise.all([
      import("../../modules/gso/js/inspections/index.js"),
      import("../../modules/gso/js/inspections/modal.js"),
      import("../../modules/gso/js/inspections/photos.js"),
    ]);
  }

  function loadGsoStocksIndex() {
    return import("../../modules/gso/js/stocks/index.js");
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

    if (document.getElementById("asset-types-table")) {
      loadGsoAssetTypesIndex().catch((err) => {
        console.error("Failed to load GSO asset types modules", err);
      });
    }

    if (document.getElementById("asset-categories-table")) {
      loadGsoAssetCategoriesIndex().catch((err) => {
        console.error("Failed to load GSO asset categories modules", err);
      });
    }

    if (document.getElementById("departments-table")) {
      loadGsoDepartmentsIndex().catch((err) => {
        console.error("Failed to load GSO departments modules", err);
      });
    }

    if (document.getElementById("fund-clusters-table")) {
      loadGsoFundClustersIndex().catch((err) => {
        console.error("Failed to load GSO fund clusters modules", err);
      });
    }

    if (document.getElementById("fund-sources-table")) {
      loadGsoFundSourcesIndex().catch((err) => {
        console.error("Failed to load GSO fund sources modules", err);
      });
    }

    if (document.getElementById("accountable-officers-table")) {
      loadGsoAccountableOfficersIndex().catch((err) => {
        console.error("Failed to load GSO accountable officers modules", err);
      });
    }

    if (document.getElementById("gso-items-table")) {
      loadGsoItemsIndex().catch((err) => {
        console.error("Failed to load GSO items modules", err);
      });
    }

    if (document.getElementById("gso-air-table")) {
      loadGsoAirIndex().catch((err) => {
        console.error("Failed to load GSO AIR index module", err);
      });
    }

    if (document.getElementById("gso-air-edit-page")) {
      loadGsoAirEdit().catch((err) => {
        console.error("Failed to load GSO AIR edit module", err);
      });
    }

    if (document.getElementById("gso-air-inspect-page")) {
      loadGsoAirInspect().catch((err) => {
        console.error("Failed to load GSO AIR inspect module", err);
      });
    }

    if (document.getElementById("gso-inventory-items-table")) {
      loadGsoInventoryItemsIndex().catch((err) => {
        console.error("Failed to load GSO inventory items modules", err);
      });
    }

    if (document.getElementById("gso-inspections-table")) {
      loadGsoInspectionsIndex().catch((err) => {
        console.error("Failed to load GSO inspections modules", err);
      });
    }

    if (document.getElementById("gso-stocks-table")) {
      loadGsoStocksIndex().catch((err) => {
        console.error("Failed to load GSO stocks module", err);
      });
    }
  });
})();
