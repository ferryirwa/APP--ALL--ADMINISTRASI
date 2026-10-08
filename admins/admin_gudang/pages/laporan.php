<?php
/**
 * File    : admins/admin_gudang/pages/laporan.php
 * Fungsi  : Laporan dengan filter tanggal + cetak
 */

require_once __DIR__ . '/../config.php';
auth_check(MODULE_SLUG);

$jenis   = $_GET['jenis']   ?? 'mutasi';
$dari    = $_GET['dari']    ?? date('Y-m-01');
$sampai  = $_GET['sampai']  ?? date('Y-m-d');
$tipe    = $_GET['tipe']    ?? '';

$data = [];
$judul = '';

if ($jenis === 'mutasi') {
    $judul = 'Laporan Mutasi Barang';
    $sql = "SELECT m.no_referensi, m.tanggal, b.kode, b.nama AS nama_barang,
                   s.nama AS nama_supplier, m.tipe, m.jumlah, m.keterangan
            FROM gudang_mutasi m
            JOIN gudang_barang b ON b.id=m.barang_id
            LEFT JOIN gudang_supplier s ON s.id=m.supplier_id
            WHERE m.tanggal BETWEEN ? AND ?";
    $params = [$dari, $sampai];
    if ($tipe) { $sql .= " AND m.tipe = ?"; $params[] = $tipe; }
    $sql .= " ORDER BY m.tanggal DESC, m.id DESC";
    $stmt = db()->prepare($sql); $stmt->execute($params); $data = $stmt->fetchAll();

} elseif ($jenis === 'stok') {
    $judul = 'Laporan Stok Barang';
    $sql = "SELECT b.kode, b.nama, b.kategori, b.satuan, b.stok_minimum,
              COALESCE((SELECT SUM(CASE WHEN s.tipe='masuk' THEN s.jumlah
                                        WHEN s.tipe='keluar' THEN -s.jumlah END)
                        FROM gudang_stok s WHERE s.barang_id=b.id),0) AS stok
            FROM gudang_barang b WHERE b.is_active=1
            ORDER BY b.nama";
    $data = db()->query($sql)->fetchAll();

} elseif ($jenis === 'supplier') {
    $judul = 'Laporan Supplier';
    $data = db()->query("SELECT kode,nama,kontak,telepon,email,alamat FROM gudang_supplier WHERE is_active=1 ORDER BY nama")->fetchAll();

} elseif ($jenis === 'minimum') {
    $judul = 'Laporan Barang Stok Minimum';
    $sql = "SELECT b.kode, b.nama, b.stok_minimum,
              COALESCE((SELECT SUM(CASE WHEN s.tipe='masuk' THEN s.jumlah
                                        WHEN s.tipe='keluar' THEN -s.jumlah END)
                        FROM gudang_stok s WHERE s.barang_id=b.id),0) AS stok
            FROM gudang_barang b WHERE b.is_active=1
            HAVING stok <= b.stok_minimum ORDER BY stok ASC";
    $data = db()->query($sql)->fetchAll();
}

$page_title = 'Laporan'; $active_menu = 'laporan';
include __DIR__ . '/../includes/module_header.php';
include __DIR__ . '/../includes/module_sidebar.php';
?>
<main class="col-md-10 p-4">

  <!-- FORM FILTER -->
  <div class="card border-0 shadow-sm mb-3 no-print">
    <div class="card-body">
      <h5 class="fw-bold mb-3"><i class="fa-solid fa-filter me-1"></i> Filter Laporan</h5>
      <form method="get" class="row g-3">
        <div class="col-md-3">
          <label class="form-label">Jenis Laporan</label>
          <select name="jenis" class="form-select">
            <option value="mutasi"   <?= $jenis==='mutasi'?'selected':'' ?>>Mutasi Barang</option>
            <option value="stok"     <?= $jenis==='stok'?'selected':'' ?>>Stok Barang</option>
            <option value="supplier" <?= $jenis==='supplier'?'selected':'' ?>>Supplier</option>
            <option value="minimum"  <?= $jenis==='minimum'?'selected':'' ?>>Stok Minimum</option>
          </select>
        </div>
        <div class="col-md-2">
          <label class="form-label">Dari</label>
          <input type="date" name="dari" class="form-control" value="<?= e($dari) ?>">
        </div>
        <div class="col-md-2">
          <label class="form-label">Sampai</label>
          <input type="date" name="sampai" class="form-control" value="<?= e($sampai) ?>">
        </div>
        <div class="col-md-2">
          <label class="form-label">Tipe</label>
          <select name="tipe" class="form-select">
            <option value="">Semua</option>
            <option value="masuk"  <?= $tipe==='masuk'?'selected':'' ?>>Masuk</option>
            <option value="keluar" <?= $tipe==='keluar'?'selected':'' ?>>Keluar</option>
          </select>
        </div>
        <div class="col-md-3 d-flex align-items-end gap-2">
          <button class="btn btn-primary flex-grow-1"><i class="fa-solid fa-search me-1"></i> Cari</button>
          <button type="button" onclick="cetakLaporan()" class="btn btn-success">
            <i class="fa-solid fa-print me-1"></i> Cetak
          </button>
        </div>
      </form>
    </div>
  </div>

  <!-- AREA CETAK -->
  <div class="card border-0 shadow-sm" id="areaCetak">
    <div class="card-body">

      <!-- KOP CETAK -->
      <div class="print-only text-center mb-4" style="display:none;">
        <h4 class="fw-bold mb-0"><?= e(APP_NAME) ?></h4>
        <h5 class="mb-0"><?= e(MODULE_NAME) ?></h5>
        <hr style="border:2px solid #000;">
        <h5 class="fw-bold mt-3"><?= e($judul) ?></h5>
        <p class="mb-0">Periode: <?= e(date('d M Y', strtotime($dari))) ?> — <?= e(date('d M Y', strtotime($sampai))) ?></p>
        <p class="mb-3">Dicetak: <?= e(date('d M Y H:i')) ?></p>
      </div>

      <div class="d-flex justify-content-between align-items-center mb-2 no-print">
        <h5 class="fw-bold mb-0"><?= e($judul) ?></h5>
        <span class="badge bg-primary"><?= count($data) ?> baris</span>
      </div>

      <div class="table-responsive">
        <table id="tblLaporan" class="table table-sm table-striped table-bordered">
          <thead>
            <?php if ($jenis === 'mutasi'): ?>
              <tr><th>No. Ref</th><th>Tanggal</th><th>Kode</th><th>Barang</th>
                  <th>Supplier</th><th>Tipe</th><th class="text-end">Jumlah</th><th>Keterangan</th></tr>
            <?php elseif ($jenis === 'stok' || $jenis === 'minimum'): ?>
              <tr><th>Kode</th><th>Nama Barang</th><th>Kategori</th><th>Satuan</th>
                  <th class="text-end">Stok</th><th class="text-end">Min</th></tr>
            <?php elseif ($jenis === 'supplier'): ?>
              <tr><th>Kode</th><th>Nama</th><th>Kontak</th><th>Telepon</th><th>Email</th><th>Alamat</th></tr>
            <?php endif; ?>
          </thead>
          <tbody>
            <?php if (!$data): ?>
              <tr><td colspan="10" class="text-center text-muted">Tidak ada data.</td></tr>
            <?php elseif ($jenis === 'mutasi'): ?>
              <?php foreach ($data as $r): ?>
                <tr>
                  <td><?= e($r['no_referensi']) ?></td>
                  <td><?= e(date('d/m/Y', strtotime($r['tanggal']))) ?></td>
                  <td><?= e($r['kode']) ?></td>
                  <td><?= e($r['nama_barang']) ?></td>
                  <td><?= e($r['nama_supplier'] ?? '-') ?></td>
                  <td><span class="badge bg-<?= $r['tipe']==='masuk'?'success':'danger' ?>"><?= e($r['tipe']) ?></span></td>
                  <td class="text-end"><?= (int)$r['jumlah'] ?></td>
                  <td><?= e($r['keterangan']) ?></td>
                </tr>
              <?php endforeach; ?>
            <?php elseif ($jenis === 'stok' || $jenis === 'minimum'): ?>
              <?php foreach ($data as $r): ?>
                <tr>
                  <td><?= e($r['kode']) ?></td>
                  <td><?= e($r['nama']) ?></td>
                  <td><?= e($r['kategori'] ?? '-') ?></td>
                  <td><?= e($r['satuan']) ?></td>
                  <td class="text-end fw-bold <?= $r['stok']<=$r['stok_minimum']?'text-danger':'' ?>"><?= (int)$r['stok'] ?></td>
                  <td class="text-end"><?= (int)$r['stok_minimum'] ?></td>
                </tr>
              <?php endforeach; ?>
            <?php elseif ($jenis === 'supplier'): ?>
              <?php foreach ($data as $r): ?>
                <tr>
                  <td><?= e($r['kode']) ?></td>
                  <td><?= e($r['nama']) ?></td>
                  <td><?= e($r['kontak']) ?></td>
                  <td><?= e($r['telepon']) ?></td>
                  <td><?= e($r['email']) ?></td>
                  <td><?= e($r['alamat']) ?></td>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>

      <!-- TANDA TANGAN -->
      <div class="print-only mt-5" style="display:none;">
        <div class="row text-center">
          <div class="col-6">
            <p class="mb-5">Disiapkan,</p>
            <p class="mb-0">(_________________)</p>
            <p>Admin Gudang</p>
          </div>
          <div class="col-6">
            <p class="mb-5">Disetujui,</p>
            <p class="mb-0">(_________________)</p>
            <p>Kepala Gudang</p>
          </div>
        </div>
      </div>

    </div>
  </div>
</main>

<style>
  @media print {
    .print-only { display: block !important; }
    .no-print { display: none !important; }
    .sidebar, footer, .btn { display: none !important; }
    main { width: 100% !important; padding: 0 !important; }
    body { background: #fff !important; }
  }
</style>

<script>
$(function () {
  $('#tblLaporan').DataTable({
    paging: false, info: false,
    dom: 'Bfrtip',
    buttons: [
      { extend:'excelHtml5', className:'btn btn-sm btn-success no-print', text:'<i class="fa-solid fa-file-excel"></i> Excel' },
      { extend:'pdfHtml5',   className:'btn btn-sm btn-danger  no-print', text:'<i class="fa-solid fa-file-pdf"></i> PDF' },
      { extend:'print',      className:'btn btn-sm btn-secondary no-print', text:'<i class="fa-solid fa-print"></i> Print' }
    ]
  });
});
function cetakLaporan() {
  window.print();
}
</script>

<?php include __DIR__ . '/../includes/module_footer.php'; ?>