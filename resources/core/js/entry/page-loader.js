export function onReady(fn) {
  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", fn);
    return;
  }

  fn();
}

export function loadWhenPresent(id, load, errorMessage) {
  if (!document.getElementById(id)) {
    return;
  }

  load().catch((err) => {
    console.error(errorMessage, err);
  });
}

export function bootPageLoaders(pageLoaders) {
  pageLoaders.forEach(({ id, load, errorMessage }) => {
    loadWhenPresent(id, load, errorMessage);
  });
}
