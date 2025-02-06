import $ from "jquery"; // Ensure jQuery is available
import DataTable from "datatables.net-dt"; 
import "datatables.net-dt/css/dataTables.dataTables.min.css"; // Correct CSS path


window.$ = window.jQuery = $; // Ensure jQuery is globally available

export { DataTable }; // Export DataTable to be used in Blade
