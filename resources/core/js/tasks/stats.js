(function () {
  "use strict";

  if (window.__tasksStatsBound) return;
  window.__tasksStatsBound = true;

  function onReady(fn) {
    if (document.readyState === "loading") {
      document.addEventListener("DOMContentLoaded", fn);
      return;
    }

    fn();
  }

  function toNumber(value) {
    const n = Number(value ?? 0);
    return Number.isFinite(n) ? n : 0;
  }

  function formatNumber(value) {
    return new Intl.NumberFormat().format(toNumber(value));
  }

  function buildScopeLabel(filters = {}) {
    const showModuleColumn = window.__tasks?.showModuleColumn !== false;
    const ownerModules = Array.isArray(window.__tasks?.ownerModules) ? window.__tasks.ownerModules : [];
    const selectedModuleId = String(filters.module_id || "").trim();
    const selectedModule = ownerModules.find((module) => String(module?.id || "").trim() === selectedModuleId);
    const parts = [];

    const scope = String(filters.scope || "mine").trim();
    if (scope === "all") parts.push("all tasks");
    else if (scope === "available") parts.push("claimable tasks");
    else parts.push("my tasks");

    const archived = String(filters.archived || "active").trim();
    if (archived === "archived") parts.push("archived only");
    else if (archived === "all") parts.push("active + archived");
    else parts.push("active only");

    const status = String(filters.status || "").trim();
    if (status) {
      parts.push(`status: ${status.replace(/_/g, " ")}`);
    }

    if (showModuleColumn && selectedModule) {
      parts.push(`origin: ${selectedModule.name}`);
    }

    return parts.join(" | ");
  }

  function countActiveFilters(filters = {}) {
    const showModuleColumn = window.__tasks?.showModuleColumn !== false;
    let count = 0;

    if (String(filters.search || "").trim() !== "") count++;
    if (String(filters.archived || "active").trim() !== "active") count++;
    if (String(filters.scope || "mine").trim() !== "mine") count++;
    if (showModuleColumn && String(filters.module_id || "").trim() !== "") count++;
    if (String(filters.status || "").trim() !== "") count++;
    if (String(filters.assigned_to || "").trim() !== "") count++;
    if (String(filters.date_from || "").trim() !== "") count++;
    if (String(filters.date_to || "").trim() !== "") count++;

    return count;
  }

  function initLiveSidebar() {
    const panel = document.getElementById("tasks-stats-panel");
    if (!panel) return;

    const cfg = window.__tasks || {};
    const sidebarCounts = cfg.sidebarCounts || {};

    const state = {
      visibleTotal: 0,
      myTotal: toNumber(sidebarCounts.my),
      claimableTotal: toNumber(sidebarCounts.claimable),
      pageRows: 0,
        filters: {
          search: "",
          archived: "active",
          scope: "mine",
          module_id: "",
          status: "",
          assigned_to: "",
          date_from: "",
        date_to: "",
      },
    };

    const els = {
      visible: document.getElementById("tasks-visible-total"),
      my: document.getElementById("tasks-my-total"),
      claimable: document.getElementById("tasks-claimable-total"),
      myBadge: document.getElementById("tasks-my-badge"),
      claimableBadge: document.getElementById("tasks-claimable-badge"),
      pageRows: document.getElementById("tasks-page-total"),
      caption: document.getElementById("tasks-stats-caption"),
      activeFilters: document.getElementById("tasks-filters-active"),
      chart: document.getElementById("task-list-stats"),
    };

    let chart = null;

    function renderCards() {
      if (els.visible) els.visible.textContent = formatNumber(state.visibleTotal);
      if (els.my) els.my.textContent = formatNumber(state.myTotal);
      if (els.claimable) els.claimable.textContent = formatNumber(state.claimableTotal);
      if (els.myBadge) els.myBadge.textContent = formatNumber(state.myTotal);
      if (els.claimableBadge) els.claimableBadge.textContent = formatNumber(state.claimableTotal);
      if (els.pageRows) els.pageRows.textContent = formatNumber(state.pageRows);

      const activeFilters = countActiveFilters(state.filters);
      if (els.activeFilters) {
        els.activeFilters.textContent = activeFilters === 0
          ? "No extra filters"
          : `${activeFilters} filter${activeFilters === 1 ? "" : "s"}`;
      }

      if (els.caption) {
        els.caption.textContent = `Live snapshot for ${buildScopeLabel(state.filters)}.`;
      }
    }

    function seriesValues() {
      return [
        toNumber(state.visibleTotal),
        toNumber(state.myTotal),
        toNumber(state.claimableTotal),
        toNumber(state.pageRows),
      ];
    }

    function renderChart() {
      if (!els.chart || typeof window.ApexCharts === "undefined") {
        return;
      }

      const options = {
        series: [{ data: seriesValues() }],
        chart: {
          type: "bar",
          height: 260,
          toolbar: { show: false },
        },
        plotOptions: {
          bar: {
            horizontal: true,
            borderRadius: 6,
            barHeight: "42%",
            distributed: true,
          },
        },
        colors: ["#845adf", "#28d193", "#ffbe14", "#23b7e5"],
        dataLabels: {
          enabled: true,
          formatter: function (value) {
            return formatNumber(value);
          },
        },
        xaxis: {
          categories: ["Visible", "My Active", "Claimable", "Rows On Page"],
          labels: {
            style: {
              colors: "#8c9097",
              fontSize: "11px",
              fontWeight: 600,
            },
          },
        },
        yaxis: {
          labels: {
            style: {
              colors: "#8c9097",
              fontSize: "11px",
              fontWeight: 600,
            },
          },
        },
        grid: {
          borderColor: "#f2f5f7",
          strokeDashArray: 4,
        },
        legend: { show: false },
        tooltip: {
          y: {
            formatter: function (value) {
              return `${formatNumber(value)} task(s)`;
            },
          },
        },
      };

      if (!chart) {
        chart = new window.ApexCharts(els.chart, options);
        chart.render();
        return;
      }

      chart.updateSeries([{ data: seriesValues() }]);
    }

    window.__tasksUpdateSidebarStats = function (payload = {}) {
      state.visibleTotal = toNumber(payload.visibleTotal);
      state.pageRows = toNumber(payload.currentPageRows);
      state.filters = {
        ...state.filters,
        ...(payload.filters || {}),
      };

      renderCards();
      renderChart();
    };

    renderCards();
    renderChart();
  }

  function initAdminSidebar() {
    const panel = document.getElementById("tasks-admin-stats-panel");
    if (!panel) return;

    const chartEl = document.getElementById("task-admin-stats-chart");
    if (!chartEl) return;

    const adminStats = window.__tasks?.adminStats || {};
    const chartConfig = adminStats.chart || {};
    const categories = Array.isArray(chartConfig.categories) ? chartConfig.categories : [];
    const series = Array.isArray(chartConfig.series) ? chartConfig.series : [];

    if (!categories.length || !series.length || typeof window.ApexCharts === "undefined") {
      chartEl.innerHTML = '<div class="text-xs text-[#8c9097]">No task history available yet.</div>';
      return;
    }

    const chart = new window.ApexCharts(chartEl, {
      series,
      chart: {
        type: "bar",
        height: 290,
        stacked: true,
        toolbar: { show: false },
      },
      plotOptions: {
        bar: {
          horizontal: false,
          columnWidth: "38%",
          borderRadius: 3,
        },
      },
      colors: ["#845adf", "#f5b849", "#28d193", "#23b7e5"],
      dataLabels: { enabled: false },
      stroke: {
        show: true,
        width: 1,
        colors: ["transparent"],
      },
      legend: {
        position: "bottom",
        horizontalAlign: "left",
        offsetY: 4,
      },
      grid: {
        borderColor: "#f2f5f7",
        strokeDashArray: 4,
      },
      xaxis: {
        categories,
        labels: {
          style: {
            colors: "#8c9097",
            fontSize: "11px",
            fontWeight: 600,
          },
        },
      },
      yaxis: {
        labels: {
          formatter: function (value) {
            return formatNumber(value);
          },
          style: {
            colors: "#8c9097",
            fontSize: "11px",
            fontWeight: 600,
          },
        },
      },
      tooltip: {
        y: {
          formatter: function (value) {
            return `${formatNumber(value)} task(s)`;
          },
        },
      },
    });

    chart.render();
  }

  onReady(function () {
    initLiveSidebar();
    initAdminSidebar();
  });
})();
