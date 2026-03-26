function resolveElement(target) {
  if (!target) return null;
  if (typeof target === "string") return document.querySelector(target);
  return target instanceof HTMLElement ? target : null;
}

function escapeHtml(value) {
  return String(value ?? "")
    .replace(/&/g, "&amp;")
    .replace(/</g, "&lt;")
    .replace(/>/g, "&gt;")
    .replace(/"/g, "&quot;")
    .replace(/'/g, "&#39;");
}

function normalizeValue(value) {
  return String(value ?? "").trim();
}

function getSelectedOptionText(select, targetValue = null) {
  if (!select) return "";

  const value = targetValue === null ? String(select.value || "") : String(targetValue || "");
  const option = Array.from(select.options || []).find((item) => String(item.value || "") === value);
  return String(option?.dataset?.departmentName || option?.textContent || "").trim();
}

function buildDepartmentOptionsMarkup(select, selectedId) {
  const safeSelected = String(selectedId || "");
  return Array.from(select?.options || [])
    .filter((option) => normalizeValue(option.value) !== "")
    .map((option) => {
      const value = String(option.value || "");
      const selected = value === safeSelected ? " selected" : "";
      return `<option value="${escapeHtml(value)}"${selected}>${escapeHtml(
        option.textContent || ""
      )}</option>`;
    })
    .join("");
}

export async function openAccountableOfficerDetailsModal(options = {}) {
  const {
    swal,
    saveOfficerRecord,
    departmentField,
    initialOfficer = {},
    initialDepartmentId = "",
    title = "Create Accountable Officer",
    confirmButtonText = "Create and Use",
    requireDepartment = true,
    requireDesignation = false,
  } = options;

  if (!swal || typeof swal.fire !== "function") {
    throw new Error("SweetAlert is required to open the accountable officer modal.");
  }

  if (typeof saveOfficerRecord !== "function") {
    throw new Error("A saveOfficerRecord callback is required.");
  }

  const departmentSelect = resolveElement(departmentField);
  if (!departmentSelect) {
    await swal.fire({
      icon: "error",
      title: "Department list unavailable",
      text: "The department options could not be loaded for this accountable officer.",
    });
    return null;
  }

  const selectedDepartmentId =
    normalizeValue(initialOfficer.department_id) ||
    normalizeValue(initialDepartmentId) ||
    normalizeValue(departmentSelect.value);
  const departmentOptions = buildDepartmentOptionsMarkup(departmentSelect, selectedDepartmentId);

  const result = await swal.fire({
    title,
    width: 620,
    showCancelButton: true,
    confirmButtonText,
    cancelButtonText: "Cancel",
    focusConfirm: false,
    html: `
      <div class="text-left space-y-3">
        <div>
          <label class="ti-form-label !mb-1">Full Name <span class="text-danger">*</span></label>
          <input id="gsoOfficerModalName" class="ti-form-input w-full" value="${escapeHtml(
            initialOfficer.full_name || ""
          )}" />
        </div>
        <div>
          <label class="ti-form-label !mb-1">Department <span class="text-danger">*</span></label>
          <select id="gsoOfficerModalDepartment" class="ti-form-select w-full">
            <option value="">- Select Department -</option>
            ${departmentOptions}
          </select>
        </div>
        <div>
          <label class="ti-form-label !mb-1">Designation${
            requireDesignation ? ' <span class="text-danger">*</span>' : ""
          }</label>
          <input id="gsoOfficerModalDesignation" class="ti-form-input w-full" value="${escapeHtml(
            initialOfficer.designation || ""
          )}" />
        </div>
      </div>
    `,
    preConfirm: async () => {
      const nextDepartmentId = normalizeValue(
        document.getElementById("gsoOfficerModalDepartment")?.value
      );
      const payload = {
        full_name: normalizeValue(document.getElementById("gsoOfficerModalName")?.value),
        department_id: nextDepartmentId || null,
        designation:
          normalizeValue(document.getElementById("gsoOfficerModalDesignation")?.value) || null,
        office: getSelectedOptionText(departmentSelect, nextDepartmentId) || null,
      };

      if (!payload.full_name) {
        swal.showValidationMessage("Full Name is required.");
        return false;
      }

      if (requireDepartment && !payload.department_id) {
        swal.showValidationMessage("Department is required.");
        return false;
      }

      if (requireDesignation && !payload.designation) {
        swal.showValidationMessage("Designation is required.");
        return false;
      }

      try {
        return await saveOfficerRecord(payload);
      } catch (error) {
        swal.showValidationMessage(
          error?.message || "Unable to save accountable officer details."
        );
        return false;
      }
    },
  });

  return result.isConfirmed ? result.value || null : null;
}

export async function resolveAccountableOfficerDetails(officer, options = {}) {
  if (!officer) return false;

  const requireDepartment = !!options.requireDepartment;
  const requireDesignation = !!options.requireDesignation;
  const needsDepartment = requireDepartment && normalizeValue(officer.department_id) === "";
  const needsDesignation = requireDesignation && normalizeValue(officer.designation) === "";

  if (!needsDepartment && !needsDesignation) {
    return officer;
  }

  const resolvedOfficer = await openAccountableOfficerDetailsModal({
    ...options,
    confirmButtonText: options.confirmButtonText || "Save and Use",
    initialOfficer: officer,
  });

  return resolvedOfficer || false;
}
