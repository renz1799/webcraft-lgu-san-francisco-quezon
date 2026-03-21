import Swal from "sweetalert2";
import "sweetalert2/dist/sweetalert2.min.css";

(function () {
  "use strict";

  if (window.__registerUserModalBound) return;
  window.__registerUserModalBound = true;

  function onReady(fn) {
    if (document.readyState === "loading") {
      document.addEventListener("DOMContentLoaded", fn);
      return;
    }

    fn();
  }

  function getCsrf() {
    return document.querySelector('meta[name="csrf-token"]')?.content || "";
  }

  function closeModal(selector) {
    const modal = document.querySelector(selector);
    if (!modal) return;

    if (window.HSOverlay && typeof window.HSOverlay.close === "function") {
      window.HSOverlay.close(selector);
      return;
    }

    modal.classList.add("hidden");
    modal.classList.remove("open", "opened");
    modal.removeAttribute("aria-overlay");
    modal.removeAttribute("tabindex");
  }

  function showToast(icon, title, text = "") {
    return Swal.fire({
      icon,
      title,
      text,
      timer: icon === "success" ? 1400 : undefined,
      showConfirmButton: icon !== "success",
      position: icon === "success" ? "top-end" : "center",
    });
  }

  async function fetchJson(url, options = {}) {
    const res = await fetch(url, options);

    const isJson = (res.headers.get("content-type") || "").includes("application/json");
    const data = isJson ? await res.json().catch(() => ({})) : {};

    if (!res.ok) {
      if (res.status === 422 && data?.errors) {
        const firstError = Object.values(data.errors).flat()[0];
        throw new Error(firstError || "Validation failed.");
      }

      throw new Error(data?.message || res.statusText || "Request failed.");
    }

    return data;
  }

  onReady(function () {
    const modal = document.getElementById("registerUserModal");
    if (!modal) return;

    const cfg = window.__registerUserModal || {};
    const optionsUrl = cfg.optionsUrl || "";
    const submitUrl = cfg.submitUrl || "";

    const form = document.getElementById("registerUserForm");
    const submitBtn = document.getElementById("registerUserSubmit");
    const roleSelect = document.getElementById("register-role");

    if (!form || !submitBtn || !roleSelect || !optionsUrl || !submitUrl) return;

    form.setAttribute("action", submitUrl);

    let rolesLoaded = false;
    let rolesLoading = false;

    function populateRoles(roles) {
      const oldValue = roleSelect.value;

      roleSelect.innerHTML = '<option value="">Select role</option>';

      roles.forEach((role) => {
        const name = String(role?.name || "").trim();
        if (!name) return;

        const opt = document.createElement("option");
        opt.value = name;
        opt.textContent = name;
        roleSelect.appendChild(opt);
      });

      if (oldValue) {
        roleSelect.value = oldValue;
      }
    }

    async function ensureRolesLoaded() {
      if (rolesLoaded || rolesLoading) return;

      rolesLoading = true;
      try {
        const payload = await fetchJson(optionsUrl, {
          method: "GET",
          headers: {
            Accept: "application/json",
            "X-Requested-With": "XMLHttpRequest",
          },
        });

        const roles = Array.isArray(payload?.roles) ? payload.roles : [];
        populateRoles(roles);
        rolesLoaded = true;
      } catch (err) {
        await showToast("error", "Failed to load roles", err?.message || "Please try again.");
      } finally {
        rolesLoading = false;
      }
    }

    document.addEventListener("click", function (e) {
      const trigger = e.target.closest('[data-hs-overlay="#registerUserModal"]');
      if (!trigger) return;

      ensureRolesLoaded();
    });

    submitBtn.addEventListener("click", function () {
      form.requestSubmit();
    });

    form.addEventListener("submit", async function (e) {
      e.preventDefault();

      submitBtn.setAttribute("disabled", "disabled");

      try {
        const payload = await fetchJson(submitUrl, {
          method: "POST",
          headers: {
            Accept: "application/json",
            "X-Requested-With": "XMLHttpRequest",
            "X-CSRF-TOKEN": getCsrf(),
          },
          body: new FormData(form),
        });

        form.reset();
        closeModal("#registerUserModal");

        await showToast("success", payload?.message || "Account created successfully.");
      } catch (err) {
        await showToast("error", "Registration failed", err?.message || "Please try again.");
      } finally {
        submitBtn.removeAttribute("disabled");
      }
    });
  });
})();
