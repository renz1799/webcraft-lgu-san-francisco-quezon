import { bootCorePages } from "./entry/core-pages";
import { bootModulePages } from "./entry/module-pages";
import { onReady } from "./entry/page-loader";

(function () {
  "use strict";

  onReady(function () {
    bootCorePages();
    bootModulePages();
  });
})();
