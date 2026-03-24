import { bootGsoPages } from "../../../modules/gso/js/entry";

const modulePageBootstraps = [
  bootGsoPages,
];

export function bootModulePages() {
  modulePageBootstraps.forEach((bootPages) => {
    bootPages();
  });
}
