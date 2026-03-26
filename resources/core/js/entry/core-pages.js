import { bootPageLoaders } from "./page-loader";

const corePageLoaders = [
  {
    id: "login-table",
    load: () =>
      Promise.all([
        import("../login-logs/table.js"),
        import("../login-logs/filters.js"),
      ]),
    errorMessage: "Failed to load login logs modules",
  },
  {
    id: "audit-table",
    load: () =>
      Promise.all([
        import("../audit-logs/table.js"),
        import("../audit-logs/filters.js"),
        import("../audit-logs/actions.js"),
      ]),
    errorMessage: "Failed to load audit logs modules",
  },
  {
    id: "accountable-persons-table",
    load: () =>
      Promise.all([
        import("../accountable-persons/table.js"),
        import("../accountable-persons/filters.js"),
        import("../accountable-persons/actions.js"),
      ]),
    errorMessage: "Failed to load accountable persons modules",
  },
  {
    id: "users-table",
    load: () =>
      Promise.all([
        import("../access-users/table.js"),
        import("../access-users/filters.js"),
        import("../access-users/actions.js"),
      ]),
    errorMessage: "Failed to load access users modules",
  },
  {
    id: "access-user-edit-page",
    load: () => import("../permissions.js"),
    errorMessage: "Failed to load access user edit module",
  },
  {
    id: "user-onboarding-page",
    load: () => import("../access-users/onboarding.js"),
    errorMessage: "Failed to load access user onboarding module",
  },
  {
    id: "roles-table",
    load: () =>
      Promise.all([
        import("../access-roles/table.js"),
        import("../access-roles/filters.js"),
        import("../access-roles/actions.js"),
      ]),
    errorMessage: "Failed to load access roles modules",
  },
  {
    id: "permissions-table",
    load: () =>
      Promise.all([
        import("../access-permissions/table.js"),
        import("../access-permissions/filters.js"),
        import("../access-permissions/actions.js"),
      ]),
    errorMessage: "Failed to load access permissions modules",
  },
  {
    id: "notifications-index-page",
    load: () => import("../notifications/index.js"),
    errorMessage: "Failed to load notifications index module",
  },
  {
    id: "tasks-table",
    load: () =>
      Promise.all([
        import("../tasks/table.js"),
        import("../tasks/filters.js"),
        import("../tasks/actions.js"),
        import("../tasks/stats.js"),
      ]),
    errorMessage: "Failed to load tasks index modules",
  },
  {
    id: "task-show-page",
    load: () => import("../tasks/show.js"),
    errorMessage: "Failed to load task show module",
  },
];

export function bootCorePages() {
  bootPageLoaders(corePageLoaders);
}
