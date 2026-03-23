import Swal from "sweetalert2";
import "sweetalert2/dist/sweetalert2.min.css";

(function () {
  "use strict";

  function onReady(fn) {
    if (document.readyState === "loading") {
      document.addEventListener("DOMContentLoaded", fn);
      return;
    }

    fn();
  }

  function debounce(fn, wait = 350) {
    let timer = null;

    return (...args) => {
      clearTimeout(timer);
      timer = setTimeout(() => fn(...args), wait);
    };
  }

  function escapeHtml(value) {
    return String(value ?? "")
      .replace(/&/g, "&amp;")
      .replace(/</g, "&lt;")
      .replace(/>/g, "&gt;")
      .replace(/"/g, "&quot;")
      .replace(/'/g, "&#39;");
  }

  function buildUrl(template, id) {
    return String(template || "").replace("__AIR_ID__", encodeURIComponent(String(id || "")));
  }

  function getStatusMeta(statusRaw) {
    const key = String(statusRaw ?? "").toLowerCase().trim();

    const map = {
      draft: ["bg-secondary/15 text-secondary", "Draft"],
      submitted: ["bg-warning/15 text-warning", "Submitted"],
      in_progress: ["bg-primary/15 text-primary", "In Progress"],
      inspected: ["bg-success/15 text-success", "Inspected"],
    };

    return map[key] || ["bg-light text-defaulttextcolor", key ? key.replace(/_/g, " ") : "-"];
  }

  function getCompletenessMeta(valueRaw) {
    const key = String(valueRaw ?? "").toLowerCase().trim();

    const map = {
      complete: ["bg-success/15 text-success", "Complete"],
      partial: ["bg-warning/15 text-warning", "Partial"],
    };

    return map[key] || ["bg-light text-defaulttextcolor", "-"];
  }

  function statusPillFormatter(cell) {
    const [cls, label] = getStatusMeta(cell.getValue());

    return `<span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium ${cls}">${escapeHtml(label)}</span>`;
  }

  function completenessPillFormatter(cell) {
    const [cls, label] = getCompletenessMeta(cell.getValue());

    return `<span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium ${cls}">${escapeHtml(label)}</span>`;
  }

  async function parseErrorResponse(response) {
    const contentType = response.headers.get("content-type") || "";
    const data = contentType.includes("application/json")
      ? await response.json().catch(() => null)
      : null;

    const message =
      data?.message ||
      (response.status === 401
        ? "Session expired. Please log in again."
        : response.status === 403
        ? "You do not have permission to perform this action."
        : response.status === 404
        ? "The AIR record could not be found."
        : "The request could not be completed.");

    const details = data?.errors && typeof data.errors === "object"
      ? Object.values(data.errors).flat().join("\n")
      : "";

    return details ? `${message}\n${details}` : message;
  }

  async function requestJson(url, method, csrf) {
    const response = await fetch(url, {
      method,
      headers: {
        Accept: "application/json",
        "X-Requested-With": "XMLHttpRequest",
        "X-CSRF-TOKEN": csrf || "",
      },
    });

    if (!response.ok) {
      throw new Error(await parseErrorResponse(response));
    }

    return response.json().catch(() => ({}));
  }

  onReady(function () {
    const tableElement = document.getElementById("gso-air-table");
    if (!tableElement) return;

    const config = window.__gsoAir || {};
    const infoElement = document.getElementById("gso-air-info");
    const searchInput = document.getElementById("gso-air-search");
    const archivedSelect = document.getElementById("gso-air-archived-filter");
    const statusSelect = document.getElementById("gso-air-status-filter");
    const departmentInput = document.getElementById("gso-air-department-filter");
    const supplierInput = document.getElementById("gso-air-supplier-filter");
    const dateFromInput = document.getElementById("gso-air-date-from");
    const dateToInput = document.getElementById("gso-air-date-to");
    const completenessSelect = document.getElementById("gso-air-completeness-filter");
    const clearButton = document.getElementById("gso-air-clear");
    const moreButton = document.getElementById("gso-air-more-btn");
    const morePanel = document.getElementById("gso-air-more-panel");
    const moreCloseButton = document.getElementById("gso-air-more-close");
    const advancedApplyButton = document.getElementById("gso-air-adv-apply");
    const advancedResetButton = document.getElementById("gso-air-adv-reset");
    const advancedCountBadge = document.getElementById("gso-air-adv-count");

    let filters = {
      q: "",
      archived: archivedSelect?.value || "active",
      date_from: "",
      date_to: "",
      supplier: "",
      department: "",
      inspection_status: "",
      received_completeness: "",
    };
    let lastTotal = 0;
    let panelOpen = false;
    let panelOpenedAt = 0;
    let panelPlaceholder = null;
    let panelPortaled = false;

    function setInfo(text) {
      if (infoElement) {
        infoElement.textContent = text;
      }
    }

    function countAdvancedFilters() {
      let count = 0;

      if ((dateFromInput?.value || "").trim() !== "") count++;
      if ((dateToInput?.value || "").trim() !== "") count++;
      if ((supplierInput?.value || "").trim() !== "") count++;
      if ((departmentInput?.value || "").trim() !== "") count++;
      if ((statusSelect?.value || "").trim() !== "") count++;
      if ((completenessSelect?.value || "").trim() !== "") count++;

      if (advancedCountBadge) {
        advancedCountBadge.textContent = String(count);
        advancedCountBadge.classList.toggle("hidden", count === 0);
      }

      return count;
    }

    function syncFiltersFromUi() {
      filters.q = (searchInput?.value || "").trim();
      filters.archived = (archivedSelect?.value || "active").trim();
      filters.date_from = (dateFromInput?.value || "").trim();
      filters.date_to = (dateToInput?.value || "").trim();
      filters.supplier = (supplierInput?.value || "").trim();
      filters.department = (departmentInput?.value || "").trim();
      filters.inspection_status = (statusSelect?.value || "").trim();
      filters.received_completeness = (completenessSelect?.value || "").trim();
    }

    function restorePanel() {
      if (!panelPortaled || !panelPlaceholder || !morePanel) {
        return;
      }

      panelPlaceholder.parentNode.insertBefore(morePanel, panelPlaceholder);
      panelPlaceholder.parentNode.removeChild(panelPlaceholder);
      panelPlaceholder = null;
      panelPortaled = false;

      morePanel.style.position = "";
      morePanel.style.top = "";
      morePanel.style.left = "";
      morePanel.style.right = "";
      morePanel.style.bottom = "";
      morePanel.style.zIndex = "";
      morePanel.style.transform = "";
      morePanel.style.opacity = "";
      morePanel.style.visibility = "";
      morePanel.style.pointerEvents = "";
      morePanel.style.display = "";
    }

    function portalPanel() {
      if (!morePanel || panelPortaled) {
        return;
      }

      panelPlaceholder = document.createComment("gso-air-more-panel-placeholder");
      morePanel.parentNode.insertBefore(panelPlaceholder, morePanel);
      document.body.appendChild(morePanel);
      panelPortaled = true;
    }

    function positionPanel() {
      if (!moreButton || !morePanel) {
        return;
      }

      const buttonRect = moreButton.getBoundingClientRect();
      const margin = 8;

      morePanel.classList.remove("hidden");
      morePanel.style.display = "block";
      morePanel.style.visibility = "visible";
      morePanel.style.opacity = "1";
      morePanel.style.pointerEvents = "auto";
      morePanel.style.transform = "none";
      morePanel.style.zIndex = "999999";
      morePanel.style.position = "fixed";

      const panelRect = morePanel.getBoundingClientRect();
      let left = buttonRect.right - panelRect.width;
      left = Math.max(margin, Math.min(left, window.innerWidth - panelRect.width - margin));

      morePanel.style.left = `${left}px`;
      morePanel.style.top = `${buttonRect.bottom + margin}px`;
      morePanel.style.right = "auto";
      morePanel.style.bottom = "auto";
    }

    function closePanel() {
      if (!morePanel) {
        return;
      }

      panelOpen = false;
      morePanel.classList.add("hidden");
      morePanel.style.display = "none";
      restorePanel();
    }

    function openPanel() {
      if (!morePanel) {
        return;
      }

      panelOpenedAt = Date.now();
      portalPanel();
      panelOpen = true;
      morePanel.classList.remove("hidden");
      morePanel.style.display = "block";
      positionPanel();
    }

    function updateInfo(table) {
      if (lastTotal <= 0) {
        setInfo("No records found");
        return;
      }

      const page = table.getPage() || 1;
      const size = table.getPageSize ? table.getPageSize() || 15 : 15;
      const start = (page - 1) * size + 1;
      const end = Math.min(start + size - 1, lastTotal);

      setInfo(`Showing ${start}-${end} of ${lastTotal} record(s).`);
    }

    function reload(table) {
      tableElement.classList.add("is-loading");
      setInfo("Updating...");

      if ((table.getPage?.() || 1) !== 1) {
        table.setPage(1);
        return;
      }

      table.setData();
    }

    function clearAdvancedFields() {
      if (dateFromInput) dateFromInput.value = "";
      if (dateToInput) dateToInput.value = "";
      if (supplierInput) supplierInput.value = "";
      if (departmentInput) departmentInput.value = "";
      if (statusSelect) statusSelect.value = "";
      if (completenessSelect) completenessSelect.value = "";
      countAdvancedFilters();
    }

    function resetAllFilters(table) {
      filters = {
        q: "",
        archived: "active",
        date_from: "",
        date_to: "",
        supplier: "",
        department: "",
        inspection_status: "",
        received_completeness: "",
      };

      if (searchInput) searchInput.value = "";
      if (archivedSelect) archivedSelect.value = "active";
      clearAdvancedFields();
      closePanel();
      reload(table);
    }

    const table = new Tabulator(tableElement, {
      layout: "fitColumns",
      responsiveLayout: "collapse",
      placeholder: "No AIR records found.",
      pagination: true,
      paginationMode: "remote",
      paginationSize: 15,
      paginationSizeSelector: [15, 25, 50, 100],
      ajaxURL: config.ajaxUrl,
      ajaxConfig: "GET",
      ajaxLoader: false,
      paginationDataSent: { page: "page", size: "size" },
      ajaxParams: (_, __, params) => ({
        ...(params || {}),
        ...filters,
      }),
      ajaxResponse: (_, __, response) => {
        lastTotal = Number(response?.total ?? 0);

        return {
          data: Array.isArray(response?.data) ? response.data : [],
          last_page: Number(response?.last_page ?? 1),
        };
      },
      columns: [
        {
          title: "AIR No.",
          field: "air_number",
          width: 170,
          formatter: (cell) => escapeHtml(cell.getValue() || "-"),
        },
        {
          title: "AIR Date",
          field: "air_date_text",
          width: 145,
          formatter: (cell) => escapeHtml(cell.getValue() || "-"),
        },
        {
          title: "PO No.",
          field: "po_number",
          width: 200,
          formatter: (cell) => escapeHtml(cell.getValue() || "-"),
        },
        {
          title: "Supplier",
          field: "supplier_name",
          minWidth: 220,
          formatter: (cell) => escapeHtml(cell.getValue() || "-"),
        },
        {
          title: "Department",
          field: "department_label",
          minWidth: 220,
          formatter: (cell) => escapeHtml(cell.getValue() || "-"),
        },
        {
          title: "Completeness",
          field: "received_completeness",
          width: 150,
          headerSort: false,
          formatter: completenessPillFormatter,
        },
        {
          title: "Status",
          field: "status",
          width: 140,
          formatter: statusPillFormatter,
        },
        {
          title: "Created",
          field: "created_at_text",
          width: 200,
          formatter: (cell) => escapeHtml(cell.getValue() || "-"),
        },
        {
          title: "Actions",
          field: "id",
          width: 150,
          hozAlign: "right",
          headerSort: false,
          formatter: (cell) => {
            const row = cell.getRow().getData() || {};
            const id = escapeHtml(cell.getValue() || "");
            const editUrl = escapeHtml(buildUrl(config.editUrlTemplate, row?.id));
            const deleteUrl = escapeHtml(buildUrl(config.deleteUrlTemplate, row?.id));
            const restoreUrl = escapeHtml(buildUrl(config.restoreUrlTemplate, row?.id));
            const forceDeleteUrl = escapeHtml(buildUrl(config.forceDeleteUrlTemplate, row?.id));

            if (!config.canManage) {
              return `
                <div class="hstack flex gap-3 text-[.9375rem] justify-end">
                  <a
                    aria-label="Open AIR"
                    href="${editUrl}"
                    class="ti-btn btn-wave ti-btn-sm ti-btn-light !rounded-full"
                  >
                    <i class="ri-external-link-line"></i>
                  </a>
                </div>
              `;
            }

            if (row?.is_archived) {
              return `
                <div class="hstack flex gap-3 text-[.9375rem] justify-end">
                  <a
                    aria-label="Restore AIR"
                    href="javascript:void(0);"
                    class="ti-btn btn-wave ti-btn-sm ti-btn-success !rounded-full"
                    data-action="restore-air"
                    data-id="${id}"
                    data-restore-url="${restoreUrl}"
                  >
                    <i class="ri-arrow-go-back-line"></i>
                  </a>
                  <a
                    aria-label="Permanently Delete AIR"
                    href="javascript:void(0);"
                    class="ti-btn btn-wave ti-btn-sm ti-btn-danger !rounded-full"
                    data-action="force-delete-air"
                    data-id="${id}"
                    data-force-url="${forceDeleteUrl}"
                  >
                    <i class="ri-delete-bin-2-line"></i>
                  </a>
                </div>
              `;
            }

            return `
              <div class="hstack flex gap-3 text-[.9375rem] justify-end">
                <a
                  aria-label="Edit AIR"
                  href="${editUrl}"
                  class="ti-btn btn-wave ti-btn-sm ti-btn-info !rounded-full"
                >
                  <i class="ri-edit-line"></i>
                </a>
                <a
                  aria-label="Archive AIR"
                  href="javascript:void(0);"
                  class="ti-btn btn-wave ti-btn-sm ti-btn-danger !rounded-full"
                  data-action="delete-air"
                  data-id="${id}"
                  data-delete-url="${deleteUrl}"
                >
                  <i class="ri-delete-bin-line"></i>
                </a>
              </div>
            `;
          },
        },
      ],
    });

    const debouncedPrimaryApply = debounce(() => {
      syncFiltersFromUi();
      reload(table);
    }, 350);

    searchInput?.addEventListener("input", debouncedPrimaryApply);
    archivedSelect?.addEventListener("change", () => {
      syncFiltersFromUi();
      reload(table);
    });

    moreButton?.addEventListener(
      "pointerdown",
      (event) => {
        event.preventDefault();
        event.stopPropagation();

        if (panelOpen) {
          closePanel();
          return;
        }

        openPanel();
      },
      true,
    );

    morePanel?.addEventListener(
      "pointerdown",
      (event) => {
        event.stopPropagation();
      },
      true,
    );

    moreCloseButton?.addEventListener(
      "pointerdown",
      (event) => {
        event.preventDefault();
        event.stopPropagation();
        closePanel();
      },
      true,
    );

    document.addEventListener(
      "pointerdown",
      (event) => {
        if (!panelOpen || !morePanel || !moreButton) {
          return;
        }

        if (Date.now() - panelOpenedAt < 180) {
          return;
        }

        const inside = morePanel.contains(event.target) || moreButton.contains(event.target);
        if (!inside) {
          closePanel();
        }
      },
      true,
    );

    window.addEventListener("resize", () => {
      if (panelOpen) {
        positionPanel();
      }
    });

    window.addEventListener(
      "scroll",
      () => {
        if (panelOpen) {
          positionPanel();
        }
      },
      true,
    );

    advancedApplyButton?.addEventListener("click", (event) => {
      event.preventDefault();
      syncFiltersFromUi();
      countAdvancedFilters();
      reload(table);
      closePanel();
    });

    advancedResetButton?.addEventListener("click", (event) => {
      event.preventDefault();
      clearAdvancedFields();
      syncFiltersFromUi();
      reload(table);

      if (panelOpen) {
        positionPanel();
      }
    });

    clearButton?.addEventListener("click", (event) => {
      event.preventDefault();
      resetAllFilters(table);
    });

    table.on("dataLoaded", () => {
      tableElement.classList.remove("is-loading");
      updateInfo(table);
    });

    table.on("pageLoaded", () => {
      updateInfo(table);
    });

    syncFiltersFromUi();
    countAdvancedFilters();

    tableElement.addEventListener("click", async (event) => {
      const deleteButton = event.target.closest("[data-action='delete-air']");
      const restoreButton = event.target.closest("[data-action='restore-air']");
      const forceDeleteButton = event.target.closest("[data-action='force-delete-air']");

      if (!deleteButton && !restoreButton && !forceDeleteButton) {
        return;
      }

      event.preventDefault();

      if (deleteButton) {
        const confirmation = await Swal.fire({
          icon: "warning",
          title: "Archive this AIR?",
          text: "This will move the AIR to Archived.",
          showCancelButton: true,
          confirmButtonText: "Yes, archive",
          cancelButtonText: "Cancel",
          reverseButtons: true,
        });

        if (!confirmation.isConfirmed) {
          return;
        }

        try {
          await requestJson(deleteButton.dataset.deleteUrl || "", "DELETE", config.csrf);
          await Swal.fire({
            icon: "success",
            title: "Archived",
            text: "AIR archived successfully.",
            timer: 1100,
            showConfirmButton: false,
          });
          reload(table);
        } catch (error) {
          await Swal.fire({
            icon: "error",
            title: "Archive failed",
            text: error?.message || "Please try again.",
          });
        }

        return;
      }

      if (restoreButton) {
        const confirmation = await Swal.fire({
          icon: "question",
          title: "Restore this AIR?",
          text: "This will restore the AIR from Archived.",
          showCancelButton: true,
          confirmButtonText: "Yes, restore",
          cancelButtonText: "Cancel",
          reverseButtons: true,
        });

        if (!confirmation.isConfirmed) {
          return;
        }

        try {
          await requestJson(restoreButton.dataset.restoreUrl || "", "PATCH", config.csrf);
          await Swal.fire({
            icon: "success",
            title: "Restored",
            text: "AIR restored successfully.",
            timer: 1100,
            showConfirmButton: false,
          });
          reload(table);
        } catch (error) {
          await Swal.fire({
            icon: "error",
            title: "Restore failed",
            text: error?.message || "Please try again.",
          });
        }

        return;
      }

      const confirmation = await Swal.fire({
        icon: "error",
        title: "Permanently delete this AIR?",
        html: "This action <b>cannot be undone</b>. The AIR will be removed permanently.",
        showCancelButton: true,
        confirmButtonText: "Yes, permanently delete",
        cancelButtonText: "Cancel",
        reverseButtons: true,
      });

      if (!confirmation.isConfirmed) {
        return;
      }

      try {
        await requestJson(forceDeleteButton.dataset.forceUrl || "", "DELETE", config.csrf);
        await Swal.fire({
          icon: "success",
          title: "Deleted permanently",
          text: "AIR permanently deleted.",
          timer: 1100,
          showConfirmButton: false,
        });
        reload(table);
      } catch (error) {
        await Swal.fire({
          icon: "error",
          title: "Permanent delete failed",
          text: error?.message || "Please try again.",
        });
      }
    });
  });
})();
