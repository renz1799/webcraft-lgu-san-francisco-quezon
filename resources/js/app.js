// Import custom scripts
import "../assets/js/custom";

// Import SweetAlert2 JavaScript
import Swal from "sweetalert2";

// Import SweetAlert2 CSS (for styling)
import "sweetalert2/dist/sweetalert2.min.css";

// Test SweetAlert2 popup
let timerInterval;

Swal.fire({
    title: "Auto close alert!",
    html: "I will close in <b></b> milliseconds.",
    timer: 5000,  // Set the timer for 5 seconds
    timerProgressBar: true,
    showConfirmButton: false,
    didOpen: () => {
        const b = Swal.getHtmlContainer().querySelector("b");
        timerInterval = setInterval(() => {
            b.textContent = Swal.getTimerLeft();
        }, 100);
    },
    willClose: () => {
        clearInterval(timerInterval);
    }
});

