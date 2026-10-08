/* File    : assets/js/app.js
   Fungsi  : JS global aplikasi */

document.addEventListener('DOMContentLoaded', () => {
  // Auto-dismiss alert setelah 5 detik
  document.querySelectorAll('.alert-dismissible').forEach(el => {
    setTimeout(() => {
      if (window.bootstrap) new bootstrap.Alert(el).close();
    }, 5000);
  });
});