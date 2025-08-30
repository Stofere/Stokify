// resources/js/app.js
import "./bootstrap";

import TomSelect from "tom-select";
window.TomSelect = TomSelect;

import Swal from "sweetalert2";
window.Swal = Swal;

// Optional: preset Toast yang konsisten di seluruh app
window.Toast = Swal.mixin({
    toast: true,
    position: "top-end",
    showConfirmButton: false,
    timer: 2000,
    timerProgressBar: true,
});
