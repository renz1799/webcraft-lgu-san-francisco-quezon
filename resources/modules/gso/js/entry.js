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
    id: "par-table",
    load: () =>
      Promise.all([
        import("./pars/table.js"),
        import("./pars/filters.js"),
      ]),
    errorMessage: "Failed to load GSO PAR index modules",
  },
  {
    id: "ics-table",
    load: () =>
      Promise.all([
        import("./ics/table.js"),
        import("./ics/filters.js"),
        import("./ics/actions.js"),
      ]),
    errorMessage: "Failed to load GSO ICS index modules",
  },
  {
    id: "icsForm",
    load: () =>
      Promise.all([
        import("./ics/edit.js"),
        import("./ics/edit-items.js"),
        import("./ics/workflow.js"),
      ]),
    errorMessage: "Failed to load GSO ICS edit modules",
  },
  {
    id: "ptr-table",
    load: () =>
      Promise.all([
        import("./ptrs/table.js"),
        import("./ptrs/filters.js"),
        import("./ptrs/actions.js"),
      ]),
    errorMessage: "Failed to load GSO PTR index modules",
  },
  {
    id: "ptrForm",
    load: () =>
      Promise.all([
        import("./ptrs/edit.js"),
        import("./ptrs/edit-items.js"),
        import("./ptrs/workflow.js"),
      ]),
    errorMessage: "Failed to load GSO PTR edit modules",
  },
  {
    id: "itr-table",
    load: () =>
      Promise.all([
        import("./itrs/table.js"),
        import("./itrs/filters.js"),
        import("./itrs/actions.js"),
      ]),
    errorMessage: "Failed to load GSO ITR index modules",
  },
  {
    id: "itrForm",
    load: () =>
      Promise.all([
        import("./itrs/edit.js"),
        import("./itrs/edit-items.js"),
        import("./itrs/workflow.js"),
      ]),
    errorMessage: "Failed to load GSO ITR edit modules",
  },
  {
    id: "wmr-table",
    load: () =>
      Promise.all([
        import("./wmrs/table.js"),
        import("./wmrs/filters.js"),
        import("./wmrs/actions.js"),
      ]),
    errorMessage: "Failed to load GSO WMR index modules",
  },
  {
    id: "wmrForm",
    load: () =>
      Promise.all([
        import("./wmrs/edit.js"),
        import("./wmrs/edit-items.js"),
        import("./wmrs/workflow.js"),
      ]),
    errorMessage: "Failed to load GSO WMR edit modules",
  },
  {
    id: "parShowPage",
    load: () =>
      Promise.all([
        import("./pars/show-edit.js"),
        import("./pars/show-items.js"),
        import("./pars/show-items-suggest.js"),
        import("./pars/show-workflow.js"),
      ]),
    errorMessage: "Failed to load GSO PAR show modules",
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
