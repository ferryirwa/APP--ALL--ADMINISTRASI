<?php
/**
 * File    : admins/admin_gudang/includes/module_sidebar.php
 * Fungsi  : Sidebar dinamis modul gudang
 */

if (!defined('MODULE_SLUG')) exit;

// Fallback agar tidak error jika variabel belum di-set
$active_menu = $active_menu ?? 'dashboard';
$GLOBALS['MODULE_INFO'] = $GLOBALS['MODULE_INFO'] ?? [];

$menu = [
    'dashboard' => ['label'=>'Dashboard',    'icon'=>'fa-gauge',        'url'=>'dashboard.php'],
    'barang'    => ['label'=>'Barang',       'icon'=>'fa-box',          'url'=>'pages/barang.php'],
    'barcode'   => ['label'=>'Barcode',      'icon'=>'fa-barcode',      'url'=>'pages/barcode.php'],
    'scan'      => ['label'=>'Scan Barcode', 'icon'=>'fa-camera',       'url'=>'pages/scan.php'],
    'stok'      => ['label'=>'Stok',         'icon'=>'fa-warehouse',    'url'=>'pages/stok.php'],
    'supplier'  => ['label'=>'Supplier',     'icon'=>'fa-truck',        'url'=>'pages/supplier.php'],
    'mutasi'    => ['label'=>'Mutasi',       'icon'=>'fa-right-left',   'url'=>'pages/mutasi.php'],
    'laporan'   => ['label'=>'Laporan',      'icon'=>'fa-file-lines',   'url'=>'pages/laporan.php'],
];
$icon  = $GLOBALS['MODULE_INFO']['icon'] ?? 'fa-solid fa-warehouse';
$nama  = $GLOBALS['MODULE_INFO']['nama'] ?? MODULE_NAME;
?>
<aside class="sidebar">
  <div class="sidebar-brand">
    <i class="fa-solid <?= e(str_replace('fa-solid ', '', $icon)) ?>"></i>
    <span><?= e($nama) ?></span>
  </div>

  <nav class="sidebar-nav">
    <?php foreach ($menu as $key => $item): ?>
      <a href="<?= e(url('admins/' . MODULE_SLUG . '/' . $item['url'])) ?>"
         class="sidebar-link <?= $active_menu === $key ? 'active' : '' ?>">
        <i class="fa-solid <?= e($item['icon']) ?>"></i>
        <span><?= e($item['label']) ?></span>
      </a>
    <?php endforeach; ?>
  </nav>

  <div class="sidebar-footer">
    <a href="<?= e(url('admins/' . MODULE_SLUG . '/logout.php')) ?>" class="sidebar-link logout">
      <i class="fa-solid fa-right-from-bracket"></i>
      <span>Logout</span>
    </a>
  </div>
</aside>