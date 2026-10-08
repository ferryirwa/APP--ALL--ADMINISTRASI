<?php
/**
 * File    : admins/admin_gudang/dashboard.php
 * Fungsi  : Dashboard Admin Gudang (statistik nyata + grafik)
 */

require_once __DIR__ . '/config.php';
auth_check(MODULE_SLUG);

$user          = current_user(MODULE_SLUG);
$totalBarang   = (int) db()->query("SELECT COUNT(*) FROM gudang_barang WHERE is_active=1")->fetchColumn();
$totalSupplier = (int) db()->query("SELECT COUNT(*) FROM gudang_supplier WHERE is_active=1")->fetchColumn();
$totalStok     = array_sum(gudang_map_stok());
$totalMutasiBulanIni = (int) db()->query(
    "SELECT COUNT(*) FROM gudang_mutasi
     WHERE MONTH(tanggal)=MONTH(CURDATE()) AND YEAR(tanggal)=YEAR(CURDATE())"
)->fetchColumn();

$lowStock = db()->query(
    "SELECT b.id, b.kode, b.nama, b.stok_minimum,
       COALESCE((SELECT SUM(CASE WHEN s.tipe='masuk' THEN s.jumlah
                                 WHEN s.tipe='keluar' THEN -s.jumlah END)
                 FROM gudang_stok s WHERE s.barang_id=b.id),0) AS stok
     FROM gudang_barang b WHERE b.is_active=1
     HAVING stok <= b.stok_minimum ORDER BY stok ASC LIMIT 5"
)->fetchAll();

$mutasiTerbaru = db()->query(
    "SELECT m.*, b.nama AS nama_barang
     FROM gudang_mutasi m
     JOIN gudang_barang b ON b.id = m.barang_id
     ORDER BY m.id DESC LIMIT 5"
)->fetchAll();

$page_title  = 'Dashboard';
$active_menu = 'dashboard';
include __DIR__ . '/includes/module_header.php';
include __DIR__ . '/includes/module_sidebar.php';
?>
<main class="main">
  <div class="page-header">
    <div>
      <h1>Dashboard <?= e(MODULE_NAME) ?></h1>
      <div class="subtitle">Selamat datang kembali, <strong><?= e($user['nama_lengkap']) ?></strong> 👋</div>
    </div>
    <div class="d-flex gap-2">
      <a href="<?= e(url('admins/admin_gudang/pages/scan.php')) ?>" class="btn btn-primary btn-sm">
        <i class="fa-solid fa-camera me-1"></i> Scan Barcode
      </a>
      <a href="<?= e(url('index.php')) ?>" class="btn btn-outline-secondary btn-sm">
        <i class="fa-solid fa-house"></i>
      </a>
    </div>
  </div>

  <!-- STAT CARDS -->
  <div class="row g-3 mb-4">
    <?php
      $cards = [
        ['Total Barang',     $totalBarang,                          'fa-box',        '#4f46e5', '#eef2ff'],
        ['Total Stok',       number_format($totalStok,0,',','.'),   'fa-warehouse',  '#059669', '#ecfdf5'],
        ['Supplier Aktif',   $totalSupplier,                        'fa-truck',      '#d97706', '#fffbeb'],
        ['Mutasi Bulan Ini', $totalMutasiBulanIni,                  'fa-right-left', '#dc2626', '#fef2f2'],
      ];
      foreach ($cards as $c): ?>
      <div class="col-6 col-lg-3">
        <div class="stat-card">
          <div class="d-flex justify-content-between align-items-start">
            <div>
              <div class="stat-label"><?= e($c[0]) ?></div>
              <div class="stat-value"><?= $c[1] ?></div>
            </div>
            <div class="stat-icon" style="background:<?= $c[4] ?>;color:<?= $c[3] ?>">
              <i class="fa-solid <?= $c[2] ?>"></i>
            </div>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  </div>

  <!-- CHARTS -->
  <div class="row g-3 mb-4">
    <div class="col-lg-8">
      <div class="card h-100">
        <div class="card-body">
          <h6 class="fw-bold mb-3">
            <i class="fa-solid fa-chart-line me-2 text-primary"></i>
            Mutasi Masuk vs Keluar (6 Bulan)
          </h6>
          <canvas id="chartMutasi" height="90"></canvas>
        </div>
      </div>
    </div>
    <div class="col-lg-4">
      <div class="card h-100">
        <div class="card-body">
          <h6 class="fw-bold mb-3">
            <i class="fa-solid fa-chart-pie me-2 text-success"></i>
            Barang per Kategori
          </h6>
          <canvas id="chartKategori" height="180"></canvas>
        </div>
      </div>
    </div>
  </div>

  <!-- TABLES -->
  <div class="row g-3">
    <div class="col-lg-6">
      <div class="card h-100">
        <div class="card-body">
          <h6 class="fw-bold mb-3 text-danger">
            <i class="fa-solid fa-triangle-exclamation me-2"></i> Stok Minimum
          </h6>
          <?php if (!$lowStock): ?>
            <div class="text-center text-muted py-4">
              <i class="fa-solid fa-circle-check fa-2x mb-2 text-success"></i>
              <p class="mb-0">Semua stok aman 👍</p>
            </div>
          <?php else: ?>
          <div class="table-responsive">
            <table class="table table-sm table-hover mb-0">
              <thead>
                <tr><th>Kode</th><th>Nama</th><th class="text-end">Stok</th><th class="text-end">Min</th></tr>
              </thead>
              <tbody>
                <?php foreach ($lowStock as $r): ?>
                <tr>
                  <td><code><?= e($r['kode']) ?></code></td>
                  <td><?= e($r['nama']) ?></td>
                  <td class="text-end text-danger fw-bold"><?= (int)$r['stok'] ?></td>
                  <td class="text-end"><?= (int)$r['stok_minimum'] ?></td>
                </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
          <?php endif; ?>
        </div>
      </div>
    </div>

    <div class="col-lg-6">
      <div class="card h-100">
        <div class="card-body">
          <h6 class="fw-bold mb-3">
            <i class="fa-solid fa-clock-rotate-left me-2 text-warning"></i>
            5 Mutasi Terbaru
          </h6>
          <?php if (!$mutasiTerbaru): ?>
            <div class="text-center text-muted py-4">
              <i class="fa-solid fa-inbox fa-2x mb-2 opacity-25"></i>
              <p class="mb-0">Belum ada mutasi.</p>
            </div>
          <?php else: ?>
          <div class="table-responsive">
            <table class="table table-sm table-hover mb-0">
              <thead>
                <tr><th>Tgl</th><th>Barang</th><th>Tipe</th><th class="text-end">Jml</th></tr>
              </thead>
              <tbody>
                <?php foreach ($mutasiTerbaru as $m): ?>
                <tr>
                  <td><?= e(date('d/m', strtotime($m['tanggal']))) ?></td>
                  <td class="text-truncate" style="max-width:150px"><?= e($m['nama_barang']) ?></td>
                  <td>
                    <span class="badge bg-<?= $m['tipe']==='masuk'?'success':'danger' ?>">
                      <?= e($m['tipe']) ?>
                    </span>
                  </td>
                  <td class="text-end"><?= (int)$m['jumlah'] ?></td>
                </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>
</main>

<script>
document.addEventListener('DOMContentLoaded', async () => {
  try {
    const r = await fetch('<?= e(url('admins/admin_gudang/api/dashboard.php?action=chart')) ?>');
    const d = await r.json();

    new Chart(document.getElementById('chartMutasi'), {
      type: 'line',
      data: {
        labels: d.bulan,
        datasets: [
          {label:'Masuk',  data:d.masuk,  borderColor:'#059669',
           backgroundColor:'rgba(5,150,105,.1)',  fill:true, tension:.4, borderWidth:2, pointRadius:4},
          {label:'Keluar', data:d.keluar, borderColor:'#dc2626',
           backgroundColor:'rgba(220,38,38,.1)', fill:true, tension:.4, borderWidth:2, pointRadius:4},
        ]
      },
      options:{
        responsive:true,
        plugins:{legend:{position:'bottom', labels:{usePointStyle:true, padding:16}}}
      }
    });

    new Chart(document.getElementById('chartKategori'), {
      type: 'doughnut',
      data: {
        labels: d.kategori_label,
        datasets: [{
          data: d.kategori_total,
          backgroundColor:['#4f46e5','#059669','#d97706','#dc2626','#7c3aed','#0891b2'],
          borderWidth:0
        }]
      },
      options: {
        plugins:{legend:{position:'bottom', labels:{usePointStyle:true, padding:12}}},
        cutout:'65%'
      }
    });
  } catch (e) { console.error(e); }
});
</script>

<?php include __DIR__ . '/includes/module_footer.php'; ?>