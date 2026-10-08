<?php
require_once __DIR__ . '/../config.php';
auth_check(MODULE_SLUG);

$jenis  = $_GET['jenis']  ?? 'surat_masuk';
$dari   = $_GET['dari']   ?? date('Y-m-01');
$sampai = $_GET['sampai'] ?? date('Y-m-d');
$data   = []; $judul = '';

switch ($jenis) {
    case 'surat_masuk':
        $judul = 'Laporan Surat Masuk';
        $stmt = db()->prepare("SELECT * FROM kantor_surat_masuk
            WHERE tanggal_terima BETWEEN ? AND ? ORDER BY tanggal_terima DESC");
        $stmt->execute([$dari,$sampai]); $data = $stmt->fetchAll();
        break;
    case 'surat_keluar':
        $judul = 'Laporan Surat Keluar';
        $stmt = db()->prepare("SELECT * FROM kantor_surat_keluar
            WHERE tanggal_surat BETWEEN ? AND ? ORDER BY tanggal_surat DESC");
        $stmt->execute([$dari,$sampai]); $data = $stmt->fetchAll();
        break;
    case 'disposisi':
        $judul = 'Laporan Disposisi';
        $stmt = db()->prepare("SELECT d.*, s.no_agenda, s.perihal
            FROM kantor_disposisi d JOIN kantor_surat_masuk s ON s.id=d.surat_masuk_id
            WHERE DATE(d.created_at) BETWEEN ? AND ? ORDER BY d.id DESC");
        $stmt->execute([$dari,$sampai]); $data = $stmt->fetchAll();
        break;
    case 'aset':
        $judul = 'Laporan Aset';
        $data = db()->query("SELECT * FROM kantor_aset WHERE is_active=1 ORDER BY kode")->fetchAll();
        break;
    case 'absensi':
        $judul = 'Laporan Absensi';
        $stmt = db()->prepare("SELECT a.*, p.nama, p.nip FROM kantor_absensi a
            JOIN kantor_pegawai p ON p.id=a.pegawai_id
            WHERE a.tanggal BETWEEN ? AND ? ORDER BY a.tanggal DESC");
        $stmt->execute([$dari,$sampai]); $data = $stmt->fetchAll();
        break;
}

$page_title='Laporan'; $active_menu='laporan';
include __DIR__ . '/../includes/module_header.php';
include __DIR__ . '/../includes/module_sidebar.php';
?>
<main class="col-md-10 p-4">
  <div class="card border-0 shadow-sm mb-3 no-print">
    <div class="card-body">
      <h5 class="fw-bold mb-3"><i class="fa-solid fa-filter me-1"></i> Filter Laporan</h5>
      <form method="get" class="row g-3">
        <div class="col-md-4"><label class="form-label">Jenis Laporan</label>
          <select name="jenis" class="form-select">
            <option value="surat_masuk"  <?= $jenis==='surat_masuk'?'selected':'' ?>>Surat Masuk</option>
            <option value="surat_keluar" <?= $jenis==='surat_keluar'?'selected':'' ?>>Surat Keluar</option>
            <option value="disposisi"    <?= $jenis==='disposisi'?'selected':'' ?>>Disposisi</option>
            <option value="aset"         <?= $jenis==='aset'?'selected':'' ?>>Aset</option>
            <option value="absensi"      <?= $jenis==='absensi'?'selected':'' ?>>Absensi</option>
          </select></div>
        <div class="col-md-2"><label class="form-label">Dari</label>
          <input type="date" name="dari" class="form-control" value="<?= e($dari) ?>"></div>
        <div class="col-md-2"><label class="form-label">Sampai</label>
          <input type="date" name="sampai" class="form-control" value="<?= e($sampai) ?>"></div>
        <div class="col-md-4 d-flex align-items-end gap-2">
          <button class="btn btn-primary flex-grow-1"><i class="fa-solid fa-search me-1"></i> Cari</button>
          <button type="button" onclick="window.print()" class="btn btn-success">
            <i class="fa-solid fa-print me-1"></i> Cetak
          </button>
        </div>
      </form>
    </div>
  </div>

  <div class="card border-0 shadow-sm">
    <div class="card-body">
      <div class="text-center mb-4 d-none d-print-block">
        <h4 class="fw-bold mb-0"><?= e(APP_NAME) ?></h4>
        <h5 class="mb-0"><?= e(MODULE_NAME) ?></h5>
        <hr style="border:2px solid #000;">
        <h5 class="fw-bold mt-3"><?= e($judul) ?></h5>
        <p class="mb-0">Periode: <?= e(date('d M Y', strtotime($dari))) ?> — <?= e(date('d M Y', strtotime($sampai))) ?></p>
        <p class="mb-3">Dicetak: <?= e(date('d M Y H:i')) ?></p>
      </div>

      <h5 class="fw-bold mb-2 d-print-none"><?= e($judul) ?> <span class="badge bg-primary"><?= count($data) ?></span></h5>

      <div class="table-responsive">
        <table id="tblLap" class="table table-sm table-striped table-bordered">
          <thead>
            <?php if ($jenis==='surat_masuk'): ?>
              <tr><th>Agenda</th><th>Tgl</th><th>Asal</th><th>Perihal</th><th>Sifat</th><th>Status</th></tr>
            <?php elseif ($jenis==='surat_keluar'): ?>
              <tr><th>Agenda</th><th>No</th><th>Tgl</th><th>Tujuan</th><th>Perihal</th><th>Status</th></tr>
            <?php elseif ($jenis==='disposisi'): ?>
              <tr><th>Agenda</th><th>Perihal</th><th>Kepada</th><th>Isi</th><th>Batas</th><th>Status</th></tr>
            <?php elseif ($jenis==='aset'): ?>
              <tr><th>Kode</th><th>Nama</th><th>Kategori</th><th>Lokasi</th><th>Kondisi</th><th class="text-end">Harga</th></tr>
            <?php elseif ($jenis==='absensi'): ?>
              <tr><th>Tanggal</th><th>NIP</th><th>Nama</th><th>Masuk</th><th>Keluar</th><th>Status</th></tr>
            <?php endif; ?>
          </thead>
          <tbody>
            <?php if (!$data): ?>
              <tr><td colspan="8" class="text-center text-muted">Tidak ada data.</td></tr>
            <?php elseif ($jenis==='surat_masuk'): foreach($data as $r): ?>
              <tr><td><?= e($r['no_agenda']) ?></td><td><?= e($r['tanggal_terima']) ?></td>
                <td><?= e($r['asal_surat']) ?></td><td><?= e($r['perihal']) ?></td>
                <td><?= e($r['sifat']) ?></td><td><?= e($r['status']) ?></td></tr>
            <?php endforeach; elseif ($jenis==='surat_keluar'): foreach($data as $r): ?>
              <tr><td><?= e($r['no_agenda']) ?></td><td><?= e($r['no_surat']) ?></td>
                <td><?= e($r['tanggal_surat']) ?></td><td><?= e($r['tujuan']) ?></td>
                <td><?= e($r['perihal']) ?></td><td><?= e($r['status']) ?></td></tr>
            <?php endforeach; elseif ($jenis==='disposisi'): foreach($data as $r): ?>
              <tr><td><?= e($r['no_agenda']) ?></td><td><?= e($r['perihal']) ?></td>
                <td><?= e($r['kepada']) ?></td><td><?= e($r['isi_disposisi']) ?></td>
                <td><?= e($r['batas_waktu']) ?></td><td><?= e($r['status']) ?></td></tr>
            <?php endforeach; elseif ($jenis==='aset'): foreach($data as $r): ?>
              <tr><td><?= e($r['kode']) ?></td><td><?= e($r['nama']) ?></td>
                <td><?= e($r['kategori']) ?></td><td><?= e($r['lokasi']) ?></td>
                <td><?= e($r['kondisi']) ?></td>
                <td class="text-end">Rp <?= number_format((float)$r['harga_beli'],0,',','.') ?></td></tr>
            <?php endforeach; elseif ($jenis==='absensi'): foreach($data as $r): ?>
              <tr><td><?= e($r['tanggal']) ?></td><td><?= e($r['nip']) ?></td>
                <td><?= e($r['nama']) ?></td><td><?= e($r['jam_masuk']) ?></td>
                <td><?= e($r['jam_keluar']) ?></td><td><?= e($r['status']) ?></td></tr>
            <?php endforeach; endif; ?>
          </tbody>
        </table>
      </div>

      <div class="row text-center mt-5 d-none d-print-flex">
        <div class="col-6">
          <p class="mb-5">Disiapkan,</p>
          <p class="mb-0">(_________________)</p>
          <p>Admin Kantor</p>
        </div>
        <div class="col-6">
          <p class="mb-5">Disetujui,</p>
          <p class="mb-0">(_________________)</p>
          <p>Kepala Kantor</p>
        </div>
      </div>
    </div>
  </div>
</main>

<script>
$(function(){
  $('#tblLap').DataTable({
    paging:false, info:false,
    dom:'Bfrtip',
    buttons:[
      {extend:'excelHtml5',className:'btn btn-sm btn-success no-print',text:'<i class="fa-solid fa-file-excel"></i> Excel'},
      {extend:'pdfHtml5',className:'btn btn-sm btn-danger no-print',text:'<i class="fa-solid fa-file-pdf"></i> PDF'},
      {extend:'print',className:'btn btn-sm btn-secondary no-print',text:'<i class="fa-solid fa-print"></i> Print'}
    ]
  });
});
</script>
<?php include __DIR__ . '/../includes/module_footer.php'; ?>