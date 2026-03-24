import { bootPageLoaders } from "../../../core/js/entry/page-loader";

const gsoPageLoaders = [
  {
    id: "asset-types-table",
    load: () =>
      Promise.all([
        import("./asset-types/index.js"),
        import("./asset-types/modal.js"),
      ]),
    errorMessage: "Failed to load GSO asset types modules",
  },
  {
    id: "asset-categories-table",
    load: () =>
      Promise.all([
        import("./asset-categories/index.js"),
        import("./asset-categories/modal.js"),
      ]),
    errorMessage: "Failed to load GSO asset categories modules",
  },
  {
    id: "departments-table",
    load: () =>
      Promise.all([
        import("./departments/index.js"),
        import("./departments/modal.js"),
      ]),
    errorMessage: "Failed to load GSO departments modules",
  },
  {
    id: "fund-clusters-table",
    load: () =>
      Promise.all([
        import("./fund-clusters/index.js"),
        import("./fund-clusters/modal.js"),
      ]),
    errorMessage: "Failed to load GSO fund clusters modules",
  },
  {
    id: "fund-sources-table",
    load: () =>
      Promise.all([
        import("./fund-sources/index.js"),
        import("./fund-sources/modal.js"),
      ]),
    errorMessage: "Failed to load GSO fund sources modules",
  },
  {
    id: "accountable-officers-table",
    load: () =>
      Promise.all([
        import("./accountable-officers/index.js"),
        import("./accountable-officers/modal.js"),
      ]),
    errorMessage: "Failed to load GSO accountable officers modules",
  },
  {
    id: "gso-items-table",
    load: () =>
      Promise.all([
        import("./items/index.js"),
        import("./items/modal.js"),
      ]),
    errorMessage: "Failed to load GSO items modules",
  },
  {
    id: "gso-air-table",
    load: () => import("./air/index.js"),
    errorMessage: "Failed to load GSO AIR index module",
  },
  {
    id: "gso-air-edit-page",
    load: () =>
      Promise.all([
        import("./air/edit.js"),
        import("./air/edit-files.js"),
        import("./air/edit-items.js"),
      ]),
    errorMessage: "Failed to load GSO AIR edit module",
  },
  {
    id: "gso-air-inspect-page",
    load: () =>
      Promise.all([
        import("./air/inspect.js"),
        import("./air/generate-ris.js"),
      ]),
    errorMessage: "Failed to load GSO AIR inspect module",
  },
  {
    id: "ris-table",
    load: () =>
      Promise.all([
        import("./ris/index.js"),
        import("./ris/filters.js"),
      ]),
    errorMessage: "Failed to load GSO RIS index modules",
  },
  {
    id: "risForm",
    load: () =>
      Promise.all([
        import("./ris/edit.js"),
        import("./ris/edit-items.js"),
        import("./ris/workflow.js"),
      ]),
    errorMessage: "Failed to load GSO RIS edit modules",
  },
  {
    id: "gso-inventory-items-table",
    load: () =>
      Promise.all([
        import("./inventory-items/index.js"),
        import("./inventory-items/modal.js"),
        import("./inventory-items/files.js"),
        import("./inventory-items/events.js"),
      ]),
    errorMessage: "Failed to load GSO inventory items modules",
  },
  {
    id: "gso-inspections-table",
    load: () =>
      Promise.all([
        import("./inspections/index.js"),
        import("./inspections/modal.js"),
        import("./inspections/photos.js"),
      ]),
    errorMessage: "Failed to load GSO inspections modules",
  },
  {
    id: "gso-stocks-table",
    load: () => import("./stocks/index.js"),
    errorMessage: "Failed to load GSO stocks module",
  },
];

export function bootGsoPages() {
  bootPageLoaders(gsoPageLoaders);
}
