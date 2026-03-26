function getCsrfToken() {
  return document.querySelector('meta[name="csrf-token"]')?.content || "";
}

function resolveElement(target) {
  if (!target) return null;
  if (typeof target === "string") return document.querySelector(target);
  return target instanceof HTMLElement ? target : null;
}

function normalizeName(value) {
  return String(value ?? "")
    .trim()
    .replace(/\s+/g, " ")
    .toLowerCase();
}

function escapeHtml(value) {
  return String(value ?? "")
    .replace(/&/g, "&amp;")
    .replace(/</g, "&lt;")
    .replace(/>/g, "&gt;")
    .replace(/\"/g, "&quot;")
    .replace(/'/g, "&#39;");
}

function dispatchFieldChange(el) {
  if (!el) return;
  el.dispatchEvent(new Event("input", { bubbles: true }));
  el.dispatchEvent(new Event("change", { bubbles: true }));
}

function fillIfBlank(el, value) {
  if (!el) return;
  const next = String(value ?? "").trim();
  if (next === "" || String(el.value ?? "").trim() !== "") return;
  el.value = next;
  dispatchFieldChange(el);
}

function normalizeMetaLine(value) {
  return String(value ?? "")
    .trim()
    .replace(/\s+/g, " ")
    .toLowerCase();
}

function pushUniqueMetaLine(lines, seen, value) {
  const text = String(value ?? "").trim();
  if (text === "") return;

  const key = normalizeMetaLine(text);
  if (seen.has(key)) return;

  seen.add(key);
  lines.push(escapeHtml(text));
}

function createMetaLines(officer) {
  const lines = [];
  const seen = new Set();

  pushUniqueMetaLine(lines, seen, officer?.designation);
  pushUniqueMetaLine(lines, seen, officer?.office);

  if (officer?.department_name) {
    pushUniqueMetaLine(lines, seen, officer.department_name);
  } else if (officer?.department_label) {
    pushUniqueMetaLine(lines, seen, officer.department_label);
  }
  return lines;
}

export function attachAccountableOfficerAutocomplete(options = {}) {
  const input = resolveElement(options.input);
  if (!input) return null;

  const suggestUrl = String(options.suggestUrl || "").trim();
  if (!suggestUrl) return null;

  const storeUrl = String(options.storeUrl || "").trim();
  const designationField = resolveElement(options.designationField);
  const officeField = resolveElement(options.officeField);
  const departmentField = resolveElement(options.departmentField);
  const swal = options.swal || null;
  const beforeApplyOfficer =
    typeof options.beforeApplyOfficer === "function" ? options.beforeApplyOfficer : null;
  const customCreateOfficer =
    typeof options.createOfficer === "function" ? options.createOfficer : null;
  const minChars = Number(options.minChars || 2);
  const title = String(options.title || "Accountable Officers");
  const emptyHelp = String(options.emptyHelp || `Type at least ${minChars} characters to search.`);
  const createLabel = String(options.createLabel || "Create");
  const onOfficerSelected =
    typeof options.onOfficerSelected === "function" ? options.onOfficerSelected : null;

  const wrapper = input.parentElement || input;
  if (wrapper instanceof HTMLElement && getComputedStyle(wrapper).position === "static") {
    wrapper.style.position = "relative";
  }

  const panel = document.createElement("div");
  panel.className =
    "hidden absolute left-0 right-0 mt-2 z-[9999] rounded-md border border-defaultborder bg-white dark:bg-bodybg shadow-lg";
  panel.innerHTML = `
    <div class="p-2 border-b border-defaultborder flex items-center justify-between gap-2">
      <div class="text-xs font-semibold text-defaulttextcolor dark:text-white">${escapeHtml(title)}</div>
      <button type="button" class="ti-btn ti-btn-sm ti-btn-light" data-role="close">
        <i class="ri-close-line"></i>
      </button>
    </div>
    <div data-role="list" class="max-h-[320px] overflow-auto"></div>
    <div data-role="footer" class="p-2 border-t border-defaultborder text-xs text-[#8c9097]">${escapeHtml(
      emptyHelp
    )}</div>
  `;
  wrapper.appendChild(panel);

  const list = panel.querySelector('[data-role="list"]');
  const footer = panel.querySelector('[data-role="footer"]');
  const closeBtn = panel.querySelector('[data-role="close"]');

  let debounceTimer = null;
  let blurTimer = null;
  let activeRequest = 0;
  let lastCreateAction = null;

  function setFieldValue(el, value) {
    if (!el) return;
    el.value = String(value ?? "").trim();
    dispatchFieldChange(el);
  }

  function clearOfficerMeta() {
    delete input.dataset.accountableOfficerId;
    delete input.dataset.accountableOfficerDepartmentId;
    delete input.dataset.accountableOfficerDesignation;
    delete input.dataset.accountableOfficerOffice;
  }

  function setOfficerMeta(officer) {
    if (!officer) {
      clearOfficerMeta();
      return;
    }

    input.dataset.accountableOfficerId = String(officer.id || "");
    input.dataset.accountableOfficerDepartmentId = String(officer.department_id || "");
    input.dataset.accountableOfficerDesignation = String(officer.designation || "");
    input.dataset.accountableOfficerOffice = String(officer.office || "");
  }

  function hidePanel() {
    panel.classList.add("hidden");
    list.innerHTML = "";
    footer.textContent = emptyHelp;
    lastCreateAction = null;
  }

  function showPanel() {
    panel.classList.remove("hidden");
  }

  async function applyOfficer(officer) {
    let nextOfficer = officer;

    if (beforeApplyOfficer) {
      const result = await beforeApplyOfficer(nextOfficer, {
        input,
        designationField,
        officeField,
        departmentField,
        fillIfBlank,
        setFieldValue,
        dispatchFieldChange,
        saveOfficerRecord,
        swal,
      });

      if (result === false) {
        return false;
      }

      if (result && typeof result === "object") {
        nextOfficer = result;
      }
    }

    input.value = String(nextOfficer?.full_name || "").trim();
    setOfficerMeta(nextOfficer);
    fillIfBlank(designationField, nextOfficer?.designation);
    fillIfBlank(officeField, nextOfficer?.office);
    dispatchFieldChange(input);

    if (onOfficerSelected) {
      await onOfficerSelected(nextOfficer, {
        fillIfBlank,
        setFieldValue,
        dispatchFieldChange,
      });
    }

    hidePanel();
    return true;
  }

  async function showError(message) {
    if (swal && typeof swal.fire === "function") {
      await swal.fire({
        icon: "error",
        title: "Unable to complete request",
        text: message || "Unexpected error.",
      });
      return;
    }

    window.alert(message || "Unexpected error.");
  }

  async function saveOfficerRecord(payload) {
    const response = await fetch(storeUrl, {
      method: "POST",
      headers: {
        "X-CSRF-TOKEN": getCsrfToken(),
        Accept: "application/json",
        "Content-Type": "application/json",
      },
      body: JSON.stringify(payload),
    });

    const data = await response.json().catch(() => ({}));
    if (!response.ok) {
      throw new Error(data?.message || "Failed to create accountable officer.");
    }

    return data?.data || null;
  }

  async function createOfficer(name) {
    const payload = {
      full_name: String(name || "").trim(),
      designation: designationField ? String(designationField.value || "").trim() || null : null,
      office: officeField ? String(officeField.value || "").trim() || null : null,
      department_id: departmentField ? String(departmentField.value || "").trim() || null : null,
    };

    try {
      const officer = customCreateOfficer
        ? await customCreateOfficer({
            name: payload.full_name,
            payload,
            saveOfficerRecord,
            input,
            designationField,
            officeField,
            departmentField,
          })
        : await saveOfficerRecord(payload);

      if (!officer) {
        return false;
      }

      return applyOfficer(officer);
    } catch (error) {
      await showError(error?.message || "Failed to create accountable officer.");
      return false;
    }
  }

  function renderItems(items, query) {
    const normalizedQuery = normalizeName(query);
    const listItems = Array.isArray(items) ? items : [];
    const hasExactMatch = listItems.some(
      (item) => normalizeName(item?.full_name) === normalizedQuery
    );

    list.innerHTML = listItems
      .map((item) => {
        const meta = createMetaLines(item);
        return `
          <button
            type="button"
            class="w-full text-left p-3 border-b border-defaultborder hover:bg-light dark:hover:bg-white/5"
            data-role="select"
            data-id="${escapeHtml(item.id)}"
          >
            <div class="text-sm font-semibold text-defaulttextcolor dark:text-white">${escapeHtml(
              item.full_name
            )}</div>
            ${
              meta.length
                ? `<div class="mt-1 text-xs text-[#8c9097] space-y-1">${meta
                    .map((line) => `<div>${line}</div>`)
                    .join("")}</div>`
                : ""
            }
          </button>
        `;
      })
      .join("");

    if (storeUrl && !hasExactMatch && normalizedQuery !== "") {
      const createBtn = document.createElement("button");
      createBtn.type = "button";
      createBtn.className = "w-full text-left p-3 hover:bg-light dark:hover:bg-white/5";
      createBtn.dataset.role = "create";
      createBtn.innerHTML = `
        <div class="text-sm font-semibold text-defaulttextcolor dark:text-white">${escapeHtml(
          createLabel
        )} "${escapeHtml(query)}"</div>
        <div class="mt-1 text-xs text-[#8c9097]">Create a reusable accountable officer record and use it here.</div>
      `;
      createBtn.addEventListener("click", async () => {
        await createOfficer(query);
      });
      list.appendChild(createBtn);
      lastCreateAction = () => createOfficer(query);
    } else {
      lastCreateAction = null;
    }

    if (listItems.length === 0 && normalizedQuery === "") {
      footer.textContent = emptyHelp;
    } else if (listItems.length === 0) {
      footer.textContent = `No exact match found for "${query}".`;
    } else {
      footer.textContent = storeUrl
        ? "Select an accountable officer or create a new reusable record."
        : "Select an accountable officer.";
    }

    Array.from(list.querySelectorAll('[data-role="select"]')).forEach((button) => {
      button.addEventListener("click", () => {
        const officer = listItems.find((item) => String(item.id) === String(button.dataset.id));
        if (officer) {
          void applyOfficer(officer);
        }
      });
    });

    showPanel();
  }

  async function search(query) {
    const trimmed = String(query || "").trim();
    if (trimmed.length < minChars) {
      hidePanel();
      return;
    }

    const requestId = ++activeRequest;

    try {
      const url = new URL(suggestUrl, window.location.origin);
      url.searchParams.set("q", trimmed);

      const response = await fetch(url.toString(), {
        headers: {
          Accept: "application/json",
          "X-Requested-With": "XMLHttpRequest",
        },
      });

      const data = await response.json().catch(() => ({}));
      if (!response.ok) {
        throw new Error(data?.message || "Failed to load accountable officers.");
      }

      if (requestId !== activeRequest) return;
      renderItems(Array.isArray(data?.items) ? data.items : [], trimmed);
    } catch (error) {
      if (requestId !== activeRequest) return;
      list.innerHTML = "";
      footer.textContent = error?.message || "Failed to load accountable officers.";
      showPanel();
    }
  }

  function queueSearch() {
    const query = input.value;
    if (debounceTimer) clearTimeout(debounceTimer);
    debounceTimer = setTimeout(() => {
      void search(query);
    }, 220);
  }

  input.addEventListener("input", () => {
    clearOfficerMeta();
    queueSearch();
  });
  input.addEventListener("focus", queueSearch);
  input.addEventListener("keydown", async (event) => {
    if (event.key === "Escape") {
      hidePanel();
      return;
    }

    if (event.key === "Enter" && !panel.classList.contains("hidden") && lastCreateAction) {
      event.preventDefault();
      await lastCreateAction();
    }
  });

  input.addEventListener("blur", () => {
    if (blurTimer) clearTimeout(blurTimer);
    blurTimer = setTimeout(() => {
      hidePanel();
    }, 180);
  });

  panel.addEventListener("mousedown", (event) => {
    event.preventDefault();
  });

  closeBtn?.addEventListener("click", hidePanel);

  document.addEventListener("click", (event) => {
    if (!wrapper.contains(event.target)) {
      hidePanel();
    }
  });

  return {
    hide: hidePanel,
    clearMeta: clearOfficerMeta,
  };
}
