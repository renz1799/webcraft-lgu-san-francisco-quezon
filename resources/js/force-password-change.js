import Swal from "sweetalert2";
import "sweetalert2/dist/sweetalert2.min.css";

const STORAGE_KEY = "force_pw_change_ack_v1";

function getForceFlag() {
  const meta = document.querySelector('meta[name="force-password-change"]');
  return meta && meta.getAttribute("content") === "1";
}

function isAccountSettingsPage() {
  const url = new URL(window.location.href);
  return (
    (url.pathname === "/profile" || url.pathname === "/mail-settings") &&
    url.searchParams.get("tab") === "account-settings"
  );
}

function redirectToAccountSettings() {
  window.location.href = "/profile?tab=account-settings";
}

document.addEventListener("DOMContentLoaded", async () => {
  const shouldForce = getForceFlag();
  console.log("[ForcePW] loaded; shouldForce =", shouldForce);

  // Password already changed → cleanup
  if (!shouldForce) {
    localStorage.removeItem(STORAGE_KEY);
    return;
  }

  // Not on account settings → silent redirect
  if (!isAccountSettingsPage()) {
    redirectToAccountSettings();
    return;
  }

  // Already acknowledged on this browser
  if (localStorage.getItem(STORAGE_KEY) === "1") {
    return;
  }

  // Give UI time to settle (Ynex / HS overlays)
  await new Promise((r) => setTimeout(r, 100));

  const result = await Swal.fire({
    icon: "warning",
    title: "Change password required",
    text: "You must change your password before continuing.",
    confirmButtonText: "OK, I understand",
    allowOutsideClick: false,
    allowEscapeKey: false,
    heightAuto: false,
  });

  if (result.isConfirmed) {
    localStorage.setItem(STORAGE_KEY, "1");
  }
});

