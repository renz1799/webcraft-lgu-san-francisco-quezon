import Swal from "sweetalert2";
import "sweetalert2/dist/sweetalert2.min.css";

(function () {
  "use strict";

  if (window.__userOnboardingBound) return;
  window.__userOnboardingBound = true;

  function onReady(fn) {
    if (document.readyState === "loading") {
      document.addEventListener("DOMContentLoaded", fn);
      return;
    }

    fn();
  }

  function parseConfig() {
    const raw = document.getElementById("userOnboardingConfig")?.textContent || "{}";

    try {
      return JSON.parse(raw);
    } catch (error) {
      console.error("Failed to parse onboarding config", error);
      return {};
    }
  }

  function showFeedback(icon, title, text = "") {
    return Swal.fire({
      icon,
      title,
      text,
      timer: icon === "success" ? 1600 : undefined,
      showConfirmButton: icon !== "success",
      position: icon === "success" ? "top-end" : "center",
    });
  }

  function setOptions(select, items, placeholder, selectedValue = "") {
    if (!select) return;

    const currentValue = String(selectedValue || select.value || "");
    select.innerHTML = "";

    const placeholderOption = document.createElement("option");
    placeholderOption.value = "";
    placeholderOption.textContent = placeholder;
    select.appendChild(placeholderOption);

    items.forEach((item) => {
      const option = document.createElement("option");
      option.value = String(item?.value || item?.id || item?.name || "");
      option.textContent = String(item?.name || item?.label || "");

      if (item?.code) {
        option.textContent += ` (${item.code})`;
      }

      select.appendChild(option);
    });

    if (currentValue && items.some((item) => String(item?.value || item?.id || item?.name || "") === currentValue)) {
      select.value = currentValue;
      return;
    }

    if (items[0]) {
      select.value = String(items[0]?.value || items[0]?.id || items[0]?.name || "");
      return;
    }

    select.value = "";
  }

  onReady(function () {
    const root = document.getElementById("user-onboarding-page");
    if (!root) return;

    const form = document.getElementById("user-onboarding-form");
    const submitBtn = document.getElementById("user-onboarding-submit");
    const config = parseConfig();
    const onboardingMode = String(root.dataset.onboardingMode || "module");
    const successMessage = String(root.dataset.successMessage || "").trim();
    const infoMessage = String(root.dataset.infoMessage || "").trim();
    const errorMessage = String(root.dataset.errorMessage || "").trim();

    if (successMessage) {
      showFeedback("success", "User successfully onboarded", successMessage);
    } else if (infoMessage) {
      showFeedback("info", "No changes needed", infoMessage);
    } else if (errorMessage) {
      showFeedback("error", "Onboarding failed", errorMessage);
    }

    let submitting = false;

    form?.addEventListener("submit", function (event) {
      if (submitting) {
        event.preventDefault();
        return;
      }

      submitting = true;
      submitBtn?.setAttribute("disabled", "disabled");

      Swal.fire({
        title: "Creating user account...",
        allowEscapeKey: false,
        allowOutsideClick: false,
        showConfirmButton: false,
        didOpen: () => Swal.showLoading(),
      });
    });

    if (onboardingMode !== "core") {
      return;
    }

    const moduleSelect = document.getElementById("module_id");
    const departmentSelect = document.getElementById("department_id");
    const roleSelect = document.getElementById("role");
    const departmentHint = document.getElementById("core-onboarding-department-hint");
    const moduleHint = document.getElementById("core-onboarding-module-hint");
    const departmentsByModule = config.departmentsByModule || {};
    const rolesByModule = config.rolesByModule || {};
    const moduleHints = config.moduleHints || {};

    if (!moduleSelect || !departmentSelect || !roleSelect) {
      return;
    }

    function updateCoreSelections(useCurrentSelections = true) {
      const moduleId = String(moduleSelect.value || "");
      const departments = Array.isArray(departmentsByModule[moduleId]) ? departmentsByModule[moduleId] : [];
      const roles = Array.isArray(rolesByModule[moduleId]) ? rolesByModule[moduleId] : [];
      const hint = moduleHints[moduleId] || {};
      const selectedDepartment = useCurrentSelections
        ? String(departmentSelect.value || departmentSelect.dataset.selected || config.selectedDepartmentId || "")
        : "";
      const selectedRole = useCurrentSelections
        ? String(roleSelect.value || roleSelect.dataset.selected || config.selectedRole || "")
        : "";

      setOptions(departmentSelect, departments, "Select department", selectedDepartment);
      setOptions(roleSelect, roles, "Select role", selectedRole);

      if (departmentHint) {
        if (departments.length > 0) {
          departmentHint.textContent = "Suggested department based on selected module.";
        } else {
          departmentHint.textContent = "No allowed departments are configured for the selected module.";
        }
      }

      if (moduleHint) {
        if (moduleId === "") {
          moduleHint.textContent = "Select a module to view its default department.";
        } else {
          moduleHint.textContent = `Default department: ${hint.default_department_label || "No default department configured"}`;
        }
      }
    }

    moduleSelect.addEventListener("change", function () {
      departmentSelect.dataset.selected = "";
      roleSelect.dataset.selected = "";
      updateCoreSelections(false);
    });

    updateCoreSelections(true);
  });
})();
