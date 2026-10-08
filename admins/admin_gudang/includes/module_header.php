<?php
/**
 * File    : admins/admin_gudang/includes/module_header.php
 * Fungsi  : Header dashboard modul gudang
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

<!-- ============================================ -->
<!-- SCRIPT UTAMA — DIMUAT DI HEAD, SEBELUM BODY -->
<!-- ============================================ -->
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
<script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.6/dist/JsBarcode.all.min.js"></script>
<script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>

<script>
// Konfigurasi global (setelah jQuery loaded)
window.GUDANG = {
  baseUrl: '<?= e(url('admins/admin_gudang/')) ?>',
  csrf   : '<?= e(csrf_token()) ?>'
};
console.log('✓ jQuery:', typeof jQuery);
console.log('✓ DataTables:', typeof jQuery !== 'undefined' && jQuery.fn ? typeof jQuery.fn.DataTable : 'missing');
console.log('✓ GUDANG config:', window.GUDANG);
</script>

<style>
  .barcode-wrap { text-align:center;padding:8px;border:1px dashed #ccc;border-radius:6px;background:#fff; }
  .barcode-wrap svg { max-width:100%; height:auto; }
  .barcode-label { border:1px solid #ddd;border-radius:6px;padding:10px;text-align:center;background:#fff; }
  .barcode-label .kode { font-size:11px; }
  .barcode-label .nama { font-size:12px;font-weight:600; }
  #reader { width:100%;max-width:480px;margin:0 auto; }
  .scan-video { width:100%;max-width:480px;border-radius:10px; }
</style>
</head>
<body>
<div class="layout">