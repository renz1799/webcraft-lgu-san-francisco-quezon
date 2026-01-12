import Waves from 'node-waves';
import SimpleBar from 'simplebar';
import 'simplebar/dist/simplebar.css';
import Pickr from '@simonwep/pickr';
import '@simonwep/pickr/dist/themes/classic.min.css';

(function () {
  "use strict";

  // page loader
  function hideLoader() {
    const loader = document.getElementById("loader");
    if (loader) loader.classList.add("!hidden");
  }
  window.addEventListener("load", hideLoader);

  // footer year
  const yearEl = document.getElementById("year");
  if (yearEl) yearEl.innerHTML = new Date().getFullYear();

  // waves
  try {
    Waves.attach('.btn-wave', ['waves-light']);
    Waves.init();
  } catch (e) {
    // ignore if Waves fails on some pages
  }

  // SimpleBar - sidebar
  const sidebarScroll = document.getElementById("sidebar-scroll");
  if (sidebarScroll) new SimpleBar(sidebarScroll, { autoHide: true });

  // SimpleBar - notifications
  const notifScroll = document.getElementById("header-notification-scroll");
  if (notifScroll) new SimpleBar(notifScroll, { autoHide: true });

  // SimpleBar - shortcuts
  const shortcutScroll = document.getElementById("header-shortcut-scroll");
  if (shortcutScroll) new SimpleBar(shortcutScroll, { autoHide: true });

  // Choices (only if Choices exists globally)
  document.addEventListener("DOMContentLoaded", function () {
    if (typeof window.Choices === "undefined") return;

    const genericExamples = document.querySelectorAll("[data-trigger]");
    genericExamples.forEach((element) => {
      new window.Choices(element, {
        allowHTML: true,
        placeholderValue: "This is a placeholder set in the config",
        searchPlaceholderValue: "Search",
      });
    });
  });

  // box remove
  document.querySelectorAll(".box-remove").forEach((ele) => {
    ele.addEventListener("click", function (e) {
      e.preventDefault();
      const box = ele.closest(".box");
      if (box) box.remove();
    });
  });

  // box fullscreen (template “box fullscreen”, not browser fullscreen)
  document.querySelectorAll(".box-fullscreen").forEach((ele) => {
    ele.addEventListener("click", function (e) {
      e.preventDefault();
      const box = ele.closest(".box");
      if (!box) return;
      box.classList.toggle("box-fullscreen");
      box.classList.remove("box-collapsed");
    });
  });

  // back to top (guard if not present)
  const scrollToTop = document.querySelector(".scrollToTop");
  if (scrollToTop) {
    window.onscroll = () => {
      const scrollTop = window.scrollY || window.pageYOffset;
      scrollToTop.style.display = scrollTop > 100 ? "flex" : "none";
    };

    scrollToTop.onclick = () => window.scrollTo(0, 0);
  }

  // header remove buttons (guard missing containers)
  document.querySelectorAll(".header-remove-btn").forEach((button) => {
    button.addEventListener("click", (e) => {
      e.preventDefault();
      e.stopPropagation();
      if (button.parentNode) button.parentNode.remove();
    });
  });

  // cart dropdown remove
  document.querySelectorAll(".dropdown-item-close").forEach((button) => {
    button.addEventListener("click", (e) => {
      e.preventDefault();
      e.stopPropagation();
      const item = button.closest("li") || button.parentNode;
      if (item) item.remove();
    });
  });

  // notifications dropdown remove
  document.querySelectorAll(".dropdown-item-close1").forEach((button) => {
    button.addEventListener("click", (e) => {
      e.preventDefault();
      e.stopPropagation();
      const item = button.closest("li") || button.parentNode;
      if (item) item.remove();
    });
  });

})();


// ✅ Browser fullscreen (THIS is what your header uses)
const elem = document.documentElement;

window.openFullscreen = function () {
  const isFs =
    document.fullscreenElement ||
    document.webkitFullscreenElement ||
    document.msFullscreenElement;

  if (!isFs) {
    if (elem.requestFullscreen) return elem.requestFullscreen();
    if (elem.webkitRequestFullscreen) return elem.webkitRequestFullscreen();
    if (elem.msRequestFullscreen) return elem.msRequestFullscreen();
  } else {
    if (document.exitFullscreen) return document.exitFullscreen();
    if (document.webkitExitFullscreen) return document.webkitExitFullscreen();
    if (document.msExitFullscreen) return document.msExitFullscreen();
  }
};

// icon sync (support webkit too)
function handleFullscreenChange() {
  const open = document.querySelector(".full-screen-open");
  const close = document.querySelector(".full-screen-close");
  if (!open || !close) return;

  const isFs =
    document.fullscreenElement ||
    document.webkitFullscreenElement ||
    document.msFullscreenElement;

  if (isFs) {
    close.classList.remove("hidden");
    close.classList.add("block");
    open.classList.add("hidden");
    open.classList.remove("block");
  } else {
    close.classList.add("hidden");
    close.classList.remove("block");
    open.classList.remove("hidden");
    open.classList.add("block");
  }
}

document.addEventListener("fullscreenchange", handleFullscreenChange);
document.addEventListener("webkitfullscreenchange", handleFullscreenChange);
document.addEventListener("MSFullscreenChange", handleFullscreenChange);


// count-up (guard query)
let i = 1;
setInterval(() => {
  document.querySelectorAll(".count-up").forEach((ele) => {
    const target = Number(ele.getAttribute("data-count") || 0);
    if (target >= i) {
      i++;
      ele.innerText = i;
    }
  });
}, 10);
