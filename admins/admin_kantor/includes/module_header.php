<?php
/**
 * File    : admins/admin_kantor/includes/module_header.php
 * Fungsi  : Header dashboard modul kantor
 */

if (!defined('MODULE_SLUG')) exit('Akses langsung tidak diizinkan.');

$page_title  = $page_title  ?? 'Dashboard';
$active_menu = $active_menu ?? 'dashboard';
$user        = $user        ?? current_user(MODULE_SLUG);
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($page_title) ?> - <?= e(MODULE_NAME) ?></title>

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
<link href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css" rel="stylesheet">
<link href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.bootstrap5.min.css" rel="stylesheet">
<link href="<?= e(url('assets/css/style.css')) ?>" rel="stylesheet">

<!-- SCRIPT UTAMA (di head agar tersedia saat halaman render) -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.bootstrap5.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.print.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
window.KANTOR = {
  baseUrl: '<?= e(url('admins/admin_kantor/')) ?>',
  csrf   : '<?= e(csrf_token()) ?>'
};
console.log('✓ jQuery:', typeof jQuery);
console.log('✓ DataTables:', typeof jQuery !== 'undefined' && jQuery.fn ? typeof jQuery.fn.DataTable : 'missing');
</script>

<style>
  .badge-biasa   { background:#6c757d; color:#fff; }
  .badge-penting { background:#f59e0b; color:#fff; }
  .badge-segera  { background:#ea580c; color:#fff; }
  .badge-rahasia { background:#dc2626; color:#fff; }
</style>
</head>
<body>
<div class="layout">