<?php
/**
 * File    : admins/admin_kantor/dashboard.php
 * Fungsi  : Dashboard Admin Kantor
 */

require_once __DIR__ . '/config.php';
auth_check(MODULE_SLUG);

$user = current_user(MODULE_SLUG);

// Statistik
$sm_bulan = (int) db()->query("SELECT COUNT(*) FROM kantor_surat_masuk
    WHERE MONTH(tanggal_terima)=MONTH(CURDATE()) AND YEAR(tanggal_terima)=YEAR(CURDATE())")->fetchColumn();
$sk_bulan = (int) db()->query("SELECT COUNT(*) FROM kantor_surat_keluar
    WHERE MONTH(tanggal_surat)=MONTH(CURDATE()) AND YEAR(tanggal_surat)=YEAR(CURDATE())")->fetchColumn();
$total_dok = (int) db()->query("SELECT COUNT(*) FROM kantor_dokumen")->fetchColumn();
$disp_pending = (int) db()->query("SELECT COUNT(*) FROM kantor_disposisi WHERE status='baru'")->fetchColumn();

$disposisiPending = db()->query(
    "SELECT d.*, s.no_agenda, s.perihal FROM kantor_disposisi d
     JOIN kantor_surat_masuk s ON s.id=d.surat_masuk_id
     WHERE d.status='baru' ORDER BY d.created_at DESC LIMIT 5"
)->fetchAll();

$suratTerbaru = db()->query(
    "SELECT id, no_agenda, asal_surat, perihal, tanggal_terima, status, sifat
     FROM kantor_surat_masuk ORDER BY id DESC LIMIT 5"
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
      <div class="subtitle">Selamat datang, <strong><?= e($user['nama_lengkap']) ?></strong> 👋</div>
    </div>
    <a href="<?= e(url('index.php')) ?>" class="btn btn-outline-secondary btn-sm">
      <i class="fa-solid fa-house"></i>
    </a>
  </div>

  <!-- STAT CARDS -->
  <div class="row g-3 mb-4">
    <?php
      $cards = [
        ['Surat Masuk Bulan Ini', $sm_bulan,     'fa-envelope-open-text', '#4f46e5', '#eef2ff'],
        ['Surat Keluar Bulan Ini',$sk_bulan,     'fa-paper-plane',        '#059669', '#ecfdf5'],
        ['Arsip Digital',         $total_dok,    'fa-folder-open',        '#d97706', '#fffbeb'],
        ['Disposisi Pending',     $disp_pending, 'fa-share-nodes',        '#dc2626', '#fef2f2'],
      ];
      foreach ($cards as $c): ?>
      <div class="col-6 col-lg-3">
        <div class="stat-card">
          <div class="d-flex justify-content-between align-items-start">
            <div>
              <div class="stat-label"><?= e($c[0]) ?></div>
              <div class="stat-value"><?= (int)$c[1] ?></div>
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
          <h6 class="fw-bold mb-3"><i class="fa-solid fa-chart-line me-2 text-primary"></i>Surat Masuk vs Keluar (6 Bulan)</h6>
          <canvas id="chartSurat" height="90"></canvas>
        </div>
      </div>
    </div>
    <div class="col-lg-4">
      <div class="card h-100">
        <div class="card-body">
          <h6 class="fw-bold mb-3"><i class="fa-solid fa-chart-pie me-2 text-success"></i>Surat per Sifat</h6>
          <canvas id="chartSifat" height="180"></canvas>
        </div>
      </div>
    </div>
  </div>

  <!-- TABLES -->
  <div class="row g-3">
    <div class="col-lg-6">
      <div class="card h-100">
        <div class="card-body">
          <h6 class="fw-bold mb-3 text-danger"><i class="fa-solid fa-bell me-2"></i>Disposisi Baru</h6>
          <?php if (!$disposisiPending): ?>
            <div class="text-center text-muted py-4">
              <i class="fa-solid fa-circle-check fa-2x mb-2 text-success"></i>
              <p class="mb-0">Tidak ada disposisi baru 👍</p>
            </div>
          <?php else: ?>
          <div class="table-responsive">
            <table class="table table-sm table-hover mb-0">
              <thead><tr><th>Agenda</th><th>Perihal</th><th>Kepada</th><th>Batas</th></tr></thead>
              <tbody>
                <?php foreach ($disposisiPending as $d): ?>
                <tr>
                  <td><code><?= e($d['no_agenda']) ?></code></td>
                  <td class="text-truncate" style="max-width:150px"><?= e($d['perihal']) ?></td>
                  <td><?= e($d['kepada']) ?></td>
                  <td><?= $d['batas_waktu'] ? e(date('d/m', strtotime($d['batas_waktu']))) : '-' ?></td>
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
          <h6 class="fw-bold mb-3"><i class="fa-solid fa-clock-rotate-left me-2 text-warning"></i>5 Surat Masuk Terbaru</h6>
          <?php if (!$suratTerbaru): ?>
            <div class="text-center text-muted py-4">
              <i class="fa-solid fa-inbox fa-2x mb-2 opacity-25"></i>
              <p class="mb-0">Belum ada surat.</p>
            </div>
          <?php else: ?>
          <div class="table-responsive">
            <table class="table table-sm table-hover mb-0">
              <thead><tr><th>Agenda</th><th>Asal</th><th>Sifat</th><th>Status</th></tr></thead>
              <tbody>
                <?php foreach ($suratTerbaru as $s): ?>
                <tr>
                  <td><code><?= e($s['no_agenda']) ?></code></td>
                  <td class="text-truncate" style="max-width:120px"><?= e($s['asal_surat']) ?></td>
                  <td><span class="badge badge-<?= e($s['sifat']) ?>"><?= e($s['sifat']) ?></span></td>
                  <td><span class="badge bg-secondary"><?= e($s['status']) ?></span></td>
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
    const r = await fetch('<?= e(url('admins/admin_kantor/api/dashboard.php')) ?>');
    const d = await r.json();

    new Chart(document.getElementById('chartSurat'), {
      type:'line',
      data:{labels:d.bulan, datasets:[
        {label:'Masuk',  data:d.surat_masuk,  borderColor:'#4f46e5', backgroundColor:'rgba(79,70,229,.1)',  fill:true, tension:.4, borderWidth:2, pointRadius:4},
        {label:'Keluar', data:d.surat_keluar, borderColor:'#059669', backgroundColor:'rgba(5,150,105,.1)', fill:true, tension:.4, borderWidth:2, pointRadius:4}
      ]},
      options:{responsive:true, plugins:{legend:{position:'bottom', labels:{usePointStyle:true, padding:16}}}}
    });

    new Chart(document.getElementById('chartSifat'), {
      type:'doughnut',
      data:{labels:d.sifat_label, datasets:[{data:d.sifat_total,
        backgroundColor:['#6b7280','#f59e0b','#ea580c','#dc2626'], borderWidth:0}]},
      options:{plugins:{legend:{position:'bottom', labels:{usePointStyle:true, padding:12}}}, cutout:'65%'}
    });
  } catch (e) { console.error('Chart error:', e); }
});
</script>

<?php include __DIR__ . '/includes/module_footer.php'; ?>