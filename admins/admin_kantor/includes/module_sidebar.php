<?php
/**
 * File    : admins/admin_kantor/includes/module_sidebar.php
 * Fungsi  : Sidebar dinamis modul kantor
 */

if (!defined('MODULE_SLUG')) exit;

$active_menu = $active_menu ?? 'dashboard';
$user        = $user        ?? current_user(MODULE_SLUG);

$menu = [
    ['section'=>'Utama'],
    ['key'=>'dashboard',    'label'=>'Dashboard',      'icon'=>'fa-gauge',              'url'=>'dashboard.php'],

    ['section'=>'Persuratan'],
    ['key'=>'surat_masuk',  'label'=>'Surat Masuk',    'icon'=>'fa-envelope-open-text', 'url'=>'pages/surat_masuk.php'],
    ['key'=>'surat_keluar', 'label'=>'Surat Keluar',   'icon'=>'fa-paper-plane',        'url'=>'pages/surat_keluar.php'],
    ['key'=>'disposisi',    'label'=>'Disposisi',      'icon'=>'fa-share-nodes',        'url'=>'pages/disposisi.php'],
    ['key'=>'klasifikasi',  'label'=>'Klasifikasi',    'icon'=>'fa-tags',               'url'=>'pages/klasifikasi.php'],

    ['section'=>'SDM & Kehadiran'],
    ['key'=>'pegawai',      'label'=>'Pegawai',        'icon'=>'fa-users',              'url'=>'pages/pegawai.php'],
    ['key'=>'absensi',      'label'=>'Absensi',        'icon'=>'fa-fingerprint',        'url'=>'pages/absensi.php'],
    ['key'=>'cuti',         'label'=>'Cuti / Izin',    'icon'=>'fa-calendar-check',     'url'=>'pages/cuti.php'],

    ['section'=>'Aset & Operasional'],
    ['key'=>'aset',         'label'=>'Aset',           'icon'=>'fa-boxes-stacked',      'url'=>'pages/aset.php'],
    ['key'=>'ruangan',      'label'=>'Ruangan',        'icon'=>'fa-door-open',          'url'=>'pages/ruangan.php'],
    ['key'=>'kendaraan',    'label'=>'Kendaraan',      'icon'=>'fa-car',                'url'=>'pages/kendaraan.php'],
    ['key'=>'reimbursement','label'=>'Reimbursement',  'icon'=>'fa-receipt',            'url'=>'pages/reimbursement.php'],

    ['section'=>'Dokumen & Laporan'],
    ['key'=>'dokumen',      'label'=>'Arsip Digital',  'icon'=>'fa-folder-open',        'url'=>'pages/dokumen.php'],
    ['key'=>'laporan',      'label'=>'Laporan',        'icon'=>'fa-file-lines',         'url'=>'pages/laporan.php'],
];

$info = $GLOBALS['MODULE_INFO'] ?? [];
$nama = $info['nama'] ?? MODULE_NAME;
$role = function_exists('kantor_role') ? kantor_role() : 'staff';
?>
<aside class="sidebar">
  <div class="sidebar-brand">
    <i class="fa-solid fa-building"></i>
    <div>
      <div class="brand-title"><?= e($nama) ?></div>
      <div class="brand-sub"><?= e($user['nama_lengkap'] ?? '') ?> &middot; <?= e(ucfirst($role)) ?></div>
    </div>
  </div>

  <nav class="sidebar-nav">
    <?php foreach ($menu as $m): ?>
      <?php if (isset($m['section'])): ?>
        <div class="sidebar-section"><?= e($m['section']) ?></div>
      <?php else: ?>
        <a href="<?= e(url('admins/' . MODULE_SLUG . '/' . $m['url'])) ?>"
           class="sidebar-link <?= $active_menu === $m['key'] ? 'active' : '' ?>">
          <i class="fa-solid <?= e($m['icon']) ?>"></i>
          <span><?= e($m['label']) ?></span>
        </a>
      <?php endif; ?>
    <?php endforeach; ?>
  </nav>

  <div class="sidebar-footer">
    <a href="<?= e(url('admins/' . MODULE_SLUG . '/logout.php')) ?>" class="sidebar-link logout">
      <i class="fa-solid fa-right-from-bracket"></i>
      <span>Logout</span>
    </a>
  </div>
</aside>