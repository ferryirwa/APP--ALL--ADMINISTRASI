<?php
/**
 * File    : admins/admin_karyawan/dashboard.php
 * Fungsi  : Dashboard Admin Karyawan
 */

require_once __DIR__ . '/config.php';
auth_check(MODULE_SLUG);

$user = current_user(MODULE_SLUG);

// Statistik dummy untuk Tahap 1 (akan diisi di Tahap 4)
$statistik = [
    'total_karyawan'   => 0,
    'hadir_hari_ini'   => 0,
    'total_penggajian' => 0,
    'jumlah_divisi'    => 0,
];
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Dashboard <?= e(MODULE_NAME) ?></title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
<link href="<?= e(url('assets/css/style.css')) ?>" rel="stylesheet">
</head>
<body>
<div class="container-fluid">
  <div class="row">
    <!-- SIDEBAR -->
    <aside class="col-md-2 sidebar p-3">
      <h5 class="text-white mb-4">
        <i class="<?= e($GLOBALS['MODULE_INFO']['icon']) ?> me-1"></i> <?= e(MODULE_NAME) ?>
      </h5>
      <a href="dashboard.php" class="active"><i class="fa-solid fa-gauge me-2"></i>Dashboard</a>
      <a href="#"><i class="fa-solid fa-id-card me-2"></i>Data Karyawan</a>
      <a href="#"><i class="fa-solid fa-calendar-check me-2"></i>Absensi</a>
      <a href="#"><i class="fa-solid fa-money-bill-wave me-2"></i>Penggajian</a>
      <a href="#"><i class="fa-solid fa-file-lines me-2"></i>Laporan</a>
      <hr class="text-secondary">
      <a href="logout.php" class="text-danger"><i class="fa-solid fa-right-from-bracket me-2"></i>Logout</a>
    </aside>

    <!-- MAIN -->
    <main class="col-md-10 p-4">
      <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
          <h4 class="fw-bold mb-0">Dashboard <?= e(MODULE_NAME) ?></h4>
          <small class="text-muted">Selamat datang, <?= e($user['nama_lengkap']) ?> 👋</small>
        </div>
        <a href="<?= e(url('index.php')) ?>" class="btn btn-outline-secondary btn-sm">
          <i class="fa-solid fa-house me-1"></i> Halaman Utama
        </a>
      </div>

      <div class="row g-3 mb-4">
        <div class="col-6 col-lg-3">
          <div class="card border-0 shadow-sm"><div class="card-body">
            <small class="text-muted">Total Karyawan</small>
            <h3 class="fw-bold mb-0"><?= (int)$statistik['total_karyawan'] ?></h3>
          </div></div>
        </div>
        <div class="col-6 col-lg-3">
          <div class="card border-0 shadow-sm"><div class="card-body">
            <small class="text-muted">Hadir Hari Ini</small>
            <h3 class="fw-bold mb-0"><?= (int)$statistik['hadir_hari_ini'] ?></h3>
          </div></div>
        </div>
        <div class="col-6 col-lg-3">
          <div class="card border-0 shadow-sm"><div class="card-body">
            <small class="text-muted">Penggajian Bulan Ini</small>
            <h3 class="fw-bold mb-0">Rp <?= number_format($statistik['total_penggajian'], 0, ',', '.') ?></h3>
          </div></div>
        </div>
        <div class="col-6 col-lg-3">
          <div class="card border-0 shadow-sm"><div class="card-body">
            <small class="text-muted">Jumlah Divisi</small>
            <h3 class="fw-bold mb-0"><?= (int)$statistik['jumlah_divisi'] ?></h3>
          </div></div>
        </div>
      </div>

      <div class="card border-0 shadow-sm">
        <div class="card-body">
          <h6 class="fw-bold mb-3">Grafik (akan diisi di Tahap 4)</h6>
          <canvas id="chartKaryawan" height="80"></canvas>
        </div>
      </div>
    </main>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
const ctx = document.getElementById('chartKaryawan');
new Chart(ctx, {
  type: 'doughnut',
  data: {
    labels: ['Tetap','Kontrak','Magang','Harian'],
    datasets: [{
      data: [40, 25, 10, 15],
      backgroundColor: ['#ffc107','#0d6efd','#198754','#dc3545']
    }]
  },
  options: { responsive: true }
});
</script>
</body>
</html>