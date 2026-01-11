import $ from "jquery";
import "datatables.net";

(function () {
  const escapeHtml = (s) =>
    String(s ?? "")
      .replace(/&/g, "&amp;")
      .replace(/</g, "&lt;")
      .replace(/>/g, "&gt;")
      .replace(/"/g, "&quot;")
      .replace(/'/g, "&#39;");

  $(document).ready(function () {
    $("#logs-table").DataTable({
      processing: true,
      serverSide: true,
      ajax: window.__LOGIN_LOGS_DATA_URL__, // injected from blade or set below
      pageLength: 20,
      lengthChange: false,
      responsive: true,
      autoWidth: false,

      // initial sort: Date DESC
      order: [[7, "desc"]],

      columns: [
        {
          data: "status",
          name: "success",
          render: (val, type, row) => {
            if (type !== "display") return val;

            if (val === "success") {
              return `<span class="badge bg-success/15 text-success">Success</span>`;
            }

            const reason = row.reason ? escapeHtml(row.reason) : "";
            return `
              <span class="badge bg-danger/15 text-danger">Failed</span>
              ${reason ? `<span class="text-xs text-[#8c9097] ms-2">— ${reason}</span>` : ""}
            `;
          },
        },
        { data: "user", name: "user" },
        { data: "email", name: "email" },
        { data: "ip_address", name: "ip_address" },
        { data: "device", name: "device" },
        { data: "address", name: "address" },
        {
          data: null,
          name: "location",
          orderable: false,
          searchable: false,
          render: (_, type, row) => {
            if (type !== "display") return row.location_url || "";
            if (!row.location_url) return "—";
            const url = escapeHtml(row.location_url);
            return `<a href="${url}" target="_blank" rel="noopener" class="text-primary hover:underline">Map</a>`;
          },
        },
        { data: "created_at", name: "created_at" },
      ],
    });
  });
})();
