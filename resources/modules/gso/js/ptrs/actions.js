import Swal from "sweetalert2";
import "sweetalert2/dist/sweetalert2.min.css";

(function () {
  "use strict";

  if (window.__ptrActionsBound) return;
  window.__ptrActionsBound = true;

  window.__ptrParseActionResponse = async function (res, fallback) {
    const ct = res.headers.get("content-type") || "";
    const data = ct.includes("application/json") ? await res.json().catch(() => null) : null;

    const errors = data?.errors;
    if (errors && typeof errors === "object") {
      const firstKey = Object.keys(errors)[0];
      if (firstKey && Array.isArray(errors[firstKey]) && errors[firstKey][0]) {
        return { ok: res.ok, message: errors[firstKey][0] };
      }
    }

    return {
      ok: res.ok,
      message: data?.message || fallback,
      data: data?.data || null,
    };
  };
})();
