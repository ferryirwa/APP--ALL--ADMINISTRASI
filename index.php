<?php
/**
 * File    : index.php
 * Fungsi  : Halaman utama - grid kartu semua jenis administrasi
 * Author  : Tim Aplikasi Administrasi
 * Tanggal : 2024
 */

require_once __DIR__ . '/config/app.php';

$jenisList = get_semua_jenis_admin();
$stat      = get_statistik_global();
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e(APP_NAME) ?> - <?= e(APP_TAGLINE) ?></title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
<link href="<?= e(url('assets/css/style.css')) ?>" rel="stylesheet">
</head>
<body>

<!-- HERO -->
<section class="hero text-white text-center py-5">
  <div class="container py-4">
    <h1 class="display-4 fw-bold mb-3">
      <i class="fa-solid fa-briefcase me-2"></i><?= e(APP_NAME) ?>
    </h1>
    <p class="lead mb-0"><?= e(APP_TAGLINE) ?></p>
  </div>
</section>

<!-- GRID KARTU ADMIN -->
<main class="container my-5">
  <div class="text-center mb-4">
    <h2 class="fw-semibold">Pilih Jenis Administrasi</h2>
    <p class="text-muted">Klik salah satu kartu untuk masuk ke dashboard modul</p>
  </div>

  <div class="row g-4">
    <?php foreach ($jenisList as $j): ?>
      <div class="col-12 col-sm-6 col-lg-4">
        <div class="card admin-card h-100 shadow-sm border-0">
          <div class="card-body text-center p-4">
            <div class="admin-icon mb-3" style="background: <?= e($j['warna']) ?>20; color: <?= e($j['warna']) ?>;">
              <i class="<?= e($j['icon']) ?>"></i>
            </div>
            <h5 class="card-title fw-bold"><?= e($j['nama']) ?></h5>
            <p class="card-text text-muted small"><?= e($j['deskripsi']) ?></p>
            <a href="<?= e(url('login_gateway.php?slug=' . urlencode($j['slug']))) ?>"
               class="btn btn-primary w-100 mt-2">
              <i class="fa-solid fa-right-to-bracket me-1"></i> Masuk
            </a>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
</main>

<!-- STATISTIK -->
<section class="bg-light py-5">
  <div class="container">
    <div class="row text-center g-4">
      <div class="col-6 col-md-4">
        <div class="stat-box">
          <i class="fa-solid fa-layer-group fa-2x text-primary mb-2"></i>
          <h3 class="fw-bold mb-0"><?= (int)$stat['totalJenis'] ?></h3>
          <small class="text-muted">Jenis Administrasi</small>
        </div>
      </div>
      <div class="col-6 col-md-4">
        <div class="stat-box">
          <i class="fa-solid fa-users fa-2x text-success mb-2"></i>
          <h3 class="fw-bold mb-0"><?= (int)$stat['totalUser'] ?></h3>
          <small class="text-muted">User Terdaftar</small>
        </div>
      </div>
      <div class="col-6 col-md-4">
        <div class="stat-box">
          <i class="fa-solid fa-right-to-bracket fa-2x text-warning mb-2"></i>
          <h3 class="fw-bold mb-0"><?= (int)$stat['totalLogin'] ?></h3>
          <small class="text-muted">Total Login</small>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- FOOTER -->
<footer class="bg-dark text-white text-center py-3">
  <small>&copy; <?= date('Y') ?> <?= e(APP_NAME) ?> v<?= e(APP_VERSION) ?></small>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>