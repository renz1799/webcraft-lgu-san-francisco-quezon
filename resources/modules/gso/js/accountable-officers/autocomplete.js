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

function dispatchFieldChange(el) {
  if (!el) return;
  el.dispatchEvent(new Event("input", { bubbles: true }));
  el.dispatchEvent(new Event("change", { bubbles: true }));
}

function normalizeQuery(value) {
  return String(value ?? "").trim();
}

export function attachAccountableOfficerAutocomplete(options = {}) {
  const input = resolveElement(options.input);
  if (!input) return null;

  const suggestUrl = String(options.suggestUrl || "").trim();
  if (!suggestUrl) return null;

  const minChars = Math.max(1, Number(options.minChars || 2));
  const title = String(options.title || "Accountable Officers");
  const emptyHelp = String(
    options.emptyHelp || `Type at least ${minChars} characters to search.`
  );
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

  function hidePanel() {
    panel.classList.add("hidden");
    if (list) list.innerHTML = "";
    if (footer) footer.textContent = emptyHelp;
  }

  function showPanel() {
    panel.classList.remove("hidden");
  }

  async function applyOfficer(officer) {
    input.value = normalizeQuery(officer?.full_name);
    dispatchFieldChange(input);
    hidePanel();

    if (onOfficerSelected) {
      await onOfficerSelected(officer);
    }
  }

  function renderItems(items, query) {
    if (!list) return;

    if (!Array.isArray(items) || items.length === 0) {
      list.innerHTML = '<div class="p-3 text-sm text-[#8c9097]">No matching accountable officers found.</div>';
      if (footer) footer.textContent = `No results for "${query}".`;
      showPanel();
      return;
    }

    list.innerHTML = items
      .map((officer) => {
        const meta = [
          officer?.designation ? escapeHtml(officer.designation) : "",
          officer?.office ? escapeHtml(officer.office) : "",
          officer?.department_label ? escapeHtml(officer.department_label) : "",
        ]
          .filter(Boolean)
          .map((line) => `<div>${line}</div>`)
          .join("");

        return `
          <button
            type="button"
            class="w-full text-left p-3 border-b border-defaultborder hover:bg-light dark:hover:bg-white/5"
            data-role="select"
            data-id="${escapeHtml(officer?.id || "")}"
          >
            <div class="text-sm font-semibold text-defaulttextcolor dark:text-white">${escapeHtml(
              officer?.full_name || ""
            )}</div>
            ${meta !== "" ? `<div class="mt-1 text-xs text-[#8c9097] space-y-1">${meta}</div>` : ""}
          </button>
        `;
      })
      .join("");

    if (footer) {
      footer.textContent = "Select an accountable officer.";
    }

    Array.from(list.querySelectorAll('[data-role="select"]')).forEach((button) => {
      button.addEventListener("click", () => {
        const officer = items.find(
          (item) => String(item?.id || "") === String(button.dataset.id || "")
        );
        if (officer) {
          void applyOfficer(officer);
        }
      });
    });

    showPanel();
  }

  async function search(query) {
    const trimmed = normalizeQuery(query);
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
      if (list) list.innerHTML = "";
      if (footer) {
        footer.textContent =
          error instanceof Error ? error.message : "Failed to load accountable officers.";
      }
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

  input.addEventListener("input", queueSearch);
  input.addEventListener("focus", queueSearch);
  input.addEventListener("keydown", (event) => {
    if (event.key === "Escape") {
      hidePanel();
    }
  });

  input.addEventListener("blur", () => {
    if (blurTimer) clearTimeout(blurTimer);
    blurTimer = setTimeout(() => hidePanel(), 180);
  });

  panel.addEventListener("mousedown", (event) => {
    event.preventDefault();
  });

  closeBtn?.addEventListener("click", hidePanel);

  document.addEventListener("click", (event) => {
    if (wrapper instanceof HTMLElement && !wrapper.contains(event.target)) {
      hidePanel();
    }
  });

  return {
    hide: hidePanel,
  };
}
